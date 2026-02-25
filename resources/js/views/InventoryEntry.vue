<template>
    <div class="space-y-6 max-w-6xl mx-auto pb-12">
        <!-- Header -->
        <div class="flex justify-between items-center bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
            <div class="flex items-center gap-4">
                <button @click="$router.back()" class="p-2 hover:bg-gray-100 rounded-full transition-colors text-gray-500">
                    <Icon icon="mdi:arrow-left" class="h-6 w-6" />
                </button>
                <div>
                    <h1 class="text-xl font-bold text-gray-900">{{ isEdit ? 'Edit Purchase Entry' : 'Purchase Stock Entry' }}</h1>
                    <p class="text-gray-500 text-xs">{{ isEdit ? 'Modify details of a previous arrival and update stock levels.' : 'Register new inventory arrival and update selling prices.' }}</p>
                </div>
            </div>
            <div class="flex gap-3">
                <button 
                    @click="submitEntry" 
                    :disabled="submitting || !isValid"
                    class="bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700 transition-all flex items-center gap-2 shadow-sm disabled:opacity-50 disabled:grayscale"
                >
                    <Icon v-if="submitting" icon="mdi:refresh" class="h-5 w-5 animate-spin" />
                    <Icon v-else icon="mdi:check-circle" class="h-5 w-5" />
                    <span>{{ isEdit ? 'Update Entry' : 'Complete Entry' }}</span>
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Column: Primary Selection -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Step 1: Product & Source -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-6 h-6 rounded-full bg-indigo-600 text-white text-xs flex items-center justify-center font-bold">1</span>
                            <h3 class="font-bold text-gray-900">Product & Vendor</h3>
                        </div>
                    </div>
                    <div class="p-6 space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Product Search -->
                            <div class="relative z-30">
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Product *</label>
                                <Combobox v-model="selectedProduct" @update:modelValue="onProductSelect" :disabled="isProductLocked">
                                    <div class="relative">
                                        <div class="relative w-full cursor-default overflow-hidden rounded-xl bg-white text-left border border-gray-300 focus-within:border-indigo-500 focus-within:ring-2 focus-within:ring-indigo-500 transition-all">
                                            <ComboboxInput
                                                class="w-full border-none py-3 pl-4 pr-10 text-sm leading-5 text-gray-900 focus:ring-0"
                                                :displayValue="(product) => product?.name"
                                                @change="onProductSearch($event.target.value)"
                                                placeholder="Type name or SKU..."
                                                :disabled="isProductLocked"
                                            />
                                            <ComboboxButton class="absolute inset-y-0 right-0 flex items-center pr-3">
                                                <ChevronUpDownIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
                                            </ComboboxButton>
                                        </div>
                                        <TransitionRoot leave="transition ease-in duration-100" leaveFrom="opacity-100" leaveTo="opacity-0">
                                            <ComboboxOptions class="absolute mt-1 max-h-60 w-full overflow-auto rounded-xl bg-white py-1 text-base shadow-2xl ring-1 ring-black/5 focus:outline-none sm:text-sm z-50">
                                                <div v-if="loadingProducts" class="p-4 text-center"><Icon icon="mdi:refresh" class="h-5 w-5 animate-spin mx-auto text-gray-400" /></div>
                                                <div v-else-if="productResults.length === 0" class="p-4 text-center text-gray-500">No products found.</div>
                                                <ComboboxOption v-for="product in productResults" :key="product.id" :value="product" v-slot="{ selected, active }">
                                                    <li class="relative cursor-pointer select-none py-3 pl-10 pr-4" :class="active ? 'bg-indigo-50 text-indigo-900' : 'text-gray-900'">
                                                        <div class="flex items-center gap-3">
                                                            <img :src="product.default_image?.url || '/placeholder-product.png'" class="h-8 w-8 rounded bg-gray-100 object-cover">
                                                            <div>
                                                                <div class="font-bold truncate" :class="{ 'text-indigo-600': selected }">{{ product.name }}</div>
                                                                <div class="text-[10px] text-gray-400 font-mono">{{ product.sku }}</div>
                                                            </div>
                                                        </div>
                                                        <span v-if="selected" class="absolute inset-y-0 left-0 flex items-center pl-3 text-indigo-600"><CheckIcon class="h-5 w-5" /></span>
                                                    </li>
                                                </ComboboxOption>
                                            </ComboboxOptions>
                                        </TransitionRoot>
                                    </div>
                                </Combobox>
                            </div>

                            <!-- Supplier Search -->
                            <div class="relative z-20">
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Vendor / Supplier</label>
                                <Combobox v-model="selectedSupplier" @update:modelValue="onSupplierSelect">
                                    <div class="relative">
                                        <div class="relative w-full cursor-default overflow-hidden rounded-xl bg-white text-left border border-gray-300 focus-within:border-indigo-500 focus-within:ring-2 focus-within:ring-indigo-500 transition-all">
                                            <ComboboxInput
                                                class="w-full border-none py-3 pl-4 pr-10 text-sm leading-5 text-gray-900 focus:ring-0"
                                                :displayValue="(s) => s?.name"
                                                @change="supplierQuery = $event.target.value"
                                                placeholder="Search supplier..."
                                            />
                                            <ComboboxButton class="absolute inset-y-0 right-0 flex items-center pr-3">
                                                <Icon icon="mdi:chevron-up-down" class="h-5 w-5 text-gray-400" aria-hidden="true" />
                                            </ComboboxButton>
                                        </div>
                                        <TransitionRoot leave="transition ease-in duration-100">
                                            <ComboboxOptions class="absolute mt-1 max-h-60 w-full overflow-auto rounded-xl bg-white py-1 text-base shadow-2xl ring-1 ring-black/5 focus:outline-none sm:text-sm z-50">
                                                <ComboboxOption v-for="s in filteredSuppliers" :key="s.id" :value="s" v-slot="{ selected, active }">
                                                    <li class="relative cursor-pointer select-none py-3 pl-10 pr-4" :class="active ? 'bg-indigo-50 text-indigo-900' : 'text-gray-900'">
                                                        <span class="block truncate" :class="{ 'font-bold': selected }">{{ s.name }}</span>
                                                        <span v-if="selected" class="absolute inset-y-0 left-0 flex items-center pl-3 text-indigo-600"><Icon icon="mdi:check" class="h-5 w-5" /></span>
                                                    </li>
                                                </ComboboxOption>
                                            </ComboboxOptions>
                                        </TransitionRoot>
                                    </div>
                                </Combobox>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Logistics & Pricing -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
                         <div class="flex items-center gap-2">
                            <span class="w-6 h-6 rounded-full bg-indigo-600 text-white text-xs flex items-center justify-center font-bold">2</span>
                            <h3 class="font-bold text-gray-900">Quantities & Pricing</h3>
                        </div>
                    </div>
                    <div class="p-6 space-y-8">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <!-- Warehouse -->
                            <div class="relative z-10">
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Destination Warehouse *</label>
                                <Combobox v-model="selectedWarehouse" @update:modelValue="onWarehouseSelect">
                                    <div class="relative">
                                        <div class="relative w-full cursor-default overflow-hidden rounded-xl bg-white text-left border border-gray-300 focus-within:border-indigo-500 focus-within:ring-2 focus-within:ring-indigo-500 transition-all">
                                            <ComboboxInput
                                                class="w-full border-none py-3 pl-4 pr-10 text-sm leading-5 text-gray-900 focus:ring-0"
                                                :displayValue="(w) => w?.name"
                                                @change="warehouseQuery = $event.target.value"
                                                placeholder="Select..."
                                            />
                                            <ComboboxButton class="absolute inset-y-0 right-0 flex items-center pr-3">
                                                <Icon icon="mdi:chevron-up-down" class="h-5 w-5 text-gray-400" aria-hidden="true" />
                                            </ComboboxButton>
                                        </div>
                                        <TransitionRoot leave="transition ease-in duration-100">
                                            <ComboboxOptions class="absolute mt-1 max-h-60 w-full overflow-auto rounded-xl bg-white py-1 text-base shadow-2xl ring-1 ring-black/5 focus:outline-none sm:text-sm z-50">
                                                <ComboboxOption v-for="w in filteredWarehouses" :key="w.id" :value="w" v-slot="{ selected, active }">
                                                    <li class="relative cursor-pointer select-none py-3 pl-10 pr-4" :class="active ? 'bg-indigo-50 text-indigo-900' : 'text-gray-900'">
                                                        <span class="block truncate" :class="{ 'font-bold': selected }">{{ w.name }}</span>
                                                        <span v-if="selected" class="absolute inset-y-0 left-0 flex items-center pl-3 text-indigo-600"><Icon icon="mdi:check" class="h-5 w-5" /></span>
                                                    </li>
                                                </ComboboxOption>
                                            </ComboboxOptions>
                                        </TransitionRoot>
                                    </div>
                                </Combobox>
                            </div>

                            <!-- Batch Number -->
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Batch / Lot #</label>
                                <input v-model="form.batch_number" type="text" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all text-sm" placeholder="e.g. LOT-20240114-001">
                            </div>

                            <!-- Entry Date -->
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Arrival Date *</label>
                                <input v-model="form.entry_date" type="datetime-local" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all text-sm">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 bg-gray-50 p-6 rounded-2xl border border-gray-100">
                            <!-- Quantity -->
                            <div>
                                <label class="block text-xs font-bold text-indigo-600 uppercase tracking-wider mb-2">Quantity * 
                                    <span class="text-[10px] font-bold text-gray-400 uppercase">{{ selectedProduct?.unit || 'Pcs' }}</span>
                                </label>
                                
                                <div class="relative">
                                    <input v-model.number="form.quantity" type="number" min="1" :step="stepQuantity" class="w-full px-4 py-3 border border-indigo-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all font-bold text-gray-900">
                                </div>
                            </div>

                            <!-- Cost -->
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Unit Cost (Inc. Tax) *</label>
                                <div class="relative">
                                    <span class="absolute left-4 top-3 text-gray-400">$</span>
                                    <input v-model.number="form.unit_cost" type="number" min="0" step="0.1" class="w-full pl-8 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all font-bold text-gray-900">
                                </div>
                            </div>

                            <!-- Selling Price -->
                            <div>
                                <label class="block text-xs font-bold text-emerald-600 uppercase tracking-wider mb-2">Selling Price *</label>
                                <div class="relative">
                                    <span class="absolute left-4 top-3 text-emerald-400">$</span>
                                    <input v-model.number="form.unit_price" type="number" min="0" step="0.1" class="w-full pl-8 pr-4 py-3 border border-emerald-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all font-bold text-emerald-700">
                                </div>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Public/Internal Notes</label>
                            <textarea v-model="form.notes" rows="2" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all text-sm" placeholder="Add specific details about this stock arrival..."></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Stats & Summary -->
            <div class="space-y-6">
                <!-- Product Preview Card -->
                <div v-if="detailedProductData || loadingDetails" class="bg-indigo-900 rounded-2xl shadow-xl overflow-hidden text-white transition-all duration-300">
                    <div v-if="loadingDetails" class="p-12 flex flex-col items-center justify-center space-y-4">
                        <Icon icon="mdi:refresh" class="h-10 w-10 animate-spin text-indigo-300" />
                        <p class="text-xs text-indigo-300 animate-pulse">Cargando detalles del producto...</p>
                    </div>
                    <template v-else-if="detailedProductData">
                        <div class="relative h-40 bg-white">
                            <img :src="detailedProductData.product.default_image?.url || '/placeholder-product.png'" class="w-full h-full object-contain p-4">
                            <div class="absolute bottom-0 left-0 right-0 h-1/2 bg-gradient-to-t from-black/20 to-transparent"></div>
                        </div>
                        <div class="p-6 space-y-4">
                            <div>
                                <h4 class="text-indigo-200 text-[10px] font-bold uppercase tracking-widest">Selección Activa</h4>
                                <p class="text-xl font-bold truncate">{{ detailedProductData.product.name }}</p>
                                <p class="text-indigo-300 text-xs font-mono mt-1">{{ detailedProductData.product.sku }}</p>
                            </div>
                            
                            <div class="flex items-center justify-between p-3 bg-white/10 rounded-xl">
                                <div class="text-center flex-1 border-r border-white/10">
                                    <p class="text-[10px] text-indigo-300 uppercase font-bold">Stock Actual</p>
                                    <p class="text-lg font-bold">{{ detailedProductData.total_quantity || 0 }}</p>
                                </div>
                                <div class="text-center flex-1">
                                    <p class="text-[10px] text-indigo-300 uppercase font-bold">Last Costo</p>
                                    <p class="text-lg font-bold">${{ Number(detailedProductData.product.latest_entry?.unit_cost || 0).toFixed(2) }}</p>
                                </div>
                                 <div class="text-center flex-1">
                                    <p class="text-[10px] text-indigo-300 uppercase font-bold">Last Price</p>
                                    <p class="text-lg font-bold">${{ Number(detailedProductData.product.latest_entry?.unit_price || 0).toFixed(2) }}</p>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Margin Calculator -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 space-y-6">
                    <h3 class="font-bold text-gray-900 text-sm border-b pb-4">Financial Insight</h3>
                    
                    <div class="space-y-4">
                        <div class="flex justify-between items-end">
                            <span class="text-gray-500 text-xs font-medium">Profit Per Unit</span>
                            <span class="text-xl font-black" :class="profit > 0 ? 'text-emerald-600' : 'text-red-600'">
                                ${{ profit.toFixed(2) }}
                            </span>
                        </div>
                        
                        <div class="flex justify-between items-center">
                            <span class="text-gray-500 text-xs font-medium">Margin Percentage</span>
                            <span 
                                class="px-2 py-1 rounded-lg text-xs font-black border"
                                :class="marginClass"
                            >
                                {{ margin.toFixed(1) }}%
                            </span>
                        </div>
  
                        <div class="pt-4 border-t border-gray-100 flex justify-between items-center font-bold">
                            <span class="text-gray-900 text-xs">Total Profit Estimated</span>
                            <span class="text-indigo-600">${{ (form.quantity * (form.unit_price - form.unit_cost )).toFixed(2) }}</span>
                        </div>

                        <div class="pt-4 border-t border-gray-100 flex justify-between items-center font-bold">
                            <span class="text-gray-900 text-xs">Total Investment</span>
                            <span class="text-indigo-600">${{ (form.quantity * form.unit_cost).toFixed(2) }}</span>
                        </div>

                    </div>

                    <!-- Warning for low margin -->
                    <div v-if="margin < 15 && margin > 0" class="flex gap-3 p-3 bg-amber-50 rounded-xl border border-amber-100">
                        <Icon icon="mdi:alert-triangle" class="h-5 w-5 text-amber-600 shrink-0" />
                        <p class="text-[11px] text-amber-700 font-medium leading-relaxed">
                            Low margin detected. Verify your selling price to ensure profitability after operational costs.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, reactive, computed } from 'vue';
import api from '../axios';
import { useRouter, useRoute } from 'vue-router';
import swal from '../utils/swal';
import { debounce } from 'lodash';
import {
    Combobox,
    ComboboxInput,
    ComboboxButton,
    ComboboxOptions,
    ComboboxOption,
    TransitionRoot,
} from '@headlessui/vue';
import { Icon } from '@iconify/vue';

const router = useRouter();
const route = useRoute();
const submitting = ref(false);
const loadingDetails = ref(false);
const detailedProductData = ref(null);

const isEdit = computed(() => !!route.params.id);
const isProductLocked = computed(() => !!route.query.product_id);
const stepQuantity = computed(() => {
    if(selectedProduct?.value?.unit){
        let unit = selectedProduct.value.unit;
        return unit === 'kg' || unit === 'meter' ? 0.01 : 1
    }
    return 1;
})
const warehouses = ref([]);
const warehouseQuery = ref('');
const selectedWarehouse = ref(null);

const suppliers = ref([]);
const supplierQuery = ref('');
const selectedSupplier = ref(null);

const productResults = ref([]);
const productQuery = ref('');
const loadingProducts = ref(false);
const selectedProduct = ref(null);

const form = reactive({
    product_id: '',
    warehouse_id: '',
    supplier_id: '',
    quantity: 0,
    unit_cost: 0,
    unit_price: 0,
    entry_date: new Date().toISOString().slice(0, 16),
    batch_number: '',
    notes: ''
});

const profit = computed(() => {
    if (!form.unit_cost || !form.unit_price) return 0;
    return form.unit_price - form.unit_cost;
});

const margin = computed(() => {
    if (!form.unit_price || !form.unit_cost) return 0;
    return (profit.value / form.unit_price) * 100;
});

const marginClass = computed(() => {
    if (margin.value >= 30) return 'bg-emerald-50 text-emerald-700 border-emerald-200';
    if (margin.value >= 15) return 'bg-blue-50 text-blue-700 border-blue-200';
    if (margin.value > 0) return 'bg-amber-50 text-amber-700 border-amber-200';
    return 'bg-red-50 text-red-700 border-red-200';
});

const isValid = computed(() => {
    return form.product_id && form.warehouse_id && form.quantity > 0 && form.unit_cost > 0;
});

const filteredWarehouses = computed(() => {
    if (warehouseQuery.value === '') return warehouses.value;
    const term = warehouseQuery.value.toLowerCase();
    return warehouses.value.filter(w => w.name.toLowerCase().includes(term));
});

const filteredSuppliers = computed(() => {
    if (supplierQuery.value === '') return suppliers.value;
    const term = supplierQuery.value.toLowerCase();
    return suppliers.value.filter(s => s.name.toLowerCase().includes(term));
});

const loadDependencies = async () => {
    try {
        const [warehousesRes, suppliersRes] = await Promise.all([
            api.get('/warehouses?per_page=100'),
            api.get('/suppliers?per_page=100')
        ]);
        warehouses.value = warehousesRes.data.data.data || warehousesRes.data.data; 
        suppliers.value = suppliersRes.data.data.data || suppliersRes.data.data;
        if (warehouses.value.length > 0) {
            selectedWarehouse.value = warehouses.value[0];
            form.warehouse_id = warehouses.value[0].id;
        }
    } catch (error) {
        console.error('Error loading dependencies:', error);
    }
};

const searchProducts = async (query) => {
    if (!query || query.length < 2) return;
    loadingProducts.value = true;
    try {
        const response = await api.get('/products', { params: { search: query, per_page: 20 } });
        productResults.value = response.data.data.data || response.data.data;
    } catch (error) {
        console.error('Error searching products:', error);
    } finally {
        loadingProducts.value = false;
    }
};

const onProductSearch = debounce((query) => searchProducts(query), 300);

const fetchProductDetails = async (productId) => {
    loadingDetails.value = true;
    try {
        const response = await api.get(`/inventory/product/${productId}`);
        detailedProductData.value = response.data.data;
        
        // Update form with latest suggestions
        if (detailedProductData.value.product.latest_entry) {
            let latestEntry = detailedProductData.value.product.latest_entry;
            form.unit_cost = latestEntry.unit_cost;
            form.unit_price = latestEntry.unit_price;

            warehouses.value.filter(w => w.id === latestEntry.warehouse_id).forEach(w => {
                selectedWarehouse.value = w;
                form.warehouse_id = w.id;
            });

            suppliers.value.filter(s => s.id === latestEntry.supplier_id).forEach(s => {
                selectedSupplier.value = s;
                form.supplier_id = s.id;
            });
        }
    } catch (error) {
        console.error('Error fetching product details:', error);
    } finally {
        loadingDetails.value = false;
    }
};

const fetchEntryDetails = async (id) => {
    loadingDetails.value = true;
    try {
        const response = await api.get(`/inventory/entries/${id}`);
        const entry = response.data.data;
        
        // Populate form
        form.product_id = entry.product_id;
        form.warehouse_id = entry.warehouse_id;
        form.supplier_id = entry.supplier_id;
        form.quantity = entry.quantity;
        form.unit_cost = entry.unit_cost;
        form.unit_price = entry.unit_price;
        form.entry_date = entry.entry_date;
        form.batch_number = entry.batch_number || '';
        form.notes = entry.notes || '';
        
        selectedProduct.value = entry.product;
        selectedWarehouse.value = entry.warehouse;
        selectedSupplier.value = entry.supplier;
        
        // Update product preview sidebar
        fetchProductDetails(entry.product_id);
    } catch (error) {
        console.error('Error fetching entry details:', error);
        swal.error('Could not load entry details.');
    } finally {
        loadingDetails.value = false;
    }
};

const onProductSelect = (product) => {
    if (!product) {
        detailedProductData.value = null;
        return;
    }
    form.product_id = product.id;
    fetchProductDetails(product.id);
};

const onWarehouseSelect = (w) => form.warehouse_id = w ? w.id : '';
const onSupplierSelect = (s) => form.supplier_id = s ? s.id : '';

const submitEntry = async () => {
    submitting.value = true;
    try {
        if (isEdit.value) {
            await api.put(`/inventory/entries/${route.params.id}`, form);
            swal.success('Product entry updated successfully!');
        } else {
            await api.post('/inventory/entry', form);
            swal.success('Product entry created successfully!');
        }
        router.push({ name: 'Inventory' });
    } catch (error) {
        swal.error(error.response?.data?.message || 'Failed to submit entry.');
    } finally {
        submitting.value = false;
    }
};

onMounted(async () => {
    await loadDependencies();
    if (isEdit.value) {
        fetchEntryDetails(route.params.id);
    }else if(route.query.product_id){
        const prodId = route.query.product_id;
        const response = await api.get(`/products/${prodId}`);
        
        selectedProduct.value = response.data.data;
        form.product_id = prodId;
        fetchProductDetails(prodId);
    }
});
</script>
