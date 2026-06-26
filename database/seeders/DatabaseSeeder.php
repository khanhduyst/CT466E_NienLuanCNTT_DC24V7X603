<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use App\Models\SystemSetting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $superAdminRole = Role::create(['name' => 'super_admin']);
        $managerRole = Role::create(['name' => 'manager']);
        $staffRole = Role::create(['name' => 'staff']);

        User::create([
            'role_id' => $superAdminRole->id,
            'name' => 'Chủ Cửa Hàng Admin',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('Admin@123'),
            'is_active' => true,
        ]);

        User::create([
            'role_id' => $managerRole->id,
            'name' => 'Quản Lý Kho Trần Văn B',
            'email' => 'manager@gmail.com',
            'password' => Hash::make('Manager@123'),
            'is_active' => true,
        ]);

        User::create([
            'role_id' => $staffRole->id,
            'name' => 'Thu Ngân Nguyễn Thị C',
            'email' => 'staff@gmail.com',
            'password' => Hash::make('Staff@123'),
            'is_active' => true,
        ]);

        SystemSetting::create(['key' => 'store_name', 'value' => 'Tiệm Tạp Hóa Thông Minh SmartGrocer']);
        SystemSetting::create(['key' => 'vietqr_bank_id', 'value' => '970415']);
        SystemSetting::create(['key' => 'vietqr_account_no', 'value' => '0123456789999']);
        SystemSetting::create(['key' => 'point_conversion_rate', 'value' => '10000']);
        SystemSetting::create(['key' => 'point_redeem_value', 'value' => '100']);

        $this->call([
            CategorySeeder::class,
            SupplierSeeder::class,
            ProductSeeder::class,
        ]);
    }
}
