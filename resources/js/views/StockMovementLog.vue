<template>
    <div>
        <div class="p-4 border-b border-gray-200 flex flex-wrap gap-4 items-center justify-between bg-gray-50/50">
            <div class="relative w-full md:w-64">
                <span class="absolute left-3 top-2.5 text-gray-400">
                    <Icon icon="mdi:magnify" class="h-5 w-5" />
                </span>
                <input 
                    v-model="filters.search" 
                    type="text" 
                    placeholder="Search movement #, SKU..." 
                    class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-white"
                    @input="debouncedFetch"
                >
            </div>
            
            <div class="flex gap-4">
                <select 
                    v-model="filters.type" 
                    @change="fetchData(1)"
                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 bg-white"
                >
                    <option value="">All Movement Types</option>
                    <option value="in">Input (Entry)</option>
                    <option value="out">Output (Sale)</option>
                    <option value="adjustment">Adjustment</option>
                    <option value="transfer">Transfer</option>
                </select>
                
                <button 
                    @click="fetchData(1)" 
                    class="p-2 text-gray-400 hover:text-indigo-600 transition-colors"
                >
                    <Icon icon="mdi:refresh" class="h-5 w-5" :class="{ 'animate-spin': loading }" />
                </button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider w-20">#</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider w-28">Type</th>
                        <th v-if="!productId" class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Product</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Warehouse</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right w-28">Qty</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider w-32">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <tr v-if="loading" v-for="i in 5" :key="i" class="animate-pulse">
                        <td class="px-4 py-4"><div class="h-4 bg-gray-200 rounded w-12"></div></td>
                        <td class="px-4 py-4"><div class="h-6 bg-gray-200 rounded-full w-20"></div></td>
                        <td class="px-4 py-4"><div class="h-4 bg-gray-200 rounded w-32"></div></td>
                        <td class="px-4 py-4"><div class="h-4 bg-gray-200 rounded w-28"></div></td>
                        <td class="px-4 py-4"><div class="h-4 bg-gray-200 rounded w-16 ml-auto"></div></td>
                        <td class="px-4 py-4"><div class="h-4 bg-gray-200 rounded w-24"></div></td>
                    </tr>
                    
                    <tr v-else-if="movements.length === 0">
                        <td colspan="5" class="px-6 py-12 text-center text-gray-500 italic">
                            No stock movements found.
                        </td>
                    </tr>
                    
                    <tr v-for="movement in movements" :key="movement.id" class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-4 whitespace-nowrap">
                            <div class="font-medium text-gray-900">#{{ movement.id }}</div>
                        </td>                       
                        <td class="px-4 py-4 whitespace-nowrap">
                            <span 
                                class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider border"
                                :class="getTypeClass(movement.type)"
                            >
                                {{ movement.type }}
                            </span>
                        </td>
                        <td v-if="!productId" class="px-4 py-4">
                            <div class="text-sm font-medium text-gray-900">{{ movement.product?.name }}</div>
                            <div class="text-[10px] text-gray-500">{{ movement.product?.sku }}</div>
                        </td>
                        <td class="px-4 py-4">
                            <div v-if="movement.type === 'transfer'" class="flex flex-col gap-0.5 text-[11px]">
                                <span class="text-red-500 font-medium">From: {{ movement.from_warehouse?.name }}</span>
                                <span class="text-emerald-500 font-medium">To: {{ movement.to_warehouse?.name }}</span>
                            </div>
                            <div v-else class="text-sm text-gray-700">
                                {{ movement.warehouse?.name }}
                            </div>
                        </td>
                        <td class="px-4 py-4 text-right">
                            <div class="flex flex-col items-end">
                                <span 
                                    class="font-bold text-sm"
                                    :class="getQuantityClass(movement.type)"
                                >
                                    {{ movement.type === 'out' ? '-' : '+' }}{{ movement.quantity }}
                                </span>
                                <span class="text-[10px] text-gray-400 uppercase">{{ movement.product?.unit }}</span>
                            </div>
                        </td>
                         <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ formatDateTime(movement.movement_date) }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div v-if="pagination.last_page > 1" class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-between">
            <div class="text-xs text-gray-500 font-medium uppercase tracking-wider">
                Showing {{ pagination.from }}-{{ pagination.to }} of {{ pagination.total }} Movements
            </div>
            <div class="flex gap-2">
                <button 
                    @click="fetchData(pagination.current_page - 1)" 
                    :disabled="pagination.current_page === 1"
                    class="px-4 py-1 text-sm border border-gray-300 rounded-lg font-medium hover:bg-white disabled:opacity-40 transition-colors"
                >
                    Prev
                </button>
                <button 
                    @click="fetchData(pagination.current_page + 1)" 
                    :disabled="pagination.current_page === pagination.last_page"
                    class="px-4 py-1 text-sm border border-gray-300 rounded-lg font-medium hover:bg-white disabled:opacity-40 transition-colors"
                >
                    Next
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted, watch } from 'vue';
import { Icon } from '@iconify/vue';
import api from '../axios';
import { debounce } from 'lodash';

const props = defineProps({
    productId: {
        type: [Number, String],
        default: null
    },
    filters: {
        type: Object,
        default: () => ({})
    }
});

const movements = ref([]);
const loading = ref(false);
const filters = reactive({
    search: '',
    type: '',
    product_id: props.productId
});

const pagination = ref({
    current_page: 1,
    last_page: 1,
    total: 0,
    from: 0,
    to: 0
});

const fetchData = async (page = 1) => {
    loading.value = true;
    try {
        const response = await api.get('/inventory/movements', {
            params: {
                page,
                search: filters.search,
                type: filters.type,
                product_id: filters.product_id,
                category_id: props.filters.category_id || null,
                supplier_id: props.filters.supplier_id || null,
                per_page: 10
            }
        });
        
        const resData = response.data.data;
        movements.value = resData.data;
        pagination.value = {
            current_page: resData.current_page,
            last_page: resData.last_page,
            total: resData.total,
            from: resData.from,
            to: resData.to
        };
    } catch (error) {
        console.error('Error fetching movements:', error);
    } finally {
        loading.value = false;
    }
};

const debouncedFetch = debounce(() => {
    fetchData(1);
}, 300);

const formatDateTime = (dateStr) => {
    if (!dateStr) return 'N/A';
    return new Date(dateStr).toLocaleString('en-US', {
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const getTypeClass = (type) => {
    switch (type) {
        case 'in': return 'bg-emerald-50 text-emerald-700 border-emerald-100';
        case 'out': return 'bg-rose-50 text-rose-700 border-rose-100';
        case 'adjustment': return 'bg-amber-50 text-amber-700 border-amber-100';
        case 'transfer': return 'bg-indigo-50 text-indigo-700 border-indigo-100';
        default: return 'bg-gray-50 text-gray-700 border-gray-100';
    }
};

const getQuantityClass = (type) => {
    switch (type) {
        case 'in': return 'text-emerald-600';
        case 'out': return 'text-rose-600';
        case 'adjustment': return 'text-amber-600';
        case 'transfer': return 'text-indigo-600';
        default: return 'text-gray-900';
    }
};

onMounted(() => {
    fetchData();
});

// Watch for global filter changes
watch(() => props.filters, () => {
    fetchData(1);
}, { deep: true });
</script>
