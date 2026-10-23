<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CreditBillItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'credit_bill_id',
        'product_id',
        'printout_id',
        'custom_product_id',
        'quantity',
        'unit_price',
        'total_price',
        'discount',
        'apply_discount',
        'discounted_price',
        'include_custom',
        'selling_price',
        'item_discount',
        'item_discount_type',
        'item_discount_amount',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    public function creditBill()
    {
        return $this->belongsTo(CreditBill::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function printout()
    {
        return $this->belongsTo(Printout::class);
    }

    public function customProduct()
    {
        return $this->belongsTo(CustomProduct::class);
    }
}
