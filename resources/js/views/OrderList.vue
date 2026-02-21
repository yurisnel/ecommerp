<template>
    <div class="space-y-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Sales Orders</h1>
                <p class="text-gray-500 text-sm mt-1">Track and manage customer orders and fulfillment status.</p>
            </div>
            <router-link 
                :to="{ name: 'OrderCreate' }"
                class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition-colors flex items-center gap-2"
            >
                <Icon icon="mdi:plus" class="h-5 w-5" />
                <span>New Order</span>
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
            <template #order_number="{ item }">
                <router-link 
                    :to="{ name: 'OrderView', params: { id: item.id } }"
                    class="font-medium text-indigo-600 hover:text-indigo-900"
                >
                    {{ item.order_number }}
                </router-link>
            </template>

            <template #order_date="{ item }">
                <span class="text-sm text-gray-600">{{ formatDate(item.order_date) }}</span>
            </template>

            <template #customer="{ item }">
                <div v-if="item.customer" class="flex flex-col">
                    <span class="text-gray-900 font-medium">{{ item.customer.name }}</span>
                    <span class="text-[10px] text-gray-400 uppercase tracking-wider">{{ item.customer.customer_number }}</span>
                </div>
                <span v-else class="text-gray-400 italic">No customer</span>
            </template>

            <template #status="{ item }">
                <span 
                    v-if="item.order_status"
                    class="px-2 py-0.5 rounded text-[10px] font-bold uppercase border text-white"
                    :style="{ backgroundColor: item.order_status.color, borderColor: item.order_status.color }"
                >
                    {{ item.order_status.name }}
                </span>
                <span v-else class="text-gray-400 text-[10px]">No status</span>
            </template>

            <template #total="{ item }">
                <span class="font-bold text-gray-900">${{ Number(item.total).toFixed(2) }}</span>
            </template>

             <template #rowActions="{ item }">
                <div class="flex justify-center gap-2">
                    <router-link 
                        :to="{ name: 'OrderView', params: { id: item.id } }"
                        class="p-1 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded transition-colors"
                        title="View Details"
                    >
                        <Icon icon="mdi:eye" class="h-5 w-5" />
                    </router-link>
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
    { key: 'order_number', label: 'Order #' }, 
    { key: 'customer', label: 'Customer' },
    { key: 'total', label: 'Total' },
    { key: 'status', label: 'Status' },
    { key: 'order_date', label: 'Date' },
];

const fetchData = async (page = 1) => {
    loading.value = true;
    try {
        const response = await api.get('/orders', {
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
        console.error('Error loading orders', error);
    } finally {
        loading.value = false;
    }
};

const handleSearch = debounce((val) => {
    filters.search = val;
    fetchData(1);
}, 300);

const formatDate = (date) => {
    if (!date) return 'N/A';
    return new Date(date).toLocaleDateString();
};

onMounted(() => fetchData());
</script>
