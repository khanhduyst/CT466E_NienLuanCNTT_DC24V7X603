<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = ['category_id', 'barcode', 'name', 'image', 'is_deleted'];

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function units()
    {
        return $this->hasMany(ProductUnit::class, 'product_id');
    }
}
