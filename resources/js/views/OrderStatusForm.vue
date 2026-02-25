<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center">
      <h1 class="text-2xl font-bold">{{ isEditing ? 'Edit Status' : 'New Status' }}</h1>
      <div>
        <button @click="$router.push({ name: 'OrderStatuses' })" class="px-3 py-2 border rounded">Back</button>
      </div>
    </div>

    <div class="bg-white p-6 border rounded-xl">
      <div class="grid grid-cols-1 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700">Name</label>
          <input v-model="form.name" class="w-full px-3 py-2 border rounded" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700">Slug</label>
          <input v-model="form.slug" class="w-full px-3 py-2 border rounded" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700">Description</label>
          <textarea v-model="form.description" class="w-full px-3 py-2 border rounded"></textarea>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700">Color</label>
          <div class="flex gap-3 items-center">
            <input 
              v-model="form.color" 
              type="color" 
              class="w-12 h-10 border rounded cursor-pointer"
            />
            <input 
              v-model="form.color" 
              type="text" 
              placeholder="#RRGGBB"
              class="flex-1 px-3 py-2 border rounded text-sm"
            />
          </div>
          <p v-if="form.color" class="text-xs text-gray-500 mt-1">Preview: <span class="inline-block w-4 h-4 rounded border" :style="{ backgroundColor: form.color }"></span> {{ form.color }}</p>
        </div>

        <div class="flex gap-2">
          <button @click="save" class="bg-indigo-600 text-white px-4 py-2 rounded">Save</button>
          <button @click="$router.push({ name: 'OrderStatuses' })" class="px-4 py-2 border rounded">Cancel</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import api from '../axios';
import swal from '../utils/swal';

const route = useRoute();
const router = useRouter();
const isEditing = ref(!!route.params.id);

const form = ref({ name: '', slug: '', description: '', color: '#cccccc' });

const fetch = async () => {
  if (!isEditing.value) return;
  try {
    const res = await api.get(`/order-statuses/${route.params.id}`);
    const data = res.data.data;
    form.value.name = data.name;
    form.value.slug = data.slug;
    form.value.description = data.description;
    form.value.color = data.color || '#cccccc';
  } catch (e) {
    console.error(e);
  }
};

const save = async () => {
  try {
    if (isEditing.value) {
      await api.put(`/order-statuses/${route.params.id}`, form.value);
    } else {
      await api.post('/order-statuses', form.value);
    }
    router.push({ name: 'OrderStatuses' });
  } catch (e) {
    swal.error(e.response?.data?.message || 'Save failed');
  }
};

onMounted(fetch);
</script>
