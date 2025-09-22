#!/usr/bin/env node

const { execSync } = require('child_process');
const fs = require('fs');
const path = require('path');

console.log('🚀 Building Ionic app for Laravel integration...\n');

try {
  // 1. Build the Ionic app
  console.log('📦 Building Ionic Vue app...');
  execSync('npm run build', { stdio: 'inherit' });

  // 2. Create Laravel integration files
  const laravelPublicPath = '../public';
  const ionicBuildPath = path.join(laravelPublicPath, 'ionic-build');

  console.log('\n🔗 Creating Laravel integration files...');

  // Create blade template for serving the Ionic app
  const bladeTemplate = `<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#2563eb">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }} - Mobile</title>

    <!-- PWA Manifest -->
    <link rel="manifest" href="{{ asset('ionic-build/manifest.json') }}">

    <!-- Ionic CSS -->
    @foreach(glob(public_path('ionic-build/assets/*.css')) as $css)
        <link rel="stylesheet" href="{{ asset('ionic-build/assets/' . basename($css)) }}">
    @endforeach

    <!-- Mobile-first viewport -->
    <style>
        @media (min-width: 1024px) {
            .desktop-warning {
                display: flex !important;
            }
        }
    </style>
</head>
<body>
    <div id="app"></div>

    <!-- Ionic JavaScript -->
    @foreach(glob(public_path('ionic-build/assets/*.js')) as $js)
        <script type="module" src="{{ asset('ionic-build/assets/' . basename($js)) }}"></script>
    @endforeach
</body>
</html>`;

  // Write blade template
  const viewsPath = '../resources/views/mobile';
  if (!fs.existsSync(viewsPath)) {
    fs.mkdirSync(viewsPath, { recursive: true });
  }
  fs.writeFileSync(path.join(viewsPath, 'ionic.blade.php'), bladeTemplate);

  // 3. Create PWA manifest
  const manifest = {
    name: "Sistema de Hemodiálise",
    short_name: "Hemodiálise",
    description: "Sistema Mobile de Controle de Qualidade - Estado do Maranhão",
    start_url: "/mobile/ionic",
    display: "standalone",
    background_color: "#2563eb",
    theme_color: "#2563eb",
    orientation: "portrait-primary",
    scope: "/mobile/",
    icons: [
      {
        src: "/ionic-build/assets/icon-192.png",
        sizes: "192x192",
        type: "image/png",
        purpose: "any maskable"
      },
      {
        src: "/ionic-build/assets/icon-512.png",
        sizes: "512x512",
        type: "image/png",
        purpose: "any maskable"
      }
    ]
  };

  fs.writeFileSync(path.join(ionicBuildPath, 'manifest.json'), JSON.stringify(manifest, null, 2));

  // 4. Create Laravel route suggestions
  const routesSuggestion = `
// Add these routes to your web.php file:

// Mobile Ionic app route
Route::get('/mobile/ionic', function () {
    return view('mobile.ionic');
})->name('mobile.ionic');

// Redirect mobile users to Ionic app
Route::get('/mobile', function () {
    return redirect()->route('mobile.ionic');
})->name('mobile');

// Service Worker (if needed)
Route::get('/sw.js', function () {
    return response()
        ->file(public_path('ionic-build/sw.js'))
        ->header('Content-Type', 'application/javascript');
});
`;

  fs.writeFileSync('../IONIC_INTEGRATION_ROUTES.md', routesSuggestion);

  console.log('\n✅ Build completed successfully!');
  console.log('\n📋 Next steps:');
  console.log('1. Add the routes from IONIC_INTEGRATION_ROUTES.md to your web.php');
  console.log('2. Update your Laravel middleware to allow mobile routes');
  console.log('3. Test the integration by visiting /mobile/ionic');
  console.log('\n🎯 Ionic app is now integrated with Laravel!');

} catch (error) {
  console.error('❌ Build failed:', error.message);
  process.exit(1);
}`;