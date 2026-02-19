<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-900">Inventory Management</h1>
            <div class="flex gap-3">
                <button 
                    @click="$router.push({ name: 'InventoryEntry' })"
                    class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition-colors flex items-center gap-2"
                >
                    <i class="fas fa-plus"></i> New Entry
                </button>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-200">
            <div class="flex flex-wrap gap-4">
                <div class="flex-1 min-w-[200px]">
                    <input 
                        v-model="filters.search" 
                        type="text" 
                        placeholder="Search product, SKU, barcode..." 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                        @input="debounceSearch"
                    >
                </div>
                <div class="w-48">
                    <select 
                        v-model="filters.status" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                        @change="fetchInventory"
                    >
                        <option value="">All Statuses</option>
                        <option value="in_stock">In Stock</option>
                        <option value="low_stock">Low Stock</option>
                        <option value="out_of_stock">Out of Stock</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <table class="w-full text-left">
                <thead class="bg-gray-50 text-gray-700 font-medium border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-4">Product</th>
                        <th class="px-6 py-4">SKU / Barcode</th>
                        <th class="px-6 py-4">Categories</th>
                        <th class="px-6 py-4 text-center">Total Stock</th>
                        <th class="px-6 py-4 text-center">Reserved</th>
                        <th class="px-6 py-4 text-center">Available</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <tr v-if="loading" class="animate-pulse">
                        <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                            Loading inventory...
                        </td>
                    </tr>
                    <tr v-else-if="products.length === 0">
                        <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                            No inventory records found.
                        </td>
                    </tr>
                    <tr v-for="product in products" :key="product.id" class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="h-10 w-10 rounded border border-gray-200 overflow-hidden bg-gray-50 flex-shrink-0">
                                    <img v-if="product.default_image" :src="product.default_image.url" class="h-full w-full object-cover">
                                    <div v-else class="h-full w-full flex items-center justify-center text-gray-300">
                                        <i class="far fa-image"></i>
                                    </div>
                                </div>
                                <div>
                                    <div class="font-medium text-gray-900">{{ product.name }}</div>
                                    <div class="text-xs text-gray-500" v-if="product.unit">Unit: {{ getUnitLabel(product.unit) }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            <div>{{ product.sku }}</div>
                            <div class="text-xs text-gray-400">{{ product.barcode }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div v-if="product.categories && product.categories.length > 0" class="flex flex-wrap gap-1">
                                <span 
                                    v-for="cat in product.categories" 
                                    :key="cat.id" 
                                    class="px-2 py-1 bg-gray-100 text-gray-600 rounded text-xs"
                                >
                                    {{ cat.name }}
                                </span>
                            </div>
                            <span v-else class="text-gray-400 text-sm">-</span>
                        </td>
                        <td class="px-6 py-4 text-center font-medium">
                            {{ formatNumber(product.total_quantity) }}
                        </td>
                        <td class="px-6 py-4 text-center text-amber-600">
                            {{ formatNumber(product.total_reserved) }}
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span 
                                class="px-2 py-1 rounded-full text-xs font-medium"
                                :class="getStockStatusClass(product)"
                            >
                                {{ formatNumber(product.total_available) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <button class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">
                                View Details
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
            
            <!-- Pagination -->
            <div class="px-6 py-4 border-t border-gray-200 flex justify-between items-center" v-if="pagination.total > 0">
                <div class="text-sm text-gray-500">
                    Showing {{ pagination.from }} to {{ pagination.to }} of {{ pagination.total }} results
                </div>
                <div class="flex gap-2">
                    <button 
                        @click="changePage(pagination.current_page - 1)" 
                        :disabled="pagination.current_page === 1"
                        class="px-3 py-1 border border-gray-300 rounded hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        Previous
                    </button>
                    <button 
                        @click="changePage(pagination.current_page + 1)" 
                        :disabled="pagination.current_page === pagination.last_page"
                        class="px-3 py-1 border border-gray-300 rounded hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        Next
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import api from '../axios';
import { debounce } from 'lodash';

const products = ref([]);
const loading = ref(false);
const filters = ref({
    search: '',
    status: '',
    category_id: ''
});

const pagination = ref({
    current_page: 1,
    last_page: 1,
    total: 0,
    from: 0,
    to: 0
});

const formatNumber = (num) => {
    return Number(num).toLocaleString(undefined, { minimumFractionDigits: 0, maximumFractionDigits: 2 });
};

const getStockStatusClass = (product) => {
    const available = Number(product.total_available);
    const minStock = Number(product.min_stock || 0);

    if (available <= 0) {
        return 'bg-red-100 text-red-700';
    } else if (available < minStock) {
        return 'bg-amber-100 text-amber-700';
    } else {
        return 'bg-green-100 text-green-700';
    }
};

const getUnitLabel = (unit) => {
    const units = {
        'pcs': 'Units (Pcs)',
        'kg': 'Kilogram (Kg)',
        'm': 'Meter (M)',
        'l': 'Liter (L)',
        'box': 'Box' 
    };
    return units[unit] || unit;
};

const fetchInventory = async (page = 1) => {
    loading.value = true;
    try {
        const response = await api.get('/inventory', {
            params: {
                page,
                ...filters.value
            }
        });
        
        products.value = response.data.data.data;
        pagination.value = {
            current_page: response.data.data.current_page,
            last_page: response.data.data.last_page,
            total: response.data.data.total,
            from: response.data.data.from,
            to: response.data.data.to,
        };
    } catch (error) {
        console.error('Error fetching inventory:', error);
    } finally {
        loading.value = false;
    }
};

const debounceSearch = debounce(() => {
    fetchInventory(1);
}, 300);

const changePage = (page) => {
    if (page >= 1 && page <= pagination.value.last_page) {
        fetchInventory(page);
    }
};

onMounted(() => {
    fetchInventory();
});
</script>
