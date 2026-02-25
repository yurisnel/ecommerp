<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ isEditing ? 'Edit Category' : 'New Category' }}</h1>
                <p class="text-gray-500 text-sm mt-1">Manage product categorization and structure.</p>
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
                    class="bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700 transition-colors flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <span v-if="submitting" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                    <span>{{ isEditing ? 'Update Category' : 'Create Category' }}</span>
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Main Content -->
            <div class="md:col-span-2 space-y-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4 border-b pb-2">Basic Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Category Name <span class="text-red-500">*</span>
                            </label>
                            <input 
                                v-model="form.name" 
                                type="text"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                :class="{ 'border-red-300': errors.name }"
                                @input="generateSlug"
                            >
                            <p v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name }}</p>
                        </div>

                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Slug <span class="text-red-500">*</span>
                            </label>
                            <input 
                                v-model="form.slug" 
                                type="text"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-gray-50"
                                :class="{ 'border-red-300': errors.slug }"
                            >
                            <p v-if="errors.slug" class="mt-1 text-sm text-red-600">{{ errors.slug }}</p>
                        </div>

                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                            <textarea 
                                v-model="form.description" 
                                rows="4"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                            ></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Photo card (compact, lateral) -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 flex flex-col items-center">
                    <h3 class="text-lg font-medium text-gray-900 mb-3">Photo</h3>
                    <div class="h-24 w-24 rounded-full overflow-hidden border border-gray-200 mb-3">
                        <img v-if="form.image" :src="form.image" :alt="form.name || 'Category image'" class="object-cover h-full w-full" @error="onImageError" />
                        <div v-else class="h-full w-full bg-gray-100 flex items-center justify-center">
                            <i class="fas fa-image text-gray-300"></i>
                        </div>
                    </div>

                    <div class="flex gap-2 mb-2">
                        <button @click="showUploader = !showUploader" type="button" class="px-3 py-1 text-sm bg-indigo-600 text-white rounded">Editar</button>
                        <button v-if="form.image" @click="confirmRemoveImage" type="button" class="px-3 py-1 text-sm border rounded">Eliminar</button>
                    </div>

                    <p class="text-xs text-gray-400 text-center">Recomendado 400×400 • ≤2MB</p>

                    <div v-show="showUploader" class="w-full mt-3">
                        <ImageUploader v-model="form.image" folder="categories" />
                    </div>
                </div>

                <!-- Parent Category Selection -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Structure</h3>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Parent Category</label>
                        <select 
                            v-model="form.parent_id" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                        >
                            <option :value="null">None (Root Category)</option>
                            <option 
                                v-for="cat in availableCategories" 
                                :key="cat.id" 
                                :value="cat.id"
                                :disabled="cat.id === Number($route.params.id)"
                            >
                                {{ cat.name }}
                            </option>
                        </select>
                        <p class="mt-2 text-xs text-gray-500 italic">Select a parent if this is a subcategory.</p>
                    </div>
                </div>

                <!-- Status Select -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Settings</h3>
                    <div>
                        <label class="text-sm font-medium text-gray-700">Display Status</label>
                        <div class="mt-2 flex items-center justify-between">
                            <span class="text-sm text-gray-600">Active</span>
                            <Switch
                                v-model="statusActive"
                                :class="statusActive ? 'bg-indigo-600' : 'bg-gray-200'"
                                class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                            >
                                <span
                                    :class="statusActive ? 'translate-x-6' : 'translate-x-1'"
                                    class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform"
                                />
                            </Switch>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, reactive, computed, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import api from '../axios';
import { Switch } from '@headlessui/vue';
import swal from '../utils/swal';
import ImageUploader from '../components/ImageUploader.vue';
import { debounce } from 'lodash';

const route = useRoute();
const router = useRouter();

const isEditing = computed(() => !!route.params.id);
const submitting = ref(false);
const statusActive = ref(true);
const errors = reactive({});
const availableCategories = ref([]);

const form = reactive({
    name: '',
    slug: '',
    description: '',
    parent_id: null,
    image: '',
    status: 'active'
});

watch(statusActive, (val) => {
    form.status = val ? 'active' : 'inactive';
});

// Sidebar uploader toggle
const showUploader = ref(false);

const onImageError = (event) => {
    event.target.src = 'data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 24 24%22 fill=%22%23d1d5db%22%3E%3Cpath d=%22M21 19V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z%22/%3E%3C/svg%3E';
};

const confirmRemoveImage = async () => {
    const result = await swal.confirm('Are you sure you want to remove the image?', 'Remove Image');
    if (!result.isConfirmed) return;
    form.image = '';
};

const generateSlug = () => {
    if (!isEditing.value || !form.slug) {
        form.slug = form.name
            .toLowerCase()
            .replace(/[^\w ]+/g, '')
            .replace(/ +/g, '-');
    }
};

const fetchAllCategories = async () => {
    try {
        const response = await api.get('/categories?per_page=100');
        const data = response.data.data;
        availableCategories.value = data.data || data;
    } catch (error) {
        console.error('Error fetching parent categories:', error);
    }
};

const fetchCategory = async () => {
    if (!isEditing.value) return;
    
    try {
        const response = await api.get(`/categories/${route.params.id}`);
        const category = response.data.data;
        
        form.name = category.name;
        form.slug = category.slug;
        form.description = category.description;
        form.parent_id = category.parent_id;
        form.image = category.image || '';
        form.status = category.status;
        
        statusActive.value = category.status === 'active';
    } catch (error) {
        console.error('Error fetching category:', error);
        swal.error('Failed to load category details.');
    }
};

const submit = async () => {
    errors.name = !form.name ? 'Name is required' : '';
    errors.slug = !form.slug ? 'Slug is required' : '';
    if (errors.name || errors.slug) return;

    submitting.value = true;
    try {
        if (isEditing.value) {
            await api.put(`/categories/${route.params.id}`, form);
        } else {
            await api.post('/categories', form);
        }
        
        router.push({ name: 'Categories' });
    } catch (error) {
        console.error('Error saving category:', error);
        if (error.response?.data?.errors) {
            Object.assign(errors, error.response.data.errors);
        } else {
            swal.error('Failed to save category.');
        }
    } finally {
        submitting.value = false;
    }
};

onMounted(() => {
    fetchAllCategories();
    fetchCategory();
});
</script>
