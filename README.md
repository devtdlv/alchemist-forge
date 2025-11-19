<h1 align="center">AlchemistForge</h1>
<p align="center">A Developer Utility API Toolbox — your digital laboratory for everyday transformations.</p>

---

## Overview

AlchemistForge is a lightweight, versioned API that provides a suite of developer utilities:

**Fully Implemented:**
- ✅ **Slugification** - Convert text to URL-friendly slugs
- ✅ **Timezone Conversion** - Convert datetime between timezones
- ✅ **Currency Conversion** - Real-time currency conversion (via exchangerate.host)
- ✅ **Image Processing** - Comprehensive image operations: resize, crop, rotate, filters (grayscale, brightness, contrast, saturation), watermark, format conversion
- ✅ **Image Resizing** - Simple resize endpoint (alternative to /image/process)
- ✅ **Random Test Data** - Generate realistic test data (users, addresses, companies, text)

**Optional/Coming Soon:**
- ⚠️ **QR Code Generation** - Requires package installation (see below)

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

### 5) Image Processing
POST `/api/v1/image/process`

**Fully implemented** - Comprehensive image processing using PHP GD (no additional dependencies required).

Supports multiple operations in a single request:
- **Resize**: `width`, `height` (optional, preserves aspect if omitted)
- **Crop**: `crop_x`, `crop_y`, `crop_width`, `crop_height`
- **Rotate**: `rotate` (degrees, 0-360)
- **Filters**: `filter` (grayscale|brightness|contrast|saturation), `filter_value` (-100 to 100 for brightness/contrast, 0-200 for saturation)
- **Watermark**: `watermark` (text to overlay)
- **Format**: `format` (jpeg|png|webp, default: jpeg)
- **Quality**: `quality` (1-100, default: 85)

**Option 1: Individual parameters**
```bash
curl -s -X POST \
  -F "file=@/path/to/image.jpg" \
  -F "width=512" \
  -F "filter=grayscale" \
  -F "rotate=90" \
  -F "watermark=AlchemistForge" \
  -F "format=webp" \
  "http://127.0.0.1:8000/api/v1/image/process" > out.webp
```

**Option 2: JSON operations object**
```bash
curl -s -X POST \
  -F "file=@/path/to/image.jpg" \
  -F 'operations={"resize":{"width":512},"filter":"grayscale","rotate":90,"watermark":"AlchemistForge","format":"webp"}' \
  "http://127.0.0.1:8000/api/v1/image/process" > out.webp
```

**Examples:**
- Resize and convert to grayscale: `-F "width=800" -F "filter=grayscale"`
- Crop a region: `-F "crop_x=100" -F "crop_y=100" -F "crop_width=400" -F "crop_height=300"`
- Rotate 90 degrees: `-F "rotate=90"`
- Adjust brightness: `-F "filter=brightness" -F "filter_value=20"`
- Add watermark: `-F "watermark=© 2025"`

### 6) Image Resize (Simple)
POST `/api/v1/image/resize`

**Fully implemented** - Simple resize endpoint (alternative to `/image/process`).

Form fields:
- `file` (required): image file (JPEG, PNG, or WebP)
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

**Note:** Requires PHP GD extension (usually included with PHP). WebP support depends on your PHP build.

### 7) QR Code Generation (Not Yet Implemented)
GET `/api/v1/qr?text=Hello&size=256`

**Status:** Currently returns `501 Not Implemented`. To enable:

1. Install the QR code package:
```bash
composer require simplesoftwareio/simple-qrcode
```

2. Update `app/Http/Controllers/Api/UtilityController::generateQr()` to implement the QR generation logic.

The endpoint is stubbed and ready for implementation.

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

- **Laravel 12** (PHP ^8.2)
- **PHP GD** - Built-in image resizing (JPEG, PNG, WebP)
- **Faker** - Random test data generation
- **Carbon** - Timezone conversion
- **Laravel HTTP Client** - Currency conversion API calls
- **Vite 7** (optional for frontend assets), Tailwind 4 preset (inline)

## License

MIT © AlchemistForge
