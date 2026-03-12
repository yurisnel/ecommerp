<template>
    <div class="space-y-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ t('orders.title') }}</h1>
                <p class="text-gray-500 text-sm mt-1">{{ t('orders.trackAndManageOrders') }}</p>
            </div>
            <router-link 
                :to="{ name: 'OrderCreate' }"
                class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition-colors flex items-center gap-2"
            >
                <Icon icon="mdi:plus" class="h-5 w-5" />
                <span>{{ t('orders.newOrder') }}</span>
            </router-link>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            
            <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">{{ t('orders.totalOrders') }}</p>
                        <p class="text-lg font-bold text-gray-900">{{ stats.count_orders }}</p>
                    </div>
                    <div class="p-2 bg-amber-50 rounded-lg">
                        <Icon icon="mdi:receipt" class="h-5 w-5 text-amber-600" />
                    </div>
                </div>
            </div>
            <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">{{ t('orders.totalSales') }}</p>
                        <p class="text-lg font-bold text-emerald-600">${{ stats.total_sales_amount.toFixed(2) }}</p>
                    </div>
                    <div class="p-2 bg-emerald-50 rounded-lg">
                        <Icon icon="mdi:currency-usd" class="h-5 w-5 text-emerald-600" />
                    </div>
                </div>
            </div>
            
            <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">{{ t('orders.investment') }}</p>
                        <p class="text-lg font-bold text-blue-600">${{ stats.total_cost_amount.toFixed(2) }}</p>
                    </div>
                    <div class="p-2 bg-blue-50 rounded-lg">
                        <Icon icon="mdi:chart-line" class="h-5 w-5 text-blue-600" />
                    </div>
                </div>
            </div>

            <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">{{ t('orders.profit') }}</p>
                        <p class="text-lg font-bold text-blue-600">${{ stats.total_profit_amount.toFixed(2) }}</p>
                    </div>
                    <div class="p-2 bg-blue-50 rounded-lg">
                        <Icon icon="mdi:chart-line" class="h-5 w-5 text-blue-600" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
            <div 
                class="flex items-center gap-2 mb-4 cursor-pointer"
                @click="showFilters = !showFilters"
            >
                <Icon :icon="showFilters ? 'mdi:chevron-down' : 'mdi:chevron-right'" class="h-5 w-5 text-gray-500" />
                <span class="font-medium text-gray-700">{{ t('orders.filters') }}</span>
                <span v-if="hasActiveFilters" class="bg-emerald-100 text-emerald-700 text-xs px-2 py-0.5 rounded-full">{{ t('orders.active') }}</span>
            </div>
            <div v-show="showFilters" class="flex flex-wrap gap-4 items-center">
                <div class="relative w-full md:w-64">
                    <Icon icon="mdi:magnify" class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 h-5 w-5" />
                    <input
                        v-model="filters.search"
                        type="text"
                        :placeholder="t('orders.searchOrders')"
                        class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                        @input="debouncedFetch"
                    >
                </div>

                <select
                    v-model="filters.customer_id"
                    @change="fetchData(1)"
                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 min-w-[180px]"
                >
                    <option :value="null">{{ t('orders.allCustomers') }}</option>
                    <option v-for="customer in customers" :key="customer.id" :value="customer.id">
                        {{ customer.name }}
                    </option>
                </select>

                <select
                    v-model="filters.status"
                    @change="fetchData(1)"
                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 min-w-[150px]"
                >
                    <option :value="null">{{ t('orders.allStatuses') }}</option>
                    <option v-for="status in orderStatuses" :key="status.id" :value="status.id">
                        {{ status.name }}
                    </option>
                </select>

                <select
                    v-model="filters.sales_channel_id"
                    @change="fetchData(1)"
                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 min-w-[150px]"
                >
                    <option :value="null">{{ t('orders.allChannels') }}</option>
                    <option v-for="channel in salesChannels" :key="channel.id" :value="channel.id">
                        {{ channel.name }}
                    </option>
                </select>

                <select
                    v-model="filters.payment_method_id"
                    @change="fetchData(1)"
                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 min-w-[150px]"
                >
                    <option :value="null">{{ t('orders.allPayments') }}</option>
                    <option v-for="pm in paymentMethods" :key="pm.id" :value="pm.id">
                        {{ pm.name }}
                    </option>
                </select>

                <input
                    v-model="filters.date_start"
                    type="date"
                    :placeholder="t('orders.startDate')"
                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500"
                    @change="fetchData(1)"
                >

                <input
                    v-model="filters.date_end"
                    type="date"
                    :placeholder="t('orders.endDate')"
                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500"
                    @change="fetchData(1)"
                >

                <button
                    v-if="hasActiveFilters"
                    @click="clearFilters"
                    class="text-sm text-red-600 hover:text-red-800 font-medium"
                >
                    {{ t('orders.clearFilters') }}
                </button>
            </div>
        </div>

        <DataTable
            :columns="columns"
            :items="items"
            :loading="loading"
            :pagination="pagination"            
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
                <span v-else class="text-gray-400 italic">{{ t('orders.noCustomer') }}</span>
            </template>

            <template #payment_method="{ item }">
                <div v-if="item.payments && item.payments.length > 0" class="text-xs">
                    <div class="font-medium text-gray-700">
                        {{ getPaymentMethods(item.payments) }}
                    </div>
                    <div 
                        class="font-semibold mt-0.5"
                        :class="isPaymentComplete(item) ? 'text-emerald-600' : 'text-red-600'"
                    >
                        ${{ getTotalPaid(item.payments).toFixed(2) }}                       
                    </div>
                </div>
                <span v-else class="text-red-500 text-sm italic font-medium">{{ t('orders.notPaid') }}</span>
            </template>

            <template #status="{ item }">
                <span 
                    v-if="item.order_status"
                    class="px-2 py-0.5 rounded text-[10px] font-bold uppercase border text-white"
                    :style="{ backgroundColor: item.order_status.color, borderColor: item.order_status.color }"
                >
                    {{ item.order_status.name }}
                </span>
                <span v-else class="text-gray-400 text-[10px]">{{ t('orders.noStatus') }}</span>
            </template>

            <template #total="{ item }">
                <span class="font-bold text-gray-900">${{ Number(item.total).toFixed(2) }}</span>
            </template>

             <template #rowActions="{ item }">
                <div class="flex justify-center gap-2">
                    <router-link 
                        :to="{ name: 'OrderView', params: { id: item.id } }"
                        class="p-1 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded transition-colors"
                        :title="t('common.viewDetails')"
                    >
                        <Icon icon="mdi:eye" class="h-5 w-5" />
                    </router-link>
                </div>
            </template>
        </DataTable>
    </div>
</template>

<script setup>
import { ref, onMounted, reactive, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '../axios';
import DataTable from '../components/DataTable.vue';
import { Icon } from '@iconify/vue';
import { debounce } from 'lodash';

const { t } = useI18n();

const loading = ref(false);
const showFilters = ref(false);
const items = ref([]);
const customers = ref([]);
const orderStatuses = ref([]);
const salesChannels = ref([]);
const paymentMethods = ref([]);

const filters = reactive({
    search: '',
    customer_id: null,
    status: null,
    sales_channel_id: null,
    payment_method_id: null,
    date_start: '',
    date_end: ''
});

const stats = ref({
    count_orders: 0,
    total_sales_amount: 0,
    total_cost_amount: 0,
    total_profit_amount: 0
});

const pagination = ref({
    current_page: 1,
    last_page: 1,
    total: 0
});

const columns = computed(() => [
    { key: 'order_number', label: t('orders.orderNumber') }, 
    { key: 'customer', label: t('orders.customer') },
    { key: 'payment_method', label: t('orders.payment') },
    { key: 'total', label: t('orders.total') },
    { key: 'status', label: t('common.status') },
    { key: 'order_date', label: t('common.date') },
]);

const hasActiveFilters = computed(() => {
    return filters.customer_id || filters.status || filters.sales_channel_id || 
           filters.payment_method_id || filters.date_start || filters.date_end;
});

const fetchStats = async () => {
    try {
        const params = {};
        if (filters.customer_id) params.customer_id = filters.customer_id;
        if (filters.status) params.status = filters.status;
        if (filters.sales_channel_id) params.sales_channel_id = filters.sales_channel_id;
        if (filters.payment_method_id) params.payment_method_id = filters.payment_method_id;
        if (filters.date_start) params.date_start = filters.date_start;
        if (filters.date_end) params.date_end = filters.date_end;

        const response = await api.get('/orders/stats', { params });
        stats.value = response.data.data;
    } catch (error) {
        console.error('Error loading stats', error);
    }
};

const fetchData = async (page = 1) => {
    loading.value = true;
    try {
        const params = {
            page,
            search: filters.search,
            customer_id: filters.customer_id || undefined,
            status: filters.status || undefined,
            sales_channel_id: filters.sales_channel_id || undefined,
            payment_method_id: filters.payment_method_id || undefined,
            date_start: filters.date_start || undefined,
            date_end: filters.date_end || undefined
        };
        
        const response = await api.get('/orders', { params });
        
        const resData = response.data.data;
        items.value = resData.data;
        pagination.value = {
            current_page: resData.current_page,
            last_page: resData.last_page,
            total: resData.total
        };
        
        // Also update stats when data changes
        fetchStats();
    } catch (error) {
        console.error('Error loading orders', error);
    } finally {
        loading.value = false;
    }
};

const fetchFilters = async () => {
    try {
        const [customersRes, statusesRes, channelsRes, paymentRes] = await Promise.all([
            api.get('/customers', { params: { per_page: -1 } }),
            api.get('/order-statuses', { params: { per_page: -1 } }),
            api.get('/sales-channels', { params: { per_page: -1 } }),
            api.get('/payment-methods', { params: { per_page: -1 } })
        ]);
        
        customers.value = customersRes.data.data.data || customersRes.data.data;
        orderStatuses.value = statusesRes.data.data.data || statusesRes.data.data;
        salesChannels.value = channelsRes.data.data.data || channelsRes.data.data;
        paymentMethods.value = paymentRes.data.data.data || paymentRes.data.data;
    } catch (error) {
        console.error('Error loading filters', error);
    }
};

const clearFilters = () => {
    filters.customer_id = null;
    filters.status = null;
    filters.sales_channel_id = null;
    filters.payment_method_id = null;
    filters.date_start = '';
    filters.date_end = '';
    fetchData(1);
};

const handleSearch = debounce((val) => {
    filters.search = val;
    fetchData(1);
}, 300);

const debouncedFetch = debounce(() => {
    fetchData(1);
}, 300);

const formatDate = (date) => {
    if (!date) return 'N/A';
    return new Date(date).toLocaleString();
};

// Helper functions for payments
const getPaymentMethods = (payments) => {
    if (!payments || payments.length === 0) return '';
    const methods = [...new Set(payments.map(p => p.payment_method?.name || p.paymentMethod?.name || 'Unknown'))];
    return methods.join(', ');
};

const getTotalPaid = (payments) => {
    if (!payments || payments.length === 0) return 0;
    return payments.reduce((sum, p) => sum + Number(p.amount || 0), 0);
};

const isPaymentComplete = (item) => {
    const totalPaid = getTotalPaid(item.payments);
    return totalPaid >= Number(item.total);
};

onMounted(() => {
    fetchFilters();
    fetchData();
    fetchStats();
});
</script>
