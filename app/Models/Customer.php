<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    protected $fillable = [
        'phone_number',
        'name',
        'barcode',
        'current_points',
        'total_debt',
    ];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function pointLogs(): HasMany
    {
        return $this->hasMany(PointLog::class);
    }

    public function debtLogs(): HasMany
    {
        return $this->hasMany(DebtLog::class);
    }
}