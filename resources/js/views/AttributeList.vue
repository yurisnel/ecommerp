<template>
    <div class="space-y-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Product Attributes</h1>
                <p class="text-gray-500 text-sm mt-1">Manage product attributes like Color, Size, Material, etc.</p>
            </div>
            <router-link 
                :to="{ name: 'AttributeCreate' }"
                class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition-colors flex items-center gap-2"
            >
                <Icon icon="mdi:plus" class="h-5 w-5" />
                <span>New Attribute</span>
            </router-link>
        </div>

        <DataTable
            :columns="columns"
            :items="items"
            :loading="loading"
            :pagination="pagination"
            searchable
            @search="handleSearch"
            @page-change="fetchData"
        >
            <template #name="{ item }">
                <div class="font-medium text-gray-900">{{ item.name }}</div>
            </template>

            <template #type="{ item }">
                <span class="px-2 py-1 text-xs font-medium rounded-full"
                    :class="{
                        'bg-blue-100 text-blue-800': item.type === 'select',
                        'bg-green-100 text-green-800': item.type === 'color',
                        'bg-purple-100 text-purple-800': item.type === 'size'
                    }"
                >
                    {{ item.type }}
                </span>
            </template>

            <template #values_count="{ item }">
                <span class="text-gray-600">{{ item.values ? item.values.length : 0 }} values</span>
            </template>

            <template #rowActions="{ item }">
                <div class="flex justify-center gap-2">
                    <router-link 
                        :to="{ name: 'AttributeEdit', params: { id: item.id } }"
                        class="p-1 text-indigo-600 hover:bg-indigo-50 rounded transition-colors"
                        title="Edit Attribute"
                    >
                        <Icon icon="mdi:pencil" class="h-5 w-5" />
                    </router-link>
                    <button 
                        @click="deleteAttribute(item.id)"
                        class="p-1 text-rose-600 hover:bg-rose-50 rounded transition-colors"
                        title="Delete Attribute"
                    >
                        <Icon icon="mdi:trash-can" class="h-5 w-5" />
                    </button>
                </div>
            </template>
        </DataTable>
    </div>
</template>

<script setup>
import { ref, onMounted, reactive } from 'vue';
import api from '../axios';
import DataTable from '../components/DataTable.vue';
import swal from '../utils/swal';
import { Icon } from '@iconify/vue';
import { debounce } from 'lodash';

const loading = ref(false);
const items = ref([]);
const filters = reactive({
    search: ''
});

const pagination = ref({
    current_page: 1,
    last_page: 1,
    total: 0
});

const columns = [
    { key: 'name', label: 'Name' },
    { key: 'type', label: 'Type' },
    { key: 'values_count', label: 'Values' }
];

const fetchData = async (page = 1) => {
    loading.value = true;
    try {
        const response = await api.get('/attributes', {
            params: { page, search: filters.search }
        });
        const resData = response.data.data;
        items.value = resData.data;
        pagination.value = {
            current_page: resData.current_page,
            last_page: resData.last_page,
            total: resData.total
        };
    } catch (error) {
        console.error('Error loading attributes', error);
    } finally {
        loading.value = false;
    }
};

const handleSearch = debounce((val) => {
    filters.search = val;
    fetchData(1);
}, 300);

const deleteAttribute = async (id) => {
    const result = await swal.confirm('Are you sure you want to delete this attribute?', 'Delete Attribute');
    if (!result.isConfirmed) return;
    try {
        await api.delete(`/attributes/${id}`);
        fetchData(pagination.value.current_page);
    } catch (error) {
        console.error('Error deleting attribute:', error);
        swal.error(error.response?.data?.message || 'Failed to delete attribute');
    }
};

onMounted(() => fetchData());
</script>
