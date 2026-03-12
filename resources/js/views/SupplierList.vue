<template>
    <div class="space-y-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ t('suppliers.title') }}</h1>
                <p class="text-gray-500 text-sm mt-1">{{ t('suppliers.description') }}</p>
            </div>
            <router-link 
                :to="{ name: 'SupplierCreate' }" 
                class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition-colors flex items-center gap-2"
            >
                <i class="fas fa-plus"></i>
                <span>{{ t('suppliers.addSupplier') }}</span>
            </router-link>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="p-4 border-b border-gray-200 flex flex-wrap gap-4 items-center justify-between bg-gray-50">
                <div class="relative w-full md:w-64">
                    <span class="absolute left-3 top-2.5 text-gray-400">
                        <i class="fas fa-search"></i>
                    </span>
                    <input 
                        v-model="filters.search" 
                        type="text" 
                        :placeholder="t('suppliers.searchSuppliers')" 
                        class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-white"
                        @input="debouncedFetch"
                    >
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ t('suppliers.image') }}</th>
                            <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ t('common.name') }}</th>
                            <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ t('suppliers.contact') }}</th>
                            <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ t('suppliers.taxIdCompany') }}</th>
                            <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ t('common.status') }}</th>
                            <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">{{ t('common.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <tr v-if="loading" v-for="i in 3" :key="i" class="animate-pulse">
                            <td class="px-6 py-4"><div class="h-12 w-12 bg-gray-200 rounded-full"></div></td>
                            <td class="px-6 py-4"><div class="h-4 bg-gray-200 rounded w-3/4"></div></td>
                            <td class="px-6 py-4"><div class="h-4 bg-gray-200 rounded w-1/2"></div></td>
                            <td class="px-6 py-4"><div class="h-4 bg-gray-200 rounded w-2/3"></div></td>
                            <td class="px-6 py-4"><div class="h-6 bg-gray-200 rounded-full w-16"></div></td>
                            <td class="px-6 py-4 text-right"><div class="h-4 bg-gray-200 rounded w-12 ml-auto"></div></td>
                        </tr>
                        <tr v-else-if="suppliers.length === 0">
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                <div class="flex flex-col items-center">
                                    <i class="fas fa-user-tie text-4xl mb-3 text-gray-200"></i>
                                    <p>{{ t('suppliers.noSuppliers') }}</p>
                                </div>
                            </td>
                        </tr>
                        <tr v-for="supplier in suppliers" :key="supplier.id" class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4">
                                <img v-if="supplier.image" :src="supplier.image" :alt="supplier.name" class="h-12 w-12 rounded object-cover border border-gray-200" @error="onImageError">
                                <div v-else class="h-12 w-12 rounded bg-gray-200 flex items-center justify-center">
                                    <i class="fas fa-image text-gray-400"></i>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-medium text-gray-900">{{ supplier.name }}</div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                <div v-if="supplier.email" class="flex items-center gap-1">
                                    <i class="far fa-envelope text-gray-400"></i>
                                    {{ supplier.email }}
                                </div>
                                <div v-if="supplier.phone" class="flex items-center gap-1 mt-1">
                                    <i class="fas fa-phone text-gray-400"></i>
                                    {{ supplier.phone }}
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                <div>{{ supplier.tax_id || 'N/A' }}</div>
                                <div class="text-xs text-gray-500 italic">{{ supplier.company_name }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <span 
                                    class="px-2 py-1 text-xs font-medium rounded-full"
                                    :class="supplier.status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700'"
                                >
                                    {{ supplier.status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right text-sm">
                                <div class="flex justify-end gap-3">
                                    <router-link 
                                        :to="{ name: 'SupplierEdit', params: { id: supplier.id } }" 
                                        class="text-indigo-600 hover:text-indigo-900 font-medium"
                                    >
                                        {{ t('common.edit') }}
                                    </router-link>
                                    <button 
                                        @click="deleteSupplier(supplier.id)" 
                                        class="text-red-600 hover:text-red-900 font-medium"
                                    >
                                        {{ t('common.delete') }}
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="pagination.last_page > 1" class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-between">
                <div class="text-sm text-gray-500">
                    {{ t('common.showing') }} {{ pagination.from }} {{ t('common.to') }} {{ pagination.to }} {{ t('common.of') }} {{ pagination.total }} {{ t('categories.results') }}
                </div>
                <div class="flex gap-2">
                    <button 
                        @click="fetchSuppliers(pagination.current_page - 1)" 
                        :disabled="pagination.current_page === 1"
                        class="px-3 py-1 border border-gray-300 rounded hover:bg-white disabled:opacity-50"
                    >
                        {{ t('common.previous') }}
                    </button>
                    <button 
                        @click="fetchSuppliers(pagination.current_page + 1)" 
                        :disabled="pagination.current_page === pagination.last_page"
                        class="px-3 py-1 border border-gray-300 rounded hover:bg-white disabled:opacity-50"
                    >
                        {{ t('common.next') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, reactive } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '../axios';
import { debounce } from 'lodash';
import swal from '../utils/swal';

const { t } = useI18n();

const suppliers = ref([]);
const loading = ref(false);
const filters = reactive({
    search: ''
});

const pagination = ref({
    current_page: 1,
    last_page: 1,
    total: 0,
    from: 0,
    to: 0
});

const fetchSuppliers = async (page = 1) => {
    loading.value = true;
    try {
        const response = await api.get('/suppliers', {
            params: {
                page,
                search: filters.search,
                per_page: 15
            }
        });
        
        const data = response.data.data;
        suppliers.value = data.data || [];
        pagination.value = {
            current_page: data.current_page,
            last_page: data.last_page,
            total: data.total,
            from: data.from,
            to: data.to
        };
    } catch (error) {
        console.error('Error fetching suppliers:', error);
    } finally {
        loading.value = false;
    }
};

const debouncedFetch = debounce(() => {
    fetchSuppliers(1);
}, 300);

const deleteSupplier = async (id) => {
    const result = await swal.confirm(
        t('common.deleteConfirm', { item: t('suppliers.supplier').toLowerCase() }),
        t('common.deleteTitle')
    );
    if (!result.isConfirmed) return;
    
    try {
        await api.delete(`/suppliers/${id}`);
        fetchSuppliers(pagination.value.current_page);
    } catch (error) {
        console.error('Error deleting supplier:', error);
        swal.error(error.response?.data?.message || t('common.deleteError', { item: t('suppliers.supplier').toLowerCase() }));
    }
};

const onImageError = (event) => {
    event.target.src = 'data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 24 24%22 fill=%22%23d1d5db%22%3E%3Cpath d=%22M21 19V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z%22/%3E%3C/svg%3E';
};

onMounted(() => {
    fetchSuppliers();
});
</script>
