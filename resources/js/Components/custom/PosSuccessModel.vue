<template>
  <TransitionRoot as="template" :show="open" static @after-leave="onAfterLeave">
    <Dialog class="relative z-10" static>
      <!-- Modal Overlay -->
      <TransitionChild
        as="template"
        enter="ease-out duration-300"
        enter-from="opacity-0"
        enter-to="opacity-100"
        leave="ease-in duration-200"
        leave-from="opacity-100"
        leave-to="opacity-0"
      >
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click.stop />
      </TransitionChild>

      <!-- Modal Content -->
      <div class="fixed inset-0 z-10 flex items-center justify-center">
        <TransitionChild
          as="template"
          enter="ease-out duration-300"
          enter-from="opacity-0 scale-95"
          enter-to="opacity-100 scale-100"
          leave="ease-in duration-200"
          leave-from="opacity-100 scale-100"
          leave-to="opacity-0 scale-95"
        >
          <DialogPanel class="bg-white border-4 border-blue-600 rounded-[20px] shadow-xl max-w-xl w-full p-6 text-center">
            <!-- Modal Title -->
            <DialogTitle class="text-5xl font-bold">Payment Successful!</DialogTitle>

            <div class="w-full h-full flex flex-col justify-center items-center space-y-8 mt-4">
              <p class="text-justify text-3xl text-black">Order Payment is Successful!</p>
              <div>
                <img src="/images/checked.png" class="h-24 object-cover w-full" />
              </div>
            </div>

            <div class="flex justify-center items-center space-x-4 pt-4 mt-4">
              <!-- Print -->
              <button
                @click="handlePrintReceipt"
                class="cursor-pointer bg-blue-600 text-white font-bold uppercase tracking-wider px-4 shadow-xl py-4 rounded-xl focus:bg-blue-700"
                type="button"
              >
                Print Receipt
              </button>

              <!-- Close -->
              <button
                @click="closeAndRefresh"
                class="cursor-pointer bg-red-600 text-white font-bold uppercase tracking-wider px-4 shadow-xl py-4 rounded-xl focus:bg-red-700"
                type="button"
              >
                Close
              </button>
            </div>
          </DialogPanel>
        </TransitionChild>
      </div>
    </Dialog>
  </TransitionRoot>
</template>

<script setup>
import {
  Dialog,
  DialogPanel,
  DialogTitle,
  TransitionChild,
  TransitionRoot,
} from "@headlessui/vue";
import { computed, watch, nextTick, onBeforeUnmount } from "vue";
import { usePage } from "@inertiajs/vue3";

const emit = defineEmits(["update:open"]);

const props = defineProps({
  open: { type: Boolean, required: true },
  products: { type: Array, required: true },
  cashier: Object,
  customer: Object,
  orderid: String,
  balance: Number,
  cash: Number,
  subTotal: String,
  totalDiscount: String,
  total: String,
  custom_discount: Number,
  custom_discount_type: String,
  paymentMethod: String,
  isWholesale: Boolean,
  credit_bill: Boolean,
  guide_comi: [Number, String],
  guide_cash: [Number, String],
  guide_name: String,
  guide_pending: [Boolean, Number],
  returnItems: {
    type: Array,
    required: false,
    default: () => [],
  },
});

const page = usePage();
const companyInfo = computed(() => page.props.companyInfo);

// Close modal then reload after leave transition
const closeAndRefresh = async () => {
  emit("update:open", false); // close modal
  await nextTick();           // let leave animation start
};

const onAfterLeave = () => {
  // when animation completes
  window.location.reload();
};

// Global keyboard shortcuts (active only when modal is open)
const onGlobalKeyDown = (event) => {
  if (!props.open) return;

  // Enter → Print
  if (event.key === "Enter") {
    event.preventDefault();
    handlePrintReceipt();
    return;
  }

  // Delete / Esc → Close
  if (event.key === "Delete" || event.key === "Escape") {
    event.preventDefault();
    closeAndRefresh();
  }
};

// Attach / detach on open
watch(
  () => props.open,
  (isOpen) => {
    if (isOpen) window.addEventListener("keydown", onGlobalKeyDown);
    else window.removeEventListener("keydown", onGlobalKeyDown);
  },
  { immediate: true }
);

// Cleanup on unmount
onBeforeUnmount(() => {
  window.removeEventListener("keydown", onGlobalKeyDown);
});

const handlePrintReceipt = () => {
  // --- BASE TOTALS FROM PARENT (POS PAGE) ---
  const subTotalFromParent = Number(props.subTotal || 0);
  const totalDiscountFromParent = Number(props.totalDiscount || 0); // normal discounts + coupon etc.
  const totalFromParent = Number(props.total || 0);
  const cashFromParent = Number(props.cash || 0);
  const balanceFromParent = Number(props.balance || 0);

  // --- CUSTOM-ELIGIBLE SUBTOTAL (ONLY include_custom PRODUCTS) ---
  const customEligibleSubtotal = props.products.reduce((sum, product) => {
    if (!product.include_custom) return sum;

    const unitPrice = props.isWholesale
      ? Number(product.whole_price ?? product.selling_price ?? product.price ?? 0)
      : Number(product.selling_price ?? product.price ?? 0);

    return sum + unitPrice * Number(product.quantity || 0);
  }, 0);

  // --- ACTUAL CUSTOM DISCOUNT VALUE ---
  const rawCustomValue = Number(props.custom_discount || 0);
  let customDiscountValue = 0;

  if (customEligibleSubtotal > 0 && rawCustomValue > 0) {
    if (props.custom_discount_type === "percent") {
      customDiscountValue = (customEligibleSubtotal * rawCustomValue) / 100;
    } else {
      customDiscountValue = Math.min(rawCustomValue, customEligibleSubtotal);
    }
  }

  // --- SAFETY: IF parent total is missing, recompute fallback ---
  const effectiveTotal =
    totalFromParent ||
    (subTotalFromParent - totalDiscountFromParent - customDiscountValue);

  const totalProductCount = props.products.length;

  // --- ALL PRODUCTS IN SINGLE TABLE ---

  // --- HELPER FUNCTION TO GENERATE PRODUCT ROWS (SIMPLIFIED FORMAT) ---
  const generateProductRows = (productsArray) =>
    productsArray
      .map((product) => {
        const unitPrice = props.isWholesale
          ? Number(product.whole_price ?? product.selling_price ?? product.price ?? 0)
          : Number(product.selling_price ?? product.price ?? 0);

        const qty = Number(product.quantity || 0);
        const hasLineDiscount = product.discount > 0 && product.apply_discount;

        // FINAL TOTAL AFTER DISCOUNT
        let finalLineTotal = unitPrice * qty;
        if (hasLineDiscount) {
          if (product.discounted_price != null) {
            finalLineTotal = Number(product.discounted_price) * qty;
          } else {
            const discountPercent = Number(product.discount || 0);
            finalLineTotal = unitPrice * qty * (1 - discountPercent / 100);
          }
        }

        return `
          <tr>
            <td>
              ${product.name}${product.include_custom ? ' %' : ''}
            </td>
            <td style="text-align:right;">${unitPrice.toFixed(2)}</td>
            <td style="text-align:center;">${qty}</td>
            <td style="text-align:right;">${finalLineTotal.toFixed(2)}</td>
          </tr>
        `;
      })
      .join("");

  // --- GENERATE SINGLE PRODUCT TABLE ---
  const productsTableHTML = `
    <table>
      <thead>
        <tr>
          <th>Item</th>
          <th>Price</th>
          <th>Qty</th>
          <th>Amount</th>
        </tr>
      </thead>
      <tbody>
        ${generateProductRows(props.products)}
      </tbody>
    </table>
  `;

  // --- RECEIPT HTML ---
  const receiptHTML = `
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Receipt</title>
<style>
  @media print { body { margin:0; padding:0; -webkit-print-color-adjust: none; background: white !important; } }
  body { background: white; font-size: 11px; font-family: Arial, sans-serif; margin:0; padding:10px; color:#000; }
  .info-row { display:flex; justify-content:space-between; font-size:11px; margin-top:2px; margin-bottom:2px; }
  .info-row span:first-child { font-weight: normal; }
  .info-row span:last-child { font-weight: normal; }
  .dotted-line { border-bottom: 1px dotted #000; margin: 5px 0; }
  table { width:100%; font-size:11px; border-collapse:collapse; margin-top:5px; margin-bottom:5px; }
  table th { padding:5px 2px; text-align:left; font-weight:bold; border-bottom: 1px dotted #000; }
  table td { padding:5px 2px; text-align:left; }
  table th:nth-child(2), table td:nth-child(2) { text-align:right; }
  table th:nth-child(3), table td:nth-child(3) { text-align:center; }
  table th:nth-child(4), table td:nth-child(4) { text-align:right; }
  .totals { border-top:1px dotted #000; padding-top:5px; font-size:11px; text-align:right; }
  .totals-row { display:flex; justify-content:space-between; margin-bottom:2px; }
  .totals-row.bold { font-weight:bold; }
  .footer { text-align:center; font-size:11px; margin-top:8px; line-height:1.4; }
  .header { text-align:center; padding-bottom:5px; margin-bottom:5px; border-bottom:1px dotted #000; }
  h1 { margin:0; font-size:16px; font-weight:bold; }
  .company-info { font-size:10px; margin:2px 0; }
</style>
</head>
<body>
  <div class="receipt-container">
    <!-- Header -->
    <div class="header">
      ${
        companyInfo?.value?.name
          ? `<h1>${companyInfo.value.name}</h1>`
          : ""
      }
      ${
        companyInfo?.value?.address
          ? `<div class="company-info">${companyInfo.value.address}</div>`
          : ""
      }
      ${
        (companyInfo?.value?.phone || companyInfo?.value?.phone2)
          ? `<div class="company-info">${companyInfo.value.phone || ""}${companyInfo.value.phone2 ? " | " + companyInfo.value.phone2 : ""}</div>`
          : ""
      }
    </div>

    <div class="info-row">
      <span>Order No: ${props.orderid}</span>
      <span>Cashier : ${props.cashier?.name || ""}</span>
    </div>
    
    <div class="info-row">
      <span>Customer : ${props.customer?.name || "..........................."}</span>
      <span>Billing Type : ${props.isWholesale ? "Wholesale" : "Retail"}</span>
    </div>
    
    <div class="dotted-line"></div>

    <!-- PRODUCT TABLE -->
    ${productsTableHTML}

    <!-- TOTALS -->
    <div class="totals">
      ${
        subTotalFromParent
          ? `<div class="totals-row"><span>Sub Total</span><span>${subTotalFromParent.toFixed(2)}</span></div>`
          : ""
      }
      ${
        customEligibleSubtotal
          ? `<div class="totals-row"><span>Custom Sub Total</span><span>${customEligibleSubtotal.toFixed(2)}</span></div>`
          : ""
      }
      ${
        customDiscountValue
          ? `<div class="totals-row">
               <span>Custom Discount</span>
               <span>${customDiscountValue.toFixed(2)} (${
                 props.custom_discount_type === "percent"
                   ? `${rawCustomValue.toFixed(0)}%`
                   : ""
               })</span>
             </div>`
          : ""
      }
      ${
        effectiveTotal
          ? `<div class="totals-row bold"><span>Total</span><span>${effectiveTotal.toFixed(2)}</span></div>`
          : ""
      }
    </div>
    
    <div class="dotted-line"></div>
    
    <div class="info-row">
      <span>${new Date().toLocaleDateString()} ${new Date().toLocaleTimeString()}</span>
      <span>${props.paymentMethod ? props.paymentMethod.charAt(0).toUpperCase() + props.paymentMethod.slice(1) : ""}</span>
    </div>
    
    <div class="info-row">
      <span>Total Products</span>
      <span>${totalProductCount}</span>
    </div>
    
    <div class="dotted-line"></div>

    <div class="footer">
      <div>Items can be exchanged within seven(7) days of purchase.</div>
      <div>No cash refunds will be provided for issued items.</div>
      <div style="margin-top:8px; font-weight:bold; font-size:11px;">THANK YOU COME AGAIN</div>
    </div>
  </div>
</body>
</html>
`;

  const printWindow = window.open("", "_blank");
  if (!printWindow) {
    alert("Failed to open print window. Please check your browser settings.");
    return;
  }

  printWindow.document.open();
  printWindow.document.write(receiptHTML);
  printWindow.document.close();

  printWindow.onload = () => {
    printWindow.focus();
    printWindow.print();
    printWindow.close();
  };
};
</script>
