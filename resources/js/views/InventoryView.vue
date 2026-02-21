<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Product Inventory</h1>
                <p class="text-gray-500 text-sm mt-1">Manage products, monitor stock levels, track movements, and manage product entries.</p>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
            <div class="flex flex-wrap gap-4 items-center">
                <div class="flex-1 min-w-[200px]">
                    <input 
                        v-model="globalFilters.search" 
                        type="text" 
                        placeholder="Search..." 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500"
                        @input="debouncedFetchStats"
                    >
                </div>
                <select 
                    v-model="globalFilters.category_id" 
                    @change="fetchStats"
                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 min-w-[150px]"
                >
                    <option value="">All Categories</option>
                    <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                        {{ cat.name }}
                    </option>
                </select>
                <select 
                    v-model="globalFilters.supplier_id" 
                    @change="fetchStats"
                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 min-w-[150px]"
                >
                    <option value="">All Suppliers</option>
                    <option v-for="sup in suppliers" :key="sup.id" :value="sup.id">
                        {{ sup.name }}
                    </option>
                </select>
                <button 
                    @click="fetchStats" 
                    class="p-2 text-gray-400 hover:text-indigo-600 transition-colors"
                >
                    <Icon icon="mdi:refresh" class="h-5 w-5" />
                </button>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4">
            <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Products</p>
                        <h3 class="text-2xl font-bold text-gray-900 mt-1">{{ stats.total_products }}</h3>
                    </div>
                    <div class="bg-blue-50 p-2 rounded-lg">
                        <Icon icon="mdi:package-variant" class="h-6 w-6 text-blue-600" />
                    </div>
                </div>
            </div>
            <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Low Stock</p>
                        <h3 class="text-2xl font-bold text-red-600 mt-1">{{ stats.low_stock }}</h3>
                    </div>
                    <div class="bg-red-50 p-2 rounded-lg">
                        <Icon icon="mdi:bell-alert" class="h-6 w-6 text-red-600" />
                    </div>
                </div>
            </div>
            <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Out of Stock</p>
                        <h3 class="text-2xl font-bold text-gray-600 mt-1">{{ stats.out_of_stock }}</h3>
                    </div>
                    <div class="bg-gray-100 p-2 rounded-lg">
                        <Icon icon="mdi:package-variant-remove" class="h-6 w-6 text-gray-600" />
                    </div>
                </div>
            </div>
            <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Total Cost</p>
                        <h3 class="text-2xl font-bold text-amber-600 mt-1">${{ formatPrice(stats.total_invested) }}</h3>
                    </div>
                    <div class="bg-amber-50 p-2 rounded-lg">
                        <Icon icon="mdi:cash" class="h-6 w-6 text-amber-600" />
                    </div>
                </div>
            </div>
            <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Profit</p>
                        <h3 class="text-2xl font-bold text-indigo-600 mt-1">${{ formatPrice(stats.total_profit) }}</h3>
                    </div>
                    <div class="bg-indigo-50 p-2 rounded-lg">
                        <Icon icon="mdi:trending-up" class="h-6 w-6 text-indigo-600" />
                    </div>
                </div>
            </div>
          
        </div>

        <!-- Navigation Tabs -->
        <div class="border-b border-gray-200 bg-white rounded-t-xl px-4 pt-1 shadow-sm border-x border-t">
            <nav class="flex space-x-8" aria-label="Tabs">
                <button 
                    v-for="tab in tabs" 
                    :key="tab.id"
                    @click="activeTab = tab.id"
                    class="py-4 px-1 border-b-2 font-medium text-sm transition-colors relative"
                    :class="[
                        activeTab === tab.id 
                        ? 'border-indigo-500 text-indigo-600' 
                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                    ]"
                >
                    <div class="flex items-center gap-2">
                        <Icon :icon="tab.icon" class="h-5 w-5" />
                        {{ tab.name }}
                    </div>
                    <span 
                        v-if="tab.count !== undefined" 
                        class="ml-2 py-0.5 px-2 rounded-full text-xs"
                        :class="activeTab === tab.id ? 'bg-indigo-100 text-indigo-600' : 'bg-gray-100 text-gray-600'"
                    >
                        {{ tab.count }}
                    </span>
                </button>
            </nav>
        </div>

        <!-- Tab Content -->
        <div class="bg-white border-x border-b rounded-b-xl shadow-sm overflow-hidden min-h-[500px]">
            <keep-alive>
                <component 
                    :is="activeComponent" 
                    :filters="globalFilters"
                    @refresh-stats="fetchStats"
                />
            </keep-alive>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, defineAsyncComponent, onMounted, reactive } from 'vue';
import { Icon } from '@iconify/vue';
import api from '../axios';
import { debounce } from 'lodash';

// Async components for better performance
const InventorySummary = defineAsyncComponent(() => import('./InventorySummary.vue'));
const ProductEntryHistory = defineAsyncComponent(() => import('./ProductEntryHistory.vue'));
const StockMovementLog = defineAsyncComponent(() => import('./StockMovementLog.vue'));
const InventoryOperations = defineAsyncComponent(() => import('./InventoryOperations.vue'));

const activeTab = ref('summary');

const tabs = [
    { id: 'summary', name: 'Products', icon: 'mdi:table' },
    { id: 'entries', name: 'Purchase Entries', icon: 'mdi:inbox-arrow-down' },
    { id: 'movements', name: 'Movement Log', icon: 'mdi:swap-horizontal' },
    { id: 'operations', name: 'Operations', icon: 'mdi:tune' },
];

const activeComponent = computed(() => {
    switch (activeTab.value) {
        case 'summary': return InventorySummary;
        case 'entries': return ProductEntryHistory;
        case 'movements': return StockMovementLog;
        case 'operations': return InventoryOperations;
        default: return InventorySummary;
    }
});

const stats = ref({
    total_products: 0,
    low_stock: 0,
    out_of_stock: 0,
    total_quantity: 0,
    total_invested: 0,
    total_profit: 0,
    total_value: 0
});

// Global filters that affect stats
const globalFilters = reactive({
    search: '',
    category_id: '',
    supplier_id: '',
    product_id: ''
});

const categories = ref([]);
const suppliers = ref([]);

const fetchFilters = async () => {
    try {
        const [catRes, supRes] = await Promise.all([
            api.get('/categories', { params: { per_page: 100 } }),
            api.get('/suppliers', { params: { per_page: 100 } })
        ]);
        categories.value = catRes.data.data.data || [];
        suppliers.value = supRes.data.data.data || [];
    } catch (error) {
        console.error('Error fetching filters:', error);
    }
};

const fetchStats = async () => {
    try {
        const response = await api.get('/inventory/stats', {
            params: {
                search: globalFilters.search,
                category_id: globalFilters.category_id,
                supplier_id: globalFilters.supplier_id,
                product_id: globalFilters.product_id
            }
        });
        const data = response.data.data;
        
        stats.value.total_products = data.total_products || 0;
        stats.value.low_stock = data.low_stock || 0;
        stats.value.out_of_stock = data.out_of_stock || 0;
        stats.value.total_quantity = data.total_quantity || 0;
        stats.value.total_invested = data.total_invested || 0;
        stats.value.total_profit = data.total_profit || 0;
        stats.value.total_value = data.total_value || 0;
    } catch (error) {
        console.error('Error fetching inventory stats:', error);
    }
};

const debouncedFetchStats = debounce(() => {
    fetchStats();
}, 300);

const formatPrice = (value) => {
    return new Intl.NumberFormat('en-US', { minimumFractionDigits: 2 }).format(value);
};

onMounted(() => {
    fetchFilters();
    fetchStats();
});
</script>

