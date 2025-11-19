<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>AlchemistForge - Developer Utility API</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @else
            <style>
                * { margin: 0; padding: 0; box-sizing: border-box; }
                body { 
                    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; 
                    line-height: 1.6; 
                    color: #0f172a; 
                    background: linear-gradient(to bottom, #f8fafc 0%, #ffffff 100%);
                    min-height: 100vh;
                }
                .container { max-width: 1100px; margin: 0 auto; padding: 3rem 1.5rem; }
                
                /* Header */
                .header { 
                    display: flex; 
                    align-items: center; 
                    justify-content: space-between; 
                    margin-bottom: 4rem; 
                }
                .brand { display: flex; align-items: center; gap: 0.75rem; }
                .brand-name { font-size: 1.25rem; font-weight: 700; color: #0f172a; letter-spacing: -0.025em; }
                .version { 
                    display: inline-block; 
                    padding: 0.25rem 0.625rem; 
                    background: #e0e7ff; 
                    color: #4338ca; 
                    border-radius: 6px; 
                    font-size: 0.75rem; 
                    font-weight: 600; 
                }
                
                /* Hero */
                .hero { margin-bottom: 3.5rem; }
                .hero-title { 
                    font-size: 3rem; 
                    font-weight: 700; 
                    color: #0f172a; 
                    margin-bottom: 1rem; 
                    line-height: 1.1;
                    letter-spacing: -0.02em;
                }
                .hero-subtitle { 
                    font-size: 1.25rem; 
                    color: #475569; 
                    max-width: 650px; 
                    line-height: 1.6;
                }
                
                /* Endpoints Grid */
                .endpoints { 
                    display: grid; 
                    gap: 1.25rem; 
                    margin-bottom: 3rem; 
                }
                
                .endpoint-card { 
                    background: #ffffff; 
                    border: 1px solid #e2e8f0; 
                    border-radius: 12px; 
                    padding: 1.75rem; 
                    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
                    position: relative;
                    overflow: hidden;
                }
                .endpoint-card::before {
                    content: '';
                    position: absolute;
                    top: 0;
                    left: 0;
                    right: 0;
                    height: 3px;
                    background: linear-gradient(90deg, #6366f1 0%, #8b5cf6 100%);
                    opacity: 0;
                    transition: opacity 0.2s;
                }
                .endpoint-card:hover {
                    border-color: #c7d2fe;
                    box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.05), 0 4px 6px -4px rgb(0 0 0 / 0.05);
                    transform: translateY(-2px);
                }
                .endpoint-card:hover::before { opacity: 1; }
                
                .endpoint-card.disabled { opacity: 0.6; cursor: not-allowed; }
                .endpoint-card.disabled:hover { transform: none; border-color: #e2e8f0; box-shadow: none; }
                
                .endpoint-header { 
                    display: flex; 
                    align-items: flex-start; 
                    justify-content: space-between; 
                    margin-bottom: 1rem; 
                }
                .endpoint-title-group { flex: 1; }
                .endpoint-title { 
                    font-size: 1.125rem; 
                    font-weight: 600; 
                    color: #0f172a; 
                    margin-bottom: 0.5rem; 
                }
                .endpoint-method { 
                    display: inline-block; 
                    padding: 0.25rem 0.625rem; 
                    background: #dbeafe; 
                    color: #1e40af; 
                    border-radius: 4px; 
                    font-size: 0.75rem; 
                    font-weight: 600; 
                    letter-spacing: 0.025em;
                }
                .endpoint-method.post { background: #fef3c7; color: #92400e; }
                
                .endpoint-path { 
                    font-family: 'SF Mono', Monaco, 'Cascadia Code', monospace; 
                    font-size: 0.875rem; 
                    color: #64748b; 
                    background: #f8fafc; 
                    padding: 0.75rem 1rem; 
                    border-radius: 6px; 
                    margin-bottom: 1rem;
                    border: 1px solid #e2e8f0;
                    overflow-x: auto;
                }
                
                .endpoint-description { 
                    font-size: 0.9375rem; 
                    color: #64748b; 
                    margin-bottom: 1.25rem; 
                    line-height: 1.6;
                }
                
                .endpoint-link { 
                    display: inline-flex; 
                    align-items: center; 
                    gap: 0.5rem; 
                    color: #6366f1; 
                    font-weight: 500; 
                    font-size: 0.9375rem; 
                    text-decoration: none; 
                    transition: color 0.2s;
                }
                .endpoint-link:hover { color: #4f46e5; }
                .endpoint-link svg { width: 16px; height: 16px; transition: transform 0.2s; }
                .endpoint-link:hover svg { transform: translateX(2px); }
                
                .endpoint-link.disabled { color: #94a3b8; cursor: not-allowed; pointer-events: none; }
                
                /* Footer */
                .footer { 
                    text-align: center; 
                    padding: 2.5rem 0 1rem; 
                    color: #94a3b8; 
                    font-size: 0.875rem; 
                }
                
                /* Dark mode */
                @media (prefers-color-scheme: dark) {
                    body { 
                        background: linear-gradient(to bottom, #0f172a 0%, #1e293b 100%); 
                        color: #e2e8f0; 
                    }
                    .brand-name { color: #f1f5f9; }
                    .version { background: #312e81; color: #c7d2fe; }
                    .hero-title { color: #f1f5f9; }
                    .hero-subtitle { color: #cbd5e1; }
                    .endpoint-card { background: #1e293b; border-color: #334155; }
                    .endpoint-card:hover { border-color: #475569; }
                    .endpoint-title { color: #f1f5f9; }
                    .endpoint-method { background: #1e3a8a; color: #93c5fd; }
                    .endpoint-method.post { background: #78350f; color: #fcd34d; }
                    .endpoint-path { background: #0f172a; color: #cbd5e1; border-color: #334155; }
                    .endpoint-description { color: #94a3b8; }
                    .endpoint-link { color: #818cf8; }
                    .endpoint-link:hover { color: #a5b4fc; }
                    .footer { color: #64748b; }
                }
                
                @media (max-width: 768px) {
                    .hero-title { font-size: 2rem; }
                    .hero-subtitle { font-size: 1.125rem; }
                    .endpoint-card { padding: 1.5rem; }
                }
            </style>
        @endif
    </head>
    <body>
        <div class="container">
            <header class="header">
                <div class="brand">
                    <span class="brand-name">AlchemistForge</span>
                </div>
                <span class="version">v1.0</span>
            </header>

            <div class="hero">
                <h1 class="hero-title">Developer Utility API</h1>
                <p class="hero-subtitle">Transform, convert, and generate data with a suite of powerful utility endpoints designed for modern development workflows.</p>
            </div>

            <div class="endpoints">
                <div class="endpoint-card">
                    <div class="endpoint-header">
                        <div class="endpoint-title-group">
                            <h3 class="endpoint-title">API Index</h3>
                            <span class="endpoint-method">GET</span>
                        </div>
                    </div>
                    <div class="endpoint-path">/api/v1</div>
                    <p class="endpoint-description">Complete catalog of all available endpoints with parameters, response formats, and usage examples.</p>
                    <a href="{{ url('/api/v1') }}" target="_blank" class="endpoint-link">
                        View documentation
                        <svg viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd"/>
                        </svg>
                    </a>
                </div>

                <div class="endpoint-card">
                    <div class="endpoint-header">
                        <div class="endpoint-title-group">
                            <h3 class="endpoint-title">Slugify</h3>
                            <span class="endpoint-method">GET</span>
                        </div>
                    </div>
                    <div class="endpoint-path">/api/v1/slugify?text=Hello World</div>
                    <p class="endpoint-description">Convert any text string into URL-friendly slugs with customizable separators and character handling.</p>
                    <a href="{{ url('/api/v1/slugify?text=Hello%20World') }}" target="_blank" class="endpoint-link">
                        Try it now
                        <svg viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd"/>
                        </svg>
                    </a>
                </div>

                <div class="endpoint-card">
                    <div class="endpoint-header">
                        <div class="endpoint-title-group">
                            <h3 class="endpoint-title">Timezone Conversion</h3>
                            <span class="endpoint-method">GET</span>
                        </div>
                    </div>
                    <div class="endpoint-path">/api/v1/timezone/convert</div>
                    <p class="endpoint-description">Convert datetime values between any timezone with full ISO 8601 support and daylight saving awareness.</p>
                    <a href="{{ url('/api/v1/timezone/convert?datetime=2025-01-01T12:00:00&from=UTC&to=America/New_York') }}" target="_blank" class="endpoint-link">
                        Try it now
                        <svg viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd"/>
                        </svg>
                    </a>
                </div>

                <div class="endpoint-card">
                    <div class="endpoint-header">
                        <div class="endpoint-title-group">
                            <h3 class="endpoint-title">Currency Conversion</h3>
                            <span class="endpoint-method">GET</span>
                        </div>
                    </div>
                    <div class="endpoint-path">/api/v1/currency/convert</div>
                    <p class="endpoint-description">Real-time currency conversion with support for 150+ currencies and automatic rate updates.</p>
                    <a href="{{ url('/api/v1/currency/convert?amount=10&from=USD&to=EUR') }}" target="_blank" class="endpoint-link">
                        Try it now
                        <svg viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd"/>
                        </svg>
                    </a>
                </div>

                <div class="endpoint-card">
                    <div class="endpoint-header">
                        <div class="endpoint-title-group">
                            <h3 class="endpoint-title">Random Test Data</h3>
                            <span class="endpoint-method">GET</span>
                        </div>
                    </div>
                    <div class="endpoint-path">/api/v1/random</div>
                    <p class="endpoint-description">Generate realistic test data including users, emails, addresses, and more for development and testing.</p>
                    <a href="{{ url('/api/v1/random?type=user&count=2') }}" target="_blank" class="endpoint-link">
                        Try it now
                        <svg viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd"/>
                        </svg>
                    </a>
                </div>

                <div class="endpoint-card">
                    <div class="endpoint-header">
                        <div class="endpoint-title-group">
                            <h3 class="endpoint-title">Image Processing</h3>
                            <span class="endpoint-method post">POST</span>
                        </div>
                    </div>
                    <div class="endpoint-path">/api/v1/image/process</div>
                    <p class="endpoint-description">Comprehensive image operations: resize, crop, rotate, filters (grayscale, brightness, contrast, saturation), watermark, and format conversion.</p>
                    <span class="endpoint-link disabled">
                        Requires file upload
                    </span>
                </div>

                <div class="endpoint-card">
                    <div class="endpoint-header">
                        <div class="endpoint-title-group">
                            <h3 class="endpoint-title">Image Resize</h3>
                            <span class="endpoint-method post">POST</span>
                        </div>
                    </div>
                    <div class="endpoint-path">/api/v1/image/resize</div>
                    <p class="endpoint-description">Simple resize endpoint (alternative to /image/process). Resize and optimize images with support for JPEG, PNG, and WebP formats.</p>
                    <span class="endpoint-link disabled">
                        Requires file upload
                    </span>
                </div>

                <div class="endpoint-card disabled">
                    <div class="endpoint-header">
                        <div class="endpoint-title-group">
                            <h3 class="endpoint-title">QR Code Generation</h3>
                            <span class="endpoint-method">GET</span>
                        </div>
                    </div>
                    <div class="endpoint-path">/api/v1/qr</div>
                    <p class="endpoint-description">Generate QR codes with customizable size, error correction levels, and output formats.</p>
                    <span class="endpoint-link disabled">
                        Coming soon
                    </span>
                </div>
            </div>

            <footer class="footer">
                <p>Powered by Laravel {{ app()->version() }}</p>
            </footer>
        </div>
    </body>
</html>
