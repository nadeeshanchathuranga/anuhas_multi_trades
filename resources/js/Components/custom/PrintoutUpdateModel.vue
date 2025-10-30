<template>
  <div v-if="open" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
    <div class="w-full max-w-2xl p-6 bg-white rounded-lg shadow-xl">
      <h2 class="mb-4 text-2xl font-bold">Edit Printout</h2>
      <form @submit.prevent="submit" class="space-y-4">
        <!-- Name Input -->
        <div>
          <label for="edit_name" class="block text-sm font-medium text-gray-700">Name</label>
          <input
            v-model="form.name"
            type="text"
            id="edit_name"
            required
            class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
          />
          <span v-if="form.errors.name" class="text-sm text-red-500">{{ form.errors.name }}</span>
        </div>

        <!-- Price Input -->
        <div>
          <label for="edit_price" class="block text-sm font-medium text-gray-700">Price</label>
          <input
            v-model="form.price"
            type="number"
            step="0.01"
            id="edit_price"
            required
            class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
          />
          <span v-if="form.errors.price" class="text-sm text-red-500">{{ form.errors.price }}</span>
        </div>

        <!-- Action Buttons -->
        <div class="flex justify-end space-x-3">
          <button
            type="button"
            @click="closeModal"
            class="px-4 py-2 text-gray-700 bg-gray-300 rounded hover:bg-gray-400"
          >
            Cancel
          </button>
          <button
            type="submit"
            :disabled="form.processing"
            class="px-4 py-2 text-white bg-blue-500 rounded hover:bg-blue-600 disabled:opacity-50"
          >
            Update
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
import { watch } from 'vue';

const props = defineProps({
  open: Boolean,
  selectedPrintout: Object,
});

const emit = defineEmits(['update:open']);

const form = useForm({
  name: '',
  price: 0.00,
});

// Watch for selectedPrintout changes and update form
watch(() => props.selectedPrintout, (newPrintout) => {
  if (newPrintout) {
    form.name = newPrintout.name;
    form.price = newPrintout.price;
  }
}, { immediate: true });

const submit = () => {
  if (!props.selectedPrintout) return;

  form.put(route('printouts.update', props.selectedPrintout.id), {
    preserveScroll: true,
    onSuccess: () => {
      closeModal();
      form.reset();
    },
    onError: (errors) => {
      console.error('Update failed:', errors);
    },
  });
};

const closeModal = () => {
  emit('update:open', false);
  form.clearErrors();
};
</script>