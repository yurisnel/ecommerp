<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ isEditing ? 'Order ' + form.order_number : 'New Sales Order' }}</h1>
                <p class="text-gray-500 text-sm mt-1">Create and manage customer purchase orders.</p>
            </div>
            <div class="flex gap-3">
               
                <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select v-model="form.order_status_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                                <option :value="null">Select status...</option>
                                <option v-for="s in statuses" :key="s.id" :value="s.id">{{ s.name }}</option>
                            </select>
                        </div>
                <button 
                    v-if="!isEditing"
                    @click="submit" 
                    :disabled="submitting || !isValidOrder"
                    class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition-colors flex items-center gap-2 disabled:opacity-50"
                >
                    <span v-if="submitting" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                    <span>Create Order</span>
                </button>
                <template v-else>
                     <button 
                        v-if="currentStatus?.slug === 'pending'"
                        @click="confirmOrder" 
                        class="bg-emerald-600 text-white px-4 py-2 rounded-lg hover:bg-emerald-700 transition-colors"
                    >
                        Confirm Order
                    </button>
                    <button 
                        v-if="currentStatus?.slug !== 'cancelled' && currentStatus?.slug !== 'confirmed'"
                        @click="cancelOrder" 
                        class="bg-rose-600 text-white px-4 py-2 rounded-lg hover:bg-rose-700 transition-colors"
                    >
                        Cancel Order
                    </button>
                </template>

                 <button 
                    @click="$router.push({ name: 'Orders' })" 
                    class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors"
                >
                    Back to List
                </button>
                
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Order Details -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Customer & Source Selection -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center gap-2">
                        <UserIcon class="h-5 w-5 text-gray-400" />
                        Customer Information
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="relative">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Customer <span class="text-red-500">*</span></label>
                            
                            <Combobox v-model="selectedCustomer" :disabled="isEditing">
                                <div class="relative mt-1">
                                    <div class="relative w-full cursor-default overflow-hidden rounded-lg bg-white text-left border border-gray-300 focus-within:ring-2 focus-within:ring-indigo-500 sm:text-sm">
                                        <ComboboxInput
                                            class="w-full border-none py-2 pl-3 pr-10 text-sm leading-5 text-gray-900 focus:ring-0"
                                            @change="queryCustomers($event.target.value)"
                                            :displayValue="(c) => c?.name || ''"
                                            placeholder="Search customer..."
                                        />
                                        <ComboboxButton class="absolute inset-y-0 right-0 flex items-center pr-2">
                                            <ChevronUpDownIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
                                        </ComboboxButton>
                                    </div>
                                    <TransitionRoot leave="transition ease-in duration-100" leaveFrom="opacity-100" leaveTo="opacity-0">
                                        <ComboboxOptions class="absolute mt-1 max-h-60 w-full overflow-auto rounded-md bg-white py-1 text-base shadow-lg ring-1 ring-black/5 focus:outline-none sm:text-sm z-50">
                                            <div v-if="customers.length === 0 && customerQuery !== ''" class="relative cursor-default select-none py-2 px-4 text-gray-700">
                                                Nothing found.
                                            </div>

                                            <ComboboxOption
                                                v-for="person in customers"
                                                as="template"
                                                :key="person.id"
                                                :value="person"
                                                v-slot="{ selected, active }"
                                            >
                                                <li class="relative cursor-default select-none py-2 pl-10 pr-4" :class="{'bg-indigo-600 text-white': active, 'text-gray-900': !active}">
                                                    <span class="block truncate" :class="{'font-medium': selected, 'font-normal': !selected}">
                                                        {{ person.name }} ({{ person.customer_number }})
                                                    </span>
                                                    <span v-if="selected" class="absolute inset-y-0 left-0 flex items-center pl-3" :class="{'text-white': active, 'text-teal-600': !active}">
                                                        <CheckIcon class="h-5 w-5" aria-hidden="true" />
                                                    </span>
                                                </li>
                                            </ComboboxOption>
                                        </ComboboxOptions>
                                    </TransitionRoot>
                                </div>
                            </Combobox>
                        </div>
                        <div class="flex items-end">
                            <router-link 
                                v-if="!isEditing"
                                :to="{ name: 'CustomerCreate' }" 
                                class="text-indigo-600 text-sm font-semibold hover:text-indigo-800 pb-2"
                            >
                                + Add New Customer
                            </router-link>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Sales Channel <span class="text-red-500">*</span></label>
                            <select 
                                v-model="form.sales_channel_id" 
                                :disabled="isEditing"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                            >
                                <option :value="null">Select channel...</option>
                                <option v-for="channel in channels" :key="channel.id" :value="channel.id">{{ channel.name }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Order Date <span class="text-red-500">*</span></label>
                            <input 
                                v-model="form.order_date" 
                                type="date"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                            >
                        </div>
                    </div>
                    
                    <div v-if="form.warehouse_id" class="mt-4 p-3 bg-indigo-50 rounded-lg border border-indigo-100 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-bold text-indigo-900 uppercase">Fulfillment Warehouse:</span>
                            <span class="text-sm font-medium text-indigo-700">{{ selectedWarehouseName }}</span>
                        </div>
                        <button 
                            v-if="!isEditing && form.items.length === 0" 
                            @click="form.warehouse_id = null"
                            class="text-xs text-indigo-600 hover:text-indigo-800 font-bold"
                        >
                            Change
                        </button>
                    </div>
                </div>

                <!-- Items Table -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                        <h3 class="text-lg font-medium text-gray-900">Order Items</h3>
                        <div v-if="!isEditing" class="relative">
                            <div class="flex items-center gap-2">
                                <span class="text-xs text-gray-500">Search Batch/Entry:</span>
                                <input 
                                    v-model="productSearch"
                                    @input="searchEntries"
                                    type="text"
                                    placeholder="SKU, Name or Batch..."
                                    class="border border-gray-300 rounded-lg px-4 py-2 text-sm w-80 focus:ring-2 focus:ring-indigo-500"
                                >
                            </div>
                            <!-- Dropdown for Batches -->
                            <div v-if="searchResults.length > 0" class="absolute right-0 z-10 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-xl max-h-80 overflow-y-auto min-w-[400px]">
                                <div class="px-4 py-2 bg-gray-50 border-b text-[10px] font-bold text-gray-400 uppercase">Available Batches</div>
                                <div 
                                    v-for="entry in searchResults" 
                                    :key="entry.id"
                                    @click="addItem(entry)"
                                    class="px-4 py-3 hover:bg-indigo-50 cursor-pointer border-b last:border-0"
                                >
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <div class="font-bold text-gray-900">{{ entry.product?.name }}</div>
                                            <div class="text-xs text-gray-500 font-mono">{{ entry.product?.sku }}</div>
                                            <div class="mt-1 flex gap-2">
                                                <span class="text-[10px] bg-gray-100 px-1 rounded">Batch: {{ entry.batch_number || 'N/A' }}</span>
                                                <span class="text-[10px] bg-indigo-50 text-indigo-600 px-1 rounded">WH: {{ entry.warehouse?.name }}</span>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <div class="font-black text-emerald-600">${{ Number(entry.selling_price).toFixed(2) }}</div>
                                            <div class="text-[10px] font-bold text-gray-400">Stock: {{ entry.quantity }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <table class="w-full text-left">
                        <thead class="bg-gray-50 text-gray-500 uppercase text-[10px] font-bold tracking-wider">
                            <tr>
                                <th class="px-6 py-3">Product / Batch</th>
                                <th class="px-6 py-3 text-center">Qty</th>
                                <th class="px-6 py-3 text-right">Price</th>
                                <th class="px-6 py-3 text-right">Discount</th>
                                <th class="px-6 py-3 text-right">Subtotal</th>
                                <th v-if="!isEditing" class="px-6 py-3 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-if="form.items.length === 0">
                                <td colspan="6" class="px-6 py-12 text-center text-gray-400 italic font-medium">Search and select a product batch to start the order.</td>
                            </tr>
                            <tr v-for="(item, index) in form.items" :key="index" class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="font-bold text-gray-900 group flex items-center gap-2">
                                        {{ item.product_name }}
                                        <span class="text-[10px] bg-gray-100 px-1 font-normal rounded">{{ item.batch_number }}</span>
                                    </div>
                                    <div class="text-xs text-gray-400 font-mono">{{ item.product_sku }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex justify-center">
                                        <input 
                                            v-if="!isEditing"
                                            v-model.number="item.quantity" 
                                            type="number" min="0.01" step="0.01"
                                            class="w-20 px-2 py-1 border border-gray-300 rounded text-center text-sm focus:ring-1 focus:ring-indigo-500 outline-none"
                                            @input="calculateTotal"
                                        >
                                        <span v-else class="font-bold text-gray-700">{{ item.quantity }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end">
                                        <span v-if="!isEditing" class="text-gray-400 mr-1">$</span>
                                        <input 
                                            v-if="!isEditing"
                                            v-model.number="item.unit_price" 
                                            type="number" min="0" step="0.01"
                                            class="w-24 px-2 py-1 border border-gray-300 rounded text-right text-sm focus:ring-1 focus:indigo-500 outline-none"
                                            @input="calculateTotal"
                                        >
                                        <span v-else class="font-medium">${{ item.unit_price.toFixed(2) }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end">
                                        <span v-if="!isEditing" class="text-gray-400 mr-1">$</span>
                                        <input 
                                            v-if="!isEditing"
                                            v-model.number="item.discount" 
                                            type="number" min="0" step="0.01"
                                            class="w-20 px-2 py-1 border border-gray-300 rounded text-right text-sm focus:ring-1 focus:indigo-500 outline-none"
                                            @input="calculateTotal"
                                        >
                                        <span v-else class="text-rose-600 font-medium">-${{ item.discount.toFixed(2) }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <span class="font-black text-gray-900">
                                        ${{ (item.quantity * item.unit_price - item.discount).toFixed(2) }}
                                    </span>
                                </td>
                                <td v-if="!isEditing" class="px-4 py-4 text-center">
                                    <button @click="removeItem(index)" class="p-1.5 text-gray-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-all">
                                        <TrashIcon class="h-5 w-5" />
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Notes Section -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-xs font-bold text-gray-900 mb-4 uppercase tracking-wider">Order Notes</h3>
                    <textarea 
                        v-model="form.notes"
                        rows="3"
                        placeholder="Add any additional details or customer instructions..."
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                        :disabled="isEditing && currentStatus?.slug !== 'pending'"
                    ></textarea>
                </div>
            </div>

            <!-- Summary Panel -->
            <div class="space-y-6">
                <!-- Order Totals -->
                <div class="bg-white rounded-xl shadow-base border border-gray-200 p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-6 border-b pb-2 flex justify-between items-center">
                        Summary
                        <span class="text-xs font-normal text-gray-400 uppercase tracking-widest">Pricing</span>
                    </h3>
                    
                    <div class="space-y-4">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Items Subtotal</span>
                            <span class="font-bold text-gray-900">${{ totals.subtotal.toFixed(2) }}</span>
                        </div>
                        
                        <div class="flex justify-between items-center text-sm pt-2 border-t border-gray-50">
                            <span class="text-gray-500">Global Discount</span>
                            <div v-if="!isEditing" class="flex items-center">
                                <span class="text-[10px] text-gray-400 mr-1">$</span>
                                <input 
                                    v-model.number="form.discount" 
                                    type="number" min="0" step="1"
                                    class="w-24 px-3 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-right text-sm font-bold focus:bg-white transition-all outline-none"
                                >
                            </div>
                            <span v-else class="font-bold text-rose-600">-${{ form.discount.toFixed(2) }}</span>
                        </div>

                        <div class="flex justify-between items-center text-sm">
                            <span class="text-gray-500">Manual Tax</span>
                            <div v-if="!isEditing" class="flex items-center">
                                <span class="text-[10px] text-gray-400 mr-1">$</span>
                                <input 
                                    v-model.number="form.tax" 
                                    type="number" min="0" step="0.01"
                                    class="w-24 px-3 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-right text-sm font-bold focus:bg-white transition-all outline-none"
                                >
                            </div>
                            <span v-else class="font-bold text-gray-900">${{ form.tax.toFixed(2) }}</span>
                        </div>

                        <div class="flex justify-between items-center text-sm">
                            <span class="text-gray-500">Shipping Fee</span>
                            <div v-if="!isEditing" class="flex items-center">
                                <span class="text-[10px] text-gray-400 mr-1">$</span>
                                <input 
                                    v-model.number="form.shipping" 
                                    type="number" min="0" step="1"
                                    class="w-24 px-3 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-right text-sm font-bold focus:bg-white transition-all outline-none"
                                >
                            </div>
                            <span v-else class="font-bold text-gray-900">${{ form.shipping.toFixed(2) }}</span>
                        </div>

                        <div class="mt-8 p-4 bg-indigo-50/50 rounded-xl border border-indigo-100">
                            <div class="flex justify-between items-end">
                                <span class="text-xs font-bold text-indigo-900 uppercase">Final Total</span>
                                <span class="text-3xl font-black text-indigo-600 tracking-tight">${{ totals.total.toFixed(2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Status Sidebar -->
                <div v-if="isEditing" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 overflow-hidden relative">
                    <div class="absolute top-0 right-0 p-2">
                        <div class="h-24 w-24 bg-indigo-50 rounded-full -mr-12 -mt-12 opacity-50"></div>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-4 border-b pb-2 relative z-10">Order Status</h3>
                    <div class="space-y-4 text-sm relative z-10">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-500">Current Status</span>
                            <span 
                                v-if="currentStatus"
                                class="px-3 py-1 rounded-full text-[10px] font-black uppercase border-2 shadow-sm text-white"
                                :style="{ backgroundColor: currentStatus.color, borderColor: currentStatus.color }"
                            >
                                {{ currentStatus.name }}
                            </span>
                            <span v-else class="text-gray-400 text-sm">No status assigned</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Processed On</span>
                            <span class="font-bold text-gray-700">{{ formatDate(form.created_at) }}</span>
                        </div>
                        <div class="mt-4">
                            <h4 class="text-xs font-bold text-gray-700 mb-2">Status History</h4>
                            <ul class="divide-y divide-gray-100 text-sm">
                                <li v-if="!form.status_histories || form.status_histories.length === 0" class="py-2 text-gray-400 italic">No history yet.</li>
                                <li v-for="h in form.status_histories" :key="h.id" class="py-2 flex justify-between items-start">
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span v-if="h.status?.color" class="inline-block w-3 h-3 rounded" :style="{ backgroundColor: h.status.color }"></span>
                                            <span class="font-medium">{{ h.status?.name || 'Unknown' }}</span>
                                        </div>
                                        <div class="text-xs text-gray-500">By: {{ h.changer?.name || 'System' }}</div>
                                    </div>
                                    <div class="text-xs text-gray-500">{{ formatDate(h.changed_at) }}</div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed, reactive, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import api from '../axios';
import { UserIcon, TrashIcon, CheckIcon, ChevronUpDownIcon } from '@heroicons/vue/24/outline';
import { debounce } from 'lodash';
import {
    Combobox,
    ComboboxInput,
    ComboboxButton,
    ComboboxOptions,
    ComboboxOption,
    TransitionRoot,
} from '@headlessui/vue';

const route = useRoute();
const router = useRouter();

const isEditing = computed(() => route.params.id !== undefined);
const submitting = ref(false);

const customers = ref([]);
const channels = ref([]);
const warehouses = ref([]);
const statuses = ref([]);
const selectedCustomer = ref(null);
const customerQuery = ref('');

const productSearch = ref('');
const searchResults = ref([]);
const selectedWarehouseName = computed(() => {
    const wh = warehouses.value.find(w => w.id === form.warehouse_id);
    return wh ? wh.name : 'Not set';
});

const form = reactive({
    order_number: '',
    customer_id: null,
    sales_channel_id: null,
    warehouse_id: null,
    order_status_id: null,
    tax: 0,
    discount: 0,
    shipping: 0,
    notes: '',
    items: [],
    created_at: null,
    order_date: new Date().toISOString().substr(0, 10)
});

// Watch selected customer to update form
watch(selectedCustomer, (val) => {
    form.customer_id = val ? val.id : null;
});

const isValidOrder = computed(() => {
    return form.customer_id && form.sales_channel_id && form.warehouse_id && form.items.length > 0;
});

// Get current status object
const currentStatus = computed(() => {
    return statuses.value.find(s => s.id === form.order_status_id) || null;
});

// Totals calculation
const totals = computed(() => {
    const subtotal = form.items.reduce((sum, item) => sum + (item.quantity * item.unit_price - (item.discount || 0)), 0);
    const total = subtotal + (form.tax || 0) + (form.shipping || 0) - (form.discount || 0);
    return { subtotal, total };
});

const queryCustomers = debounce(async (val) => {
    customerQuery.value = val;
    try {
        const response = await api.get('/customers', { params: { search: val, per_page: 20 } });
        customers.value = response.data.data.data;
    } catch (error) {
        console.error('Error fetching customers:', error);
    }
}, 300);

const searchEntries = debounce(async () => {
    if (productSearch.value.length < 2) {
        searchResults.value = [];
        return;
    }
    try {
        // Search in product entries directly to identify batches
        const response = await api.get('/inventory/entries', { 
            params: { 
                search: productSearch.value, 
                warehouse_id: form.warehouse_id, // Filter by selected warehouse if any
                per_page: 10,
                status: 'active'
            } 
        });
        searchResults.value = response.data.data.data;
    } catch (error) {
        console.error('Error searching entries:', error);
    }
}, 300);

const addItem = (entry) => {
    // Check if entry (batch) already added
    const existing = form.items.find(i => i.product_entry_id === entry.id);
    if (existing) {
        existing.quantity++;
    } else {
        // Auto-set warehouse from the first item
        if (!form.warehouse_id) {
            form.warehouse_id = entry.warehouse_id;
        }
        
        form.items.push({
            product_id: entry.product_id,
            product_entry_id: entry.id,
            product_name: entry.product?.name,
            product_sku: entry.product?.sku,
            batch_number: entry.batch_number || 'N/A',
            quantity: 1,
            unit_price: Number(entry.selling_price), // Auto-complete price from entry
            unit_cost: Number(entry.cost_per_unit),
            discount: 0,
            tax: 0
        });
    }
    productSearch.value = '';
    searchResults.value = [];
};

const removeItem = (index) => {
    form.items.splice(index, 1);
    // Reset warehouse if no items left and not editing
    if (form.items.length === 0 && !isEditing.value) {
        // form.warehouse_id = null; // Maybe keep it? Or reset? Let's keep it for now unless explicitly requested to reset.
    }
};

const fetchInitialData = async () => {
    try {
        // Fetch channels and warehouses first
        const [chanRes, whRes, statusRes] = await Promise.all([
            api.get('/sales-channels', { params: { per_page: -1 } }),
            api.get('/warehouses', { params: { per_page: -1 } }),
            api.get('/order-statuses', { params: { per_page: -1 } }),
        ]);
        
        channels.value = chanRes.data.data;
        warehouses.value = whRes.data.data;
        statuses.value = statusRes.data.data;
        
        if (channels.value.length > 0) form.sales_channel_id = channels.value[0].id;
        // if (warehouses.value.length > 0) form.warehouse_id = warehouses.value[0].id; // Don't pre-set warehouse
        
        // Initial customer load (empty search)
        queryCustomers('');
    } catch (error) {
        console.error('Error fetching initial data:', error);
    }
};

const fetchOrder = async () => {
    if (!isEditing.value) return;
    try {
        const response = await api.get(`/orders/${route.params.id}`);
        const order = response.data.data;
        Object.assign(form, {
            order_number: order.order_number,
            customer_id: order.customer_id,
            sales_channel_id: order.sales_channel_id,
            warehouse_id: order.warehouse_id,
            order_status_id: order.order_status_id,
            tax: Number(order.tax),
            discount: Number(order.discount),
            shipping: Number(order.shipping),
            notes: order.notes,
            created_at: order.created_at,
            order_date: order.order_date ? order.order_date.substr(0, 10) : new Date().toISOString().substr(0, 10),
            items: order.items.map(item => ({
                product_id: item.product_id,
                product_entry_id: item.product_entry_id,
                product_name: item.product?.name || 'Unknown',
                product_sku: item.product?.sku || 'N/A',
                batch_number: item.product_entry?.batch_number || 'N/A',
                quantity: Number(item.quantity),
                unit_price: Number(item.unit_price),
                unit_cost: Number(item.unit_cost || 0),
                discount: Number(item.discount || 0),
                tax: Number(item.tax || 0)
            }))
        });
        
        // Set selected customer for combobox
        if (order.customer) {
            selectedCustomer.value = order.customer;
        }
    } catch (error) {
        console.error('Error fetching order:', error);
    }
};

const submit = async () => {
    submitting.value = true;
    try {
        if (isEditing.value) {
            await api.put(`/orders/${route.params.id}`, form);
        } else {
            await api.post('/orders', form);
        }
        router.push({ name: 'Orders' });
    } catch (error) {
        alert(error.response?.data?.message || 'Failed to create order');
    } finally {
        submitting.value = false;
    }
};

const confirmOrder = async () => {
    if (!confirm('Confirm this order? This will deduct stock from the selected batches.')) return;
    try {
        await api.post(`/orders/${route.params.id}/confirm`);
        fetchOrder();
    } catch (error) {
        alert(error.response?.data?.message || 'Failed to confirm order');
    }
};

const cancelOrder = async () => {
    if (!confirm('Cancel this order? Stock reserved (if any) will be released.')) return;
    try {
        await api.post(`/orders/${route.params.id}/cancel`);
        fetchOrder();
    } catch (error) {
        alert(error.response?.data?.message || 'Failed to cancel order');
    }
};

const formatDate = (date) => {
    if (!date) return 'N/A';
    return new Date(date).toLocaleString();
};

onMounted(() => {
    fetchInitialData();
    fetchOrder();
});
</script>
