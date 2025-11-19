<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UtilityController;

Route::prefix('v1')->group(function () {
	Route::get('/', function () {
		return response()->json([
			'name' => 'AlchemistForge',
			'version' => 'v1',
			'endpoints' => [
				[
					'method' => 'GET',
					'path' => '/api/v1/slugify',
					'query' => ['text' => 'Hello World', 'separator' => '-'],
					'description' => 'Convert arbitrary text into a URL-safe slug.',
					'example' => url('/api/v1/slugify?text=Hello%20World'),
				],
				[
					'method' => 'GET',
					'path' => '/api/v1/timezone/convert',
					'query' => ['datetime' => '2025-01-01T12:00:00', 'from' => 'UTC', 'to' => 'America/New_York'],
					'description' => 'Convert a datetime between timezones.',
					'example' => url('/api/v1/timezone/convert?datetime=2025-01-01T12:00:00&from=UTC&to=America/New_York'),
				],
				[
					'method' => 'GET',
					'path' => '/api/v1/currency/convert',
					'query' => ['amount' => 10, 'from' => 'USD', 'to' => 'EUR'],
					'description' => 'Convert currency using exchangerate.host.',
					'example' => url('/api/v1/currency/convert?amount=10&from=USD&to=EUR'),
				],
				[
					'method' => 'GET',
					'path' => '/api/v1/random',
					'query' => ['type' => 'user', 'count' => 2],
					'description' => 'Generate random test data (user, address, company, text).',
					'example' => url('/api/v1/random?type=user&count=2'),
				],
				[
					'method' => 'POST',
					'path' => '/api/v1/image/resize',
					'form' => ['file' => 'image/*', 'width' => 512, 'height' => '(optional)', 'format' => 'jpeg|png|webp'],
					'description' => 'Resize an image using GD.',
					'example_curl' => 'curl -s -F "file=@/path/to/image.jpg" -F "width=512" ' . url('/api/v1/image/resize') . ' > out.jpg',
				],
				[
					'method' => 'GET',
					'path' => '/api/v1/qr',
					'query' => ['text' => 'Hello'],
					'description' => 'QR code generation (not implemented by default).',
					'example' => url('/api/v1/qr?text=Hello'),
				],
			],
		]);
	});
	Route::get('/slugify', [UtilityController::class, 'slugify']);
	Route::get('/timezone/convert', [UtilityController::class, 'convertTimezone']);
	Route::get('/currency/convert', [UtilityController::class, 'convertCurrency']);
	Route::get('/random', [UtilityController::class, 'randomData']);
	Route::post('/image/resize', [UtilityController::class, 'resizeImage']);
	Route::get('/qr', [UtilityController::class, 'generateQr']); // may require optional dependency
});


