<template>
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <!-- Header / Toolbar -->
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
            <h3 class="font-bold text-gray-700">{{ title }}</h3>
            <div class="flex space-x-2">
                <input 
                    v-if="searchable"
                    type="text" 
                    :placeholder="t('common.search')" 
                    class="border border-gray-300 rounded px-3 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500"
                    @input="$emit('search', $event.target.value)"
                >
                <slot name="actions"></slot>
            </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-100 text-gray-600 uppercase text-xs leading-normal">
                        <th v-for="col in columns" :key="col.key" class="py-3 px-6 text-left font-semibold">
                            {{ col.label }}
                        </th>
                        <th v-if="$slots.rowActions" class="py-3 px-6 text-center font-semibold">{{ t('common.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="text-gray-600 text-sm font-light">
                    <tr v-if="loading">
                        <td :colspan="columns.length + 1" class="py-4 text-center">{{ t('common.loading') }}</td>
                    </tr>
                    <tr v-else-if="items.length === 0">
                        <td :colspan="columns.length + 1" class="py-4 text-center">{{ t('common.noData') }}</td>
                    </tr>
                    <tr v-else v-for="(item, index) in items" :key="index" class="border-b border-gray-200 hover:bg-gray-100">
                        <td v-for="col in columns" :key="col.key" class="py-3 px-6 text-left whitespace-nowrap">
                            <slot :name="col.key" :item="item">
                                {{ item[col.key] }}
                            </slot>
                        </td>
                        <td v-if="$slots.rowActions" class="py-3 px-6 text-center">
                            <slot name="rowActions" :item="item"></slot>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination (Simple placeholder) -->
        <div class="px-6 py-4 border-t border-gray-100 flex justify-between items-center" v-if="pagination">
            <span class="text-sm text-gray-500">{{ t('common.showingPage', { current: pagination.current_page, total: pagination.last_page }) }}</span>
            <div class="flex space-x-1">
                <button 
                    :disabled="pagination.current_page <= 1"
                    @click="$emit('page-change', pagination.current_page - 1)"
                    class="px-3 py-1 rounded border hover:bg-gray-100 disabled:opacity-50"
                >{{ t('common.previous') }}</button>
                <button 
                    :disabled="pagination.current_page >= pagination.last_page"
                    @click="$emit('page-change', pagination.current_page + 1)"
                    class="px-3 py-1 rounded border hover:bg-gray-100 disabled:opacity-50"
                >{{ t('common.next') }}</button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

defineProps({
    title: String,
    columns: Array, // [{key: 'name', label: 'Name'}]
    items: Array,
    loading: Boolean,
    searchable: Boolean,
    pagination: Object
});
</script>
