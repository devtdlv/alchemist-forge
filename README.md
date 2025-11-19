<h1 align="center">AlchemistForge</h1>
<p align="center">A Developer Utility API Toolbox — your digital laboratory for everyday transformations.</p>

---

## Overview

AlchemistForge is a lightweight, versioned API that ships a shelf of small but powerful developer utilities:

- Slugification
- Timezone conversion
- Currency conversion (via exchangerate.host)
- Random test data generation (users, addresses, text, etc.)
- Image resizing (GD, no extra deps)
- QR code generation (optional)

The app is built on Laravel 12 (PHP 8.2) and exposes endpoints under `/api/v1`.

## Requirements

- PHP 8.2+
- Composer
- Node 20+ (only if you want to use Vite assets; not required for the API)

## Quick Start

1) Install dependencies and set up the app

```bash
composer install
cp .env.example .env
php artisan key:generate
```

2) Run the server

```bash
php artisan serve
```

The API will be available at `http://127.0.0.1:8000`.

3) Explore the API catalog

```bash
open http://127.0.0.1:8000/api/v1
```

## Endpoints

All endpoints are versioned under `/api/v1`.

### 1) Slugify
GET `/api/v1/slugify?text={text}&separator=-`

```bash
curl "http://127.0.0.1:8000/api/v1/slugify?text=Hello World&separator=-"
```

Response:
```json
{ "input": "Hello World", "slug": "hello-world", "separator": "-" }
```

### 2) Timezone Conversion
GET `/api/v1/timezone/convert?datetime=2025-01-01T12:00:00&from=UTC&to=America/New_York`

```bash
curl "http://127.0.0.1:8000/api/v1/timezone/convert?datetime=2025-01-01T12:00:00&from=UTC&to=America/New_York"
```

Response:
```json
{
  "input": { "datetime": "2025-01-01T12:00:00", "from": "UTC", "to": "America/New_York" },
  "result": { "iso": "2025-01-01T07:00:00-05:00", "unix": 1735726800, "readable": "2025-01-01 07:00:00" }
}
```

### 3) Currency Conversion
GET `/api/v1/currency/convert?amount=10&from=USD&to=EUR`

```bash
curl "http://127.0.0.1:8000/api/v1/currency/convert?amount=10&from=USD&to=EUR"
```

Notes:
- Uses `https://api.exchangerate.host/convert` (no API key required for basic usage).
- Response includes the applied `rate` and `result`.

### 4) Random Test Data
GET `/api/v1/random?type={user|address|company|text}&count=1`

```bash
curl "http://127.0.0.1:8000/api/v1/random?type=user&count=2"
```

### 5) Image Resize
POST `/api/v1/image/resize`

Form fields:
- `file` (required): image file
- `width` (required): target width (int)
- `height` (optional): target height (int). If omitted, preserves aspect ratio.
- `format` (optional): `jpeg|png|webp` (default `jpeg`)
- `quality` (optional): 1–100 (default `85`)

Example:
```bash
curl -s -X POST \
  -F "file=@/path/to/image.jpg" \
  -F "width=512" \
  -F "format=webp" \
  "http://127.0.0.1:8000/api/v1/image/resize" > out.webp
```

### 6) QR Code (Optional)
GET `/api/v1/qr?text=Hello&size=256`

By default, the route returns a `501 Not Implemented` and suggests an install:
```bash
composer require simplesoftwareio/simple-qrcode
```
Then wire it in `UtilityController::generateQr`.

## Project Scripts

Composer:
- `composer setup` — end-to-end install (deps, key, sqlite, migrate, npm build)
- `composer dev` — local dev helpers (serve, queue, logs, vite)
- `composer test` — run the test suite

Node:
- `npm run dev` / `npm run build` — only needed if you touch frontend assets

## Testing

Feature tests are available:
```bash
php artisan test
```

They cover core endpoints (slugify, timezone conversion, image resize, random data, QR stub).

## Tech Stack

- Laravel 12 (PHP ^8.2)
- Vite 7 (optional for assets), Tailwind 4 preset (inline)
- Faker for random data
- PHP GD for image processing

## License

MIT © AlchemistForge
