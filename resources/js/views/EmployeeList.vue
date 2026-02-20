<template>
    <div class="space-y-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Employees</h1>
                <p class="text-gray-500 text-sm mt-1">Manage employee records and assignments.</p>
            </div>
            <router-link 
                :to="{ name: 'EmployeeCreate' }"
                class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition-colors flex items-center gap-2"
            >
                <Icon icon="mdi:plus" class="h-5 w-5" />
                <span>New Employee</span>
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
                    <img v-if="item.image" :src="item.image" :alt="item.user?.name || item.employee_number" class="h-8 w-8 rounded-full object-cover border border-gray-200" @error="onImageError">
                    <div v-else class="h-8 w-8 rounded-full bg-gray-200 flex items-center justify-center">
                        <i class="fas fa-user text-gray-400 text-xs"></i>
                    </div>
                </div>
            </template>

            <template #name="{ item }">
                <div class="font-medium text-gray-900">{{ item.user?.name || item.employee_number }}</div>
                <div class="text-xs text-gray-500">{{ item.user?.email || '' }}</div>
            </template>

            <template #rowActions="{ item }">
                <div class="flex justify-center gap-2">
                    <router-link 
                        :to="{ name: 'EmployeeEdit', params: { id: item.id } }"
                        class="p-1 text-indigo-600 hover:bg-indigo-50 rounded transition-colors"
                        title="Edit Employee"
                    >
                        <Icon icon="mdi:pencil" class="h-5 w-5" />
                    </router-link>
                    <button 
                        @click="deleteEmployee(item.id)"
                        class="p-1 text-rose-600 hover:bg-rose-50 rounded transition-colors"
                        title="Delete Employee"
                    >
                        <Icon icon="mdi:trash-can" class="h-5 w-5" />
                    </button>
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
    { key: 'image', label: 'Image' },
    { key: 'employee_number', label: 'ID' },
    { key: 'name', label: 'Name' },
    { key: 'position', label: 'Position' },
    { key: 'department', label: 'Department' },
    { key: 'status', label: 'Status' }
];

const fetchData = async (page = 1) => {
    loading.value = true;
    try {
        const response = await api.get('/employees', {
            params: { page, search: filters.search }
        });
        const resData = response.data.data;
        items.value = resData.data;
        pagination.value = {
            current_page: resData.current_page,
            last_page: resData.last_page,
            total: resData.total
        };
    } catch (error) {
        console.error('Error loading employees', error);
    } finally {
        loading.value = false;
    }
};

const handleSearch = debounce((val) => {
    filters.search = val;
    fetchData(1);
}, 300);

const deleteEmployee = async (id) => {
    if (!confirm('Are you sure you want to delete this employee?')) return;
    try {
        await api.delete(`/employees/${id}`);
        fetchData(pagination.value.current_page);
    } catch (error) {
        console.error('Error deleting employee:', error);
        alert(error.response?.data?.message || 'Failed to delete employee');
    }
};

const onImageError = (event) => {
    event.target.src = 'data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 24 24%22 fill=%22%23d1d5db%22%3E%3Cpath d=%22M21 19V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z%22/%3E%3C/svg%3E';
};

onMounted(() => fetchData());
</script>
