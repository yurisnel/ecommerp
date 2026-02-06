<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ isEditing ? 'Edit Product' : 'New Product' }}</h1>
                <p class="text-gray-500 text-sm mt-1">Manage product details and categorization.</p>
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
                    class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition-colors flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <span v-if="submitting" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                    <span>{{ isEditing ? 'Update Product' : 'Create Product' }}</span>
                </button>
            </div>
        </div>

        <!-- Tabs Header (Only when editing) -->
        <div v-if="isEditing" class="border-b border-gray-200">
            <nav class="flex space-x-8" aria-label="Tabs">
                <button 
                    v-for="tab in tabs" 
                    :key="tab.id"
                    @click="activateTab(tab.id)"
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
                                Product Name <span class="text-red-500">*</span>
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
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                SKU <span class="text-red-500">*</span>
                            </label>
                            <input 
                                v-model="form.sku" 
                                type="text"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                :class="{ 'border-red-300': errors.sku }"
                            >
                            <p v-if="errors.sku" class="mt-1 text-sm text-red-600">{{ errors.sku }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Barcode</label>
                            <input 
                                v-model="form.barcode" 
                                type="text"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                            >
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Unit <span class="text-red-500">*</span>
                            </label>
                            <select 
                                v-model="form.unit" 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                            >
                                <option value="u">Units (u)</option>
                                <option value="kg">Kilogram (kg)</option>
                                <option value="m">Meter (m)</option>
                                <option value="l">Liter (l)</option>
                                <option value="box">Box</option>
                            </select>
                        </div>
                        
                        <div class="col-span-2">
                             <div class="flex justify-between items-center mb-2">
                                <label class="block text-sm font-medium text-gray-700">Description</label>
                                <button 
                                    type="button"
                                    @click="showHtml = !showHtml"
                                    class="text-xs font-medium text-indigo-600 hover:text-indigo-800 flex items-center gap-1"
                                >
                                    <template v-if="!showHtml">
                                        <PlusIcon class="h-3 w-3 rotate-45" /> <!-- Placeholder for Code icon -->
                                        <span>View HTML Source</span>
                                    </template>
                                    <template v-else>
                                        <span>Back to Editor</span>
                                    </template>
                                </button>
                             </div>
                             <div class="bg-white">
                                <QuillEditor 
                                    v-if="!showHtml"
                                    v-model:content="form.description" 
                                    contentType="html"
                                    theme="snow"
                                    class="min-h-[150px]"
                                    toolbar="essential"
                                />
                                <textarea
                                    v-else
                                    v-model="form.description"
                                    class="w-full min-h-[150px] p-4 font-mono text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-gray-50"
                                    placeholder="Enter HTML source code..."
                                ></textarea>
                             </div>
                        </div>
                        <div class="flex items-center h-full pt-6">
                             <SwitchGroup as="div" class="flex items-center">
                                <Switch 
                                    v-model="isStatusActive" 
                                    :class="[isStatusActive ? 'bg-indigo-600' : 'bg-gray-200', 'relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2']"
                                >
                                    <span aria-hidden="true" :class="[isStatusActive ? 'translate-x-5' : 'translate-x-0', 'pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out']" />
                                </Switch>
                                <SwitchLabel as="span" class="ml-3 cursor-pointer">
                                    <span class="text-sm font-medium text-gray-900">{{ isStatusActive ? 'Active' : 'Inactive' }}</span>
                                </SwitchLabel>
                            </SwitchGroup>
                        </div>
                        
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4 border-b pb-2">Inventory Settings</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Min Stock Alert <span class="text-red-500">*</span>
                            </label>
                            <input 
                                v-model="form.min_stock" 
                                type="number" 
                                min="0"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                            >
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Max Stock Target</label>
                            <input 
                                v-model="form.max_stock" 
                                type="number" 
                                min="0"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                            >
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4 border-b pb-2">Categorization</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Categories</label>
                             <Listbox v-model="form.categories" multiple>
                                <div class="relative mt-1">
                                    <ListboxButton
                                        class="relative w-full cursor-default rounded-lg bg-white py-2 pl-3 pr-10 text-left border border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                    >
                                        <span class="block truncate">
                                            {{ form.categories.length > 0 
                                                ? getCategoryNames(form.categories)
                                                : 'Select Categories' 
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
                                                :value="category.id"
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
                            <p class="text-xs text-gray-500 mt-1">Select one or more categories for this product.</p>
                        </div>
                    </div>
                </div>

                <!-- Image Gallery -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4 border-b pb-2">Product Images</h3>
                    
                    <div class="space-y-4">
                        <!-- Upload Area -->
                        <div 
                            @click="triggerFileUpload"
                            class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center cursor-pointer hover:border-indigo-500 hover:bg-indigo-50 transition-all group"
                        >
                            <input 
                                type="file" 
                                ref="fileInput" 
                                class="hidden" 
                                multiple 
                                accept="image/*"
                                @change="handleFileUpload"
                            >
                            <div class="flex flex-col items-center">
                                <template v-if="!uploading">
                                    <div class="p-3 bg-indigo-50 rounded-full text-indigo-600 group-hover:scale-110 transition-transform mb-3">
                                        <PlusIcon class="h-6 w-6" />
                                    </div>
                                    <p class="text-sm font-medium text-gray-900">Click to upload images</p>
                                    <p class="text-xs text-gray-500 mt-1">PNG, JPG, WEBP up to 2MB</p>
                                </template>
                                <template v-else>
                                    <div class="w-10 h-10 border-4 border-indigo-200 border-t-indigo-600 rounded-full animate-spin mb-3"></div>
                                    <p class="text-sm font-medium text-indigo-600">Uploading...</p>
                                </template>
                            </div>
                        </div>

                        <!-- Image List -->
                        <div v-if="form.product_images && form.product_images.length > 0" class="space-y-3">
                            <div 
                                v-for="(img, index) in form.product_images" 
                                :key="index"
                                class="flex items-center gap-3 p-2 border border-gray-100 rounded-lg hover:bg-gray-50 group"
                                :class="{ 'ring-2 ring-indigo-500 bg-indigo-50': img.is_default }"
                            >
                                <div class="h-12 w-12 rounded overflow-hidden bg-gray-200 flex-shrink-0">
                                    <img :src="img.url" class="h-full w-full object-cover" alt="Product">
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm text-gray-600 truncate">{{ img.url }}</p>
                                    <p v-if="img.is_default" class="text-xs text-indigo-600 font-medium">Default Image</p>
                                </div>
                                <div class="flex gap-1">
                                    <button 
                                        type="button"
                                        @click="setDefaultImage(img.url)"
                                        class="p-1 text-gray-400 hover:text-yellow-500 transition-colors"
                                        title="Set as Default"
                                    >
                                        <StarIcon class="h-5 w-5" :class="{ 'text-yellow-500': img.is_default }" />
                                    </button>
                                    <button 
                                        type="button"
                                        @click="removeImage(index)"
                                        class="p-1 text-gray-400 hover:text-red-600 transition-colors"
                                        title="Remove"
                                    >
                                        <TrashIcon class="h-5 w-5" />
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div v-else class="border-2 border-dashed border-gray-200 rounded-lg p-6 text-center text-gray-400">
                            <p class="text-sm">No images added yet.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

        <!-- Entries Tab Content -->
        <div v-if="isEditing && activeTab === 'entries'" class="space-y-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <ProductEntryHistory :productId="product_id" />
            </div>
        </div>

        <!-- Movements Tab Content -->
        <div v-if="isEditing && activeTab === 'movements'" class="space-y-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <StockMovementLog :productId="product_id" />
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed, reactive } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import api from '../axios';
import {
    Listbox,
    ListboxButton,
    ListboxOptions,
    ListboxOption,
    Switch,
    SwitchGroup,
    SwitchLabel,
} from '@headlessui/vue';
import { CheckIcon, ChevronUpDownIcon, TrashIcon, StarIcon, PlusIcon } from '@heroicons/vue/20/solid';
import { QuillEditor } from '@vueup/vue-quill';
import '@vueup/vue-quill/dist/vue-quill.snow.css';

// Components for tabs
import ProductEntryHistory from './ProductEntryHistory.vue';
import StockMovementLog from './StockMovementLog.vue';

const route = useRoute();
const router = useRouter();

const isEditing = computed(() => route.params.id !== undefined);
const submitting = ref(false);
const uploading = ref(false);
const fileInput = ref(null);
const categories = ref([]);
const errors = ref({});
const newImageUrl = ref('');
const showHtml = ref(false);
const product_id = computed(() => route.params.id);

const activeTab = ref('general');
const tabs = [
    { id: 'general', name: 'General Information' },
    { id: 'entries', name: 'Purchase Entries' },
    { id: 'movements', name: 'Movement History' }
];


const activateTab = (tabId) => {
    activeTab.value = tabId;
};

const form = reactive({
    name: '',
    sku: '',
    barcode: '',
    unit: 'u',
    categories: [], 
    description: '',
    min_stock: 0,
    max_stock: 0,
    status: 'active',
    product_images: []
});

const isStatusActive = computed({
    get: () => form.status === 'active',
    set: (val) => form.status = val ? 'active' : 'inactive'
});

const triggerFileUpload = () => {
    fileInput.value.click();
};

const handleFileUpload = async (event) => {
    const files = event.target.files;
    if (!files || files.length === 0) return;

    uploading.value = true;
    
    try {
        for (let i = 0; i < files.length; i++) {
            const formData = new FormData();
            formData.append('file', files[i]);
            formData.append('folder', 'products');

            const response = await api.post('/upload', formData, {
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            });

            if (response.data.success) {
                const url = response.data.url;
                
                // Determine if it should be default
                const isDefault = form.product_images.length === 0;
                
                form.product_images.push({
                    url: url,
                    is_default: isDefault
                });
            }
        }
    } catch (error) {
        console.error('Upload failed:', error);
        alert('Some images failed to upload. Please try again.');
    } finally {
        uploading.value = false;
        // Reset file input
        event.target.value = '';
    }
};

const addImage = () => {
    if (!newImageUrl.value) return;
    
    // Initialize images if it's not an array (just safety)
    if (!Array.isArray(form.images)) {
        form.images = [];
    }

    form.images.push(newImageUrl.value);
    
    // Set as default if it's the first image
    if (form.images.length === 1 || !form.image) {
        form.image = newImageUrl.value;
    }
    
    newImageUrl.value = '';
};

const removeImage = (index) => {
    const wasDefault = form.product_images[index].is_default;
    form.product_images.splice(index, 1);
    
    // If we removed the default image, pick a new one if available
    if (wasDefault && form.product_images.length > 0) {
        form.product_images[0].is_default = true;
    }
};

const setDefaultImage = (url) => {
    form.product_images.forEach(img => {
        img.is_default = (img.url === url);
    });
};

const getCategoryNames = (ids) => {
    return categories.value
        .filter(c => ids.includes(c.id))
        .map(c => c.name)
        .join(', ');
};

const fetchCategories = async () => {
    try {
        const response = await api.get('/categories?per_page=100');
        categories.value = response.data.data.data || response.data.data;
    } catch (error) {
        console.error('Error fetching categories:', error);
    }
};

const fetchProduct = async () => {
    if (!isEditing.value) return;
    
    try {
        const response = await api.get(`/products/${route.params.id}`);
        const product = response.data.data;
        
        form.name = product.name;
        form.sku = product.sku;
        form.barcode = product.barcode;
        form.unit = product.unit;
        form.description = product.description;
        form.min_stock = product.min_stock;
        form.max_stock = product.max_stock;
        form.status = product.status;
        
        // Load images
        form.product_images = product.images.map(img => ({
            url: img.url,
            is_default: Boolean(img.is_default)
        }));

        // Load categories
        if (product.categories) {
            form.categories = product.categories.map(c => c.id);
        }
    } catch (error) {
        console.error('Error fetching product:', error);
        alert('Failed to load product details');
    }
};

const submit = async () => {
    submitting.value = true;
    errors.value = {};
    
    try {
        if (isEditing.value) {
            await api.put(`/products/${route.params.id}`, form);
        } else {
            await api.post('/products', form);
        }
        router.push({ name: 'Products' });
    } catch (error) {
        if (error.response && error.response.status === 422) {
            if (error.response.data.errors) {
                // Map laravel errors to simple object
                Object.keys(error.response.data.errors).forEach(key => {
                    errors.value[key] = error.response.data.errors[key][0];
                });
            } else {
                 errors.value = { name: error.response.data.message };
            }
        } else {
            console.error('Error saving product:', error);
            alert('An error occurred while saving the product');
        }
    } finally {
        submitting.value = false;
    }
};

onMounted(() => {
    fetchCategories();
    fetchProduct();
});
</script>

<style>
/* Fix Quill toolbar and content box styles */
.ql-toolbar.ql-snow {
    border-top-left-radius: 0.5rem;
    border-top-right-radius: 0.5rem;
    border-color: #d1d5db !important;
}
.ql-container.ql-snow {
    border-bottom-left-radius: 0.5rem;
    border-bottom-right-radius: 0.5rem;
    border-color: #d1d5db !important;
    font-family: inherit;
}
.ql-editor {
    min-height: 150px;
    font-size: 0.875rem;
}
</style>
