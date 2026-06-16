<?php
if (!isset($_GET['run']) || $_GET['run'] !== 'nexo2024') {
    die('Access denied');
}

$path = '/home/modiryat/repositories/nexo-laravel';
echo "<pre style='font-family:monospace;direction:ltr;text-align:left;background:#1a1a1a;color:#00ff00;padding:20px'>";

echo "=== PHP Version ===\n";
echo PHP_VERSION . "\n\n";

echo "=== Vendor Check ===\n";
echo file_exists($path . '/vendor/autoload.php') ? "vendor: OK ✓\n" : "vendor: MISSING ✗\n";

echo "\n=== Artisan Migrate ===\n";
echo shell_exec("cd {$path} && php artisan migrate --force 2>&1") ?: "no output\n";

echo "\n=== Seed Settings ===\n";
echo shell_exec("cd {$path} && php artisan db:seed --class=SettingsSeeder --force 2>&1") ?: "no output\n";

echo "\n=== Seed Blog ===\n";
echo shell_exec("cd {$path} && php artisan db:seed --class=BlogSeeder --force 2>&1") ?: "no output\n";

echo "\n=== Storage Link ===\n";
echo shell_exec("cd {$path} && php artisan storage:link 2>&1") ?: "no output\n";

echo "\n=== Config Cache ===\n";
echo shell_exec("cd {$path} && php artisan config:cache 2>&1") ?: "no output\n";

echo "\n=== Route Cache ===\n";
echo shell_exec("cd {$path} && php artisan route:cache 2>&1") ?: "no output\n";

echo "\n=== Check .env ===\n";
echo file_exists($path . '/.env') ? ".env: OK ✓\n" : ".env: MISSING ✗\n";

echo "\n=== DONE ===\n";
echo "</pre>";
echo "<p style='color:red;font-weight:bold'>این فایل را پس از اتمام حذف کنید!</p>";
