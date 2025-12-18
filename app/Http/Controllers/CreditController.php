<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use App\Models\CreditBill;
use App\Models\CreditBillPayment;
use App\Models\Cheque;
use App\Models\Customer;

class CreditController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!Gate::allows('hasRole', ['Admin', 'Cashier'])) {
                abort(403, 'Unauthorized');
            }
            return $next($request);
        });
    }

    /**
     * Display all credit bills with their payments
     */
    public function index()
    {
        $creditBills = CreditBill::with([
                'customer:id,name',
                'employee:id,name',
                'payments' => function($query) {
                    $query->orderBy('created_at', 'desc');
                }
            ])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($bill) {
                // Recalculate paid_amount from payments to ensure accuracy
                $totalPaid = (float) $bill->payments->sum('amount');

                \Log::info('Processing bill', [
                    'bill_id' => $bill->id,
                    'db_paid_amount' => $bill->paid_amount,
                    'calculated_paid_amount' => $totalPaid,
                    'payments_count' => $bill->payments->count()
                ]);

                // Update the credit bill if there's a discrepancy
                if ($totalPaid != $bill->paid_amount) {
                    $bill->paid_amount = $totalPaid;

                    // Update status based on payment
                    if ($totalPaid >= $bill->total_amount) {
                        $bill->status = 'completed';
                    } elseif ($totalPaid > 0) {
                        $bill->status = 'partial';
                    } else {
                        $bill->status = 'pending';
                    }

                    $bill->save();
                }

                $pending = max(0, $bill->total_amount - $totalPaid);

                return [
                    'id' => $bill->id,
                    'order_id' => $bill->order_id,
                    'customer_name' => $bill->customer ? $bill->customer->name : '-',
                    'employee_name' => $bill->employee ? $bill->employee->name : '-',
                    'total_amount' => (float) $bill->total_amount,
                    'paid_amount' => (float) $totalPaid,
                    'pending_amount' => (float) $pending,
                    'status' => ucfirst($bill->status),
                    'sale_date' => $bill->sale_date->format('Y-m-d'),
                    'payment_method' => $bill->payment_method,
                    'payments' => $bill->payments->map(function ($payment) {
                        return [
                            'id' => $payment->id,
                            'amount' => (float) $payment->amount,
                            'payment_method' => $payment->payment_method,
                            'description' => $payment->description ?? '',
                            'date' => $payment->created_at->format('Y-m-d H:i'),
                        ];
                    })->values()->toArray(),
                ];
            })->values()->toArray();

        $customers = Customer::select('id', 'name')->orderBy('name')->get()->toArray();

        // Log the data being sent
        \Log::info('Sending credit bills:', ['data' => $creditBills]);

        return Inertia::render('CreditPayment/Index', [
            'creditBills' => $creditBills,
            'customers' => $customers,
        ]);
    }

    /**
     * Store a new partial payment for a credit bill
     */
    public function store(Request $request)
    {
        \Log::info('Payment submission received', ['data' => $request->all()]);

        $validated = $request->validate([
            'credit_bill_id' => 'required|exists:credit_bills,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string|in:cash,card,cheque',
            'description' => 'nullable|string|max:500',

            // Cheque fields (required only when payment_method is cheque)
            'cheque_number' => 'nullable|required_if:payment_method,cheque|string|max:120',
            'bank_name' => 'nullable|required_if:payment_method,cheque|string|max:120',
            'cheque_date' => 'nullable|required_if:payment_method,cheque|date',
        ]);

        DB::beginTransaction();
        try {
            $creditBill = CreditBill::findOrFail($validated['credit_bill_id']);

            // Calculate remaining amount
            $remaining = $creditBill->total_amount - $creditBill->paid_amount;

            // Prevent overpayment
            if ($validated['amount'] > $remaining) {
                return redirect()->back()->with('error', "Payment amount ({$validated['amount']}) exceeds remaining balance ({$remaining}).");
            }

            // Handle cheque if payment method is cheque
            $chequeId = null;
            if ($validated['payment_method'] === 'cheque') {
                $cheque = Cheque::create([
                    'cheque_number' => $validated['cheque_number'],
                    'bank_name' => $validated['bank_name'],
                    'cheque_date' => $validated['cheque_date'],
                    'amount' => $validated['amount'],
                    'notes' => $validated['description'] ?? null,
                    'status' => 'pending',
                ]);
                $chequeId = $cheque->id;
            }

            // Create payment record
            CreditBillPayment::create([
                'credit_bill_id' => $creditBill->id,
                'customer_id' => $creditBill->customer_id,
                'amount' => $validated['amount'],
                'payment_method' => $validated['payment_method'],
                'description' => $validated['description'] ?? null,
                'cheque_id' => $chequeId,
            ]);

            // Update credit bill paid amount and status
            $newPaidAmount = $creditBill->paid_amount + $validated['amount'];
            $creditBill->paid_amount = $newPaidAmount;

            if ($newPaidAmount >= $creditBill->total_amount) {
                $creditBill->status = 'completed';
            } elseif ($newPaidAmount > 0) {
                $creditBill->status = 'partial';
            }

            $creditBill->save();

            DB::commit();

            $newPending = max(0, $creditBill->total_amount - $newPaidAmount);

            // Return back to the page with updated data
            return redirect()->back()
                ->with('success', "Payment of {$validated['amount']} recorded successfully. Remaining: {$newPending}");

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to record payment: ' . $e->getMessage());
        }
    }

    /**
     * Get details of a specific credit bill with all its payments
     */
    public function show($id)
    {
        $creditBill = CreditBill::with(['customer', 'employee', 'payments.cheque', 'items.product'])
            ->findOrFail($id);

        return response()->json([
            'id' => $creditBill->id,
            'order_id' => $creditBill->order_id,
            'customer' => $creditBill->customer,
            'employee' => $creditBill->employee,
            'total_amount' => $creditBill->total_amount,
            'paid_amount' => $creditBill->paid_amount,
            'pending_amount' => $creditBill->pending_amount,
            'status' => $creditBill->status,
            'sale_date' => $creditBill->sale_date->format('Y-m-d'),
            'payment_method' => $creditBill->payment_method,
            'items' => $creditBill->items,
            'payments' => $creditBill->payments->map(function ($payment) {
                return [
                    'id' => $payment->id,
                    'amount' => $payment->amount,
                    'payment_method' => $payment->payment_method,
                    'description' => $payment->description,
                    'date' => $payment->created_at->format('Y-m-d H:i'),
                    'cheque' => $payment->cheque,
                ];
            }),
        ]);
    }
}
