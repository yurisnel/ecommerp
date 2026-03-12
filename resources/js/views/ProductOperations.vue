<template>
    <div class="h-full bg-gray-50/30">
        <div class="p-8 max-w-4xl mx-auto">
            <h2 class="text-xl font-semibold text-gray-900 mb-6">Inventory Operations</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- New Stock card -->
                <router-link 
                    :to="{ name: 'ProductEntry' }"
                    class="group bg-white p-6 rounded-2xl border border-gray-200 shadow-sm hover:shadow-md hover:border-indigo-300 transition-all text-left"
                >
                    <div class="bg-indigo-50 w-12 h-12 rounded-xl flex items-center justify-center mb-4 group-hover:bg-indigo-600 transition-colors">
                        <Icon icon="mdi:inbox-arrow-down" class="h-6 w-6 text-indigo-600 group-hover:text-white" />
                    </div>
                    <h3 class="text-lg font-bold text-gray-900">Purchase Entry</h3>
                    <p class="text-sm text-gray-500 mt-2">Add new stock batches from suppliers with cost and selling price tracking.</p>
                    <div class="mt-6 flex items-center text-sm font-semibold text-indigo-600">
                        Start Entry
                        <Icon icon="mdi:chevron-right" class="h-4 w-4 ml-1" />
                    </div>
                </router-link>

                <!-- Adjustment card -->
                <button 
                    @click="openAdjustment"
                    class="group bg-white p-6 rounded-2xl border border-gray-200 shadow-sm hover:shadow-md hover:border-amber-300 transition-all text-left"
                >
                    <div class="bg-amber-50 w-12 h-12 rounded-xl flex items-center justify-center mb-4 group-hover:bg-amber-600 transition-colors">
                        <Icon icon="mdi:tune" class="h-6 w-6 text-amber-600 group-hover:text-white" />
                    </div>
                    <h3 class="text-lg font-bold text-gray-900">Stock Adjustment</h3>
                    <p class="text-sm text-gray-500 mt-2">Correct inventory levels manually (losses, findings, corrections).</p>
                    <div class="mt-6 flex items-center text-sm font-semibold text-amber-600">
                        Adjust Stock
                        <Icon icon="mdi:chevron-right" class="h-4 w-4 ml-1" />
                    </div>
                </button>

                <!-- Transfer card -->
                <button 
                    @click="openTransfer"
                    class="group bg-white p-6 rounded-2xl border border-gray-200 shadow-sm hover:shadow-md hover:border-emerald-300 transition-all text-left"
                >
                    <div class="bg-emerald-50 w-12 h-12 rounded-xl flex items-center justify-center mb-4 group-hover:bg-emerald-600 transition-colors">
                        <Icon icon="mdi:transfer" class="h-6 w-6 text-emerald-600 group-hover:text-white" />
                    </div>
                    <h3 class="text-lg font-bold text-gray-900">Inter-Warehouse Transfer</h3>
                    <p class="text-sm text-gray-500 mt-2">Move existing stock between different warehouse locations.</p>
                    <div class="mt-6 flex items-center text-sm font-semibold text-emerald-600">
                        New Transfer
                        <Icon icon="mdi:chevron-right" class="h-4 w-4 ml-1" />
                    </div>
                </button>
            </div>

            <!-- Modals for Adjustment and Transfer (Placeholders for now) -->
            <div v-if="showModal" class="fixed inset-0 bg-gray-900/50 flex items-center justify-center z-50 px-4">
                <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden animate-in fade-in zoom-in duration-200">
                    <div class="p-6 border-b border-gray-100 flex justify-between items-center">
                        <h3 class="text-lg font-bold text-gray-900">{{ modalTitle }}</h3>
                        <button @click="showModal = false" class="text-gray-400 hover:text-gray-600">
                            <Icon icon="mdi:close" class="h-6 w-6" />
                        </button>
                    </div>
                    <div class="p-8 text-center">
                        <div class="bg-indigo-50 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                            <Icon icon="mdi:wrench" class="h-8 w-8 text-indigo-600" />
                        </div>
                        <p class="text-gray-600 italic">This operation interface is being refined. Please use the "New Stock Entry" for now if you need to add stock.</p>
                    </div>
                    <div class="p-6 bg-gray-50 flex justify-end">
                        <button @click="showModal = false" class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-bold text-gray-700 hover:bg-gray-100 transition-colors">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { Icon } from '@iconify/vue';

const showModal = ref(false);
const modalTitle = ref('');

const openAdjustment = () => {
    modalTitle.value = 'Stock Adjustment';
    showModal.value = true;
};

const openTransfer = () => {
    modalTitle.value = 'Stock Transfer';
    showModal.value = true;
};
</script>
