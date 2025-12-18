<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CreditBillPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'credit_bill_id',
        'customer_id',
        'amount',
        'payment_method',
        'description',
        'cheque_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function creditBill()
    {
        return $this->belongsTo(CreditBill::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function cheque()
    {
        return $this->belongsTo(Cheque::class);
    }
}
