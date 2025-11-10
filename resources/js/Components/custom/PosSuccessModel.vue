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
import { computed, ref, watch, nextTick, onBeforeUnmount } from "vue";
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
  emit("update:open", false);     // close modal
  await nextTick();               // let leave animation start
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
  // Calculate totals
  const subTotal = props.products.reduce(
    (sum, product) => sum + parseFloat(product.selling_price) * product.quantity,
    0
  );
  const customDiscount = Number(props.custom_discount || 0);
  const totalDiscount = props.products
    .reduce((total, item) => {
      if (item.discount && item.discount > 0 && item.apply_discount == true) {
        const discountAmount =
          (parseFloat(item.selling_price) - parseFloat(item.discounted_price)) *
          item.quantity;
        return total + discountAmount;
      }
      return total;
    }, 0)
    .toFixed(2);

  const total = subTotal - Number(totalDiscount) - customDiscount;
  
  const totalProductCount = props.products.length;

    const productRows = props.products
    .map((product) => {
      return `
        <tr>
          <td colspan="3" style="padding: 4px 0; font-weight: bold;">
            ${product.name}
          </td>
        </tr>
        <tr style="border-bottom: 1px dashed #999;">
          <td></td>
          <td style="text-align: center; padding: 2px 0;">
            ${product.selling_price} × ${product.quantity}
            ${
              product.discount > 0 && product.apply_discount
                ? `<div style="font-weight: bold; font-size: 9px; background:black; color:white; text-align:center; margin-top:2px; border-radius:3px; display:inline-block; padding:0 4px;">
                     ${product.discount}% OFF
                   </div>`
                : ""
            }
          </td>
          <td style="text-align: right; padding: 2px 0;">
            ${
              product.discount > 0 && product.apply_discount
                ? (product.selling_price * product.quantity * (1 - product.discount / 100)).toFixed(2)
                : (product.selling_price * product.quantity).toFixed(2)
            }
          </td>
        </tr>
      `;
    })
    .join("");

  const receiptHTML = `
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Receipt</title>
<style>
  @media print { 
    body { margin:0; padding:0; -webkit-print-color-adjust: exact; } 
    @page { margin: 0; size: 80mm auto; }
  }
  body { 
    background:#fff; 
    font-size:12px; 
    font-family: 'Courier New', Courier, monospace; 
    margin:0; 
    padding:8px; 
    color:#000; 
    width: 80mm;
    max-width: 80mm;
    font-weight:bold;
  }
  .header { text-align:center; margin-bottom:10px; }
  .header h1 { margin:2px 0; font-size:14px; font-weight:bold; }
  .header p { margin:1px 0; font-size:11px; }
  .dashed { border-top:1px dashed #000; margin:8px 0; }
  .info-row { display:flex; justify-content:space-between; font-size:11px; margin:3px 0; }
  .section-title { text-align:center; font-size:11px; margin:8px 0; }
  table { width:100%; font-size:11px; border-collapse:collapse; margin-top:5px; }
  table th { text-align:left; padding:2px 0; border-bottom:1px dashed #000; }
  table td { padding:2px 0; }
  .totals { margin-top:8px; font-size:11px; }
  .totals div { display:flex; justify-content:space-between; margin:3px 0; }
  .totals .total-line { font-size:13px; font-weight:bold;   padding-top:5px; margin-top:5px; }
  .footer { text-align:center; font-size:11px; margin-top:10px; }
</style>
</head>
<body>
  <div class="receipt-container">
    <!-- Header -->
    <div class="header">
      <h1>${companyInfo?.value?.name || ""}</h1>
      <p>${companyInfo?.value?.address || ""}</p>
      <p>${companyInfo?.value?.phone || ""}${companyInfo?.value?.phone2 ? " / " + companyInfo.value.phone2 : ""}</p>
    </div>

  

    <!-- Info Section -->
    <div class="info-row">
      <span>Cashier:${props.cashier?.name || "Manager"}</span>
      <span>MachNo:${props.orderid || "0000"}</span>
    </div>

    

    <!-- Products Table -->
    <table>
      <thead>
        <tr>
         <th style="text-align:left; padding:4px;">Items</th>
            <th style="text-align:center; padding:4px;">Price × Qty</th>
            <th style="text-align:right; padding:4px;">Amount</th>
        </tr>
      </thead>
      <tbody>
        ${productRows}
      </tbody>
    </table>

  

    <!-- Totals Section -->
    <div class="totals">
      ${Number(props.subTotal || 0)
        ? `<div><span>Subtotal</span><span>${(Number(props.subTotal || 0)).toFixed(2)}</span></div>`
        : ""
      }
      ${Number(props.totalDiscount || 0)
        ? `<div><span>Discount:</span><span>${
            props.custom_discount_type === "percent" 
              ? props.custom_discount + "%" 
              : (Number(props.totalDiscount || 0)).toFixed(2)
          }</span></div>`
        : ""
      }
      ${Number(props.totalDiscount || 0)
        ? `<div><span>Subtotal</span><span>${(Number(props.subTotal || 0) - Number(props.totalDiscount || 0)).toFixed(2)}</span></div>`
        : ""
      }
      
      <div class="dashed"></div>
      
      <div><span>Item:</span><span>${props.products.length}</span></div>
      <div class="total-line"><span>TOTAL</span><span>${(Number(props.total || 0)).toFixed(2)}</span></div>
      ${Number(props.cash || 0)
        ? `<div><span>Cash</span><span>${(Number(props.cash || 0)).toFixed(2)}</span></div>`
        : ""
      }
      <div><span>${new Date().toLocaleDateString('en-GB')} ${new Date().toLocaleTimeString('en-GB', {hour: '2-digit', minute: '2-digit'})}</span><span>#${props.orderid || "0000"}</span></div>
    </div>

   

    <!-- Footer -->
    <div class="footer">
      <p>THANK YOU,COME AGAIN</p>
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
