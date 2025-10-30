<template>
  <div v-if="open" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
    <div class="w-full max-w-2xl p-6 bg-white rounded-lg shadow-xl">
      <h2 class="mb-4 text-2xl font-bold">Create Printout</h2>
      <form @submit.prevent="submit" class="space-y-4">
        <!-- Name Input -->
        <div>
          <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
          <input
            v-model="form.name"
            type="text"
            id="name"
            required
            class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
          />
          <span v-if="form.errors.name" class="text-sm text-red-500">{{ form.errors.name }}</span>
        </div>

        <!-- Price Input -->
        <div>
          <label for="price" class="block text-sm font-medium text-gray-700">Price</label>
          <input
            v-model="form.price"
            type="number"
            step="0.01"
            id="price"
            required
            class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
          />
          <span v-if="form.errors.price" class="text-sm text-red-500">{{ form.errors.price }}</span>
        </div>

        <div class="flex justify-end">
          <button
            type="button"
            @click="closeModal"
            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300"
          >
            Cancel
          </button>
          <button
            type="submit"
            class="px-4 py-2 ml-2 text-sm font-medium text-white bg-blue-500 rounded-md hover:bg-blue-600"
          >
            Create
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
  open: Boolean,
});

const emit = defineEmits(['update:open']);

const form = useForm({
  name: '',
  price: 0.00,
});

const submit = () => {
  form.post(route('printouts.store'), {
    preserveScroll: true,
    onSuccess: () => {
      closeModal();
      form.reset();
    },
  });
};

const closeModal = () => {
  emit('update:open', false);
};
</script>