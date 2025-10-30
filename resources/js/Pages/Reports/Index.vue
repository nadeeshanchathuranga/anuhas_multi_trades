<template>
  <Head title="Reports" />
  <Banner />

  <div class="flex flex-col items-center justify-start min-h-screen py-8 space-y-8 bg-gray-100 md:px-36 px-16">
    <Header />

    <!-- Page header + date filters -->
    <div class="w-full py-12 space-y-16">
      <div class="flex md:flex-row flex-col md:items-center items-start justify-between md:space-y-0 space-y-4">
        <div class="flex items-center justify-center space-x-4">
          <Link href="/"><img src="/images/back-arrow.png" class="w-14 h-14" /></Link>
          <p class="text-4xl font-bold tracking-wide text-black uppercase">Reports</p>
        </div>

      <div date-rangepicker class="flex items-center space-x-4">
          <div class="relative">
            <input v-model="startDate" type="date"
              class="text-xl font-normal tracking-wider text-blue-600 bg-white border border-blue-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
              placeholder="Start Date" />
          </div>
          <span class="text-xl font-bold tracking-wider text-blue-600">To</span>
          <div class="relative">
            <input v-model="endDate" type="date"
              class="text-xl font-normal tracking-wider text-blue-600 bg-white border border-blue-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
              placeholder="End Date" />
          </div>

          <button @click="filterData"
            class="px-6 py-3 text-xl font-normal tracking-wider text-white text-center bg-blue-600 rounded-lg custom-select hidden sm:inline-block">
            Filter
          </button>
          <Link href="/reports"
            class="px-6 py-3 text-xl font-normal tracking-wider text-white text-center bg-blue-600 rounded-lg custom-select hidden sm:inline-block">
            Reset
          </Link>
          <button 
            @click="openInCashModal"
            class="px-6 py-3 text-xl font-normal tracking-wider text-white text-center bg-green-600 rounded-lg custom-select hidden sm:inline-block hover:bg-green-700">
            AddInCash
          </button>
        </div>


        <div class="w-full flex justify-center items-center space-x-4 md:hidden">
          <button @click="filterData"
            class="px-6 py-3 text-xl font-normal tracking-wider text-white text-center bg-blue-600 rounded-lg custom-select">
            Filter
          </button>
          <Link href="/reports"
            class="px-6 py-3 text-xl font-normal tracking-wider text-white text-center bg-blue-600 rounded-lg custom-select">
            Reset
          </Link>
            <button 
            @click="openInCashModal"
            class="px-6 py-3 text-xl font-normal tracking-wider text-white text-center bg-green-600 rounded-lg custom-select hidden sm:inline-block hover:bg-green-700">
            + Add In Cash
          </button>
        </div>
      </div>
    </div>

    <!-- KPI Cards -->
    <div class="grid w-full md:grid-cols-5 grid-cols-3 gap-4 mb-4">
      <div class="py-6 flex flex-col justify-center items-center border-2 border-[#EC6116] w-full space-y-4 rounded-2xl bg-[#EC611666] shadow-lg hover:-translate-y-1 transition">
        <div class="flex flex-col items-center justify-center">
          <h2 class="text-xl font-extrabold tracking-wide text-black uppercase">Total Sales</h2>
          <h2 class="text-xl font-extrabold tracking-wide text-black uppercase">Amount</h2>
        </div>
        <p class="text-2xl font-bold text-black">{{ salesGrossTotal.toLocaleString(undefined,{minimumFractionDigits:2,maximumFractionDigits:2}) }} LKR</p>
      </div>

      <div class="py-6 flex flex-col justify-center items-center border-2 border-[#488D3F] w-full space-y-8 rounded-2xl bg-[#488D3F66] shadow-lg hover:-translate-y-1 transition">
        <h2 class="text-xl font-extrabold tracking-wide text-black uppercase">Net Profit</h2>
        <p class="text-2xl font-bold text-black">{{ toMoney(salesProfitTotal) }} LKR</p>
      </div>

      <div class="py-6 flex flex-col justify-center items-center border-2 border-[#16D0EC] w-full space-y-4 rounded-2xl bg-[#16D0EC66] shadow-lg hover:-translate-y-1 transition">
        <h2 class="text-xl font-extrabold tracking-wide text-black uppercase">Total Discount</h2>
        <p class="text-2xl font-bold text-black">{{ toMoney(salesDiscountTotal) }} LKR</p>
      </div>

      <div class="py-6 flex flex-col justify-center items-center border-2 border-[#9E16EC] w-full space-y-4 rounded-2xl bg-[#9E16EC66] shadow-lg hover:-translate-y-1 transition">
        <h2 class="text-xl font-extrabold tracking-wide text-black uppercase">Number of Transactions</h2>
        <p class="text-2xl font-bold text-black">{{ totalTransactions }}</p>
      </div>

      <div class="py-6 flex flex-col justify-center items-center border-2 border-[#EC16D7] w-full space-y-4 rounded-2xl bg-[#EC16D766] shadow-lg hover:-translate-y-1 transition">
        <h2 class="text-xl font-extrabold tracking-wide text-black uppercase">Total Number of Customers</h2>
        <p class="text-2xl font-bold text-black">{{ totalCustomer }}</p>
      </div>

     
    </div>

    <div class="grid w-full md:grid-cols-5 grid-cols-3 gap-4 mb-4">
      <div class="py-6 flex flex-col justify-center items-center border-2 border-[#ffb224] w-full space-y-4 rounded-2xl bg-[#ffb224] shadow-lg">
        <h2 class="text-xl font-extrabold tracking-wide text-black uppercase">Total Quantity In Stock:</h2>
        <p class="text-2xl font-bold text-black">{{ totalQty }} QTY</p>
      </div>

       <div class="py-6 flex flex-col justify-center items-center border-2 border-[#EC16D7] w-full space-y-4 rounded-2xl bg-[#EC16D766] shadow-lg hover:-translate-y-1 transition">
        <h2 class="text-xl font-extrabold tracking-wide text-black uppercase">InCash</h2>
        <p class="text-2xl font-bold text-black">{{ toMoney(totalInCashAmount) }} LKR</p>
      </div>

       <div class="py-6 flex flex-col justify-center items-center border-2 border-[#EC16D7] w-full space-y-4 rounded-2xl bg-[#EC16D766] shadow-lg hover:-translate-y-1 transition">
        <h2 class="text-xl font-extrabold tracking-wide text-black uppercase">Total InCash:</h2>
        <p class="text-2xl font-bold text-black">{{ (salesGrossTotal + totalInCashAmount).toLocaleString(undefined,{minimumFractionDigits:2,maximumFractionDigits:2}) }} LKR</p>
      </div>

      <div class="py-6 flex flex-col justify-center items-center border-2 border-[#41ec16] w-full space-y-4 rounded-2xl bg-[#41ec16] shadow-lg">
        <h2 class="text-xl font-extrabold tracking-wide text-black uppercase">Total Selling Price In Stock:</h2>
        <p class="text-2xl font-bold text-black">{{ totalSellingPrice.toFixed(2) }} LKR</p>
      </div>

      <div class="py-6 flex flex-col justify-center items-center border-2 border-[#3e41ff] w-full space-y-4 rounded-2xl bg-[#3e41ff] shadow-lg">
        <h2 class="text-xl font-extrabold tracking-wide text-black uppercase">Total Cost Price In Stock:</h2>
        <p class="text-2xl font-bold text-black">{{ totalRetailValue.toFixed(2) }} LKR</p>
      </div>
    </div>

    <!-- Charts -->
    <div class="flex md:flex-row flex-col items-center justify-center w-full h-full md:space-x-4 md:space-y-0 space-y-4">
      <div class="flex flex-col justify-between items-center md:w-1/3 w-full bg-white border-4 border-black rounded-xl h-[450px]">
        <div class="chart-container w-full md:pt-12">
          <div class="w-full flex justify-between items-center pb-8">
            <h2 class="text-2xl font-medium tracking-wide text-slate-700 text-left">Top Employee Sales</h2>
            <button @click="downloadPDF" class="w-full mt-6 px-4 py-2 text-md font-normal tracking-wider text-white bg-orange-600 rounded-lg custom-select hover:bg-orange-700 hover:shadow-lg">Download PDF</button>
          </div>
          <div class="w-full h-full flex justify-center items-center">
            <Doughnut :data="chartData4" :options="chartOptions4" />
          </div>
        </div>
      </div>

      <div class="flex flex-col justify-between items-center md:w-1/3 w-full bg-white border-4 border-black rounded-xl h-[450px]">
        <div class="chart-container w-full p-4">
          <div class="w-full flex justify-between items-center md:pt-12">
            <h2 class="text-2xl font-medium tracking-wide text-slate-700 text-left">Product</h2>
            <button @click="downloadPDF2" class="w-full mt-6 px-4 py-2 text-md font-normal tracking-wider text-white bg-orange-600 rounded-lg custom-select hover:bg-orange-700 hover:shadow-lg">Download PDF</button>
          </div>
          <Pie :data="chartData" :options="chartOptions" />
        </div>
      </div>

      <div class="flex flex-col justify-between items-center md:w-1/3 w-full bg-white border-4 border-black rounded-xl h-[450px]">
        <div class="chart-container w-full p-4">
          <div class="w-full flex justify-between items-center md:pt-12">
            <h2 class="text-2xl font-medium tracking-wide text-slate-700 text-left">Top Sales By Payment Method</h2>
            <button @click="downloadPDF3" class="w-full mt-6 px-4 py-2 text-md font-normal tracking-wider text-white bg-orange-600 rounded-lg custom-select hover:bg-orange-700 hover:shadow-lg">Download PDF</button>
          </div>
          <Doughnut :data="chartData1" :options="chartOptions1" />
        </div>
      </div>
    </div>

    <!-- Sales Table -->
    <div class="w-full bg-white border-4 border-black rounded-xl p-6">
      <h2 class="text-2xl font-semibold text-slate-700 text-center pb-4">Sales Table</h2>

      <div class="flex justify-between items-center pb-4">
        <div class="flex gap-4">
          <button @click="downloadSalesTablePDF"
                  class="px-4 py-2 text-md font-semibold text-white bg-orange-600 rounded-lg hover:bg-orange-700 shadow-md">
            Download PDF
          </button>
        </div>

        <div class="flex items-center gap-3">
          <div class="py-2 px-4 border-2 border-green-600 rounded-xl bg-green-100 shadow-sm text-center">
            <p class="text-sm font-extrabold text-black uppercase">
              Total Qty:
              <span class="text-base font-bold">{{ salesTotalQty.toLocaleString() }}</span>
            </p>
          </div>
          <div class="py-2 px-4 border-2 border-blue-600 rounded-xl bg-blue-100 shadow-sm text-center">
            <p class="text-sm font-extrabold text-black uppercase">
              Final Selling Price :
              <span class="text-base font-bold">
                {{ salesGrossTotal.toLocaleString(undefined,{minimumFractionDigits:2,maximumFractionDigits:2}) }} LKR
              </span>
            </p>
          </div>
          <div class="py-2 px-4 border-2 border-yellow-600 rounded-xl bg-yellow-100 shadow-sm text-center">
            <p class="text-sm font-extrabold text-black uppercase">
              Discounts:
              <span class="text-base font-bold">
                {{ salesDiscountTotal.toLocaleString(undefined,{minimumFractionDigits:2,maximumFractionDigits:2}) }} LKR
              </span>
            </p>
          </div>
          <div class="py-2 px-4 border-2 border-red-600 rounded-xl bg-red-100 shadow-sm text-center">
            <p class="text-sm font-extrabold text-black uppercase">
              Profit:
              <span class="text-base font-bold">
                {{ salesProfitTotal.toLocaleString(undefined,{minimumFractionDigits:2,maximumFractionDigits:2}) }} LKR
              </span>
            </p>
          </div>
        </div>
      </div>

      <div class="overflow-x-auto overflow-y-auto max-h-[480px] border rounded-xl mt-2">
        <table id="salesTbl" class="sales-table w-full text-gray-800 bg-white border border-gray-300 rounded-lg shadow-md">
          <colgroup>
            <col style="width:48px" />
            <col style="width:110px" />
            <col style="width:170px" />
            <col style="width:200px" />
            <col style="width:70px" />
            <col style="width:140px" />
            <col style="width:140px" />
            <col style="width:140px" />
            <col style="width:140px" />
          </colgroup>

          <thead class="sticky top-0 z-10">
            <tr class="bg-gradient-to-r from-slate-700 via-slate-600 to-slate-700 text-white text-[14px] border-b border-slate-800">
              <th class="p-3 text-left font-semibold">#</th>
              <th class="p-3 text-left font-semibold">Date</th>
              <th class="p-3 text-left font-semibold">Order Number</th>
              <th class="p-3 text-left font-semibold">Customer</th>
              <th class="p-3 text-center font-semibold">Qty</th>
              <th class="p-3 num font-semibold">Final Selling Price(LKR)</th>
              <th class="p-3 num font-semibold">Discounts</th>
              <th class="p-3 num font-semibold">Cost (LKR)</th>
              <th class="p-3 num font-semibold">Profit (LKR)</th>
            </tr>
          </thead>

          <tbody class="text-[12px] font-medium">
            <tr v-for="(s, i) in sales" :key="s.id ?? i" class="border-b transition duration-200 hover:bg-gray-100">
              <td class="p-3 text-center">{{ i + 1 }}</td>
              <td class="p-3 whitespace-nowrap text-center">{{ formatDate(s.sale_date) }}</td>
              <td class="p-3 text-center">
                {{ s.order_id ? s.order_id : 'Service -' }} {{ s.service_name }}
              </td>
              <td class="p-3">{{ s.customer?.name ?? 'N/A' }}</td>
              <td class="p-3 text-center">{{ saleQty(s) }}</td>
              <td class="p-3 num text-center">
                {{
                  toMoney(
                    s.total_amount -
                    (
                      s.custom_discount_type === 'percent'
                        ? (Number(s.total_amount || 0) * Number(s.custom_discount || 0) / 100)
                        : Number(s.custom_discount || 0)
                    )
                  )
                }}
              </td>
              <td class="p-3 num text-center">
                {{ discountDisplay(s) }}
              </td>
              <td class="p-3 num text-center">{{ toMoney(s.total_cost || 0) }}</td>
              <td class="p-3 num text-center">
                <ul>
                  <li class="font-semibold text-green-600">
                    <span v-if="s.is_service">
                      {{ toMoney(s.total_amount || 0) }}
                    </span>
                    <span v-else>
                      {{ toMoney(saleProfit(s)) }}
                    </span>
                  </li>
                </ul>
              </td>
            </tr>
          </tbody>

          <tfoot class="bg-gray-50 text-[12px] font-semibold">
            <tr>
              <td class="p-3 text-right" colspan="4">Totals:</td>
              <td class="p-3 text-center">{{ salesTotalQty.toLocaleString() }}</td>
              <td class="p-3 num text-center">
                {{ salesGrossTotal.toLocaleString(undefined,{minimumFractionDigits:2,maximumFractionDigits:2}) }}
              </td>
              <td class="p-3 num text-center">
                {{ salesDiscountTotal.toLocaleString(undefined,{minimumFractionDigits:2,maximumFractionDigits:2}) }}
              </td>
              <td class="p-3 num text-center">
                {{ salesCostTotal.toLocaleString(undefined,{minimumFractionDigits:2,maximumFractionDigits:2}) }}
              </td>
              <td class="p-3 num text-center">
                {{ salesProfitTotal.toLocaleString(undefined,{minimumFractionDigits:2,maximumFractionDigits:2}) }}
              </td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>

    <!-- Top Products Stock Table -->
    <div class="w-full bg-white border-4 border-black rounded-xl p-6">
      <h2 class="text-2xl font-semibold text-slate-700 text-center pb-4">Top Products Stock Table</h2>

      <div class="flex justify-between items-center pb-4 gap-4">
        <div class="flex gap-4 items-center">
          <button @click="downloadStockTablePDF" class="px-4 py-2 text-md font-semibold text-white bg-orange-600 rounded-lg hover:bg-orange-700 shadow-md">
            Download PDF
          </button>
          <div class="relative">
            <input
              v-model="productSearchQuery"
              type="text"
              placeholder="Search products..."
              class="px-4 py-2 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-blue-500 w-64"
            />
            <i class="ri-search-line absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
          </div>
        </div>

        <div class="flex items-center gap-3">
          <div class="py-2 px-4 border-2 border-green-600 rounded-xl bg-green-100 shadow-sm text-center">
            <p class="text-sm font-extrabold text-black uppercase">
              Total Products:
              <span class="text-base font-bold">{{ totalProductCount.toLocaleString() }}</span>
            </p>
          </div>
          <div class="py-2 px-4 border-2 border-blue-600 rounded-xl bg-blue-100 shadow-sm text-center">
            <p class="text-sm font-extrabold text-black uppercase">
              Total Worth:
              <span class="text-base font-bold">
                {{ totalProductWorth.toLocaleString(undefined,{minimumFractionDigits:2,maximumFractionDigits:2}) }} LKR
              </span>
            </p>
          </div>
        </div>
      </div>

      <div class="overflow-x-auto max-h-[420px] border rounded-xl mt-2">
        <table id="stockQtyTbl" class="w-full text-gray-800 bg-white border border-gray-300 rounded-lg shadow-md table-auto">
          <thead>
            <tr class="bg-gradient-to-r from-blue-700 via-blue-600 to-blue-700 text-white text-[14px] border-b border-blue-800">
              <th class="p-3 text-left font-semibold">#</th>
              <th class="p-3 text-left font-semibold">Product</th>
              <th class="p-3 text-center font-semibold">Stock Qty</th>
              <th class="p-3 text-center font-semibold">Sales Qty</th>
              <th class="p-3 text-center font-semibold">Total Sales Value (LKR)</th>
              <th class="p-3 text-center font-semibold">Price (LKR)</th>
              <th class="p-3 text-center font-semibold">Discount</th>
              <th class="p-3 text-center font-semibold">Price After Discount</th>
              <th class="p-3 text-center font-semibold">Profit</th>
            </tr>
          </thead>

          <tbody class="text-[12px] font-medium">
            <tr v-if="filteredProducts.length === 0">
              <td colspan="9" class="p-6 text-center text-gray-500 text-lg">
                No products found matching "{{ productSearchQuery }}"
              </td>
            </tr>
            <tr v-for="(p, i) in filteredProducts" :key="p.id ?? i" class="border-b transition duration-200 hover:bg-gray-100">
              <td class="p-3 text-center">{{ i + 1 }}</td>
              <td class="p-3 font-bold">{{ p.name || 'N/A' }}</td>
              <td class="p-3 text-center font-semibold" :class="Number(p.stock_quantity || 0) > 0 ? 'text-green-600' : 'text-red-600'">
                {{ Number(p.stock_quantity || 0) }}
              </td>
              <td class="p-3 text-center">{{ Number(p.sales_qty || 0) }}</td>
              <td class="p-3 text-center">
                {{ (Number(p.sales_qty || 0) * Number(p.selling_price || 0)).toFixed(2) }}
              </td>
              <td class="p-3 text-center">{{ Number(p.selling_price || 0).toFixed(2) }}</td>
              <td class="p-3 text-center">
                <span v-if="Number(p.discount || 0) <= 100">{{ Number(p.discount || 0) }}%</span>
                <span v-else>Rs. {{ Number(p.discount).toFixed(2) }}</span>
              </td>
              <td class="p-3 text-center">{{ priceAfterDiscount(p).toFixed(2) }}</td>
              <td class="p-3 text-center">{{ totalProfit(p).toFixed(2) }} LKR</td>
            </tr>
          </tbody>

          <tfoot class="bg-gray-50 text-[12px] font-semibold">
            <tr>
              <td class="p-3 text-right" colspan="2">Totals:</td>
              <td class="p-3 text-right">{{ totalStockQty.toLocaleString() }}</td>
              <td class="p-3 text-right">{{ totalSalesQty.toLocaleString() }}</td>
              <td class="p-3 text-right"></td>
              <td class="p-3 text-right"></td>
              <td class="p-3 text-right"></td>
              <td class="p-3 text-right"></td>
              <td class="p-3 text-right">
                {{ grandTotalProfit.toFixed(2) }} LKR
              </td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>

    <!-- EXPENSES SECTION -->
    <div class="w-full bg-white border-4 border-black rounded-xl p-6 mt-8">
      <h2 class="text-2xl font-semibold text-slate-700 text-center pb-4">Expenses</h2>

      <div class="flex justify-between items-center pb-4">
        <div class="flex gap-4">
          <button @click="downloadExpensesPDF"
                  class="px-4 py-2 text-md font-semibold text-white bg-orange-600 rounded-lg hover:bg-orange-700 shadow-md">
            Download PDF
          </button>
        </div>

        <div class="flex items-center gap-3">
          <div class="py-2 px-4 border-2 border-red-600 rounded-xl bg-red-100 shadow-sm text-center">
            <p class="text-sm font-extrabold text-black uppercase">
              Total Expenses:
              <span class="text-base font-bold">
                {{ totalExpenseAmount.toFixed(2) }} LKR
              </span>
            </p>
          </div>
          <div class="py-2 px-4 border-2 border-slate-600 rounded-xl bg-slate-100 shadow-sm text-center">
            <p class="text-sm font-extrabold text-black uppercase">
              Records:
              <span class="text-base font-bold">{{ totalExpenseCount }}</span>
            </p>
          </div>
        </div>
      </div>

      <div class="overflow-x-auto overflow-y-auto max-h-[420px] border rounded-xl mt-2">
        <table id="expenseTbl"
               class="w-full text-gray-800 bg-white border border-gray-300 rounded-lg shadow-md table-auto">
          <colgroup>
            <col style="width:60px" />
            <col style="width:220px" />
            <col style="width:160px" />
            <col style="width:140px" />
          </colgroup>

          <thead class="sticky top-0 z-10">
            <tr class="bg-gradient-to-r from-rose-700 via-rose-600 to-rose-700 text-white text-[14px] border-b border-rose-800">
              <th class="p-3 text-center font-semibold">#</th>
              <th class="p-3 text-left font-semibold">Title</th>
              <th class="p-3 text-right font-semibold">Amount (LKR)</th>
              <th class="p-3 text-center font-semibold">Expense Date</th>
            </tr>
          </thead>

          <tbody class="text-[12px] font-medium">
            <tr v-for="(e, i) in expenses" :key="e.id ?? i"
                class="border-b transition duration-200 hover:bg-gray-100">
              <td class="p-3 text-center">{{ i + 1 }}</td>
              <td class="p-3 text-left">{{ e.title || '—' }}</td>
              <td class="p-3 text-left">{{ toMoney(e.amount) }}</td>
              <td class="p-3 text-left">{{ formatDate(e.expense_date) }}</td>
            </tr>
          </tbody>

          <tfoot class="bg-gray-50 text-[12px] font-semibold">
            <tr>
              <td class="p-3 text-right" colspan="2">Total:</td>
              <td class="p-3 text-right">
                {{ totalExpenseAmount.toFixed(2) }}
              </td>
              <td class="p-3"></td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>

    <!-- IN CASH SECTION -->
<div class="w-full bg-white border-4 border-black rounded-xl p-6 mt-8">
  <div class="w-full bg-white rounded-xl p-6">
    <h2 class="text-2xl font-semibold text-slate-700 text-center pb-4">In Cash Records</h2>
  </div>

  <div class="flex justify-between items-center pb-4">
    <div class="flex gap-4">
      <button @click="downloadInCashPDF"
              class="px-4 py-2 text-md font-semibold text-white bg-orange-600 rounded-lg hover:bg-orange-700 shadow-md">
        Download PDF
      </button>
    </div>

    <div class="flex items-center gap-3">
      <div class="py-2 px-4 border-2 border-green-600 rounded-xl bg-green-100 shadow-sm text-center">
        <p class="text-sm font-extrabold text-black uppercase">
          Total In Cash:
          <span class="text-base font-bold">
            {{ toMoney(totalInCashAmount) }} LKR
          </span>
        </p>
      </div>
      <div class="py-2 px-4 border-2 border-slate-600 rounded-xl bg-slate-100 shadow-sm text-center">
        <p class="text-sm font-extrabold text-black uppercase">
          Records:
          <span class="text-base font-bold">{{ totalInCashCount }}</span>
        </p>
      </div>
    </div>
  </div>

  <div class="overflow-x-auto overflow-y-auto max-h-[420px] border rounded-xl mt-2">
    <table id="inCashTbl" class="w-full text-gray-800 bg-white border border-gray-300 rounded-lg shadow-md table-auto">
      <thead class="sticky top-0 z-10">
        <tr class="bg-gradient-to-r from-green-700 via-green-600 to-green-700 text-white text-[14px] border-b border-green-800">
          <th class="p-3 text-center font-semibold">#</th>
          <th class="p-3 text-center font-semibold">Amount (LKR)</th>
          <th class="p-3 text-center font-semibold">Note</th>
          <th class="p-3 text-center font-semibold">Date & Time</th>
        </tr>
      </thead>

      <tbody class="text-[12px] font-medium">
        <tr v-for="(record, i) in inCashRecords" :key="record.id ?? i"
            class="border-b transition duration-200 hover:bg-gray-100">
          <td class="p-3 text-center">{{ i + 1 }}</td>
          <td class="p-3 text-center">{{ toMoney(record.amount) }}</td>
          <td class="p-3 text-center">{{ record.note || '—' }}</td>
          <td class="p-3 text-center">{{ formatDateTime(record.created_at) }}</td>
        </tr>
      </tbody>

      <tfoot class="bg-gray-50 text-[12px] font-semibold">
        <tr>
          <td class="p-3 text-right" colspan="2">Total:</td>
          <td class="p-3 text-center">
            {{ toMoney(totalInCashAmount) }} LKR
          </td>
          <td class="p-3"></td>
        </tr>
      </tfoot>
    </table>
  </div>
</div>

    <!-- Return Items Table -->
    <div class="flex md:flex-row flex-col items-center justify-center w-full h-full md:space-x-4 md:space-y-0 space-y-4">
      <div class="w-full bg-white border-4 border-black rounded-xl p-6">
        <h2 class="text-2xl font-semibold text-slate-700 text-center pb-4">
          Return Items Table
        </h2>

        <div class="flex justify-between items-center pb-4">
          <div class="flex gap-4">
            <button
              @click="downloadPDFTableReturn"
              class="px-4 py-2 text-md font-semibold text-white bg-orange-600 rounded-lg hover:bg-orange-700 shadow-md"
            >
              Download PDF
            </button>
          </div>
        </div>

        <div class="overflow-x-auto max-h-[400px] border rounded-xl mt-4">
          <table
            id="StockTransactionTbl"
            class="w-full text-gray-800 bg-white border border-gray-300 rounded-lg shadow-md table-auto"
          >
            <thead>
              <tr class="bg-gradient-to-r from-blue-700 via-blue-600 to-blue-700 text-white text-[14px] border-b border-blue-800">
                <th class="p-3 text-left font-semibold">#</th>
                <th class="p-3 text-center font-semibold">Product Name</th>
                <th class="p-3 text-center font-semibold">Transaction Date</th>
                <th class="p-3 text-center font-semibold">Quantity</th>
                <th class="p-3 text-center font-semibold">Selling Price</th>
                <th class="p-3 text-center font-semibold">Total Selling Price</th> 
              </tr>
            </thead>

            <tbody class="text-[12px] font-medium">
              <tr
                v-for="(stock, index) in stockTransactionsReturn"
                :key="index"
                class="border-b transition duration-200 hover:bg-gray-100"
              >
                <td class="p-3 text-center">{{ index + 1 }}</td>
                <td class="p-3 text-center">{{ stock.product?.name }}</td>
                <td class="p-3 text-center">{{ stock.transaction_date }}</td>
                <td class="p-3 text-center">{{ stock.quantity}}</td>
                <td class="p-3 text-center">{{ stock.product?.selling_price}}</td>
                <td class="p-3 text-center">
                  {{ (stock.quantity * stock.product?.selling_price).toLocaleString() }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

  </div>

  <!-- IN CASH MODAL -->
  <div v-if="showInCashModal" 
       class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
       @click.self="closeInCashModal">
    <div class="bg-white rounded-xl shadow-2xl p-8 w-full max-w-md">
      <h3 class="text-2xl font-bold text-gray-800 mb-6">Add In Cash</h3>
      
      <div class="space-y-4">
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-2">
            Amount (LKR) <span class="text-red-500">*</span>
          </label>
          <input 
            v-model="inCashForm.amount"
            type="number"
            step="0.01"
            min="0"
            placeholder="Enter amount"
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
            :disabled="isSubmittingInCash"
          />
        </div>

        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-2">
            Note (Optional)
          </label>
          <textarea 
            v-model="inCashForm.note"
            rows="3"
            placeholder="Add a note..."
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
            :disabled="isSubmittingInCash"
          ></textarea>
        </div>
      </div>

      <div class="flex justify-end gap-3 mt-6">
        <button 
          @click="closeInCashModal"
          :disabled="isSubmittingInCash"
          class="px-6 py-2 text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 font-semibold disabled:opacity-50">
          Cancel
        </button>
        <button 
          @click="submitInCash"
          :disabled="isSubmittingInCash"
          class="px-6 py-2 text-white bg-green-600 rounded-lg hover:bg-green-700 font-semibold disabled:opacity-50">
          {{ isSubmittingInCash ? 'Saving...' : 'Save' }}
        </button>
      </div>
    </div>
  </div>

  <Footer />
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { Doughnut, Pie } from "vue-chartjs";
import { Link, router, Head } from "@inertiajs/vue3";
import Header from "@/Components/custom/Header.vue";
import Footer from "@/Components/custom/Footer.vue";
import Banner from "@/Components/Banner.vue";
import jsPDF from "jspdf";
import "jspdf-autotable";
import axios from "axios";
import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  ArcElement,
  CategoryScale,
  LinearScale,
  BarElement,
} from "chart.js";

ChartJS.register(Title, Tooltip, Legend, ArcElement, CategoryScale, LinearScale, BarElement);

// Props
const props = defineProps({
  products: { type: Array, required: true },
  sales: { type: Array, required: true },
  totalSaleAmount: { type: Number, required: true },
  averageTransactionValue: { type: Number, required: true },
  netProfit: { type: Number, required: true },
  totalTransactions: { type: Number, required: true },
  totalDiscountLkr: { type: Number, required: true },
  totalCustomDiscountLkr: { type: Number, required: true },
  totalCustomer: { type: Number, required: true },
  startDate: { type: String, default: "" },
  endDate: { type: String, default: "" },
  categorySales: { type: Object, required: true },
  employeeSalesSummary: { type: Object, required: true },
  expenses: { type: Array, required: true },
  totalExpenseAmount: { type: Number, required: true },
  totalExpenseCount: { type: Number, required: true },
  stockTransactionsReturn: { type: Array, default: () => [] },
  inCashRecords: { type: Array, default: () => [] },
  totalInCashAmount: { type: Number, default: 0 },
  totalInCashCount: { type: Number, default: 0 },
});

// State
const startDate = ref(props.startDate);
const endDate = ref(props.endDate);
const products = ref(props.products);
const stockTransactionsReturn = ref(props.stockTransactionsReturn);
const sales = ref(props.sales);
const expenses = ref(props.expenses);
const totalExpenseAmount = ref(props.totalExpenseAmount || 0);
const totalExpenseCount = ref(props.totalExpenseCount || 0);
const productSearchQuery = ref('');

// In Cash State
const showInCashModal = ref(false);
const inCashForm = ref({
  amount: '',
  note: ''
});
const isSubmittingInCash = ref(false);
const inCashRecords = ref(props.inCashRecords || []);
const totalInCashAmount = ref(props.totalInCashAmount || 0);
const totalInCashCount = ref(props.totalInCashCount || 0);

// Formatting helpers
const toMoney = (n) => (Number(n || 0)).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
const formatDate = (d) => (d ? new Date(d).toLocaleDateString() : "");
const formatDateTime = (d) => (d ? new Date(d).toLocaleString() : "");

// In Cash Functions
const openInCashModal = () => {
  showInCashModal.value = true;
  inCashForm.value = { amount: '', note: '' };
};

const closeInCashModal = () => {
  showInCashModal.value = false;
  inCashForm.value = { amount: '', note: '' };
};

const submitInCash = async () => {
  if (!inCashForm.value.amount || inCashForm.value.amount <= 0) {
    alert('Please enter a valid amount');
    return;
  }

  isSubmittingInCash.value = true;

  try {
    await axios.post('/in-cash', {
      amount: parseFloat(inCashForm.value.amount),
      note: inCashForm.value.note
    });

    alert('In Cash added successfully!');
    closeInCashModal();
    router.reload({ preserveScroll: true });
  } catch (error) {
    console.error('Error adding in cash:', error);
    alert('Failed to add in cash. Please try again.');
  } finally {
    isSubmittingInCash.value = false;
  }
};

const deleteInCashRecord = async (id) => {
  if (!confirm('Are you sure you want to delete this record?')) {
    return;
  }

  try {
    await axios.delete(`/in-cash/${id}`);
    alert('Record deleted successfully!');
    router.reload({ preserveScroll: true });
  } catch (error) {
    console.error('Error deleting record:', error);
    alert('Failed to delete record.');
  }
};

// Totals (inventory cards)
const totalQty = computed(() => products.value.reduce((sum, p) => sum + (p.stock_quantity || 0), 0));
const totalSellingPrice = computed(() =>
  products.value.reduce((sum, p) => sum + (p.stock_quantity || 0) * (parseFloat(p.selling_price) || 0), 0)
);
const totalRetailValue = computed(() =>
  products.value.reduce((sum, p) => sum + (p.stock_quantity || 0) * (parseFloat(p.cost_price) || 0), 0)
);

// Price helpers for product table
const priceAfterDiscount = (product) => {
  const price = Number(product.selling_price || 0);
  const discount = Number(product.discount || 0);
  return discount <= 100 ? price * (1 - discount / 100) : price - discount;
};
const profitPerUnit = (product) => priceAfterDiscount(product) - Number(product.cost_price || 0);
const totalProfit = (product) => profitPerUnit(product) * Number(product.sales_qty || 0);

// Filtered products based on search query
const filteredProducts = computed(() => {
  if (!productSearchQuery.value.trim()) {
    return products.value;
  }
  const query = productSearchQuery.value.toLowerCase();
  return products.value.filter(p => 
    (p.name || '').toLowerCase().includes(query) ||
    (p.barcode || '').toLowerCase().includes(query)
  );
});

// Product table totals
const totalStockQty = computed(() => filteredProducts.value.reduce((s, p) => s + Number(p.stock_quantity || 0), 0));
const totalSalesQty = computed(() => filteredProducts.value.reduce((s, p) => s + Number(p.sales_qty || 0), 0));
const grandTotalProfit = computed(() => filteredProducts.value.reduce((s, p) => s + totalProfit(p), 0));
const totalProductCount = computed(() => filteredProducts.value.length);
const totalProductWorth = computed(() => filteredProducts.value.reduce((s, p) => s + (Number(p.stock_quantity || 0) * Number(p.selling_price || 0)), 0));

// Date filter
const filterData = () => {
  if (startDate.value && endDate.value && new Date(startDate.value) > new Date(endDate.value)) {
    alert("Start date cannot be greater than end date.");
    return;
  }
  router.get(
    route("reports.index"),
    { start_date: startDate.value, end_date: endDate.value },
    { preserveScroll: true, preserveState: false }
  );
};

// Charts data
const sortDescending = (data) =>
  Object.entries(data).sort((a, b) => b[1] - a[1]).reduce((acc, [k, v]) => ((acc[k] = v), acc), {});

const productQuantities = computed(() => {
  const quantities = {};
  props.sales.forEach((sale) => {
    sale.sale_items.forEach((item) => {
      const name = item.product && item.product.name ? item.product.name : "N/A";
      quantities[name] = (quantities[name] || 0) + item.quantity;
    });
  });
  return sortDescending(quantities);
});

const chartData = computed(() => ({
  labels: Object.keys(productQuantities.value),
  datasets: [{ data: Object.values(productQuantities.value),
    backgroundColor: ["#FF6384","#36A2EB","#FFCE56","#4BC0C0","#9966FF","#28a745","#ffc107","#17a2b8","#e83e8c","#fd7e14","#6610f2","#6f42c1","#dc3545","#adb5bd","#20c997","#ffc93c","#6a0572","#8ac926","#ff595e","#198754"] }],
}));
const chartOptions = { responsive: true, plugins: { legend: { display: true, position: "bottom" } } };

const paymentMethodTotals = computed(() => {
  const totals = {};
  props.sales.forEach((s) => {
    const m = s.payment_method;
    totals[m] = (totals[m] || 0) + parseFloat(s.total_amount);
  });
  return sortDescending(totals);
});
const chartData1 = computed(() => ({
  labels: Object.keys(paymentMethodTotals.value),
  datasets: [{ data: Object.values(paymentMethodTotals.value),
    backgroundColor: ["#FF6384","#36A2EB","#FFCE56","#4BC0C0","#9966FF","#28a745","#ffc107","#17a2b8","#e83e8c","#fd7e14","#6610f2","#6f42c1","#dc3545","#adb5bd","#20c997","#ffc93c","#6a0572","#8ac926","#ff595e","#198754"] }],
}));
const chartOptions1 = {
  responsive: true,
  plugins: { legend: { display: true, position: "bottom" },
    tooltip: { callbacks: { label: (c) => `LKR ${(+c.raw || 0).toLocaleString()}` } } },
};

const sortedEmployeeSales = computed(() =>
  Object.fromEntries(Object.entries(props.employeeSalesSummary).sort(([, a], [, b]) => b["Total Sales Amount"] - a["Total Sales Amount"]))
);
const chartData4 = computed(() => ({
  labels: Object.keys(sortedEmployeeSales.value),
  datasets: [{ data: Object.values(sortedEmployeeSales.value).map((e) => e["Total Sales Amount"]),
    backgroundColor: ["#6610f2","#36A2EB","#8ac926","#ff595e","#198754","#6f42c1","#dc3545","#adb5bd","#20c997","#28a745","#ffc107","#17a2b8","#e83e8c","#fd7e14","#FF6384","#FFCE56","#4BC0C0","#9966FF","#ffc93c"] }],
}));
const chartOptions4 = { responsive: true, plugins: { legend: { display: true, position: "bottom" },
  tooltip: { callbacks: { label: (c) => `LKR ${(+c.raw).toLocaleString()}` } } } };

// Sales Table helpers
const saleQty = (s) => (Array.isArray(s.sale_items) ? s.sale_items.reduce((n, it) => n + Number(it.quantity || 0), 0) : 0);

const customDiscountLkr = (s) => {
  const gross = Number(s.total_amount || 0);
  const val = Number(s.custom_discount || 0);
  const type = s.custom_discount_type || 'fixed';
  return type === 'percent' ? (gross * val / 100) : val;
};

const discountLkr = (s) => customDiscountLkr(s);

const discountDisplay = (s) => {
  const parts = [];
  const gross = Number(s.total_amount || 0);
  const customVal = Number(s.custom_discount || 0);
  const customType = s.custom_discount_type || "fixed";

  if (customVal > 0) {
    if (customType === "percent") {
      const lkr = (gross * customVal) / 100;
      parts.push(`${customVal}% (${toMoney(lkr)} LKR)`);
    } else {
      parts.push(`${toMoney(customVal)} LKR`);
    }
  }

  return parts.join("  |  ");
};

const saleProfit = (s) => {
  const gross = Number(s.total_amount || 0);
  const discount =
    (s.custom_discount_type || "fixed") === "percent"
      ? (gross * Number(s.custom_discount || 0)) / 100
      : Number(s.custom_discount || 0);

  const net = gross - discount;
  const cost = Number(s.total_cost || 0);
  return net - cost;
};

// Totals
const salesTotalQty = computed(() => sales.value.reduce((a, s) => a + saleQty(s), 0));
const salesGrossTotal = computed(() =>
  sales.value.reduce((a, s) => {
    const total = Number(s.total_amount || 0);
    let customDiscountLkr = 0;
    if (s.custom_discount_type === "percent") {
      customDiscountLkr = (total * Number(s.custom_discount || 0)) / 100;
    } else {
      customDiscountLkr = Number(s.custom_discount || 0);
    }
    return a + (total - customDiscountLkr);
  }, 0)
);

const salesDiscountTotal = computed(() =>
  sales.value.reduce((a, s) => a + discountLkr(s), 0)
);

const salesCostTotal = computed(() => sales.value.reduce((a, s) => a + Number(s.total_cost || 0), 0));

const salesProfitTotal = computed(() =>
  sales.value.reduce((sum, s) => sum + saleProfit(s), 0)
);

// Date label for PDFs
const dateRangeLabel = computed(() => {
  const s = startDate.value ? new Date(startDate.value).toLocaleDateString() : "All";
  const e = endDate.value ? new Date(endDate.value).toLocaleDateString() : "All";
  return `${s} — ${e}`;
});

// Chart PDFs
const downloadPDF = () => {
  const doc = new jsPDF();
  doc.text("Top Employee Sales", 14, 10);
  const rows = Object.entries(sortedEmployeeSales.value).map(([employee, entry]) => [employee, entry["Total Sales Amount"]]);
  doc.autoTable({ head: [["Employee", "Total Sales Amount"]], body: rows, startY: 20 });
  doc.save("EmployeeSales.pdf");
};

const downloadPDF2 = () => {
  const doc = new jsPDF();
  doc.text("Product Quantities", 14, 10);
  const rows = Object.entries(productQuantities.value).map(([product, qty]) => [product, qty]);
  doc.autoTable({ head: [["Product Name", "Quantity"]], body: rows, startY: 20 });
  doc.save("ProductQuantities.pdf");
};

const downloadPDF3 = () => {
  const doc = new jsPDF();
  doc.text("Payment Method Totals", 14, 10);
  const rows = Object.entries(paymentMethodTotals.value).map(([m, t]) => [m, `LKR ${t.toLocaleString()}`]);
  doc.autoTable({ head: [["Payment Method", "Total Amount"]], body: rows, startY: 20 });
  doc.save("PaymentMethodTotals.pdf");
};

// Sales table PDF
const downloadSalesTablePDF = () => {
  const doc = new jsPDF("l", "mm", "a4");
  const now = new Date();

  const rowData = sales.value.map((s, i) => {
    const date = formatDate(s.sale_date);
    const orderNumber = s.order_id ? s.order_id : `Service - ${s.service_name || ""}`;
    const customer = s.customer?.name ?? "N/A";
    const qty = saleQty(s);
    const grossNet = Number(s.total_amount || 0) - customDiscountLkr(s);
    const discounts = discountDisplay(s);
    const cost = Number(s.total_cost || 0);
    const profit = saleProfit(s);

    return [
      i + 1,
      date,
      orderNumber,
      customer,
      qty.toString(),
      toMoney(grossNet),
      discounts,
      toMoney(cost),
      toMoney(profit),
    ];
  });

  const totalQty = sales.value.reduce((a, s) => a + saleQty(s), 0);
  const totalGrossNet = sales.value.reduce((a, s) => a + (Number(s.total_amount || 0) - customDiscountLkr(s)), 0);
  const totalDiscounts = sales.value.reduce((a, s) => a + customDiscountLkr(s), 0);
  const totalCost = sales.value.reduce((a, s) => a + Number(s.total_cost || 0), 0);
  const totalProfit = sales.value.reduce((a, s) => a + saleProfit(s), 0);

  doc.setFontSize(16);
  doc.text("Sales Report", 14, 12);
  doc.setFontSize(10);
  doc.text(`Date range: ${dateRangeLabel.value} • Generated: ${now.toLocaleString()}`, 14, 18);

  const head = [[
    "#",
    "Date",
    "Order Number",
    "Customer",
    "Qty",
    "Final Selling Price(LKR)",
    "Discounts",
    "Cost (LKR)",
    "Profit (LKR)",
  ]];

  const foot = [[
    { content: "Totals:", colSpan: 4, styles: { halign: "right", fontStyle: "bold" } },
    { content: totalQty.toLocaleString(), styles: { halign: "center", fontStyle: "bold" } },
    toMoney(totalGrossNet),
    toMoney(totalDiscounts),
    toMoney(totalCost),
    toMoney(totalProfit),
  ]];

  doc.autoTable({
    head,
    body: rowData,
    foot,
    startY: 24,
    theme: "striped",
    styles: { fontSize: 9, cellPadding: 2 },
    headStyles: { fillColor: [51, 65, 85], textColor: 255 },
    columnStyles: {
      0: { cellWidth: 10 },
      1: { cellWidth: 22 },
      2: { cellWidth: 36 },
      3: { cellWidth: 36 },
      4: { cellWidth: 14, halign: "center" },
      5: { cellWidth: 28, halign: "right" },
      6: { cellWidth: 28, halign: "right" },
      7: { cellWidth: 28, halign: "right" },
      8: { cellWidth: 28, halign: "right" },
    },
    margin: { top: 18, left: 8, right: 8 },
  });

  const safe = (s) => String(s).replace(/[^\dA-Za-z-]/g, "_");
  doc.save(`Sales_Report_${safe(dateRangeLabel.value)}.pdf`);
};

// Stock table PDF
const downloadStockTablePDF = () => {
  const tableEl = document.getElementById("stockQtyTbl");
  if (!tableEl) return;

  const toNumber = (txt) => Number(String(txt).replace(/[^\d.\-]/g, "")) || 0;
  const rows = [];
  const $ = window.$;

  if ($ && $.fn && $.fn.dataTable && $.fn.dataTable.isDataTable("#stockQtyTbl")) {
    const dt = $("#stockQtyTbl").DataTable();
    dt.rows({ search: "applied" }).every(function () {
      const tr = this.node();
      const cells = Array.from(tr.querySelectorAll("td")).map(td => (td.textContent || "").trim());
      rows.push([cells[0], cells[1], cells[2], cells[3], cells[4], cells[5], cells[6], cells[7]]);
    });
  } else {
    tableEl.querySelectorAll("tbody tr").forEach((tr) => {
      const cells = Array.from(tr.querySelectorAll("td")).map(td => (td.textContent || "").trim());
      rows.push([cells[0], cells[1], cells[2], cells[3], cells[4], cells[5], cells[6], cells[7]]);
    });
  }

  const filteredTotals = rows.reduce(
    (acc, r) => {
      acc.qty += Number(r[2]) || 0;
      acc.sales += toNumber(r[3]);
      acc.profit += toNumber(r[7]);
      return acc;
    },
    { qty: 0, sales: 0, profit: 0 }
  );

  const doc = new jsPDF("l", "mm", "a4");
  doc.setFontSize(16);
  doc.text("Top Products Stock Report", 14, 12);
  doc.setFontSize(10);
  doc.text(`Date range: ${dateRangeLabel.value} • Generated: ${new Date().toLocaleString()}`, 14, 18);

  const head = [["#","Product","Sales Qty","Total Sales Value (LKR)","Price (LKR)","Discount","Price After Discount","Profit"]];

  doc.autoTable({
    head, body: rows, startY: 24, theme: "striped",
    styles: { fontSize: 9 }, headStyles: { fillColor: [33, 102, 197], textColor: 255 },
    columnStyles: {
      0: { cellWidth: 10 }, 1: { cellWidth: 60 }, 2: { cellWidth: 22, halign: "right" },
      3: { cellWidth: 34, halign: "right" }, 4: { cellWidth: 28, halign: "right" },
      5: { cellWidth: 28, halign: "center" }, 6: { cellWidth: 34, halign: "right" }, 7: { cellWidth: 34, halign: "right" },
    },
    margin: { top: 18, left: 8, right: 8 },
  });

  const totalsRow = [
    "", "Totals:", filteredTotals.qty.toLocaleString(),
    filteredTotals.sales.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + " LKR",
    "—", "—", "—",
    filteredTotals.profit.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + " LKR",
  ];

  doc.autoTable({
    body: [totalsRow], startY: doc.lastAutoTable ? doc.lastAutoTable.finalY + 2 : 24,
    theme: "plain", styles: { fontSize: 10, fontStyle: "bold" },
    columnStyles: {
      0: { cellWidth: 10 }, 1: { cellWidth: 60, halign: "right" }, 2: { cellWidth: 22, halign: "right" },
      3: { cellWidth: 34, halign: "right" }, 4: { cellWidth: 28 }, 5: { cellWidth: 28 }, 6: { cellWidth: 34 },
      7: { cellWidth: 34, halign: "right" },
    },
    margin: { left: 8, right: 8 },
  });

  const safe = (s) => s.replace(/[^\dA-Za-z-]/g, "_");
  doc.save(`Top_Products_Stock_${safe(dateRangeLabel.value)}.pdf`);
};

// Expenses PDF
const downloadExpensesPDF = () => {
  const doc = new jsPDF("p", "mm", "a4");
  const now = new Date();

  const fmtDate = (d) => (d ? new Date(d).toLocaleDateString() : "—");

  const rows = expenses.value.map((e, i) => [
    i + 1,
    e.title || "—",
    toMoney(e.amount),
    fmtDate(e.expense_date),
  ]);

  const total = expenses.value.reduce((sum, e) => sum + Number(e.amount || 0), 0);

  doc.setFontSize(16);
  doc.text("Expenses Report", 14, 12);
  doc.setFontSize(10);
  doc.text(`Date range: ${dateRangeLabel.value} • Generated: ${now.toLocaleString()}`, 14, 18);

  doc.autoTable({
    head: [["#", "Title", "Amount (LKR)", "Expense Date"]],
    body: rows,
    foot: [[
      "",
      { content: "Total:", styles: { halign: "right", fontStyle: "bold" } },
      { content: `${toMoney(total)} LKR`, styles: { halign: "center", fontStyle: "bold" } },
      "",
    ]],
    startY: 24,
    theme: "striped",
    styles: { fontSize: 9, cellPadding: 2 },
    headStyles: { fillColor: [190, 18, 60], textColor: 255 },
    columnStyles: {
      0: { cellWidth: 12, halign: "center" },
      1: { cellWidth: 106 },
      2: { cellWidth: 40, halign: "center" },
      3: { cellWidth: 40, halign: "center" },
    },
    margin: { top: 18, left: 8, right: 8 },
  });

  const safe = (s) => String(s).replace(/[^\dA-Za-z-]/g, "_");
  doc.save(`Expenses_${safe(dateRangeLabel.value)}.pdf`);
};

// In Cash PDF
const downloadInCashPDF = () => {
  const doc = new jsPDF("p", "mm", "a4");
  const now = new Date();

  const rows = inCashRecords.value.map((record, i) => [
    i + 1,
    toMoney(record.amount),
    record.note || "—",
    formatDateTime(record.created_at),
  ]);

  const total = inCashRecords.value.reduce((sum, r) => sum + Number(r.amount || 0), 0);

  doc.setFontSize(16);
  doc.text("In Cash Report", 14, 12);
  doc.setFontSize(10);
  doc.text(`Date range: ${dateRangeLabel.value} • Generated: ${now.toLocaleString()}`, 14, 18);

  doc.autoTable({
    head: [["#", "Amount (LKR)", "Note", "Date & Time"]],
    body: rows,
    foot: [[
      "",
      { content: `Total: ${toMoney(total)} LKR`, styles: { halign: "right", fontStyle: "bold" }, colSpan: 3 },
    ]],
    startY: 24,
    theme: "striped",
    styles: { fontSize: 9, cellPadding: 2 },
    headStyles: { fillColor: [34, 139, 34], textColor: 255 },
    columnStyles: {
      0: { cellWidth: 12, halign: "center" },
      1: { cellWidth: 40, halign: "right" },
      2: { cellWidth: 80 },
      3: { cellWidth: 46, halign: "center" },
    },
    margin: { top: 18, left: 8, right: 8 },
  });

  const safe = (s) => String(s).replace(/[^\dA-Za-z-]/g, "_");
  doc.save(`InCash_Report_${safe(dateRangeLabel.value)}.pdf`);
};

// Return Items PDF
const downloadPDFTableReturn = () => {
  const doc = new jsPDF("p", "mm", "a4");

  doc.setFontSize(18);
  doc.text("Return Items Table", 14, 15);

  const tableColumn = [
    "#",
    "Product Name",
    "Transaction Date",
    "Quantity",
    "Selling Price",
    "Total Selling Price",
  ];

  const tableRows = stockTransactionsReturn.value.map((stock, index) => [
    index + 1,
    stock.product?.name || "N/A",
    stock.transaction_date || "N/A",
    stock.quantity || 0,
    Number(stock.product?.selling_price) || 0,
    (stock.quantity * (stock.product?.selling_price || 0)).toFixed(2),
  ]);

  const totalSum = tableRows.reduce((sum, row) => sum + Number(row[5]), 0);
  tableRows.push(["", "Total", "", "", "", totalSum.toFixed(2)]);

  doc.autoTable({
    head: [tableColumn],
    body: tableRows,
    startY: 25,
    theme: "striped",
    styles: { fontSize: 10 },
    headStyles: { fillColor: [44, 62, 80] },
    columnStyles: {
      0: { cellWidth: 20 },
      1: { cellWidth: 50 },
      2: { cellWidth: 35 },
      3: { cellWidth: 25 },
      4: { cellWidth: 30 },
      5: { cellWidth: 30 },
    },
    margin: { left: 5, right: 10, top: 20 },
  });

  doc.save("Return_Items.pdf");
};

// DataTables init
onMounted(() => {
  const jq = window.$;

  // Stock table
  const $stock = jq && jq("#stockQtyTbl");
  if ($stock && jq.fn.dataTable) {
    if (jq.fn.dataTable.isDataTable($stock)) $stock.DataTable().destroy();
    const dt = $stock.DataTable({
      dom: "Bfrtip",
      paging: false,
      buttons: [],
      columnDefs: [{ targets: 0, searchable: false, orderable: false }],
      initComplete: function () {
        const $input = jq("div.dataTables_filter input");
        $input.attr("placeholder", "Search stock...");
        $input.on("keypress", function (e) {
          if (e.which == 13) dt.search(this.value).draw();
        });
      },
      language: { search: "" },
    });
  }

  // Expenses table
  const $exp = jq && jq("#expenseTbl");
  if ($exp && jq.fn.dataTable) {
    if (jq.fn.dataTable.isDataTable($exp)) $exp.DataTable().destroy();
    const de = $exp.DataTable({
      dom: "Bfrtip",
      paging: false,
      buttons: [],
      columnDefs: [{ targets: 0, searchable: false, orderable: false }],
      initComplete: function () {
        const $input = jq("div.dataTables_filter input");
        $input.attr("placeholder", "Search expenses...");
        $input.on("keypress", function (e) {
          if (e.which == 13) de.search(this.value).draw();
        });
      },
      language: { search: "" },
    });
  }

  // In Cash table
  const $inCash = jq && jq("#inCashTbl");
  if ($inCash && jq.fn.dataTable) {
    if (jq.fn.dataTable.isDataTable($inCash)) $inCash.DataTable().destroy();
    const di = $inCash.DataTable({
      dom: "Bfrtip",
      paging: false,
      buttons: [],
      columnDefs: [{ targets: 0, searchable: false, orderable: false }],
      initComplete: function () {
        const $input = jq("div.dataTables_filter input");
        $input.attr("placeholder", "Search in cash...");
        $input.on("keypress", function (e) {
          if (e.which == 13) di.search(this.value).draw();
        });
      },
      language: { search: "" },
    });
  }
});
</script>

<style>
/* DataTables cosmetics */
.dataTables_wrapper .dataTables_paginate {
  display: flex; justify-content: center; align-items: center; margin-top: 20px;
}
#salesTbl_filter, #stockQtyTbl_filter, #expenseTbl_filter, #inCashTbl_filter {
  display: flex; justify-content: flex-end; align-items: center; margin-bottom: 16px; float: left;
}
#salesTbl_filter label, #stockQtyTbl_filter label, #expenseTbl_filter label, #inCashTbl_filter label { 
  font-size: 17px; color: #000000; display: flex; align-items: center; 
}
#salesTbl_filter input[type="search"], #stockQtyTbl_filter input[type="search"], 
#expenseTbl_filter input[type="search"], #inCashTbl_filter input[type="search"] {
  font-weight: 400; padding: 9px 15px; font-size: 14px; color: #000000cc;
  border: 1px solid rgb(209 213 219); border-radius: 5px; background: #fff;
  outline: none; transition: all 0.5s ease;
}
#salesTbl_filter input[type="search"]:focus, #stockQtyTbl_filter input[type="search"]:focus, 
#expenseTbl_filter input[type="search"]:focus, #inCashTbl_filter input[type="search"]:focus {
  border: 1px solid #4b5563; box-shadow: none;
}
.dataTables_wrapper { margin-bottom: 10px; }
</style>

<style scoped>
.chart-container {
  display: flex; flex-direction: column; align-items: center; justify-content: center;
  width: 100%; height: calc(100% - 50px); position: relative;
}
thead { position: sticky; top: 0; z-index: 10; }
.max-h-64 { max-height: 16rem; }
</style>