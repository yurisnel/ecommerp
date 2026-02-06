<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ isEditing ? 'Edit Customer' : 'New Customer' }}</h1>
                <p class="text-gray-500 text-sm mt-1">Manage customer profiles and contact information.</p>
            </div>
            <div class="flex gap-3">
                <button 
                    @click="$router.back()" 
                    class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors"
                >
                    Cancel
                </button>
                <button 
                    @click="submit" 
                    :disabled="submitting"
                    class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition-colors flex items-center gap-2 disabled:opacity-50"
                >
                    <span v-if="submitting" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                    <span>{{ isEditing ? 'Update Customer' : 'Create Customer' }}</span>
                </button>
            </div>
        </div>

        <!-- Tabs Header (Only when editing) -->
        <div v-if="isEditing" class="border-b border-gray-200">
            <nav class="flex space-x-8" aria-label="Tabs">
                <button 
                    v-for="tab in tabs" 
                    :key="tab.id"
                    @click="activeTab = tab.id"
                    class="pb-4 px-1 border-b-2 font-medium text-sm transition-colors"
                    :class="[
                        activeTab === tab.id 
                        ? 'border-indigo-500 text-indigo-600' 
                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                    ]"
                >
                    {{ tab.name }}
                </button>
            </nav>
        </div>

        <div v-show="activeTab === 'general'">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Main Info -->
                <div class="md:col-span-2 space-y-6">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4 border-b pb-2">Basic Information</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Full Name / Company Name <span class="text-red-500">*</span>
                                </label>
                                <input 
                                    v-model="form.name" 
                                    type="text"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                    :class="{ 'border-red-300': errors.name }"
                                >
                                <p v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                <input 
                                    v-model="form.email" 
                                    type="email"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                >
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                                <input 
                                    v-model="form.phone" 
                                    type="text"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                >
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tax ID / RUC / NIT</label>
                                <input 
                                    v-model="form.tax_id" 
                                    type="text"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                >
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                                <select 
                                    v-model="form.type" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                >
                                    <option value="retail">Retail</option>
                                    <option value="wholesale">Wholesale</option>
                                </select>
                            </div>

                            <div class="col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                                <textarea 
                                    v-model="form.notes"
                                    rows="3"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                ></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4 border-b pb-2">Status & Group</h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                <select 
                                    v-model="form.status" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                >
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Customer Group</label>
                                <select 
                                    v-model="form.customer_group_id" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                >
                                    <option :value="null">None</option>
                                    <option v-for="group in groups" :key="group.id" :value="group.id">{{ group.name }}</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div v-show="activeTab === 'addresses' && isEditing">
             <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-medium text-gray-900">Manage Addresses</h3>
                    <button 
                        @click="openAddressModal()"
                        class="text-sm bg-indigo-50 text-indigo-600 px-3 py-1.5 rounded-lg hover:bg-indigo-100 font-semibold transition-colors"
                    >
                        Add New Address
                    </button>
                </div>

                <div v-if="loadingAddresses" class="py-8 text-center text-gray-500">Loading addresses...</div>
                <div v-else-if="addresses.length === 0" class="py-12 border-2 border-dashed border-gray-100 rounded-xl text-center text-gray-400 italic">
                    No addresses registered for this customer.
                </div>
                <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div 
                        v-for="addr in addresses" 
                        :key="addr.id"
                        class="p-4 border border-gray-200 rounded-xl relative hover:border-indigo-300 transition-colors group"
                        :class="{'ring-2 ring-indigo-500 bg-indigo-50/30': addr.is_default}"
                    >
                        <div class="flex justify-between">
                            <span class="text-xs font-bold uppercase tracking-wider text-gray-400">{{ addr.type }} Address</span>
                            <span v-if="addr.is_default" class="text-[10px] font-bold bg-indigo-600 text-white px-1.5 py-0.5 rounded uppercase">Default</span>
                        </div>
                        <div class="mt-2 text-sm text-gray-700">
                            <p class="font-medium text-gray-900">{{ addr.address_line1 }}</p>
                            <p v-if="addr.address_line2">{{ addr.address_line2 }}</p>
                            <p>{{ addr.city }}, {{ addr.state }} {{ addr.postal_code }}</p>
                            <p class="text-xs text-gray-400 mt-1 uppercase">{{ addr.country }}</p>
                        </div>
                        <div class="mt-4 flex gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                            <button @click="openAddressModal(addr)" class="text-xs text-indigo-600 font-semibold">Edit</button>
                            <button v-if="!addr.is_default" @click="setDefaultAddress(addr.id)" class="text-xs text-emerald-600 font-semibold">Set as Default</button>
                            <button @click="deleteAddress(addr.id)" class="text-xs text-rose-600 font-semibold ml-auto">Delete</button>
                        </div>
                    </div>
                </div>
             </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed, reactive } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import api from '../axios';

const route = useRoute();
const router = useRouter();

const isEditing = computed(() => route.params.id !== undefined);
const submitting = ref(false);
const groups = ref([]);
const errors = ref({});
const activeTab = ref('general');
const tabs = [
    { id: 'general', name: 'General Information' },
    { id: 'addresses', name: 'Addresses' }
];

const form = reactive({
    name: '',
    email: '',
    phone: '',
    tax_id: '',
    type: 'retail',
    notes: '',
    status: 'active',
    customer_group_id: null
});

const addresses = ref([]);
const loadingAddresses = ref(false);

const fetchGroups = async () => {
    try {
        const response = await api.get('/customer-groups');
        groups.value = response.data.data.data || response.data.data;
    } catch (error) {
        console.error('Error fetching groups:', error);
    }
};

const fetchCustomer = async () => {
    if (!isEditing.value) return;
    try {
        const response = await api.get(`/customers/${route.params.id}`);
        const customer = response.data.data;
        Object.assign(form, {
            name: customer.name,
            email: customer.email,
            phone: customer.phone,
            tax_id: customer.tax_id,
            type: customer.type,
            notes: customer.notes,
            status: customer.status,
            customer_group_id: customer.customer_group_id
        });
        fetchAddresses();
    } catch (error) {
        console.error('Error fetching customer:', error);
    }
};

const fetchAddresses = async () => {
    loadingAddresses.value = true;
    try {
        const response = await api.get(`/customers/${route.params.id}/addresses`);
        addresses.value = response.data.data;
    } catch (error) {
        console.error('Error fetching addresses:', error);
    } finally {
        loadingAddresses.value = false;
    }
};

const submit = async () => {
    submitting.value = true;
    errors.value = {};
    try {
        if (isEditing.value) {
            await api.put(`/customers/${route.params.id}`, form);
        } else {
            await api.post('/customers', form);
        }
        router.push({ name: 'Customers' });
    } catch (error) {
        if (error.response?.status === 422) {
            errors.value = error.response.data.errors;
        } else {
            console.error('Error saving customer:', error);
            alert('Failed to save customer');
        }
    } finally {
        submitting.value = false;
    }
};

// Address actions (Stub for now, will implement properly if high priority)
const openAddressModal = (addr = null) => {
    alert('Address management feature is coming next!');
};

const setDefaultAddress = async (id) => {
    try {
        await api.post(`/addresses/${id}/default`);
        fetchAddresses();
    } catch (error) {
        console.error('Error setting default address:', error);
    }
};

const deleteAddress = async (id) => {
    if (!confirm('Are you sure?')) return;
    try {
        await api.delete(`/addresses/${id}`);
        fetchAddresses();
    } catch (error) {
        console.error('Error deleting address:', error);
    }
};

onMounted(() => {
    fetchGroups();
    fetchCustomer();
});
</script>
