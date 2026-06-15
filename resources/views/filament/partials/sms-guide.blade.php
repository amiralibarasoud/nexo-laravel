<div class="space-y-3 text-sm text-gray-600" dir="rtl">
    <div class="flex items-start gap-3 p-3 bg-blue-50 rounded-lg">
        <span class="text-blue-500 text-lg flex-shrink-0">۱</span>
        <div>
            <strong>دریافت API Key:</strong>
            وارد پنل sms.ir شوید، از منوی <strong>توسعه‌دهندگان → API Key</strong> کلید را کپی کنید.
        </div>
    </div>
    <div class="flex items-start gap-3 p-3 bg-blue-50 rounded-lg">
        <span class="text-blue-500 text-lg flex-shrink-0">۲</span>
        <div>
            <strong>ساخت قالب OTP:</strong>
            از منوی <strong>ارسال پیامک → قالب‌های تأیید هویت</strong> یک قالب بسازید.
            متن قالب باید شامل پارامتر <code class="bg-gray-100 px-1 rounded">#Code#</code> باشد.
        </div>
    </div>
    <div class="flex items-start gap-3 p-3 bg-blue-50 rounded-lg">
        <span class="text-blue-500 text-lg flex-shrink-0">۳</span>
        <div>
            <strong>Template ID:</strong>
            شناسه عددی قالب ساخته شده را در فیلد بالا وارد کنید.
        </div>
    </div>
    <div class="flex items-start gap-3 p-3 bg-amber-50 rounded-lg border border-amber-200">
        <span class="text-amber-500 text-lg flex-shrink-0">⚠️</span>
        <div>
            <strong>توجه:</strong>
            تا زمانی که API Key وارد نشده، سیستم در حالت تست کار می‌کند.
            برای محیط production، حالت تست را <strong>غیرفعال</strong> کنید.
        </div>
    </div>
</div>
