<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center">
      <h1 class="text-2xl font-bold">Order Statuses</h1>
      <button @click="$router.push({ name: 'OrderStatusCreate' })" class="bg-indigo-600 text-white px-4 py-2 rounded">New Status</button>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
      <table class="w-full text-left">
        <thead class="text-xs text-gray-500 uppercase">
          <tr>
            <th class="px-4 py-2">Name</th>
            <th class="px-4 py-2">Slug</th>
            <th class="px-4 py-2">Description</th>
            <th class="px-4 py-2 text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y">
          <tr v-if="items.length === 0"><td colspan="4" class="p-4 text-gray-400">No statuses yet.</td></tr>
          <tr v-for="s in items" :key="s.id">
            <td class="px-4 py-3">{{ s.name }}</td>
            <td class="px-4 py-3">{{ s.slug }}</td>
            <td class="px-4 py-3">{{ s.description }}</td>
            <td class="px-4 py-3 text-right">
              <button @click="edit(s.id)" class="text-indigo-600 mr-2">Edit</button>
              <button @click="del(s.id)" class="text-rose-600">Delete</button>
            </td>
          </tr>
        </tbody>
      </table>

      <div class="mt-4">
        <button v-if="meta.prev_page_url" @click="fetch(meta.current_page - 1)" class="px-3 py-1 border rounded mr-2">Prev</button>
        <button v-if="meta.next_page_url" @click="fetch(meta.current_page + 1)" class="px-3 py-1 border rounded">Next</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import api from '../axios';
import swal from '../utils/swal';

const items = ref([]);
const meta = ref({ current_page: 1, last_page: 1, prev_page_url: null, next_page_url: null });

const fetch = async (page = 1) => {
  try {
    const res = await api.get('/order-statuses', { params: { per_page: 15, page } });
    items.value = res.data.data.data || res.data.data; // handle service differences
    meta.value = res.data.data.meta || { current_page: 1, last_page: 1 };
  } catch (e) {
    console.error(e);
  }
};

const edit = (id) => {
  // go to edit route
  window.router.push({ name: 'OrderStatusEdit', params: { id } });
};

const del = async (id) => {
  const result = await swal.confirm('Delete status?', 'Delete Status');
  if (!result.isConfirmed) return;
  try {
    await api.delete(`/order-statuses/${id}`);
    fetch(meta.value.current_page);
  } catch (e) {
    swal.error(e.response?.data?.message || 'Delete failed');
  }
};

onMounted(() => fetch());
</script>
