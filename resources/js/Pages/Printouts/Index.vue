<template>
  <Head title="Printouts" />
  <Banner />
  <div class="flex flex-col items-center justify-start min-h-screen py-8 space-y-8 bg-gray-100 md:px-36 px-16">
    <Header />
    <div class="w-full md:w-5/6 py-12 space-y-16">
      <div class="flex items-center justify-between">
        <div class="flex items-center justify-center space-x-4">
          <Link href="/">
            <img src="/images/back-arrow.png" class="w-14 h-14" />
          </Link>
          <p class="text-4xl font-bold tracking-wide text-black uppercase">
            Printouts
          </p>
        </div>
        <p class="text-3xl italic font-bold text-black">
          <span class="px-4 py-1 mr-3 text-white bg-black rounded-xl">{{ totalPrintouts }}</span>
          <span class="text-xl">/ Total Printouts</span>
        </p>
        <p
          @click="isCreateModalOpen = true"
          class="md:px-12 py-4 px-4 md:text-2xl font-bold tracking-wider text-white uppercase bg-blue-600 rounded-xl cursor-pointer"
        >
          <i class="md:pr-4 ri-add-circle-fill"></i> Add New Printout
        </p>
      </div>

      <!-- Search -->
      <div class="flex items-center space-x-4">
        <div class="md:w-1/4 w-full">
          <input
            v-model="search"
            @input="performSearch"
            type="text"
            placeholder="Search printouts..."
            class="w-full custom-input"
          />
        </div>
      </div>

      <!-- Printouts Grid -->
      <div class="grid md:grid-cols-4 grid-cols-1 gap-8">
        <template v-if="printouts.data.length > 0">
          <div
            v-for="printout in printouts.data"
            :key="printout.id"
            class="space-y-4 text-white transition-transform duration-300 transform bg-black border-4 border-black shadow-lg hover:-translate-y-4"
          >
            <div class="px-2 py-4 space-y-4">
              <div class="w-full text-[11px] font-bold tracking-wide space-y-2 p-3 rounded-lg">
                <p class="text-center text-white text-[13px] font-semibold">
                  {{ printout.name || "N/A" }}
                </p>
                <div class="flex justify-center items-center text-white text-[12px]">
                  <div class="bg-blue-600 px-3 py-1 rounded-full">
                    Price: Rs. {{ printout.price || "0.00" }}
                  </div>
                </div>
              </div>
              
              <div class="flex justify-center space-x-2 items-start w-full">
                <div class="flex space-x-1 text-gray-400">
                  <p class="font-bold">Qty:</p>
                  <p>{{ printout.quantity || "1" }}</p>
                </div>
              </div>
              
              <!-- <div class="flex items-center justify-center w-full space-x-4">
                <p class="flex items-center space-x-2 text-justify text-gray-400">
                  Description: <b> &nbsp; {{ printout.description || "N/A" }} </b>
                </p>
              </div> -->
              
              <div class="flex items-center justify-between">
                <p
                  v-if="printout.quantity > 0"
                  class="text-xl font-bold tracking-wider text-green-500"
                >
                  <i class="ri-checkbox-blank-circle-fill"></i> In Stock ({{ printout.quantity }})
                </p>
                <p v-else class="text-xl font-bold tracking-wider text-red-500">
                  <i class="ri-checkbox-blank-circle-fill"></i> Out of Stock
                </p>
                <div class="flex space-x-4">
                  <button
                    @click="openEditModal(printout)"
                    class="flex items-center justify-center w-10 h-10 text-gray-800 transition duration-200 bg-gray-100 rounded-full hover:bg-green-600 hover:text-white"
                  >
                    <i class="ri-pencil-line"></i>
                  </button>
                  <button
                    @click="openDeleteModal(printout)"
                    class="flex items-center justify-center w-10 h-10 text-gray-800 transition duration-200 bg-gray-100 rounded-full hover:bg-red-600 hover:text-white"
                  >
                    <i class="ri-delete-bin-line"></i>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </template>
        <template v-else>
          <div class="col-span-4 text-center text-gray-500">
            <p class="text-center text-red-500 text-[17px]">
              No Printouts Available
            </p>
          </div>
        </template>
      </div>

      <!-- Pagination -->
      <div class="flex space-x-2 pagination">
        <span
          v-if="printouts.links[0]"
          @click.prevent="navigateTo(printouts.links[0].url)"
          :class="[
            'pagination-btn',
            { 'pagination-disabled': !printouts.links[0].url },
          ]"
        >
          Previous
        </span>
        <span
          v-for="(link) in printouts.links.slice(1, printouts.links.length - 1)"
          :key="link.label"
          @click.prevent="navigateTo(link.url)"
          :class="['pagination-btn', { 'pagination-active': link.active }]"
          v-html="link.label"
        ></span>
        <span
          v-if="printouts.links[printouts.links.length - 1]"
          @click.prevent="navigateTo(printouts.links[printouts.links.length - 1].url)"
          :class="[
            'pagination-btn',
            {
              'pagination-disabled': !printouts.links[printouts.links.length - 1].url,
            },
          ]"
        >
          Next
        </span>
      </div>
    </div>
  </div>

  <!-- Create Modal -->
  <PrintoutCreateModel v-model:open="isCreateModalOpen" />

  <!-- Edit Modal -->
  <PrintoutUpdateModel
    v-model:open="isEditModalOpen"
    :selected-printout="selectedPrintout"
  />

  <!-- Delete Modal -->
  <PrintoutDeleteModel
    v-model:open="isDeleteModalOpen"
    :selected-printout="selectedPrintout"
    @delete="deletePrintout"
  />

  <Footer />
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from "@inertiajs/vue3";
import { debounce } from "lodash";
import Header from "@/Components/custom/Header.vue";
import Footer from "@/Components/custom/Footer.vue";
import Banner from "@/Components/Banner.vue";
import PrintoutCreateModel from "@/Components/custom/PrintoutCreateModel.vue";
import PrintoutUpdateModel from "@/Components/custom/PrintoutUpdateModel.vue";
import PrintoutDeleteModel from "@/Components/custom/PrintoutDeleteModel.vue";

const props = defineProps({
  printouts: Object,
  totalPrintouts: Number,
  search: String,
});

const search = ref(props.search || "");
const isCreateModalOpen = ref(false);
const isEditModalOpen = ref(false);
const isDeleteModalOpen = ref(false);
const selectedPrintout = ref(null);

const performSearch = debounce(() => {
  router.get(
    route("printouts.index"),
    { search: search.value },
    { preserveState: true,
      replace: true,
      only: ['printouts', 'search']
     }
  );
}, 500);

const openEditModal = (printout) => {
  selectedPrintout.value = printout;
  isEditModalOpen.value = true;
};

const openDeleteModal = (printout) => {
  selectedPrintout.value = printout;
  isDeleteModalOpen.value = true;
};

const deletePrintout = (id) => {
  router.delete(route('printouts.destroy', id), {
    onSuccess: () => {
      isDeleteModalOpen.value = false;
    },
  });
};

const navigateTo = (url) => {
  if (!url) return;
  const urlParams = new URLSearchParams(new URL(url, window.location.origin).search);
  const page = urlParams.get("page");

  router.get(
    route("printouts.index"),
    { page, search: search.value },
    { preserveState: true, preserveScroll: true }
  );
};
</script>

<style scoped>
.pagination-disabled {
  color: rgb(37 99 235);
  transition: all 0.5s ease;
  background: rgb(229 231 235 / var(--tw-bg-opacity));
}
.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  margin-top: 20px;
  font-size: 14px;
  float: right;
}
.pagination a:first-child,
.pagination a:last-child {
  padding: 8px 16px;
}
</style>