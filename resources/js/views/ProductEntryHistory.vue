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
                    placeholder="Search entry #, SKU..." 
                    class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-white"
                    @input="debouncedFetch"
                >
            </div>
            
            <div class="flex gap-4">
                <button 
                    @click="fetchData(pagination.current_page)" 
                    class="p-2 text-gray-400 hover:text-indigo-600 transition-colors"
                >
                    <Icon icon="mdi:refresh" class="h-5 w-5" :class="{ 'animate-spin': loading }" />
                </button>

                <router-link 
                    :to="{ name: 'InventoryEntry', query: { product_id: productId } }"
                    class="ml-2 bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition-all flex items-center gap-2 text-sm font-semibold shadow-sm"
                >
                    <Icon icon="mdi:plus" class="h-4 w-4" />
                    <span>New Stock Entry</span>
                </router-link>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Entry Detail</th>
                        <th v-if="!productId" class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Product</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Supplier</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Qty</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Cost</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Selling Price</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <tr v-if="loading" v-for="i in 5" :key="i" class="animate-pulse">
                        <td class="px-6 py-4"><div class="h-4 bg-gray-200 rounded w-24"></div></td>
                        <td class="px-6 py-4"><div class="h-4 bg-gray-200 rounded w-32"></div></td>
                        <td class="px-6 py-4"><div class="h-4 bg-gray-200 rounded w-24"></div></td>
                        <td class="px-6 py-4"><div class="h-4 bg-gray-200 rounded w-8 ml-auto"></div></td>
                        <td class="px-6 py-4"><div class="h-4 bg-gray-200 rounded w-16 ml-auto"></div></td>
                        <td class="px-6 py-4"><div class="h-4 bg-gray-200 rounded w-16 ml-auto"></div></td>
                        <td class="px-6 py-4"><div class="h-4 bg-gray-200 rounded w-20 ml-auto"></div></td>
                    </tr>
                    
                    <tr v-else-if="entries.length === 0">
                        <td colspan="7" class="px-6 py-12 text-center text-gray-500 italic">
                            No entry history found.
                        </td>
                    </tr>
                    
                    <tr v-for="entry in entries" :key="entry.id" class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="font-medium text-indigo-600">{{ entry.entry_number }}</div>
                            <div class="text-[10px] text-gray-400 uppercase font-semibold mt-1">{{ formatDate(entry.entry_date) }}</div>
                        </td>
                        <td v-if="!productId" class="px-6 py-4 text-sm">
                            <div class="text-gray-900 font-medium">{{ entry.product?.name }}</div>
                            <div class="text-xs text-gray-500">{{ entry.product?.sku }}</div>
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <span v-if="entry.supplier" class="text-gray-700">{{ entry.supplier.name }}</span>
                            <span v-else class="text-gray-400 italic">No supplier</span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <span class="font-semibold text-gray-900">{{ entry.quantity }}</span> 
                            <span class="text-[10px] text-gray-400 uppercase ml-1">{{ entry.product?.unit }}</span>
                        </td>
                        <td class="px-6 py-4 text-right font-medium text-gray-700">
                            ${{ Number(entry.cost_per_unit).toFixed(2) }}
                        </td>
                        <td class="px-6 py-4 text-right">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-100">
                                ${{ Number(entry.selling_price).toFixed(2) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right space-x-3">
                            <router-link 
                                :to="{ name: 'InventoryEntryEdit', params: { id: entry.id } }"
                                class="text-indigo-600 hover:text-indigo-900 transition-colors"
                                title="Edit Entry"
                            >
                                <Icon icon="mdi:pencil" class="h-5 w-5 inline" />
                            </router-link>
                            <button 
                                @click="deleteEntry(entry.id)"
                                class="text-rose-600 hover:text-rose-900 transition-colors"
                                title="Delete Entry"
                            >
                                <Icon icon="mdi:trash-can" class="h-5 w-5 inline" />
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div v-if="pagination.last_page > 1" class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-between">
            <div class="text-xs text-gray-500 font-medium uppercase tracking-wider">
                Showing {{ pagination.from }}-{{ pagination.to }} of {{ pagination.total }} Entries
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
import { ref, reactive, onMounted } from 'vue';
import { MagnifyingGlassIcon, ArrowPathIcon, PencilSquareIcon, TrashIcon, PlusIcon } from '@heroicons/vue/24/outline';
import api from '../axios';
import { debounce } from 'lodash';

const props = defineProps({
    productId: {
        type: [Number, String],
        default: null
    }
});

const entries = ref([]);
const loading = ref(false);
const filters = reactive({
    search: '',
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
        const response = await api.get('/inventory/entries', {
            params: {
                page,
                search: filters.search,
                product_id: filters.product_id,
                per_page: 10
            }
        });
        
        const resData = response.data.data;
        entries.value = resData.data;
        pagination.value = {
            current_page: resData.current_page,
            last_page: resData.last_page,
            total: resData.total,
            from: resData.from,
            to: resData.to
        };
    } catch (error) {
        console.error('Error fetching entry history:', error);
    } finally {
        loading.value = false;
    }
};

const debouncedFetch = debounce(() => {
    fetchData(1);
}, 300);

const deleteEntry = async (id) => {
    if (!confirm('Are you sure you want to delete this purchase entry? This will revert the stock in the warehouse. If the stock has already been used, deletion will fail.')) {
        return;
    }

    try {
        await api.delete(`/inventory/entries/${id}`);
        alert('Entry deleted successfully');
        fetchData(pagination.value.current_page);
    } catch (error) {
        console.error('Error deleting entry:', error);
        alert(error.response?.data?.message || 'Failed to delete entry');
    }
};

const formatDate = (dateStr) => {
    if (!dateStr) return 'N/A';
    return new Date(dateStr).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};

onMounted(() => {
    fetchData();
});
</script>
