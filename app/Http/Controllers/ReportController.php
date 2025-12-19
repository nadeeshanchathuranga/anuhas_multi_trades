<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Product;
use App\Models\Report;
use App\Models\Sale;
use App\Models\CreditBill;
use App\Models\ExpenseNew;
use App\Models\SaleItem;
use App\Models\StockTransaction;
use App\Models\InCash;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        if (!Gate::allows('hasRole', ['Admin'])) {
            abort(403, 'Unauthorized');
        }

        // Dates (normalize to day bounds)
        $startDateRaw = $request->input('start_date');
        $endDateRaw   = $request->input('end_date');

        $from = $startDateRaw ? Carbon::parse($startDateRaw)->startOfDay() : null;
        $to   = $endDateRaw   ? Carbon::parse($endDateRaw)->endOfDay()     : null;

        // Reusable created_at window
        $applyCreatedWindow = function ($q) use ($from, $to) {
            if ($from && $to) {
                $q->whereBetween('created_at', [$from, $to]);
            } elseif ($from) {
                $q->where('created_at', '>=', $from);
            } elseif ($to) {
                $q->where('created_at', '<=', $to);
            }
        };

        // -------- Top Products (sold in range via Sale.created_at) --------
        if ($from || $to) {
            $productIds = SaleItem::whereHas('sale', function ($q) use ($applyCreatedWindow) {
                    $applyCreatedWindow($q);
                })
                ->pluck('product_id')
                ->unique();

            $products = Product::whereIn('id', $productIds)
                ->orderBy('created_at', 'desc')
                ->get();
        } else {
            $products = Product::orderBy('created_at', 'desc')->get();
        }

        // -------- Sales (filter by created_at) --------
        $salesQuery = Sale::with(['saleItems.product.category', 'employee', 'customer']);

        if ($from || $to) {
            $applyCreatedWindow($salesQuery);
        }

        // -------- Credit Bills (filter by payment dates, not creation date) --------
        $creditBillsQuery = \App\Models\CreditBill::with(['customer', 'employee', 'payments' => function($query) use ($from, $to) {
            // Filter payments by date range
            if ($from) {
                $query->where('created_at', '>=', $from);
            }
            if ($to) {
                $query->where('created_at', '<=', $to);
            }
        }]);

        // Only include credit bills that have payments in the date range
        if ($from || $to) {
            $creditBillsQuery->whereHas('payments', function($query) use ($from, $to) {
                if ($from) {
                    $query->where('created_at', '>=', $from);
                }
                if ($to) {
                    $query->where('created_at', '<=', $to);
                }
            });
        } else {
            // If no date filter, only show bills with any payment
            $creditBillsQuery->where('paid_amount', '>', 0);
        }

        $creditBills = $creditBillsQuery->orderBy('created_at', 'desc')->get();

        // Recalculate paid_amount based on filtered payments
        $creditBills->each(function($bill) {
            $bill->filtered_paid_amount = $bill->payments->sum('amount');
        });

        // For qty per product (respect same window through parent sale)
        $salesQuantitiesQuery = SaleItem::query()->whereHas('sale', function ($q) use ($applyCreatedWindow, $from, $to) {
            if ($from || $to) $applyCreatedWindow($q);
        });

        $salesQuantities = $salesQuantitiesQuery
            ->select('product_id')
            ->selectRaw('SUM(quantity) as total_sales_qty')
            ->groupBy('product_id')
            ->get()
            ->keyBy('product_id');

        // Attach sales_qty to products
        $products->transform(function ($product) use ($salesQuantities) {
            $product->sales_qty = (float) ($salesQuantities->get($product->id)->total_sales_qty ?? 0);
            return $product;
        });

        $sales = $salesQuery->orderBy('created_at', 'desc')->get();

        // Helpers
        $customDiscountToLkr = function ($sale) {
            // The custom_discount field always contains the calculated LKR amount,
            // regardless of custom_discount_type, since POS calculates and stores the final amount
            return (float) ($sale->custom_discount ?? 0);
        };

        $totalDiscountToLkr = function ($sale) use ($customDiscountToLkr) {
            $productDiscount = (float) ($sale->discount ?? 0);
            $customDiscount = $customDiscountToLkr($sale);
            return $productDiscount + $customDiscount;
        };

        // Category totals (from filtered sales)
        $categorySales = [];
        foreach ($sales as $sale) {
            foreach ($sale->saleItems as $item) {
                $categoryName = $item->product->category->name ?? 'No Category';
                $categorySales[$categoryName] = ($categorySales[$categoryName] ?? 0) + (float) $item->total_price;
            }
        }

        // Payment totals - for regular sales use full amount, for credit bills use paid amount only
        $paymentMethodTotals = $sales->groupBy('payment_method')->map(
            fn($g) => (float) $g->sum('total_amount')
        )->toArray();

        // Add credit bill payments to payment method totals
        // Use actual payment methods from the filtered payments
        foreach ($creditBills as $cb) {
            foreach ($cb->payments as $payment) {
                $method = $payment->payment_method;
                $paymentMethodTotals[$method] = ($paymentMethodTotals[$method] ?? 0) + (float) $payment->amount;
            }
        }

        // Employee sales (NET)
        $employeeSalesSummary = [];
        foreach ($sales as $sale) {
            if (!$sale->employee) continue;
            $name = $sale->employee->name;
            $employeeSalesSummary[$name] ??= [
                'Employee Name' => $name,
                'Total Sales Amount' => 0,
            ];
            $gross       = (float) ($sale->total_amount ?? 0);
            $totalDisc   = $totalDiscountToLkr($sale);
            $employeeSalesSummary[$name]['Total Sales Amount'] += ($gross - $totalDisc);
        }

        // Overall stats from regular sales
        $totalSaleAmount         = (float) $sales->sum('total_amount');
        $totalCost               = (float) $sales->sum('total_cost');
        $totalProductDiscountLkr = (float) $sales->sum('discount');
        $totalCustomDiscountLkr  = (float) $sales->reduce(function($c, $s) use ($customDiscountToLkr) { return $c + $customDiscountToLkr($s); }, 0.0);
        $totalAllDiscountLkr     = (float) $sales->reduce(function($c, $s) use ($totalDiscountToLkr) { return $c + $totalDiscountToLkr($s); }, 0.0);

        // Credit bills - only count payments made in the filtered date range
        $totalCreditBillPaidAmount = (float) $creditBills->sum('filtered_paid_amount');

        // Calculate the proportion of payment to apply costs and discounts
        $totalCreditDiscountLkr = 0;
        foreach ($creditBills as $cb) {
            if ($cb->total_amount > 0) {
                // Use filtered paid amount (payments in date range)
                $paymentRatio = $cb->filtered_paid_amount / $cb->total_amount;
                // Only include proportional cost and discount based on what's been paid in this period
                $totalCost += ($cb->total_cost * $paymentRatio);
                $cbProductDiscount = ($cb->discount * $paymentRatio);
                $cbCustomDiscount = ($customDiscountToLkr($cb) * $paymentRatio);
                $totalProductDiscountLkr += $cbProductDiscount;
                $totalCustomDiscountLkr += $cbCustomDiscount;
                $totalCreditDiscountLkr += ($cbProductDiscount + $cbCustomDiscount);
            }
        }
        $totalAllDiscountLkr += $totalCreditDiscountLkr;

        // Net profit = (regular sales + credit bill payments) - costs - discounts
        $totalRevenue = $totalSaleAmount + $totalCreditBillPaidAmount;
        $netProfit = $totalRevenue - $totalCost - $totalAllDiscountLkr;

        $totalTransactions       = $sales->count() + $creditBills->count();
        $averageTransactionValue = $totalTransactions > 0 ? ($totalRevenue / $totalTransactions) : 0;

        // Distinct customers (same filter)
        $totalCustomer = (clone $salesQuery)->distinct('customer_id')->count('customer_id');

        // -------- Expenses (filter by created_at) --------
        $expenseQuery = ExpenseNew::query();
        if ($from || $to) {
            $applyCreatedWindow($expenseQuery);
        }
        $expenses = $expenseQuery->orderBy('created_at', 'desc')->get();
        $totalExpenseAmount = (float) $expenses->sum('amount');
        $totalExpenseCount  = $expenses->count();

        // -------- In Cash Records (filter by created_at) --------
        $inCashQuery = InCash::query();
        if ($from || $to) {
            $applyCreatedWindow($inCashQuery);
        }
        $inCashRecords = $inCashQuery->orderBy('created_at', 'desc')->get();
        $totalInCashAmount = (float) $inCashRecords->sum('amount');
        $totalInCashCount = $inCashRecords->count();

        // -------- Stock Transactions Return --------
        $stockTransactionsReturn = StockTransaction::with('product')
            ->where('transaction_type', 'Returned')
            ->get();

        if ($startDateRaw && $endDateRaw) {
            $stockTransactionsReturn = StockTransaction::with('product')
                ->where('transaction_type', 'Returned')
                ->whereBetween('transaction_date', [$startDateRaw, $endDateRaw])
                ->get();
        }

        return Inertia::render('Reports/Index', [
            'products'                  => $products,
            'sales'                     => $sales,
            'creditBills'               => $creditBills,

            'totalSaleAmount'           => round($totalSaleAmount, 2),
            'totalCreditBillPaidAmount' => round($totalCreditBillPaidAmount, 2),
            'totalRevenue'              => round($totalRevenue, 2),
            'totalDiscountLkr'          => round($totalAllDiscountLkr, 2),
            'totalProductDiscountLkr'   => round($totalProductDiscountLkr, 2),
            'totalCustomDiscountLkr'    => round($totalCustomDiscountLkr, 2),
            'netProfit'                 => round($netProfit, 2),
            'totalTransactions'         => $totalTransactions,
            'averageTransactionValue'   => round($averageTransactionValue, 2),
            'totalCustomer'             => $totalCustomer,

            'startDate'                 => $startDateRaw,
            'endDate'                   => $endDateRaw,

            'categorySales'             => $categorySales,
            'employeeSalesSummary'      => $employeeSalesSummary,
            'paymentMethodTotals'       => $paymentMethodTotals,

            'expenses'                  => $expenses,
            'totalExpenseAmount'        => round($totalExpenseAmount, 2),
            'totalExpenseCount'         => $totalExpenseCount,

            'inCashRecords'             => $inCashRecords,
            'totalInCashAmount'         => round($totalInCashAmount, 2),
            'totalInCashCount'          => $totalInCashCount,

            'stockTransactionsReturn'   => $stockTransactionsReturn,
        ]);
    }

    public function searchByCode(Request $request)
    {
        $code = $request->input('code');

        if (!$code) {
            return response()->json([
                'products' => [],
                'totalQuantity' => 0,
                'remainingQuantity' => 0
            ]);
        }

        $products = Product::where('code', $code)
            ->select([
                'batch_no',
                'total_quantity',
                'stock_quantity',
                'expire_date',
                'purchase_date',
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        $totalQuantity = $products->sum('total_quantity');
        $remainingQuantity = $products->sum('stock_quantity');

        return response()->json([
            'products' => $products,
            'totalQuantity' => $totalQuantity,
            'remainingQuantity' => $remainingQuantity
        ]);
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        //
    }

    public function show(Report $report)
    {
        //
    }

    public function edit(Report $report)
    {
        //
    }

    public function update(Request $request, Report $report)
    {
        //
    }

    public function destroy(Report $report)
    {
        //
    }

     // Add this NEW method

}
