<template>
    <div>
        <div class="p-4 border-b border-gray-200 flex flex-wrap gap-4 items-center justify-between bg-gray-50/50">
            <div class="relative w-full md:w-64">
                <span class="absolute left-3 top-2.5 text-gray-400">
                    <MagnifyingGlassIcon class="h-5 w-5" />
                </span>
                <input 
                    v-model="filters.search" 
                    type="text" 
                    placeholder="Search SKU, name..." 
                    class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-white"
                    @input="debouncedFetch"
                >
            </div>
            
            <div class="flex gap-4">
                <select 
                    v-model="filters.status" 
                    @change="fetchData(1)"
                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 bg-white"
                >
                    <option value="">All Stock Status</option>
                    <option value="in_stock">In Stock</option>
                    <option value="low_stock">Low Stock</option>
                    <option value="out_of_stock">Out of Stock</option>
                </select>
                
                <button 
                    @click="fetchData(1)" 
                    class="p-2 text-gray-400 hover:text-indigo-600 transition-colors"
                >
                    <ArrowPathIcon class="h-5 w-5" :class="{ 'animate-spin': loading }" />
                </button>

                <router-link 
                    :to="{ name: 'ProductCreate' }"
                    class="ml-2 bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition-all flex items-center gap-2 text-sm font-semibold shadow-sm"
                >
                    <PlusIcon class="h-4 w-4" />
                    <span>New Product</span>
                </router-link>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Product</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Categories</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Total Stock</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Available</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Reserved</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <tr v-if="loading" v-for="i in 5" :key="i" class="animate-pulse">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="h-10 w-10 bg-gray-200 rounded-lg"></div>
                                <div class="space-y-2">
                                    <div class="h-4 bg-gray-200 rounded w-24"></div>
                                    <div class="h-3 bg-gray-200 rounded w-16"></div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4"><div class="h-4 bg-gray-200 rounded w-16"></div></td>
                        <td class="px-6 py-4"><div class="h-4 bg-gray-200 rounded w-8 ml-auto"></div></td>
                        <td class="px-6 py-4"><div class="h-4 bg-gray-200 rounded w-8 ml-auto"></div></td>
                        <td class="px-6 py-4 text-right text-amber-600 font-medium"><div class="h-4 bg-gray-200 rounded w-8 ml-auto"></div></td>
                        <td class="px-6 py-4"><div class="h-6 bg-gray-200 rounded-full w-16"></div></td>
                    </tr>
                    
                    <tr v-else-if="products.length === 0">
                        <td colspan="6" class="px-6 py-12 text-center text-gray-500 italic">
                            No products found matching filters.
                        </td>
                    </tr>
                    
                    <tr v-for="product in products" :key="product.id" class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <img 
                                    :src="product.default_image?.url || '/placeholder-product.png'" 
                                    class="h-10 w-10 rounded-lg object-cover border border-gray-100 bg-gray-50"
                                >
                                <div>
                                    <div class="font-medium text-gray-900">{{ product.name }}</div>
                                    <div class="text-xs text-gray-500">{{ product.sku }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-wrap gap-1">
                                <span 
                                    v-for="cat in product.categories" 
                                    :key="cat.id"
                                    class="px-2 py-0.5 bg-indigo-50 text-indigo-700 text-[10px] rounded uppercase font-semibold"
                                >
                                    {{ cat.name }}
                                </span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-right font-semibold text-gray-900">
                            {{ product.total_quantity }} <span class="text-[10px] text-gray-400 font-normal uppercase ml-0.5">{{ product.unit }}</span>
                        </td>
                        <td class="px-6 py-4 text-right text-emerald-600 font-medium">
                            {{ product.total_available }}
                        </td>
                        <td class="px-6 py-4 text-right text-amber-600 font-medium">
                            {{ product.total_reserved || 0 }}
                        </td>
                        <td class="text-right">
                            <router-link 
                                :to="{ name: 'ProductEdit', params: { id: product.id } }"
                                class="p-1.5 text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors inline-block"
                                title="Edit Product Metadata"
                            >
                                <PencilSquareIcon class="h-4 w-4" />
                            </router-link>
                            <button 
                                @click="deleteProduct(product.id)"
                                class="p-1.5 text-rose-600 hover:bg-rose-50 rounded-lg transition-colors"
                                title="Delete Product"
                            >
                                <TrashIcon class="h-4 w-4" />
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div v-if="pagination.last_page > 1" class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-between">
            <div class="text-xs text-gray-500 font-medium uppercase tracking-wider">
                Showing {{ pagination.from }}-{{ pagination.to }} of {{ pagination.total }} Products
            </div>
            <div class="flex gap-2">
                <button 
                    @click="fetchData(pagination.current_page - 1)" 
                    :disabled="pagination.current_page === 1"
                    class="px-4 py-1 text-sm border border-gray-300 rounded-lg font-medium hover:bg-white disabled:opacity-40 disabled:cursor-not-allowed transition-colors"
                >
                    Prev
                </button>
                <button 
                    @click="fetchData(pagination.current_page + 1)" 
                    :disabled="pagination.current_page === pagination.last_page"
                    class="px-4 py-1 text-sm border border-gray-300 rounded-lg font-medium hover:bg-white disabled:opacity-40 disabled:cursor-not-allowed transition-colors"
                >
                    Next
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import { MagnifyingGlassIcon, ArrowPathIcon, PlusIcon, PencilSquareIcon, TrashIcon } from '@heroicons/vue/24/outline';
import api from '../axios';
import { debounce } from 'lodash';

const products = ref([]);
const loading = ref(false);
const filters = reactive({
    search: '',
    status: ''
});

const pagination = ref({
    current_page: 1,
    last_page: 1,
    total: 0,
    from: 0,
    to: 0
});

const emit = defineEmits(['refresh-stats']);

const fetchData = async (page = 1) => {
    loading.value = true;
    try {
        const response = await api.get('/inventory', {
            params: {
                page,
                search: filters.search,
                status: filters.status,
                per_page: 10
            }
        });
        
        const resData = response.data.data;
        products.value = resData.data;
        pagination.value = {
            current_page: resData.current_page,
            last_page: resData.last_page,
            total: resData.total,
            from: resData.from,
            to: resData.to
        };
        
        emit('refresh-stats');
    } catch (error) {
        console.error('Error fetching inventory summary:', error);
    } finally {
        loading.value = false;
    }
};

const debouncedFetch = debounce(() => {
    fetchData(1);
}, 300);


const deleteProduct = async (id) => {
    if (!confirm('Are you sure you want to delete this product? This will also remove all its inventory records and images.')) {
        return;
    }

    try {
        await api.delete(`/products/${id}`);
        alert('Product deleted successfully');
        fetchData(pagination.value.current_page);
    } catch (error) {
        console.error('Error deleting product:', error);
        alert(error.response?.data?.message || 'Failed to delete product');
    }
};

onMounted(() => {
    fetchData();
});
</script>
