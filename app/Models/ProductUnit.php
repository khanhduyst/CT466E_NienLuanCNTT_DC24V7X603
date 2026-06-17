<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductUnit extends Model
{
    protected $fillable = ['product_id', 'unit_name', 'conversion_rate', 'base_price', 'sale_price', 'is_base'];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
