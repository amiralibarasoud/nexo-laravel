<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        foreach (Setting::themeSeedDefaults() as $item) {
            Setting::firstOrCreate(['key' => $item['key']], $item);
        }
    }

    public function down(): void
    {
        //
    }
};
