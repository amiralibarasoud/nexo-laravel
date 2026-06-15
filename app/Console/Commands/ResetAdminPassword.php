<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ResetAdminPassword extends Command
{
    protected $signature = 'admin:reset-password
                            {--email=admin@nexocourse.ir : ایمیل ادمین}
                            {--password=Admin@12345 : رمز عبور جدید}
                            {--mobile=09129512697 : موبایل ادمین}';

    protected $description = 'ریست رمز عبور پنل ادمین';

    public function handle(): int
    {
        $email    = $this->option('email');
        $password = $this->option('password');
        $mobile   = $this->option('mobile');

        $user = User::where('email', $email)->orWhere('mobile', $mobile)->first();

        if (!$user) {
            // Create admin if not exists
            DB::table('users')->insert([
                'name'                => 'مدیر سیستم',
                'mobile'              => $mobile,
                'email'               => $email,
                'password'            => bcrypt($password),
                'is_admin'            => true,
                'is_active'           => true,
                'mobile_verified_at'  => now(),
                'created_at'          => now(),
                'updated_at'          => now(),
            ]);
            $this->info("✅ ادمین ساخته شد.");
        } else {
            // Update existing user directly via DB (bypass model cast)
            DB::table('users')->where('id', $user->id)->update([
                'email'      => $email,
                'mobile'     => $mobile,
                'password'   => bcrypt($password),
                'is_admin'   => true,
                'is_active'  => true,
                'updated_at' => now(),
            ]);
            $this->info("✅ رمز ادمین ریست شد.");
        }

        $this->table(
            ['فیلد', 'مقدار'],
            [
                ['ایمیل', $email],
                ['موبایل', $mobile],
                ['رمز عبور', $password],
                ['آدرس پنل', '/admin'],
            ]
        );

        return self::SUCCESS;
    }
}
