<template>
  <TransitionRoot :show="open" as="template">
    <Dialog as="div" class="relative z-10" @close="() => emit('update:open', false)">
      <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"></div>
      <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
          <DialogPanel class="relative bg-white rounded-lg text-left shadow-xl transform transition-all sm:my-8 sm:w-full sm:max-w-lg">
            <DialogTitle class="text-lg font-medium leading-6 text-gray-900">Printouts</DialogTitle>
            <div class="mt-4">
              <ul>
                <li v-for="printout in printouts" :key="printout.id" class="border-b py-2">
                  <span class="font-bold">{{ printout.product_name }}</span> - Price: {{ printout.price }} - Quantity: {{ printout.quantity }}
                </li>
              </ul>
            </div>
            <div class="mt-4">
              <button @click="() => emit('update:open', false)" class="inline-flex justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                Close
              </button>
            </div>
          </DialogPanel>
        </div>
      </div>
    </Dialog>
  </TransitionRoot>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { Dialog, DialogPanel, DialogTitle, TransitionRoot } from '@headlessui/vue';
import axios from 'axios';

const emit = defineEmits(['update:open']);
const props = defineProps({
  open: {
    type: Boolean,
    required: true,
  },
});

const printouts = ref([]);

onMounted(async () => {
  try {
    const response = await axios.get('/printouts');
    printouts.value = response.data;
  } catch (error) {
    console.error('Error fetching printouts:', error);
  }
});
</script>