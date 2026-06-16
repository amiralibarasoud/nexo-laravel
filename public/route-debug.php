<?php

/**
 * عیب‌یابی صفحات عمومی (Inertia/Vite) — وقتی /admin کار می‌کند ولی / خطای 500 می‌دهد.
 *
 * استفاده:  https://your-domain/route-debug.php?run=nexo2024
 * لاگ:       storage/logs/route-debug.log
 * هشدار:    پس از رفع مشکل حذف کنید.
 */

if (!isset($_GET['run']) || $_GET['run'] !== 'nexo2024') {
    http_response_code(403);
    exit('Access denied');
}

@set_time_limit(120);
header('Content-Type: text/html; charset=utf-8');

$baseDir = realpath(__DIR__ . '/..') ?: dirname(__DIR__);
$logFile = $baseDir . '/storage/logs/route-debug.log';
$logDir = dirname($logFile);
if (!is_dir($logDir)) {
    @mkdir($logDir, 0755, true);
}

$lines = [];
$log = static function (string $message, string $level = 'INFO') use (&$lines, $logFile): void {
    $line = '[' . date('Y-m-d H:i:s') . "] [{$level}] {$message}";
    $lines[] = $line;
    if (is_dir(dirname($logFile)) && is_writable(dirname($logFile))) {
        @file_put_contents($logFile, $line . PHP_EOL, FILE_APPEND | LOCK_EX);
    }
};

$log('=== route-debug started ===');

// ─── Vite build (شایع‌ترین علت 500 صفحه اصلی) ─────────────────
$manifestPath = __DIR__ . '/build/manifest.json';
if (!file_exists($manifestPath)) {
    $log('Vite manifest MISSING: public/build/manifest.json', 'FAIL');
    $log('Filament/admin از public/js/filament استفاده می‌کند؛ صفحات عمومی به public/build/ نیاز دارند', 'HINT');
    $log('روی لوکال: npm run build && commit/public/build/* را deploy کنید', 'HINT');
} else {
    $log('Vite manifest OK: ' . $manifestPath, 'OK');
    $manifest = json_decode((string) file_get_contents($manifestPath), true);
    if (!is_array($manifest)) {
        $log('manifest.json invalid JSON', 'FAIL');
    } else {
        $log('manifest entries: ' . count($manifest), 'OK');
        $missingAssets = [];
        foreach ($manifest as $entry) {
            if (!isset($entry['file'])) {
                continue;
            }
            $asset = __DIR__ . '/build/' . $entry['file'];
            if (!file_exists($asset)) {
                $missingAssets[] = 'build/' . $entry['file'];
            }
        }
        if ($missingAssets !== []) {
            $log('missing Vite assets: ' . count($missingAssets), 'FAIL');
            foreach (array_slice($missingAssets, 0, 5) as $m) {
                $log("  missing: {$m}", 'FAIL');
            }
        } else {
            $log('all manifest assets exist on disk', 'OK');
        }
    }
}

// ─── Laravel: simulate GET / ───────────────────────────────────
$autoload = $baseDir . '/vendor/autoload.php';
if (!file_exists($autoload)) {
    $log('vendor/autoload.php missing', 'FAIL');
} else {
    try {
        require $autoload;
        $app = require $baseDir . '/bootstrap/app.php';
        $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
        $kernel->bootstrap();

        $log('Laravel bootstrap OK', 'OK');

        // DB tables used by home page
        $tables = ['courses', 'categories', 'blog_posts', 'enrollments', 'settings'];
        foreach ($tables as $table) {
            try {
                Illuminate\Support\Facades\DB::table($table)->limit(1)->get();
                $log("table {$table}: OK", 'OK');
            } catch (Throwable $e) {
                $log("table {$table}: FAIL — " . $e->getMessage(), 'FAIL');
            }
        }

        // Full HTTP simulation through kernel
        $request = Illuminate\Http\Request::create('/', 'GET');
        $response = $app->handle($request);
        $status = $response->getStatusCode();
        $log("GET / → HTTP {$status}", $status >= 400 ? 'FAIL' : 'OK');

        if ($status >= 400) {
            $body = $response->getContent();
            if (str_contains($body, 'Vite manifest not found')) {
                $log('confirmed: ViteManifestNotFoundException (public/build missing on host)', 'FAIL');
            }
        }
    } catch (Throwable $e) {
        $log('request simulation FAILED: ' . $e->getMessage(), 'FAIL');
        $log($e->getTraceAsString(), 'TRACE');
    }
}

// ─── Last laravel.log lines ────────────────────────────────────
$laravelLog = $baseDir . '/storage/logs/laravel.log';
if (file_exists($laravelLog)) {
    $log('--- last laravel.log errors ---', 'INFO');
    $tail = array_slice(file($laravelLog, FILE_IGNORE_NEW_LINES) ?: [], -30);
    foreach ($tail as $line) {
        if (str_contains($line, 'ERROR') || str_contains($line, 'Exception')) {
            $log(trim($line), 'LOG');
        }
    }
} else {
    $log('storage/logs/laravel.log not found', 'WARN');
}

$log('=== route-debug finished ===');

echo "<pre style='font-family:monospace;direction:ltr;text-align:left;background:#1a1a1a;color:#00ff00;padding:20px;line-height:1.6'>";
foreach ($lines as $line) {
    $color = '#00ff00';
    if (str_contains($line, '[FAIL]') || str_contains($line, '[FATAL]')) {
        $color = '#ff4d4d';
    } elseif (str_contains($line, '[WARN]') || str_contains($line, '[HINT]')) {
        $color = '#ffcc00';
    } elseif (str_contains($line, '[LOG]')) {
        $color = '#88ccff';
    }
    echo '<span style="color:' . $color . '">' . htmlspecialchars($line, ENT_QUOTES, 'UTF-8') . "</span>\n";
}
echo "\nLog: storage/logs/route-debug.log\n";
echo "</pre>";
echo "<p style='color:#ff4d4d;font-weight:bold;font-family:sans-serif'>⚠️ این فایل را پس از رفع مشکل حذف کنید!</p>";
