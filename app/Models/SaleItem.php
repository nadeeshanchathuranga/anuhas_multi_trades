<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'product_id',
        'printout_id',
        'custom_product_id',
        'quantity',
        'unit_price',
        'total_price',
        'reason_id',

    ];


    public function sale()
    {
        return $this->belongsTo(Sale::class, 'sale_id','id');
    }


    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id','id');
    }

    public function printout()
    {
        return $this->belongsTo(Printout::class, 'printout_id','id');
    }

    public function customProduct()
    {
        return $this->belongsTo(CustomProduct::class, 'custom_product_id','id');
    }

    public function returnReason()
    {
        return $this->belongsTo(ReturnReason::class, 'reason_id');
    }
}
