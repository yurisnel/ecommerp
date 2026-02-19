<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Product Inventory</h1>
                <p class="text-gray-500 text-sm mt-1">Manage products, monitor stock levels, track movements, and manage product entries.</p>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Total Products</p>
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
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Low Stock Items</p>
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
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Stock Transfers</p>
                        <h3 class="text-2xl font-bold text-indigo-600 mt-1">{{ stats.recent_transfers }}</h3>
                    </div>
                    <div class="bg-indigo-50 p-2 rounded-lg">
                        <Icon icon="mdi:transfer" class="h-6 w-6 text-indigo-600" />
                    </div>
                </div>
            </div>
            <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Inventory Value</p>
                        <h3 class="text-2xl font-bold text-emerald-600 mt-1">${{ formatPrice(stats.total_value) }}</h3>
                    </div>
                    <div class="bg-emerald-50 p-2 rounded-lg">
                        <Icon icon="mdi:currency-usd" class="h-6 w-6 text-emerald-600" />
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
                    @refresh-stats="fetchStats"
                />
            </keep-alive>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, defineAsyncComponent, onMounted } from 'vue';
import { Icon } from '@iconify/vue';
import api from '../axios';

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
    recent_transfers: 0,
    total_value: 0
});

const fetchStats = async () => {
    try {
        // In a real app, this would be a dedicated stats endpoint
        const response = await api.get('/inventory?per_page=1');
        stats.value.total_products = response.data.data.total;
        
        // Mocking other stats for demo purposes since we don't have a stats endpoint yet
        // We could fetch them or create a dedicated API method later
        stats.value.low_stock = 3; 
        stats.value.recent_transfers = 12;
        stats.value.total_value = 145000;
    } catch (error) {
        console.error('Error fetching inventory stats:', error);
    }
};

const formatPrice = (value) => {
    return new Intl.NumberFormat('en-US', { minimumFractionDigits: 2 }).format(value);
};

onMounted(() => {
    fetchStats();
});
</script>
