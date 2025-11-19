<?php

namespace Tests\Feature;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Tests\TestCase;

class UtilityApiTest extends TestCase
{
	public function test_slugify_endpoint(): void
	{
		$response = $this->getJson('/api/v1/slugify?text=Hello World!');
		$response->assertOk();
		$response->assertJsonPath('slug', 'hello-world');
	}

	public function test_timezone_convert_endpoint(): void
	{
		$response = $this->getJson('/api/v1/timezone/convert?datetime=2025-01-01T12:00:00&from=UTC&to=America/New_York');
		$response->assertOk();
		$response->assertJsonStructure(['result' => ['iso', 'unix', 'readable']]);
	}

	public function test_random_data_endpoint(): void
	{
		$response = $this->getJson('/api/v1/random?type=user&count=2');
		$response->assertOk();
		$response->assertJsonPath('count', 2);
		$response->assertJsonStructure(['data' => [['name', 'username', 'email', 'phone']]]);
	}

	public function test_image_resize_endpoint(): void
	{
		// Create a tiny 10x10 png
		$image = imagecreatetruecolor(10, 10);
		ob_start();
		imagepng($image);
		$pngData = ob_get_clean();
		$this->assertNotFalse($pngData);

		$file = UploadedFile::fake()->createWithContent('tiny.png', $pngData);
		$response = $this->post('/api/v1/image/resize', [
			'file' => $file,
			'width' => 5,
			'format' => 'png',
		]);
		$response->assertOk();
		$this->assertTrue(Str::startsWith($response->headers->get('Content-Type'), 'image/'));
	}

	public function test_qr_not_implemented(): void
	{
		$response = $this->getJson('/api/v1/qr?text=Hello');
		$response->assertStatus(501);
	}

	public function test_image_process_endpoint(): void
	{
		// Create a tiny 20x20 png
		$image = imagecreatetruecolor(20, 20);
		$color = imagecolorallocate($image, 100, 150, 200);
		imagefill($image, 0, 0, $color);
		ob_start();
		imagepng($image);
		$pngData = ob_get_clean();
		imagedestroy($image);
		$this->assertNotFalse($pngData);

		$file = UploadedFile::fake()->createWithContent('test.png', $pngData);
		
		// Test resize + grayscale
		$response = $this->post('/api/v1/image/process', [
			'file' => $file,
			'width' => 10,
			'filter' => 'grayscale',
			'format' => 'png',
		]);
		$response->assertOk();
		$this->assertTrue(Str::startsWith($response->headers->get('Content-Type'), 'image/'));
	}
}


