<template>
  <TransitionRoot as="template" :show="open">
    <Dialog class="relative z-50" @close="closeModal">
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
        <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" />
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
          <DialogPanel class="bg-white border border-gray-300 rounded-xl shadow-xl w-11/12 max-w-6xl p-6 max-h-[90vh] overflow-hidden flex flex-col">
            <!-- Header -->
            <div class="flex items-center justify-between mb-6 pb-4 border-b">
              <DialogTitle class="text-2xl font-bold text-gray-800">
                Select Printouts for Sale
              </DialogTitle>
              <button
                @click="closeModal"
                class="text-gray-400 hover:text-gray-600 text-2xl font-bold"
              >
                &times;
              </button>
            </div>

            <!-- Search and Filters -->
            <div class="mb-6 space-y-4">
              <div class="flex space-x-4 items-center">
                <!-- Search -->
                <div class="flex-1">
                  <input
                    v-model="search"
                    @input="performSearch"
                    type="text"
                    placeholder="Search printouts..."
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  />
                </div>

                <!-- Stock Filter -->
                <select
                  v-model="stockFilter"
                  @change="fetchPrintouts"
                  class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                >
                  <option value="all">All Stock</option>
                  <option value="in">In Stock Only</option>
                  <option value="out">Out of Stock</option>
                </select>

                <!-- Price Sort -->
                <select
                  v-model="priceSort"
                  @change="fetchPrintouts"
                  class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                >
                  <option value="">Sort by Price</option>
                  <option value="asc">Price: Low to High</option>
                  <option value="desc">Price: High to Low</option>
                </select>

                <button
                  @click="resetFilters"
                  class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600"
                >
                  Reset
                </button>
              </div>

              <!-- Selected Count -->
              <div v-if="selectedPrintouts.length > 0" class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                <p class="text-blue-800 font-semibold">
                  {{ selectedPrintouts.length }} printout(s) selected
                </p>
              </div>
            </div>

            <!-- Printouts Grid -->
            <div class="flex-1 overflow-y-auto">
              <template v-if="loading">
                <div class="text-center py-8">
                  <p class="text-gray-500 text-lg">Loading printouts...</p>
                </div>
              </template>

              <template v-else-if="printouts.data && printouts.data.length > 0">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                  <div
                    v-for="printout in printouts.data"
                    :key="printout.id"
                    @click="togglePrintoutSelection(printout)"
                    :class="[
                      'border-2 rounded-lg p-4 cursor-pointer transition-all duration-200',
                      isPrintoutSelected(printout.id)
                        ? 'border-blue-500 bg-blue-50 shadow-md'
                        : 'border-gray-300 hover:border-blue-300 hover:shadow'
                    ]"
                  >
                    <!-- Printout Card -->
                    <div class="space-y-3">
                      <!-- Header -->
                      <div class="flex justify-between items-start">
                        <h3 class="font-semibold text-gray-800 text-lg line-clamp-2">
                          {{ printout.title }}
                        </h3>
                        <span
                          class="px-2 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800"
                        >
                          Unlimited Stock
                        </span>
                      </div>

                      <!-- Description -->
                      <p class="text-black-600  line-clamp-2">
                        {{ printout.name || 'No name' }}
                      </p>

                      <!-- Price and Stock -->
                      <div class="flex justify-between items-center">
                        <span class="text-lg font-bold text-blue-600">
                          Rs. {{ printout.price }}
                        </span>
                        <span class="text-sm text-green-600 font-semibold">
                          ∞ Unlimited
                        </span>
                      </div>

                    
                      <!-- Selection Indicator -->
                      <div v-if="isPrintoutSelected(printout.id)" class="flex items-center justify-center mt-2">
                        <span class="bg-blue-500 text-white px-3 py-1 rounded-full text-sm font-semibold">
                          ✓ Selected
                        </span>
                      </div>
                    </div>
                  </div>
                </div>
              </template>

              <template v-else>
                <div class="text-center py-8">
                  <p class="text-gray-500 text-lg">No printouts found</p>
                  <p class="text-gray-400 text-sm mt-2">
                    Try adjusting your search or filters
                  </p>
                </div>
              </template>
            </div>

            <!-- Footer Actions -->
            <div class="flex justify-between items-center pt-6 mt-6 border-t">
              <div class="text-sm text-gray-600">
                {{ selectedPrintouts.length }} item(s) selected
              </div>
              <div class="flex space-x-3">
                <button
                  @click="closeModal"
                  class="px-6 py-2 text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 transition"
                >
                  Cancel
                </button>
                <button
                  @click="confirmSelection"
                  :disabled="selectedPrintouts.length === 0"
                  :class="[
                    'px-6 py-2 text-white rounded-lg transition',
                    selectedPrintouts.length === 0
                      ? 'bg-gray-400 cursor-not-allowed'
                      : 'bg-blue-600 hover:bg-blue-700'
                  ]"
                >
                  Add to Sale ({{ selectedPrintouts.length }})
                </button>
              </div>
            </div>
          </DialogPanel>
        </TransitionChild>
      </div>
    </Dialog>
  </TransitionRoot>
</template>

<script setup>
import { ref, watch, computed } from 'vue';
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue';
import { debounce } from 'lodash';
import axios from 'axios';

const props = defineProps({
  open: {
    type: Boolean,
    required: true,
  }
});

const emit = defineEmits(['update:open', 'selected-printouts']);

// Reactive data
const printouts = ref({ data: [] });
const selectedPrintouts = ref([]);
const loading = ref(false);
const search = ref('');
const stockFilter = ref('all');
const priceSort = ref('');

// Methods
const fetchPrintouts = async () => {
  loading.value = true;
  
  try {
    const response = await axios.get('/printouts/api', {
      params: {
        search: search.value,
        stock: stockFilter.value,
        sort: priceSort.value
      }
    });
    
    if (response.data && response.data.data) {
      printouts.value = {
        data: response.data.data,
        total: response.data.count || 0
      };
    }
  } catch (error) {
    console.error('Error fetching printouts:', error);
    printouts.value = { data: [], total: 0 };
  } finally {
    loading.value = false;
  }
};

const performSearch = debounce(() => {
  fetchPrintouts();
}, 500);

const togglePrintoutSelection = (printout) => {
  // Printouts have unlimited stock - no validation needed
  // if (printout.quantity <= 0) {
  //   alert(`Printout '${printout.title}' is out of stock and cannot be selected.`);
  //   return;
  // }

  const index = selectedPrintouts.value.findIndex(p => p.id === printout.id);
  if (index === -1) {
    selectedPrintouts.value.push({
      ...printout,
      quantity: 1 // Default quantity for selection
    });
  } else {
    selectedPrintouts.value.splice(index, 1);
  }
};

const isPrintoutSelected = (printoutId) => {
  return selectedPrintouts.value.some(p => p.id === printoutId);
};

const resetFilters = () => {
  search.value = '';
  stockFilter.value = 'all';
  priceSort.value = '';
  fetchPrintouts();
};

const confirmSelection = () => {
  emit('selected-printouts', selectedPrintouts.value);
  closeModal();
};

const closeModal = () => {
  selectedPrintouts.value = [];
  emit('update:open', false);
};

// Watch for modal open to fetch data
watch(() => props.open, (newVal) => {
  if (newVal) {
    fetchPrintouts();
  }
});
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  line-clamp: 2; /* Standard property for compatibility */
  overflow: hidden;
}
</style>