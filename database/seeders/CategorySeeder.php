<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        DB::table('categories')->insert([
            ['name' => 'Nhu yếu phẩm', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Nước giải khát', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Gia vị & Dầu ăn', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}