<?php
/**
 * ping.php — تشخیص مسیر واقعی سایت روی هاست
 * https://nexovest.ir/ping.php?token=ping2026
 * بعد از دیدن نتیجه حذف کنید.
 */
if (($_GET['token'] ?? '') !== 'ping2026') {
    http_response_code(403);
    header('Content-Type: text/html; charset=utf-8');
    exit('forbidden');
}

header('Content-Type: text/html; charset=utf-8');

$public = __DIR__;
$root = dirname($public);
$manifest = $public . '/build/manifest.json';
$appHash = '(manifest نیست)';
$hasFaq = false;

if (is_file($manifest)) {
    $m = file_get_contents($manifest) ?: '';
    if (preg_match('/"file"\s*:\s*"(assets\/app-[^"]+\.js)"/', $m, $mm)) {
        $appHash = $mm[1];
    }
    $hasFaq = str_contains($m, 'Pages/Faq') || str_contains($m, 'Faq-');
}

$expectedPublic = '/home/modiryat/nexovest.ir/public';
$isCorrectTree = is_dir($root . '/app') && is_dir($root . '/storage') && is_file($root . '/.env');

echo '<!DOCTYPE html><html dir="rtl" lang="fa"><head><meta charset="utf-8">
<style>
body{font-family:Tahoma,sans-serif;max-width:860px;margin:30px auto;padding:20px;background:#f8fafc}
.ok{color:#16a34a}.err{color:#dc2626}.warn{color:#d97706}
.box{background:#fff;border:1px solid #e2e8f0;border-radius:10px;padding:16px;margin:12px 0}
code{background:#e2e8f0;padding:2px 6px;border-radius:4px;word-break:break-all}
</style></head><body>';
echo '<h1>تشخیص مسیر نکسووست</h1>';

echo '<div class="box">';
echo '<p><b>مسیر این فایل (Document Root واقعی):</b><br><code>' . htmlspecialchars($public) . '</code></p>';
echo '<p><b>پوشه پروژه Laravel:</b><br><code>' . htmlspecialchars($root) . '</code></p>';
echo '<p><b>مسیر درست مورد انتظار:</b><br><code>' . htmlspecialchars($expectedPublic) . '</code></p>';

if (realpath($public) === realpath($expectedPublic) || str_replace('\\', '/', $public) === $expectedPublic) {
    echo '<p class="ok">✅ Document Root درست است: nexovest.ir/public</p>';
} elseif (str_contains($public, 'public_html')) {
    echo '<p class="err">❌ الان از public_html سرو می‌شوید — این کپی قدیمی/غلط است.</p>';
    echo '<p class="warn">در cPanel → Domains → nexovest.ir → Document Root را بگذارید روی:<br><code>/home/modiryat/nexovest.ir/public</code></p>';
} else {
    echo '<p class="warn">⚠️ مسیر با مقدار مورد انتظار یکی نیست. اگر سایت کار می‌کند، همین مسیر را مبنا بگیرید و فقط همین‌جا build را عوض کنید.</p>';
}

echo '<p>' . ($isCorrectTree ? '<span class="ok">✅ کنار این public، پوشه app/storage/.env پیدا شد</span>' : '<span class="err">❌ ساختار Laravel کنار این public کامل نیست</span>') . '</p>';
echo '</div>';

echo '<div class="box">';
echo '<h2>وضعیت build</h2>';
echo '<p>manifest: <code>' . htmlspecialchars($manifest) . '</code> — ' . (is_file($manifest) ? '<span class="ok">هست</span>' : '<span class="err">نیست</span>') . '</p>';
echo '<p>فایل JS اصلی: <code>' . htmlspecialchars($appHash) . '</code></p>';
echo '<p>Faq در manifest: ' . ($hasFaq ? '<span class="ok">بله ✅</span>' : '<span class="err">خیر ❌ — همین علت سفید بودن FAQ است</span>') . '</p>';

if (str_contains($appHash, 'wMzF5M8w')) {
    echo '<p class="err">این همان بیلد قدیمی است. باید پوشه build عوض شود.</p>';
} elseif ($hasFaq) {
    echo '<p class="ok">بیلد شامل FAQ است. اگر هنوز خطا می‌بینید Ctrl+Shift+R بزنید.</p>';
}
echo '</div>';

echo '<div class="box">';
echo '<h2>کار بعدی</h2>';
echo '<ol>';
echo '<li>اگر مسیر public_html بود → Document Root را به <code>/home/modiryat/nexovest.ir/public</code> تغییر دهید</li>';
echo '<li>سپس <code>force-update.php</code> را در <b>همین پوشه‌ای که ping.php هست</b> آپلود و اجرا کنید</li>';
echo '<li>یا کل <code>public/build</code> لوکال را روی <b>همین</b> مسیر آپلود کنید</li>';
echo '<li>این فایل ping.php را حذف کنید</li>';
echo '</ol>';
echo '</div></body></html>';
