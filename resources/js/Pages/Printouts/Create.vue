<template>
  <AdminLayout>
    <div class="min-h-screen p-6 bg-gray-100">
      <div class="max-w-4xl min-h-screen p-6 mx-auto bg-gray-100">
        <h1 class="mb-6 text-2xl font-bold text-gray-800">Create Printout</h1>
        <form @submit.prevent="submit" class="space-y-6">
          <!-- Title Input -->
          <div>
            <label for="title" class="block text-sm font-medium text-gray-700"
              >Title</label
            >
            <input
              v-model="form.title"
              type="text"
              id="title"
              required
              class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
            />
            <span v-if="form.errors.title" class="text-sm text-red-500">{{
              form.errors.title
            }}</span>
          </div>

          <!-- Description Input -->
          <div>
            <label
              for="description"
              class="block text-sm font-medium text-gray-700"
              >Description</label
            >
            <textarea
              v-model="form.description"
              id="description"
              rows="4"
              class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
            ></textarea>
            <span v-if="form.errors.description" class="text-sm text-red-500">{{
              form.errors.description
            }}</span>
          </div>

          <!-- Price Input -->
          <div>
            <label for="price" class="block text-sm font-medium text-gray-700"
              >Price</label
            >
            <input
              v-model="form.price"
              type="number"
              step="0.01"
              id="price"
              required
              class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
            />
            <span v-if="form.errors.price" class="text-sm text-red-500">{{
              form.errors.price
            }}</span>
          </div>

          <!-- Stock Quantity Input -->
          <div>
            <label
              for="stock_quantity"
              class="block text-sm font-medium text-gray-700"
              >Stock Quantity</label
            >
            <input
              v-model="form.stock_quantity"
              type="number"
              id="stock_quantity"
              required
              class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
            />
            <span
              v-if="form.errors.stock_quantity"
              class="text-sm text-red-500"
              >{{ form.errors.stock_quantity }}</span
            >
          </div>

          <!-- Default Quantity Input -->
          <div>
            <label
              for="default_quantity"
              class="block text-sm font-medium text-gray-700"
              >Default Quantity</label
            >
            <input
              v-model="form.default_quantity"
              type="number"
              id="default_quantity"
              required
              class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
            />
            <span
              v-if="form.errors.default_quantity"
              class="text-sm text-red-500"
              >{{ form.errors.default_quantity }}</span
            >
          </div>

          <!-- Submit Button -->
          <div class="flex space-x-4">
            <button
              type="submit"
              :disabled="form.processing"
              class="px-4 py-2 text-white bg-blue-500 rounded hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-400 disabled:opacity-50"
            >
              Save
            </button>
            <Link
              :href="route('printouts.index')"
              class="px-4 py-2 text-gray-700 bg-gray-300 rounded hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-400"
            >
              Cancel
            </Link>
          </div>
        </form>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { useForm } from "@inertiajs/vue3";
import { Link } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";

const form = useForm({
  title: "",
  description: "",
  price: 0.00,
  stock_quantity: 0,
  default_quantity: 1,
});

const submit = () => {
  form.post(route("printouts.store"), {
    preserveScroll: true,
    onSuccess: () => {
      form.reset();
    },
    onError: (errors) => {
      console.error("Form submission failed:", errors);
    },
  });
};
</script>