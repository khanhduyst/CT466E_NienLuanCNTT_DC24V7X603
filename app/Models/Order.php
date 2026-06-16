<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = ['user_id', 'customer_id', 'invoice_number', 'total_amount', 'discount_amount', 'final_amount', 'paid_amount', 'change_amount', 'payment_method'];
}
