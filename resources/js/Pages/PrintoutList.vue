<template>
  <div class="printout-list">
    <h1 class="text-2xl font-bold mb-4">Printouts</h1>

    <button @click="openCreateModal" class="btn btn-primary mb-4">Create New Printout</button>

    <table class="table-auto w-full">
      <thead>
        <tr>
          <th class="px-4 py-2">Title</th>
          <th class="px-4 py-2">Description</th>
          <th class="px-4 py-2">Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="printout in printouts" :key="printout.id">
          <td class="border px-4 py-2">{{ printout.title }}</td>
          <td class="border px-4 py-2">{{ printout.description }}</td>
          <td class="border px-4 py-2">
            <button @click="editPrintout(printout)" class="btn btn-secondary">Edit</button>
            <button @click="deletePrintout(printout.id)" class="btn btn-danger">Delete</button>
          </td>
        </tr>
      </tbody>
    </table>

    <CreatePrintoutModal v-if="isCreateModalOpen" @close="closeCreateModal" @created="fetchPrintouts" />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';


const printouts = ref([]);
const isCreateModalOpen = ref(false);

const fetchPrintouts = async () => {
  const response = await axios.get('/api/printouts');
  printouts.value = response.data;
};

const openCreateModal = () => {
  isCreateModalOpen.value = true;
};

const closeCreateModal = () => {
  isCreateModalOpen.value = false;
};

const deletePrintout = async (id) => {
  await axios.delete(`/api/printouts/${id}`);
  fetchPrintouts();
};

onMounted(fetchPrintouts);
</script>

<style>
.btn {
  padding: 0.5rem 1rem;
  border-radius: 0.375rem;
  color: white;
}

.btn-primary {
  background-color: #3b82f6;
  transition: background-color 0.3s;
}

.btn-primary:hover {
  background-color: #1d4ed8;
}

.btn-secondary {
  background-color: #f59e0b;
  transition: background-color 0.3s;
}

.btn-secondary:hover {
  background-color: #d97706;
}

.btn-danger {
  background-color: #ef4444;
  transition: background-color 0.3s;
}

.btn-danger:hover {
  background-color: #b91c1c;
}
</style>