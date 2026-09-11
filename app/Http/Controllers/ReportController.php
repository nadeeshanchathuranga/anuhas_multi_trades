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

        // Reusable created_at window (for InCash, Expenses, etc.)
        $applyCreatedWindow = function ($q) use ($from, $to) {
            if ($from && $to) {
                $q->whereBetween('created_at', [$from, $to]);
            } elseif ($from) {
                $q->where('created_at', '>=', $from);
            } elseif ($to) {
                $q->where('created_at', '<=', $to);
            }
        };

        // Reusable sale_date window (for Sales and SaleItems)
        $applySaleDateWindow = function ($q) use ($from, $to) {
            if ($from && $to) {
                $q->whereBetween('sale_date', [$from, $to]);
            } elseif ($from) {
                $q->where('sale_date', '>=', $from);
            } elseif ($to) {
                $q->where('sale_date', '<=', $to);
            }
        };

        // -------- Top Products (sold in range via Sale.sale_date) - Paginate to limit memory --------
        if ($from || $to) {
            $productIds = SaleItem::whereHas('sale', function ($q) use ($applySaleDateWindow) {
                    $applySaleDateWindow($q);
                })
                ->pluck('product_id')
                ->unique();

            $products = Product::whereIn('id', $productIds)
                ->orderBy('created_at', 'desc')
                ->limit(100)  // Limit display to top 100 products
                ->get();
        } else {
            $products = Product::orderBy('created_at', 'desc')
                ->limit(100)  // Limit display to top 100 products
                ->get();
        }

        // -------- Sales (filter by sale_date) - Paginate to limit memory --------
        $salesQuery = Sale::with(['saleItems.product.category', 'employee', 'customer']);

        if ($from || $to) {
            $applySaleDateWindow($salesQuery);
        }

        // -------- Credit Bills (filter by payment dates, not creation date) - Paginate to limit memory --------
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

        // For display only - limited to 100 rows, safe to hydrate full models
        $creditBillsForDisplay = (clone $creditBillsQuery)->orderBy('created_at', 'desc')->limit(100)->get();
        $creditBillsForDisplay->each(function($bill) {
            $bill->filtered_paid_amount = $bill->payments->sum('amount');
        });

        // For qty per product (respect same window through parent sale)
        $salesQuantitiesQuery = SaleItem::query()->whereHas('sale', function ($q) use ($applySaleDateWindow, $from, $to) {
            if ($from || $to) $applySaleDateWindow($q);
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

        // For display, limit sales; calculations are done via SQL aggregates below (no full hydration)
        $salesForDisplay = (clone $salesQuery)->orderBy('created_at', 'desc')->limit(100)->get();

        // Base (unclamped) query for sales aggregates in this date window
        $salesAggQuery = Sale::query();
        if ($from || $to) {
            $applySaleDateWindow($salesAggQuery);
        }

        // Overall sales totals via SQL aggregate (no row hydration)
        $salesTotalsRow = (clone $salesAggQuery)
            ->selectRaw('
                COALESCE(SUM(total_amount), 0) as total_amount,
                COALESCE(SUM(total_cost), 0) as total_cost,
                COALESCE(SUM(discount), 0) as discount,
                COALESCE(SUM(custom_discount), 0) as custom_discount,
                COUNT(*) as cnt
            ')
            ->first();

        $totalSaleAmount         = (float) $salesTotalsRow->total_amount;
        $totalCost               = (float) $salesTotalsRow->total_cost;
        $totalProductDiscountLkr = (float) $salesTotalsRow->discount;
        $totalCustomDiscountLkr  = (float) $salesTotalsRow->custom_discount;
        $totalAllDiscountLkr     = $totalProductDiscountLkr + $totalCustomDiscountLkr;
        $salesCount              = (int) $salesTotalsRow->cnt;

        // Category totals via SQL join + groupBy (no full model hydration)
        $categorySales = [];
        (clone $salesAggQuery)
            ->join('sale_items', 'sale_items.sale_id', '=', 'sales.id')
            ->leftJoin('products', 'products.id', '=', 'sale_items.product_id')
            ->leftJoin('categories', 'categories.id', '=', 'products.category_id')
            ->selectRaw("COALESCE(categories.name, 'No Category') as category_name, SUM(sale_items.total_price) as total")
            ->groupBy('category_name')
            ->get()
            ->each(function ($row) use (&$categorySales) {
                $categorySales[$row->category_name] = ($categorySales[$row->category_name] ?? 0) + (float) $row->total;
            });

        // Payment totals via SQL groupBy — normalize keys to Title Case so 'card' and 'Card' merge correctly
        $paymentMethodTotals = [];
        (clone $salesAggQuery)
            ->selectRaw('payment_method, SUM(total_amount) as total')
            ->groupBy('payment_method')
            ->get()
            ->each(function ($row) use (&$paymentMethodTotals) {
                $key = ucfirst(strtolower((string) $row->payment_method));
                $paymentMethodTotals[$key] = ($paymentMethodTotals[$key] ?? 0) + (float) $row->total;
            });

        // Employee sales (NET) via SQL join + groupBy
        $employeeSalesSummary = [];
        (clone $salesAggQuery)
            ->join('employees', 'employees.id', '=', 'sales.employee_id')
            ->selectRaw('employees.name as employee_name, SUM(sales.total_amount - sales.discount - sales.custom_discount) as net_total')
            ->groupBy('employees.id', 'employees.name')
            ->get()
            ->each(function ($row) use (&$employeeSalesSummary) {
                $name = $row->employee_name;
                $employeeSalesSummary[$name] ??= [
                    'Employee Name' => $name,
                    'Total Sales Amount' => 0,
                ];
                $employeeSalesSummary[$name]['Total Sales Amount'] += (float) $row->net_total;
            });

        // Credit bills - aggregate via SQL join against payments in the filtered date range
        $creditBillAggQuery = \App\Models\CreditBill::query();
        if ($from || $to) {
            $creditBillAggQuery->whereHas('payments', function($query) use ($from, $to) {
                if ($from) $query->where('created_at', '>=', $from);
                if ($to)   $query->where('created_at', '<=', $to);
            });
        } else {
            $creditBillAggQuery->where('paid_amount', '>', 0);
        }

        $paymentsSub = \App\Models\CreditBillPayment::query()
            ->select('credit_bill_id')
            ->selectRaw('SUM(amount) as filtered_paid_amount');
        if ($from) $paymentsSub->where('created_at', '>=', $from);
        if ($to)   $paymentsSub->where('created_at', '<=', $to);
        $paymentsSub->groupBy('credit_bill_id');

        $creditBillRows = (clone $creditBillAggQuery)
            ->joinSub($paymentsSub, 'filtered_payments', 'filtered_payments.credit_bill_id', '=', 'credit_bills.id')
            ->select('credit_bills.total_amount', 'credit_bills.total_cost', 'credit_bills.discount', 'credit_bills.custom_discount', 'filtered_payments.filtered_paid_amount')
            ->get();

        $totalCreditBillPaidAmount = 0.0;
        $totalCreditDiscountLkr    = 0.0;
        $creditBillsCount          = $creditBillRows->count();

        foreach ($creditBillRows as $cb) {
            $paidAmount = (float) $cb->filtered_paid_amount;
            $totalCreditBillPaidAmount += $paidAmount;

            if ($cb->total_amount > 0) {
                $paymentRatio = $paidAmount / (float) $cb->total_amount;
                $totalCost += ((float) $cb->total_cost * $paymentRatio);
                $cbProductDiscount = ((float) $cb->discount * $paymentRatio);
                $cbCustomDiscount  = ((float) $cb->custom_discount * $paymentRatio);
                $totalProductDiscountLkr += $cbProductDiscount;
                $totalCustomDiscountLkr  += $cbCustomDiscount;
                $totalCreditDiscountLkr  += ($cbProductDiscount + $cbCustomDiscount);
            }
        }
        $totalAllDiscountLkr += $totalCreditDiscountLkr;

        // Merge credit bill payments into payment method totals (same normalized key)
        $creditPaymentTotalsQuery = \App\Models\CreditBillPayment::query();
        if ($from) $creditPaymentTotalsQuery->where('created_at', '>=', $from);
        if ($to)   $creditPaymentTotalsQuery->where('created_at', '<=', $to);
        $creditPaymentTotalsQuery
            ->selectRaw('payment_method, SUM(amount) as total')
            ->groupBy('payment_method')
            ->get()
            ->each(function ($row) use (&$paymentMethodTotals) {
                $key = ucfirst(strtolower((string) $row->payment_method));
                $paymentMethodTotals[$key] = ($paymentMethodTotals[$key] ?? 0) + (float) $row->total;
            });

        // Net profit = (regular sales + credit bill payments) - costs - discounts
        $totalRevenue = $totalSaleAmount + $totalCreditBillPaidAmount;
        $netProfit = $totalRevenue - $totalCost - $totalAllDiscountLkr;

        $totalTransactions       = $salesCount + $creditBillsCount;
        $averageTransactionValue = $totalTransactions > 0 ? ($totalRevenue / $totalTransactions) : 0;

        // Distinct customers (same filter)
        $totalCustomer = (clone $salesQuery)->distinct('customer_id')->count('customer_id');

        // -------- Expenses (filter by created_at) - Paginate for display, aggregate for totals --------
        $expenseQuery = ExpenseNew::query();
        if ($from || $to) {
            $applyCreatedWindow($expenseQuery);
        }
        $expensesForDisplay = (clone $expenseQuery)->orderBy('created_at', 'desc')->limit(100)->get();

        $expenseTotalsRow = (clone $expenseQuery)->selectRaw('COALESCE(SUM(amount), 0) as total, COUNT(*) as cnt')->first();
        $totalExpenseAmount = (float) $expenseTotalsRow->total;
        $totalExpenseCount  = (int) $expenseTotalsRow->cnt;

        // -------- In Cash Records (filter by created_at) - Paginate for display, aggregate for totals --------
        $inCashQuery = InCash::query();
        if ($from || $to) {
            $applyCreatedWindow($inCashQuery);
        }
        $inCashRecordsForDisplay = (clone $inCashQuery)->orderBy('created_at', 'desc')->limit(100)->get();

        $inCashTotalsRow = (clone $inCashQuery)->selectRaw('COALESCE(SUM(amount), 0) as total, COUNT(*) as cnt')->first();
        $totalInCashAmount = (float) $inCashTotalsRow->total;
        $totalInCashCount = (int) $inCashTotalsRow->cnt;

        // -------- Stock Transactions Return - Limit to reduce memory --------
        $stockTransactionsReturn = StockTransaction::with('product')
            ->where('transaction_type', 'Returned')
            ->limit(100);

        if ($startDateRaw && $endDateRaw) {
            $stockTransactionsReturn->whereBetween('transaction_date', [$startDateRaw, $endDateRaw]);
        }
        
        $stockTransactionsReturn = $stockTransactionsReturn->get();

        return Inertia::render('Reports/Index', [
            'products'                  => $products,
            'sales'                     => $salesForDisplay,
            'creditBills'               => $creditBillsForDisplay,
            'expenses'                  => $expensesForDisplay,
            'inCashRecords'             => $inCashRecordsForDisplay,

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

            'totalCostAmount'            => round($totalCost, 2),

            'totalExpenseAmount'        => round($totalExpenseAmount, 2),
            'totalExpenseCount'         => $totalExpenseCount,

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
