<template>
    <div>
        <div class="mb-4 flex gap-4 items-end">
            <div class="flex-1">
                 <!-- Search is handled by DataTable internally via event, but we can add external filters here if needed -->
            </div>
             <!-- Multi-select Category Filter -->
            <div class="w-64">
                <label class="block text-sm font-medium text-gray-700 mb-1">Filter by Category</label>
                <Listbox v-model="selectedCategories" multiple>
                    <div class="relative mt-1">
                        <ListboxButton
                            class="relative w-full cursor-default rounded-lg bg-white py-2 pl-3 pr-10 text-left shadow-md focus:outline-none focus-visible:border-indigo-500 focus-visible:ring-2 focus-visible:ring-white/75 focus-visible:ring-offset-2 focus-visible:ring-offset-orange-300 sm:text-sm border border-gray-300"
                        >
                            <span class="block truncate">
                                {{ selectedCategories.length > 0 
                                    ? selectedCategories.map(c => c.name).join(', ') 
                                    : 'All Categories' 
                                }}
                            </span>
                            <span
                                class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2"
                            >
                                <ChevronUpDownIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
                            </span>
                        </ListboxButton>

                        <transition
                            leave-active-class="transition duration-100 ease-in"
                            leave-from-class="opacity-100"
                            leave-to-class="opacity-0"
                        >
                            <ListboxOptions
                                class="absolute mt-1 max-h-60 w-full overflow-auto rounded-md bg-white py-1 text-base shadow-lg ring-1 ring-black/5 focus:outline-none sm:text-sm z-50"
                            >
                                <ListboxOption
                                    v-for="category in categories"
                                    :key="category.id"
                                    :value="category"
                                    as="template"
                                    v-slot="{ active, selected }"
                                >
                                    <li
                                        :class="[
                                            active ? 'bg-indigo-100 text-indigo-900' : 'text-gray-900',
                                            'relative cursor-default select-none py-2 pl-10 pr-4',
                                        ]"
                                    >
                                        <span
                                            :class="[
                                                selected ? 'font-medium' : 'font-normal',
                                                'block truncate',
                                            ]"
                                        >
                                            {{ category.name }}
                                        </span>
                                        <span
                                            v-if="selected"
                                            class="absolute inset-y-0 left-0 flex items-center pl-3 text-indigo-600"
                                        >
                                            <CheckIcon class="h-5 w-5" aria-hidden="true" />
                                        </span>
                                    </li>
                                </ListboxOption>
                            </ListboxOptions>
                        </transition>
                    </div>
                </Listbox>
            </div>
        </div>

        <DataTable
            title="Products"
            :columns="columns"
            :items="items"
            :loading="loading"
            searchable
            :pagination="pagination"
            @search="search"
            @page-change="fetchData"
        >
            <template #actions>
                <button 
                    @click="$router.push({ name: 'ProductCreate' })"
                    class="bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700 text-sm flex items-center"
                >
                    <span class="mr-1">+</span> New Product
                </button>
            </template>

            <template #categories="{ item }">
                <div v-if="item.categories && item.categories.length > 0" class="flex flex-wrap gap-1">
                    <span 
                        v-for="cat in item.categories" 
                        :key="cat.id"
                        class="px-2 py-1 bg-gray-100 text-gray-600 rounded text-xs"
                    >
                        {{ cat.name }}
                    </span>
                </div>
                <span v-else class="text-gray-400 text-sm">-</span>
            </template>

            <template #cost="{ item }">
                <span v-if="item.latest_entry" class="text-gray-600">
                    ${{ Number(item.latest_entry.cost_per_unit).toFixed(2) }}
                </span>
                <span v-else class="text-gray-400 text-xs italic">N/A</span>
            </template>

            <template #price="{ item }">
                <span v-if="item.latest_entry" class="font-medium text-gray-900">
                    ${{ Number(item.latest_entry.selling_price).toFixed(2) }}
                </span>
                <span v-else class="text-gray-400 text-xs italic">N/A</span>
            </template>

            <template #status="{ item }">
                <span :class="{
                    'bg-green-100 text-green-800': item.status === 'active',
                    'bg-red-100 text-red-800': item.status === 'inactive',
                    'bg-gray-100 text-gray-800': item.status === 'draft'
                }" class="px-2 py-1 rounded-full text-xs font-semibold">
                    {{ item.status }}
                </span>
            </template>

            <template #rowActions="{ item }">
                <button 
                    @click="$router.push({ name: 'ProductEdit', params: { id: item.id } })"
                    class="text-blue-600 hover:text-blue-900 mr-3"
                >
                    Edit
                </button>
                <button class="text-red-600 hover:text-red-900">Delete</button>
            </template>
        </DataTable>
    </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import DataTable from '../components/DataTable.vue';
import api from '../axios';
import {
    Listbox,
    ListboxButton,
    ListboxOptions,
    ListboxOption,
} from '@headlessui/vue';
import { CheckIcon, ChevronUpDownIcon } from '@heroicons/vue/20/solid';

const loading = ref(false);
const items = ref([]);
const pagination = ref({});
const categories = ref([]);
const selectedCategories = ref([]);

const columns = [
    { key: 'sku', label: 'SKU' },
    { key: 'name', label: 'Product Name' },
    { key: 'categories', label: 'Categories' },
    { key: 'cost', label: 'Last Cost' },
    { key: 'price', label: 'Selling Price' },
    { key: 'status', label: 'Status' }
];

const fetchCategories = async () => {
    try {
        const response = await api.get('/categories?per_page=100'); // Get more categories for filter
        categories.value = response.data.data.data || response.data.data; 
    } catch (error) {
        console.error('Error loading categories', error);
    }
};

const fetchData = async (page = 1, query = '') => {
    loading.value = true;
    try {
        const params = {
            page,
            search: query,
        };

        if (selectedCategories.value.length > 0) {
            params.category_id = selectedCategories.value.map(c => c.id);
        }

        const response = await api.get('/products', { params });
        
        if (response.data.success) {
            const paginator = response.data.data;
            items.value = paginator.data;
            pagination.value = {
                current_page: paginator.current_page,
                last_page: paginator.last_page,
                total: paginator.total
            };
        }
        loading.value = false;
    } catch (error) {
        console.error('Error loading products', error);
        loading.value = false;
    }
};

const search = (query) => {
    fetchData(1, query);
};

// Re-fetch when categories change
watch(selectedCategories, () => {
    fetchData(1);
});

onMounted(() => {
    fetchCategories();
    fetchData();
});
</script>
