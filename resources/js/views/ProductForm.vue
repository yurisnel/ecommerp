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
                                <option value="pcs">Units (Pcs)</option>
                                <option value="kg">Kilogram (Kg)</option>
                                <option value="m">Meter (M)</option>
                                <option value="l">Liter (L)</option>
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
                                        <Icon icon="mdi:code-plus" class="h-3 w-3" /> <!-- Placeholder for Code icon -->
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
                                            <Icon icon="mdi:chevron-up-down" class="h-5 w-5 text-gray-400" aria-hidden="true" />
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
                                                        <Icon icon="mdi:check" class="h-5 w-5" aria-hidden="true" />
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
                        <!-- Upload Area with Drag & Drop -->
                        <div 
                            @click="triggerFileUpload"
                            @drop.prevent="handleDrop"
                            @dragover.prevent="isDraggingFiles = true"
                            @dragleave.prevent="isDraggingFiles = false"
                            class="border-2 border-dashed rounded-lg p-8 text-center cursor-pointer transition-all group"
                            :class="isDraggingFiles 
                                ? 'border-indigo-500 bg-indigo-50' 
                                : 'border-gray-300 hover:border-indigo-500 hover:bg-indigo-50'"
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
                                        <i class="fas fa-cloud-upload-alt text-2xl"></i>
                                    </div>
                                    <p class="text-sm font-medium text-gray-900">
                                        {{ isDraggingFiles ? 'Drop images here' : 'Click to upload or drag & drop' }}
                                    </p>
                                    <p class="text-xs text-gray-500 mt-1">PNG, JPG, WEBP up to 2MB each. Multiple files supported.</p>
                                </template>
                                <template v-else>
                                    <div class="w-10 h-10 border-4 border-indigo-200 border-t-indigo-600 rounded-full animate-spin mb-3"></div>
                                    <p class="text-sm font-medium text-indigo-600">Uploading images...</p>
                                    <p class="text-xs text-gray-500 mt-1">{{ uploadProgress }}</p>
                                </template>
                            </div>
                        </div>

                        <!-- Image Gallery Component -->
                        <ImageGallery 
                            :images="form.product_images"
                            @set-default="setDefaultImage"
                            @delete="removeImage"
                            @view="viewImage"
                            @edit="editImage"
                            @reorder="reorderImages"
                            empty-message="No images added yet. Drag and drop or click above to add images."
                        />

                        <!-- Image Options -->
                        <div class="space-y-3 border-t pt-4">
                            <div class="flex items-center gap-3">
                                <input 
                                    v-model="autoCompress"
                                    type="checkbox"
                                    class="w-4 h-4 text-indigo-600"
                                >
                                <label class="text-sm text-gray-700">Automatically compress images (recommended)</label>
                            </div>
                            <div v-if="autoCompress" class="flex items-center gap-3 ml-7">
                                <label class="text-sm text-gray-700">Compression quality:</label>
                                <input 
                                    v-model.number="compressionQuality"
                                    type="range"
                                    min="50"
                                    max="100"
                                    step="5"
                                    class="w-32 h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-indigo-600"
                                >
                                <span class="text-sm font-medium text-indigo-600 w-12">{{ compressionQuality }}%</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Image Cropper Modal -->
                <ImageCropper 
                    v-if="showCropper"
                    :imageUrl="croppingImage"
                    @close="showCropper = false"
                    @crop="applyCrop"
                />
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

        <!-- Variations Tab Content -->
        <div v-if="isEditing && activeTab === 'variations'" class="space-y-6">
             <ProductVariants :productId="product_id" />
        </div> 
    </div>
</template>

<script setup>
import { ref, onMounted, computed, reactive, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import api from '../axios';
import swal from '../utils/swal';
import {
    Listbox,
    ListboxButton,
    ListboxOptions,
    ListboxOption,
    Switch,
    SwitchGroup,
    SwitchLabel,
} from '@headlessui/vue';
import { Icon } from '@iconify/vue';
import { QuillEditor } from '@vueup/vue-quill';
import '@vueup/vue-quill/dist/vue-quill.snow.css';

// Components for tabs
import ProductEntryHistory from './ProductEntryHistory.vue';
import StockMovementLog from './StockMovementLog.vue';
import ImageGallery from '../components/ImageGallery.vue';
import ImageCropper from '../components/ImageCropper.vue';
import ProductVariants from './ProductVariants.vue';

const route = useRoute();
const router = useRouter();

const isEditing = computed(() => route.params.id !== undefined);
const submitting = ref(false);
const uploading = ref(false);
const isDraggingFiles = ref(false);
const uploadProgress = ref('');
const fileInput = ref(null);
const categories = ref([]);
const errors = ref({});
const newImageUrl = ref('');
const showHtml = ref(false);
const product_id = computed(() => route.params.id);

// Image cropper
const showCropper = ref(false);
const croppingImage = ref('');
const croppingImageIndex = ref(null);

// Image options
const autoCompress = ref(true);
const compressionQuality = ref(85);

const activeTab = ref('general');
const tabs = [
    { id: 'general', name: 'General Information' },
    { id: 'variations', name: 'Variaciones' },
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

const handleDrop = async (event) => {
    isDraggingFiles.value = false;
    const files = event.dataTransfer.files;
    if (!files || files.length === 0) return;

    // Similar to handleFileUpload
    const fileList = new DataTransfer();
    for (let file of files) {
        fileList.items.add(file);
    }
    fileInput.value.files = fileList.files;
    
    await handleFileUpload({ target: { files: fileList.files } });
};

const handleFileUpload = async (event) => {
    const files = event.target.files;
    if (!files || files.length === 0) return;

    uploading.value = true;
    let successCount = 0;
    
    try {
        for (let i = 0; i < files.length; i++) {
            uploadProgress.value = `${i + 1} of ${files.length}`;
            
            const formData = new FormData();
            formData.append('file', files[i]);
            formData.append('folder', 'products');
            // Convert boolean to string that server can parse
            formData.append('compress', autoCompress.value ? '1' : '0');
            formData.append('quality', String(compressionQuality.value));

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
                    is_default: isDefault,
                    sort_order: form.product_images.length
                });
                successCount++;
            }
        }
        
        if (successCount > 0) {
            uploadProgress.value = `${successCount} image${successCount > 1 ? 's' : ''} uploaded successfully`;
        }
    } catch (error) {
        console.error('Upload failed:', error);
        swal.error('Some images failed to upload. Please try again.');
    } finally {
        uploading.value = false;
        uploadProgress.value = '';
        // Reset file input
        event.target.value = '';
    }
};

const addImage = () => {
    if (!newImageUrl.value) return;
    
    // Initialize images if it's not an array (just safety)
    if (!Array.isArray(form.product_images)) {
        form.product_images = [];
    }

    const isDefault = form.product_images.length === 0;
    form.product_images.push({
        url: newImageUrl.value,
        is_default: isDefault
    });
    
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

const setDefaultImage = (index) => {
    form.product_images.forEach((img, i) => {
        img.is_default = (i === index);
    });
};

const viewImage = (url) => {
    // Image preview is handled in ImageGallery component
    window.open(url, '_blank');
};

const editImage = (index) => {
    if (form.product_images[index]) {
        croppingImageIndex.value = index;
        croppingImage.value = form.product_images[index].url;
        showCropper.value = true;
    }
};

const applyCrop = async (cropData) => {
    if (croppingImageIndex.value !== null) {
        // Convert base64 to blob and upload
        const blob = await fetch(cropData.dataUrl).then(res => res.blob());
        const formData = new FormData();
        formData.append('file', blob, 'cropped-image.jpg');
        formData.append('folder', 'products');
        formData.append('quality', String(cropData.quality));
        formData.append('compress', '0'); // Already cropped, no need to compress again

        try {
            uploading.value = true;
            const response = await api.post('/upload', formData, {
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            });

            if (response.data.success) {
                // Replace the cropped image
                form.product_images[croppingImageIndex.value].url = response.data.url;
            }
        } catch (error) {
            console.error('Error uploading cropped image:', error);
            swal.error('Failed to save cropped image');
        } finally {
            uploading.value = false;
            showCropper.value = false;
            croppingImageIndex.value = null;
            croppingImage.value = '';
        }
    }
};

const reorderImages = (reorderData) => {
    const { fromIndex, toIndex } = reorderData;
    
    // Move image in array
    const [movedImage] = form.product_images.splice(fromIndex, 1);
    form.product_images.splice(toIndex, 0, movedImage);
    
    // Update sort_order
    form.product_images.forEach((img, index) => {
        img.sort_order = index;
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
        swal.error('Failed to load product details');
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
        router.push({ name: 'Inventory' });
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
            swal.error('An error occurred while saving the product');
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
