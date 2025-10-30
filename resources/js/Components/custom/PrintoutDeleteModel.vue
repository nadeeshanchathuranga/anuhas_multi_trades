<template>
  <div v-if="open" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
    <div class="w-full max-w-md p-6 bg-white rounded-lg shadow-xl">
      <!-- Warning Icon -->
      <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 bg-red-100 rounded-full">
        <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.35 16.5c-.77.833.192 2.5 1.732 2.5z" />
        </svg>
      </div>

      <!-- Confirmation Message -->
      <h2 class="mb-2 text-xl font-bold text-center text-gray-800">Delete Printout</h2>
      <p class="mb-6 text-center text-gray-600">
        Are you sure you want to delete 
        <span class="font-semibold text-red-600">"{{ selectedPrintout?.title }}"</span>?
        This action cannot be undone.
      </p>

      <!-- Printout Details -->
      <div v-if="selectedPrintout" class="p-4 mb-6 bg-gray-100 rounded-lg">
        <div class="space-y-2 text-sm">
          <div class="flex justify-between">
            <span class="font-medium text-gray-700">Title:</span>
            <span class="text-gray-900">{{ selectedPrintout.title }}</span>
          </div>
          <div class="flex justify-between">
            <span class="font-medium text-gray-700">Price:</span>
            <span class="text-gray-900">Rs. {{ selectedPrintout.price }}</span>
          </div>
          <div class="flex justify-between">
            <span class="font-medium text-gray-700">Stock:</span>
            <span :class="selectedPrintout.stock_quantity > 0 ? 'text-green-600' : 'text-red-600'" class="font-semibold">
              {{ selectedPrintout.stock_quantity }} units
            </span>
          </div>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="flex justify-center space-x-3">
        <button
          type="button"
          @click="closeModal"
          class="px-6 py-2 text-gray-700 bg-gray-300 rounded-lg hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-400 transition duration-200"
        >
          Cancel
        </button>
        <button
          @click="confirmDelete"
          :disabled="form.processing"
          class="px-6 py-2 text-white bg-red-600 rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 disabled:opacity-50 transition duration-200"
        >
          <span v-if="form.processing">Deleting...</span>
          <span v-else>Delete Printout</span>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
  open: Boolean,
  selectedPrintout: Object,
});

const emit = defineEmits(['update:open', 'delete']);

const form = useForm({});

const confirmDelete = () => {
  if (!props.selectedPrintout) return;

  form.delete(route('printouts.destroy', props.selectedPrintout.id), {
    preserveScroll: true,
    onSuccess: () => {
      closeModal();
      emit('delete', props.selectedPrintout.id);
    },
    onError: (errors) => {
      console.error('Delete failed:', errors);
    },
  });
};

const closeModal = () => {
  emit('update:open', false);
};
</script>