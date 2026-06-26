<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // Lấy đại diện một category_id đầu tiên trong DB
        $categoryId = DB::table('categories')->value('id') ?? 1;

        DB::table('products')->insert([
            [
                'category_id' => $categoryId,
                'barcode' => '8934563123456',
                'name' => 'Sữa tươi Vinamilk 180ml',
                'image' => null,
                'is_deleted' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'category_id' => $categoryId,
                'barcode' => '8934588012114',
                'name' => 'Nước giải khát Coca Cola Chai 390ml',
                'image' => null,
                'is_deleted' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'category_id' => $categoryId,
                'barcode' => '8935049500411',
                'name' => 'Dầu ăn Meizan Thượng Hạng 1L',
                'image' => null,
                'is_deleted' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}