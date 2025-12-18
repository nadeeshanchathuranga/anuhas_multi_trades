<template>

  <Head title="Credit Payments" />
  <Banner />

  <div class="flex flex-col items-center justify-start min-h-screen py-8 space-y-8 bg-gray-100 md:px-36 px-16">
    <Header />

    <!-- Flash Messages -->
    <div class="w-full md:w-5/6 space-y-2">
      <div v-if="successMessage" class="p-3 text-green-800 bg-green-200 rounded">
        {{ successMessage }}
      </div>
      <div v-if="errorMessage" class="p-3 text-red-800 bg-red-200 rounded">
        {{ errorMessage }}
      </div>
    </div>

    <div class="w-full md:w-5/6 py-12 space-y-24">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <p class="text-3xl italic font-bold text-black">
          <span class="px-4 py-1 mr-3 text-white bg-black rounded-xl">{{ creditBills?.length || 0 }}</span>
          <span class="text-xl">/ Total Credit Bills</span>
        </p>
      </div>

      <!-- Header -->
      <div class="flex w-full justify-between items-center">
        <div class="flex items-center w-full h-16 space-x-4">
          <Link href="/"><img src="/images/back-arrow.png" class="w-14 h-14" /></Link>
          <p class="text-4xl font-bold tracking-wide text-black uppercase">Credit Bills & Payments</p>
        </div>
      </div>

      <!-- Credit Bills Table -->
      <div v-if="creditBills && creditBills.length" class="overflow-x-auto w-full">
        <table id="CreditBillsTable"
          class="min-w-[900px] w-full text-gray-700 bg-white border border-gray-300 rounded-lg shadow-md table-auto">
          <thead>
            <tr
              class="bg-gradient-to-r from-blue-600 via-blue-500 to-blue-600 text-[16px] text-white border-b border-blue-700">
              <th class="p-3 md:p-4 font-semibold uppercase text-left">#</th>
              <th class="p-3 md:p-4 font-semibold uppercase text-left">Order ID</th>
              <th class="p-3 md:p-4 font-semibold uppercase text-left">Customer</th>
              <th class="p-3 md:p-4 font-semibold uppercase text-left">Total Amount</th>
              <th class="p-3 md:p-4 font-semibold uppercase text-left">Paid Amount</th>
              <th class="p-3 md:p-4 font-semibold uppercase text-left">Pending Amount</th>
              <th class="p-3 md:p-4 font-semibold uppercase text-left">Status</th>
              <th class="p-3 md:p-4 font-semibold uppercase text-left">Date</th>
              <th class="p-3 md:p-4 font-semibold uppercase text-left">Actions</th>
            </tr>
          </thead>
          <tbody>
            <template v-for="(bill, index) in creditBills" :key="bill.id">
              <tr class="hover:bg-gray-50" :class="{ 'bg-blue-50': expandedBillId === bill.id }">
                <td class="px-2 md:px-6 py-2 md:py-3 whitespace-nowrap">{{ index + 1 }}</td>
                <td class="px-2 md:px-6 py-2 md:py-3 whitespace-nowrap">{{ bill.order_id }}</td>
                <td class="px-2 md:px-6 py-2 md:py-3 whitespace-nowrap">{{ bill.customer_name }}</td>
                <td class="px-2 md:px-6 py-2 md:py-3 whitespace-nowrap">LKR {{ bill.total_amount }}</td>
                <td class="px-2 md:px-6 py-2 md:py-3 whitespace-nowrap">LKR {{ bill.paid_amount }}</td>
                <td class="px-2 md:px-6 py-2 md:py-3 whitespace-nowrap">LKR {{ bill.pending_amount }}</td>
                <td class="px-2 md:px-6 py-2 md:py-3 whitespace-nowrap">
                  <span :class="{
                    'px-2 py-1 rounded text-xs font-semibold': true,
                    'bg-red-100 text-red-800': bill.status === 'Pending',
                    'bg-yellow-100 text-yellow-800': bill.status === 'Partial',
                    'bg-green-100 text-green-800': bill.status === 'Completed'
                  }">
                    {{ bill.status }}
                  </span>
                </td>
                <td class="px-2 md:px-6 py-2 md:py-3 whitespace-nowrap">{{ bill.sale_date }}</td>
                <td class="px-2 md:px-6 py-2 md:py-3 whitespace-nowrap space-x-2">
                  <button class="px-3 py-1 text-sm text-white bg-green-600 rounded hover:bg-green-700"
                    @click="toggleViewDetails(bill)">
                    {{ expandedBillId === bill.id ? 'Hide' : 'View' }}
                  </button>
                  <button v-if="bill.status !== 'Completed' && HasRole(['Admin', 'Cashier'])"
                    class="px-3 py-1 text-sm text-white bg-blue-600 rounded hover:bg-blue-700"
                    @click="togglePaymentForm(bill)">
                    {{ showPaymentForm && expandedBillId === bill.id ? 'Cancel' : 'Add Payment' }}
                  </button>
                </td>
              </tr>

              <!-- Expanded Details Row -->
              <tr v-if="expandedBillId === bill.id" :key="`${bill.id}-details`">
                <td colspan="9" class="bg-gray-50 p-6">
                  <!-- View Details Section -->
                  <div v-if="!showPaymentForm" class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-xl font-bold mb-4 text-gray-800">Credit Bill Details - {{ bill.order_id }}</h3>

                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-6">
                      <div>
                        <p class="text-sm text-gray-600">Customer</p>
                        <p class="font-semibold">{{ bill.customer_name }}</p>
                      </div>
                      <div>
                        <p class="text-sm text-gray-600">Employee</p>
                        <p class="font-semibold">{{ bill.employee_name }}</p>
                      </div>
                      <div>
                        <p class="text-sm text-gray-600">Date</p>
                        <p class="font-semibold">{{ bill.sale_date }}</p>
                      </div>
                      <div>
                        <p class="text-sm text-gray-600">Total Amount</p>
                        <p class="font-semibold text-lg">LKR {{ bill.total_amount }}</p>
                      </div>
                      <div>
                        <p class="text-sm text-gray-600">Paid Amount</p>
                        <p class="font-semibold text-lg text-green-600">LKR {{ bill.paid_amount }}</p>
                      </div>
                      <div>
                        <p class="text-sm text-gray-600">Pending Amount</p>
                        <p class="font-semibold text-lg text-red-600">LKR {{ bill.pending_amount }}</p>
                      </div>
                    </div>

                    <div class="mt-6">
                      <h4 class="text-lg font-semibold mb-3 text-gray-800">Payment History</h4>
                      <div class="overflow-x-auto">
                        <table class="min-w-full border border-gray-300 rounded">
                          <thead class="bg-gray-100">
                            <tr>
                              <th class="p-3 text-left text-sm font-semibold">#</th>
                              <th class="p-3 text-left text-sm font-semibold">Amount</th>
                              <th class="p-3 text-left text-sm font-semibold">Method</th>
                              <th class="p-3 text-left text-sm font-semibold">Date</th>
                              <th class="p-3 text-left text-sm font-semibold">Description</th>
                            </tr>
                          </thead>
                          <tbody>
                            <tr v-for="(payment, idx) in bill.payments" :key="payment.id" class="border-t hover:bg-gray-50">
                              <td class="p-3">{{ idx + 1 }}</td>
                              <td class="p-3 font-semibold text-green-600">LKR {{ payment.amount }}</td>
                              <td class="p-3 capitalize">{{ payment.payment_method }}</td>
                              <td class="p-3">{{ payment.date }}</td>
                              <td class="p-3">{{ payment.description || '-' }}</td>
                            </tr>
                            <tr v-if="!bill.payments || !bill.payments.length">
                              <td colspan="5" class="p-4 text-center text-gray-500">No payments recorded yet</td>
                            </tr>
                          </tbody>
                        </table>
                      </div>
                    </div>

                    <div class="mt-4 flex justify-end">
                      <button class="px-4 py-2 text-sm text-white bg-blue-600 rounded hover:bg-blue-700"
                        @click="downloadPdf(bill)">
                        <i class="ri-download-line mr-2"></i>Download PDF
                      </button>
                    </div>
                  </div>

                  <!-- Add Payment Form Section -->
                  <div v-if="showPaymentForm" class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-xl font-bold mb-4 text-gray-800">Add Payment - {{ bill.order_id }}</h3>

                    <form @submit.prevent="submitPayment">
                      <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                          <label class="block text-sm font-medium mb-1 text-gray-700">Pending Amount</label>
                          <input type="text" :value="'LKR ' + paymentForm.pending_amount" readonly
                            class="w-full px-3 py-2 border rounded bg-gray-100 text-gray-700" />
                        </div>

                        <div>
                          <label class="block text-sm font-medium mb-1 text-gray-700">Payment Amount *</label>
                          <input type="number" v-model="paymentForm.amount" step="0.01" min="0.01"
                            :max="paymentForm.pending_amount" required
                            class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500" />
                        </div>

                        <div>
                          <label class="block text-sm font-medium mb-1 text-gray-700">Payment Method *</label>
                          <select v-model="paymentForm.payment_method" required
                            class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="cash">Cash</option>
                            <option value="card">Card</option>
                            <option value="cheque">Cheque</option>
                          </select>
                        </div>
                      </div>

                      <!-- Cheque fields -->
                      <div v-if="paymentForm.payment_method === 'cheque'" class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4 p-4 bg-gray-50 rounded">
                        <div>
                          <label class="block text-sm font-medium mb-1 text-gray-700">Cheque Number *</label>
                          <input type="text" v-model="paymentForm.cheque_number" required
                            class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500" />
                        </div>
                        <div>
                          <label class="block text-sm font-medium mb-1 text-gray-700">Bank Name *</label>
                          <input type="text" v-model="paymentForm.bank_name" required
                            class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500" />
                        </div>
                        <div>
                          <label class="block text-sm font-medium mb-1 text-gray-700">Cheque Date *</label>
                          <input type="date" v-model="paymentForm.cheque_date" required
                            class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500" />
                        </div>
                      </div>

                      <div class="mb-4">
                        <label class="block text-sm font-medium mb-1 text-gray-700">Description</label>
                        <textarea v-model="paymentForm.description" rows="3"
                          class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                          placeholder="Optional notes about this payment"></textarea>
                      </div>

                      <div class="flex justify-end space-x-2">
                        <button type="button" @click="cancelPaymentForm"
                          class="px-6 py-2 text-sm text-gray-700 bg-gray-200 rounded hover:bg-gray-300">
                          Cancel
                        </button>
                        <button type="submit"
                          class="px-6 py-2 text-sm text-white bg-blue-600 rounded hover:bg-blue-700">
                          <i class="ri-save-line mr-2"></i>Submit Payment
                        </button>
                      </div>
                    </form>
                  </div>
                </td>
              </tr>
            </template>
          </tbody>
        </table>

      </div>

      <p v-else class="text-center text-red-500 text-[17px]">No credit bills available</p>
    </div>
  </div>

  <Footer />
</template>

<script setup>
import { ref, watch, onMounted } from "vue";
import { Link, Head, usePage, router } from "@inertiajs/vue3";
import Header from "@/Components/custom/Header.vue";
import Footer from "@/Components/custom/Footer.vue";
import Banner from "@/Components/Banner.vue";
import { HasRole } from "@/Utils/Permissions";
import jsPDF from "jspdf";
import autoTable from "jspdf-autotable";

// Props from backend
defineProps({
  creditBills: { type: Array, default: () => [] },
  customers: { type: Array, default: () => [] },
});

// Expandable row state
const expandedBillId = ref(null);
const showPaymentForm = ref(false);

const paymentForm = ref({
  credit_bill_id: null,
  order_id: '',
  pending_amount: 0,
  amount: 0,
  payment_method: 'cash',
  description: '',
  cheque_number: '',
  bank_name: '',
  cheque_date: '',
});

const toggleViewDetails = (bill) => {
  if (expandedBillId.value === bill.id && !showPaymentForm.value) {
    expandedBillId.value = null;
  } else {
    expandedBillId.value = bill.id;
    showPaymentForm.value = false;
  }
};

const togglePaymentForm = (bill) => {
  if (expandedBillId.value === bill.id && showPaymentForm.value) {
    expandedBillId.value = null;
    showPaymentForm.value = false;
  } else {
    expandedBillId.value = bill.id;
    showPaymentForm.value = true;
    paymentForm.value = {
      credit_bill_id: bill.id,
      order_id: bill.order_id,
      pending_amount: bill.pending_amount,
      amount: 0,
      payment_method: 'cash',
      description: '',
      cheque_number: '',
      bank_name: '',
      cheque_date: '',
    };
  }
};

const cancelPaymentForm = () => {
  expandedBillId.value = null;
  showPaymentForm.value = false;
  paymentForm.value = {
    credit_bill_id: null,
    order_id: '',
    pending_amount: 0,
    amount: 0,
    payment_method: 'cash',
    description: '',
    cheque_number: '',
    bank_name: '',
    cheque_date: '',
  };
};

const submitPayment = () => {
  console.log('Submitting payment:', paymentForm.value);
  router.post('/credit_payment', paymentForm.value, {
    preserveScroll: true,
    preserveState: false, // Don't preserve state, get fresh data
    onBefore: () => {
      console.log('Starting payment submission...');
    },
    onStart: () => {
      console.log('Request started...');
    },
    onSuccess: (page) => {
      console.log('Payment successful!', page);
      cancelPaymentForm();
    },
    onError: (errors) => {
      console.error('Payment submission error:', errors);
    },
    onFinish: () => {
      console.log('Request finished');
    }
  });
};

// Flash messages
const { props } = usePage();
const successMessage = ref(props.flash?.success || "");
const errorMessage = ref(props.flash?.error || "");

// Auto-clear messages
watch([successMessage, errorMessage], ([success, error]) => {
  if (success || error) {
    setTimeout(() => {
      successMessage.value = "";
      errorMessage.value = "";
    }, 5000);
  }
});

// Initialize DataTable with left search box
onMounted(() => {
  if (window.$ && $("#CreditBillsTable").length) {
    $("#CreditBillsTable").DataTable({
      dom: '<"flex justify-start mb-4"f>rtip',
      pageLength: 10,
      columnDefs: [{ targets: -1, searchable: false }],
      initComplete: function () {
        $("div.flex input").attr("placeholder", "Search credit bills...").addClass("px-3 py-2 border rounded");
      },
      language: { search: "" },
    });
  }
});

// Download PDF function
const downloadPdf = (bill) => {
  if (!bill) return;

  const doc = new jsPDF();

  doc.setFontSize(18);
  doc.text("Credit Bill Details", 14, 20);

  doc.setFontSize(12);
  doc.text(`Order ID: ${bill.order_id}`, 14, 30);
  doc.text(`Customer: ${bill.customer_name}`, 14, 38);
  doc.text(`Total Amount: LKR ${bill.total_amount}`, 14, 46);
  doc.text(`Paid Amount: LKR ${bill.paid_amount}`, 14, 54);
  doc.text(`Pending Amount: LKR ${bill.pending_amount}`, 14, 62);
  doc.text(`Status: ${bill.status}`, 14, 70);
  doc.text(`Date: ${bill.sale_date}`, 14, 78);

  if (bill.payments && bill.payments.length) {
    const tableColumn = ["#", "Amount", "Method", "Date", "Description"];
    const tableRows = bill.payments.map((payment, idx) => [
      idx + 1,
      `LKR ${payment.amount}`,
      payment.payment_method,
      payment.date,
      payment.description || "-"
    ]);

    autoTable(doc, {
      head: [tableColumn],
      body: tableRows,
      startY: 88,
      theme: "grid",
      headStyles: { fillColor: [59, 130, 246] },
    });
  } else {
    doc.text("No payments recorded.", 14, 88);
  }

  doc.save(`CreditBill_${bill.order_id}.pdf`);
};
</script>
Expandable row state
const expandedBillId = ref(null);
const showPaymentForm = ref(false);

const paymentForm = ref({
  credit_bill_id: null,
  order_id: '',
  pending_amount: 0,
  amount: 0,
  payment_method: 'cash',
  description: '',
  cheque_number: '',
  bank_name: '',
  cheque_date: '',
});

const toggleViewDetails = (bill) => {
  if (expandedBillId.value === bill.id && !showPaymentForm.value) {
    expandedBillId.value = null;
  } else {
    expandedBillId.value = bill.id;
    showPaymentForm.value = false;
  }
};

const togglePaymentForm = (bill) => {
  if (expandedBillId.value === bill.id && showPaymentForm.value) {
    expandedBillId.value = null;
    showPaymentForm.value = false;
  } else {
    expandedBillId.value = bill.id;
    showPaymentForm.value = true;
    paymentForm.value = {
      credit_bill_id: bill.id,
      order_id: bill.order_id,
      pending_amount: bill.pending_amount,
      amount: 0,
      payment_method: 'cash',
      description: '',
      cheque_number: '',
      bank_name: '',
      cheque_date: '',
    };
  }
};

const cancelPaymentForm = () => {
  expandedBillId.value = null;
  showPaymentForm.value = false;
  paymentForm.value = {
    credit_bill_id: null,
    order_id: '',
    pending_amount: 0,
    amount: 0,
    payment_method: 'cash',
    description: '',
    cheque_number: '',
    bank_name: '',
    cheque_date: '',
  };
};

const submitPayment = () => {
  router.post('/credit_payment', paymentForm.value, {
    onSuccess: () => {
      cancelPaymentForm
