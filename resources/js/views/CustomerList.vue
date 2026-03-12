<template>
    <div class="space-y-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ t('customers.title') }}</h1>
                <p class="text-gray-500 text-sm mt-1">{{ t('customers.description') }}</p>
            </div>
            <router-link 
                :to="{ name: 'CustomerCreate' }"
                class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition-colors flex items-center gap-2"
            >
                <Icon icon="mdi:plus" class="h-5 w-5" />
                <span>{{ t('customers.newCustomer') }}</span>
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
            <template #image="{ item }">
                <div class="flex items-center justify-center">
                    <img v-if="item.image" :src="item.image" :alt="item.name" class="h-8 w-8 rounded-full object-cover border border-gray-200" @error="onImageError">
                    <div v-else class="h-8 w-8 rounded-full bg-gray-200 flex items-center justify-center">
                        <i class="fas fa-user text-gray-400 text-xs"></i>
                    </div>
                </div>
            </template>

            <template #customer_group="{ item }">
                <span v-if="item.customer_group" class="px-2 py-1 bg-indigo-100 text-indigo-700 rounded-full text-xs font-medium">
                    {{ item.customer_group.name }}
                </span>
                <span v-else class="text-gray-400 text-xs">-</span>
            </template>

            <template #rowActions="{ item }">
                <div class="flex justify-center gap-2">
                    <router-link 
                        :to="{ name: 'CustomerEdit', params: { id: item.id } }"
                        class="p-1 text-indigo-600 hover:bg-indigo-50 rounded transition-colors"
                        :title="t('common.editTitle')"
                    >
                        <Icon icon="mdi:pencil" class="h-5 w-5" />
                    </router-link>
                    <button 
                        @click="deleteCustomer(item.id)"
                        class="p-1 text-rose-600 hover:bg-rose-50 rounded transition-colors"
                        :title="t('common.deleteTitle')"
                    >
                        <Icon icon="mdi:trash-can" class="h-5 w-5" />
                    </button>
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
import swal from '../utils/swal';

const { t } = useI18n();
const loading = ref([]);
const items = ref([]);
const filters = reactive({
    search: ''
});

const pagination = ref({
    current_page: 1,
    last_page: 1,
    total: 0
});

const columns = computed(() =>[
    { key: 'image', label: t('common.image') },
    { key: 'customer_number', label: t('common.id') },
    { key: 'name', label: t('common.name') },
    { key: 'email', label: t('common.email') },
    { key: 'phone', label: t('common.phone') },
    { key: 'customer_group', label: t('customers.group') }
]);


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
    const result = await swal.confirm(
        t('common.deleteConfirm', { item: t('customers.customer').toLowerCase() }),
        t('common.deleteTitle')
    );
    if (!result.isConfirmed) return;
    
    try {
        await api.delete(`/customers/${id}`);
        fetchData(pagination.value.current_page);
    } catch (error) {
        console.error('Error deleting customer:', error);
        swal.error(error.response?.data?.message || t('common.deleteError', { item: t('customers.customer').toLowerCase() }));
    }
};

const onImageError = (event) => {
    event.target.src = 'data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 24 24%22 fill=%22%23d1d5db%22%3E%3Cpath d=%22M21 19V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z%22/%3E%3C/svg%3E';
};

onMounted(() => fetchData());
</script>
