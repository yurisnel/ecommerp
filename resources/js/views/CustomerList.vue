<template>
    <div class="space-y-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Customers</h1>
                <p class="text-gray-500 text-sm mt-1">Manage your customer database and viewing their history.</p>
            </div>
            <router-link 
                :to="{ name: 'CustomerCreate' }"
                class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition-colors flex items-center gap-2"
            >
                <Icon icon="mdi:plus" class="h-5 w-5" />
                <span>New Customer</span>
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
            <template #type="{ item }">
                <span 
                    class="px-2 py-0.5 rounded text-[10px] font-bold uppercase border"
                    :class="item.type === 'wholesale' ? 'bg-purple-50 text-purple-700 border-purple-100' : 'bg-blue-50 text-blue-700 border-blue-100'"
                >
                    {{ item.type }}
                </span>
            </template>
             <template #rowActions="{ item }">
                 <div class="flex justify-center gap-2">
                    <router-link 
                        :to="{ name: 'CustomerEdit', params: { id: item.id } }"
                        class="p-1 text-indigo-600 hover:bg-indigo-50 rounded transition-colors"
                        title="Edit Customer"
                    >
                        <Icon icon="mdi:pencil" class="h-5 w-5" />
                    </router-link>
                    <button 
                        @click="deleteCustomer(item.id)"
                        class="p-1 text-rose-600 hover:bg-rose-50 rounded transition-colors"
                        title="Delete Customer"
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
    { key: 'customer_number', label: 'ID' },
    { key: 'name', label: 'Name' },
    { key: 'email', label: 'Email' },
    { key: 'phone', label: 'Phone' },
    { key: 'type', label: 'Type' }
];

const fetchData = async (page = 1) => {
    loading.value = true;
    try {
        const response = await api.get('/customers', {
            params: {
                page,
                search: filters.search
            }
        });
        
        const resData = response.data.data;
        items.value = resData.data;
        pagination.value = {
            current_page: resData.current_page,
            last_page: resData.last_page,
            total: resData.total
        };
    } catch (error) {
        console.error('Error loading customers', error);
    } finally {
        loading.value = false;
    }
};

const handleSearch = debounce((val) => {
    filters.search = val;
    fetchData(1);
}, 300);

const deleteCustomer = async (id) => {
    if (!confirm('Are you sure you want to delete this customer?')) return;
    
    try {
        await api.delete(`/customers/${id}`);
        fetchData(pagination.value.current_page);
    } catch (error) {
        console.error('Error deleting customer:', error);
        alert(error.response?.data?.message || 'Failed to delete customer');
    }
};

onMounted(() => fetchData());
</script>
