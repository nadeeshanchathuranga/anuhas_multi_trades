<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Printout extends Model
{
    use HasFactory;

    protected $fillable = [
        'price',
        'name',
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    /**
     * Check if printout is in stock
     */
    public function getInStockAttribute()
    {
        return $this->stock_quantity > 0;
    }

    /**
     * Decrease stock quantity
     */
    public function decreaseStock($quantity = 1)
    {
        $this->decrement('stock_quantity', $quantity);
    }

    /**
     * Increase stock quantity
     */
    public function increaseStock($quantity = 1)
    {
        $this->increment('stock_quantity', $quantity);
    }
}