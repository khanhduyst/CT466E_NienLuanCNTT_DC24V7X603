<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('suppliers')->insert([
            [
                'name' => 'Công ty TNHH Unilever Việt Nam',
                'phone_number' => '02838236651',
                'email' => 'unilever.vn@gmail.com',
                'address' => 'KCN Tây Bắc Củ Chi, TP. Hồ Chí Minh',
                'barcode' => 'NCC_UNILEVER',
                'total_debt' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Công ty TNHH Nước Giải Khát Coca-Cola Việt Nam',
                'phone_number' => '02838961000',
                'email' => 'cocacola.vn@gmail.com',
                'address' => 'Quận Thủ Đức, TP. Hồ Chí Minh',
                'barcode' => 'NCC_COCACOLA',
                'total_debt' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Nhà Phân Phối Sữa Vinamilk Cần Thơ',
                'phone_number' => '02923821000',
                'email' => 'vinamilk.cantho@gmail.com',
                'address' => '35 Đường 3/2, Ninh Kiều, Cần Thơ',
                'barcode' => 'NCC_VINAMILK',
                'total_debt' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}