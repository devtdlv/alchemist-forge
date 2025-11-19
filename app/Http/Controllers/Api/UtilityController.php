<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Carbon\CarbonImmutable;
use Faker\Factory as FakerFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class UtilityController extends Controller
{
	/**
	 * GET /api/v1/slugify?text=Hello World&separator=-
	 */
	public function slugify(Request $request)
	{
		$text = (string) $request->query('text', '');
		$separator = (string) $request->query('separator', '-');

		if ($text === '') {
			return response()->json(['error' => 'Missing required query param: text'], Response::HTTP_BAD_REQUEST);
		}

		return response()->json([
			'input' => $text,
			'slug' => Str::slug($text, $separator),
			'separator' => $separator,
		]);
	}

	/**
	 * GET /api/v1/timezone/convert?datetime=2025-01-01T12:00:00&from=UTC&to=America/New_York
	 */
	public function convertTimezone(Request $request)
	{
		$datetime = (string) $request->query('datetime', '');
		$fromTz = (string) $request->query('from', 'UTC');
		$toTz = (string) $request->query('to', 'UTC');

		if ($datetime === '') {
			return response()->json(['error' => 'Missing required query param: datetime'], Response::HTTP_BAD_REQUEST);
		}

		try {
			$dt = CarbonImmutable::parse($datetime, $fromTz);
			$converted = $dt->setTimezone($toTz);
		} catch (\Throwable $e) {
			return response()->json(['error' => 'Invalid datetime or timezone', 'details' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
		}

		return response()->json([
			'input' => [
				'datetime' => $datetime,
				'from' => $fromTz,
				'to' => $toTz,
			],
			'result' => [
				'iso' => $converted->toIso8601String(),
				'unix' => $converted->getTimestamp(),
				'readable' => $converted->format('Y-m-d H:i:s'),
			],
		]);
	}

	/**
	 * GET /api/v1/currency/convert?amount=10&from=USD&to=EUR
	 * Uses https://api.exchangerate.host/convert (no API key, free)
	 */
	public function convertCurrency(Request $request)
	{
		$amount = (float) $request->query('amount', 0);
		$from = strtoupper((string) $request->query('from', 'USD'));
		$to = strtoupper((string) $request->query('to', 'USD'));

		if ($amount <= 0) {
			return response()->json(['error' => 'amount must be > 0'], Response::HTTP_BAD_REQUEST);
		}
		if ($from === '' || $to === '') {
		 return response()->json(['error' => 'from and to are required'], Response::HTTP_BAD_REQUEST);
		}

		try {
			$response = Http::acceptJson()->get('https://api.exchangerate.host/convert', [
				'amount' => $amount,
				'from' => $from,
				'to' => $to,
			]);
			if (!$response->ok()) {
				return response()->json(['error' => 'Currency provider error', 'status' => $response->status()], Response::HTTP_BAD_GATEWAY);
			}
			$data = $response->json();
			return response()->json([
				'input' => compact('amount', 'from', 'to'),
				'rate' => $data['info']['rate'] ?? null,
				'result' => $data['result'] ?? null,
				'provider' => 'exchangerate.host',
			]);
		} catch (\Throwable $e) {
			return response()->json(['error' => 'Conversion failed', 'details' => $e->getMessage()], Response::HTTP_BAD_GATEWAY);
		}
	}

	/**
	 * POST /api/v1/image/process
	 * Comprehensive image processing endpoint supporting multiple operations
	 * multipart/form-data:
	 * - file: image file (required)
	 * - operations: JSON string with operations array, or individual params:
	 *   - resize: {width, height?}
	 *   - crop: {x, y, width, height}
	 *   - rotate: degrees (0-360)
	 *   - filter: grayscale|brightness|contrast|saturation
	 *   - filter_value: value for filter (-100 to 100 for brightness/contrast, 0-200 for saturation)
	 *   - watermark: text to overlay
	 *   - format: jpeg|png|webp (default: jpeg)
	 *   - quality: 1-100 (default: 85)
	 */
	public function processImage(Request $request)
	{
		$file = $request->file('file');
		if (!$file || !$file->isValid()) {
			return response()->json(['error' => 'Invalid or missing file'], Response::HTTP_BAD_REQUEST);
		}

		$data = @file_get_contents($file->getRealPath());
		if ($data === false) {
			return response()->json(['error' => 'Failed to read image data'], Response::HTTP_BAD_REQUEST);
		}

		$src = @imagecreatefromstring($data);
		if ($src === false) {
			return response()->json(['error' => 'Unsupported image format'], Response::HTTP_UNSUPPORTED_MEDIA_TYPE);
		}

		$img = $src;
		$operations = [];

		// Parse operations (can be JSON string or individual params)
		$opsJson = $request->input('operations');
		if ($opsJson) {
			$ops = json_decode($opsJson, true);
			if (json_last_error() === JSON_ERROR_NONE && is_array($ops)) {
				$operations = $ops;
			}
		}

		// Apply resize
		if ($request->has('width') || isset($operations['resize'])) {
			$resize = $operations['resize'] ?? ['width' => $request->input('width'), 'height' => $request->input('height')];
			$width = (int) ($resize['width'] ?? 0);
			$height = isset($resize['height']) ? (int) $resize['height'] : null;
			if ($width > 0) {
				$srcW = imagesx($img);
				$srcH = imagesy($img);
				if ($height === null || $height <= 0) {
					$ratio = $srcH / $srcW;
					$height = (int) round($width * $ratio);
				}
				$newImg = imagescale($img, $width, $height, IMG_BICUBIC);
				if ($newImg !== false) {
					imagedestroy($img);
					$img = $newImg;
				}
			}
		}

		// Apply crop
		if ($request->has('crop_x') || isset($operations['crop'])) {
			$crop = $operations['crop'] ?? [
				'x' => $request->input('crop_x'),
				'y' => $request->input('crop_y'),
				'width' => $request->input('crop_width'),
				'height' => $request->input('crop_height'),
			];
			$x = (int) ($crop['x'] ?? 0);
			$y = (int) ($crop['y'] ?? 0);
			$cropW = (int) ($crop['width'] ?? 0);
			$cropH = (int) ($crop['height'] ?? 0);
			if ($cropW > 0 && $cropH > 0) {
				$newImg = imagecrop($img, ['x' => $x, 'y' => $y, 'width' => $cropW, 'height' => $cropH]);
				if ($newImg !== false) {
					imagedestroy($img);
					$img = $newImg;
				}
			}
		}

		// Apply rotate
		if ($request->has('rotate') || isset($operations['rotate'])) {
			$degrees = (float) ($operations['rotate'] ?? $request->input('rotate', 0));
			if ($degrees != 0) {
				$bgColor = imagecolorallocatealpha($img, 255, 255, 255, 127);
				$newImg = imagerotate($img, -$degrees, $bgColor);
				if ($newImg !== false) {
					imagealphablending($newImg, false);
					imagesavealpha($newImg, true);
					imagedestroy($img);
					$img = $newImg;
				}
			}
		}

		// Apply filters
		if ($request->has('filter') || isset($operations['filter'])) {
			$filter = $operations['filter'] ?? $request->input('filter');
			$filterValue = (int) ($operations['filter_value'] ?? $request->input('filter_value', 0));
			
			switch ($filter) {
				case 'grayscale':
					imagefilter($img, IMG_FILTER_GRAYSCALE);
					break;
				case 'brightness':
					$filterValue = max(-100, min(100, $filterValue));
					imagefilter($img, IMG_FILTER_BRIGHTNESS, $filterValue);
					break;
				case 'contrast':
					$filterValue = max(-100, min(100, $filterValue));
					imagefilter($img, IMG_FILTER_CONTRAST, $filterValue);
					break;
				case 'saturation':
					// Saturation is approximated via colorize
					$filterValue = max(0, min(200, $filterValue));
					$sat = (int) (($filterValue - 100) * 2.55);
					imagefilter($img, IMG_FILTER_COLORIZE, 0, 0, 0, $sat);
					break;
			}
		}

		// Apply watermark
		if ($request->has('watermark') || isset($operations['watermark'])) {
			$watermarkText = $operations['watermark'] ?? $request->input('watermark');
			if ($watermarkText) {
				$fontSize = 5;
				$textColor = imagecolorallocate($img, 255, 255, 255);
				$bgColor = imagecolorallocate($img, 0, 0, 0);
				$textWidth = imagefontwidth($fontSize) * strlen($watermarkText);
				$textHeight = imagefontheight($fontSize);
				$imgWidth = imagesx($img);
				$imgHeight = imagesy($img);
				$x = $imgWidth - $textWidth - 10;
				$y = $imgHeight - $textHeight - 10;
				imagestring($img, $fontSize, $x + 1, $y + 1, $watermarkText, $bgColor);
				imagestring($img, $fontSize, $x, $y, $watermarkText, $textColor);
			}
		}

		// Output format
		$format = strtolower((string) ($operations['format'] ?? $request->input('format', 'jpeg')));
		$quality = (int) ($operations['quality'] ?? $request->input('quality', 85));
		$quality = max(1, min(100, $quality));

		ob_start();
		$contentType = 'image/jpeg';
		switch ($format) {
			case 'png':
				$contentType = 'image/png';
				imagealphablending($img, false);
				imagesavealpha($img, true);
				imagepng($img);
				break;
			case 'webp':
				$contentType = 'image/webp';
				if (function_exists('imagewebp')) {
					imagewebp($img, null, $quality);
				} else {
					$contentType = 'image/jpeg';
					imagejpeg($img, null, $quality);
				}
				break;
			default:
				imagejpeg($img, null, $quality);
		}
		imagedestroy($img);
		$binary = ob_get_clean();

		return response($binary, 200, [
			'Content-Type' => $contentType,
			'Content-Disposition' => 'inline; filename="processed.' . $format . '"',
		]);
	}

	/**
	 * POST /api/v1/image/resize
	 * multipart/form-data:
	 * - file: image file
	 * - width: int
	 * - height: int (optional; preserves aspect if omitted)
	 *
	 * Returns a binary image (JPEG) by default.
	 */
	public function resizeImage(Request $request)
	{
		$file = $request->file('file');
		$width = (int) $request->input('width', 0);
		$height = $request->has('height') ? (int) $request->input('height', 0) : null;
		$format = strtolower((string) $request->input('format', 'jpeg')); // jpeg|png|webp
		$quality = (int) $request->input('quality', 85);

		if (!$file || !$file->isValid()) {
			return response()->json(['error' => 'Invalid or missing file'], Response::HTTP_BAD_REQUEST);
		}
		if ($width <= 0) {
			return response()->json(['error' => 'width must be > 0'], Response::HTTP_BAD_REQUEST);
		}

		$data = @file_get_contents($file->getRealPath());
		if ($data === false) {
			return response()->json(['error' => 'Failed to read image data'], Response::HTTP_BAD_REQUEST);
		}

		$src = @imagecreatefromstring($data);
		if ($src === false) {
			return response()->json(['error' => 'Unsupported image'], Response::HTTP_UNSUPPORTED_MEDIA_TYPE);
		}

		$srcW = imagesx($src);
		$srcH = imagesy($src);

		if ($height === null || $height <= 0) {
			$ratio = $srcH / $srcW;
			$height = (int) round($width * $ratio);
		}

		$dst = imagescale($src, $width, $height, IMG_BICUBIC);
		imagedestroy($src);

		if ($dst === false) {
			return response()->json(['error' => 'Resize failed'], Response::HTTP_INTERNAL_SERVER_ERROR);
		}

		ob_start();
		$contentType = 'image/jpeg';
		switch ($format) {
			case 'png':
				$contentType = 'image/png';
				imagepng($dst);
				break;
			case 'webp':
				$contentType = 'image/webp';
				if (function_exists('imagewebp')) {
					imagewebp($dst, null, $quality);
				} else {
					// fallback to jpeg if webp unsupported
					$contentType = 'image/jpeg';
					imagejpeg($dst, null, $quality);
				}
				break;
			default:
				imagejpeg($dst, null, $quality);
		}
		imagedestroy($dst);
		$binary = ob_get_clean();

		return response($binary, 200, [
			'Content-Type' => $contentType,
			'Content-Disposition' => 'inline; filename="resized.' . $format . '"',
		]);
	}

	/**
	 * GET /api/v1/qr?text=Hello&size=256
	 *
	 * Note: For production-quality QR generation, consider installing:
	 * - simplesoftwareio/simple-qrcode (BaconQrCode)
	 * Here we return a 501 to document the optional dependency.
	 */
	public function generateQr(Request $request)
	{
		return response()->json([
			'error' => 'QR generation not enabled',
			'message' => 'Install simplesoftwareio/simple-qrcode or similar and wire it here.',
			'suggestion' => 'composer require simplesoftwareio/simple-qrcode',
		], Response::HTTP_NOT_IMPLEMENTED);
	}

	/**
	 * GET /api/v1/random?type=user&count=1
	 */
	public function randomData(Request $request)
	{
		$type = (string) $request->query('type', 'user'); // user|address|company|text
		$count = max(1, min(100, (int) $request->query('count', 1)));
		$faker = FakerFactory::create();

		$items = [];
		for ($i = 0; $i < $count; $i++) {
			switch ($type) {
				case 'address':
					$items[] = [
						'street' => $faker->streetAddress(),
						'city' => $faker->city(),
						'state' => $faker->state(),
						'postcode' => $faker->postcode(),
						'country' => $faker->country(),
					];
					break;
				case 'company':
					$items[] = [
						'name' => $faker->company(),
						'catchPhrase' => $faker->catchPhrase(),
						'bs' => $faker->bs(),
						'vat' => $faker->vat(),
					];
					break;
				case 'text':
					$items[] = [
						'title' => $faker->sentence(6),
						'paragraph' => $faker->paragraph(4),
					];
					break;
				case 'user':
				default:
					$items[] = [
						'name' => $faker->name(),
						'username' => $faker->userName(),
						'email' => $faker->safeEmail(),
						'phone' => $faker->phoneNumber(),
					];
			}
		}

		return response()->json([
			'type' => $type,
			'count' => $count,
			'data' => $items,
		]);
	}
}


