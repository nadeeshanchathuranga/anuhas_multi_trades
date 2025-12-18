<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CreditBill extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'employee_id',
        'user_id',
        'order_id',
        'total_amount',
        'discount',
        'total_cost',
        'payment_method',
        'sale_date',
        'cash',
        'paid_amount',
        'status',
        'is_whole',
        'custom_discount_type',
        'custom_discount',
        'cheque_id',
    ];

    protected $casts = [
        'sale_date' => 'date',
        'total_amount' => 'decimal:2',
        'discount' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'cash' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'custom_discount' => 'decimal:2',
        'is_whole' => 'boolean',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(CreditBillItem::class);
    }

    public function cheque()
    {
        return $this->belongsTo(Cheque::class);
    }

    public function payments()
    {
        return $this->hasMany(CreditBillPayment::class);
    }

    public function getPendingAmountAttribute()
    {
        return max(0, $this->total_amount - $this->paid_amount);
    }
}
