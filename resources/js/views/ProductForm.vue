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

            <!-- Header row -->
            <div class="flex justify-between items-center">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Variaciones del Producto</h3>
                    <p class="text-sm text-gray-500 mt-0.5">Agrega variantes con combinaciones de atributos (Color, Talla, etc.)</p>
                </div>
                <button
                    @click="openNewVariantForm"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors text-sm font-medium"
                >
                    <Icon icon="mdi:plus" class="w-4 h-4" />
                    Nueva Variante
                </button>
            </div>

            <!-- Variant Form Panel -->
            <transition
                enter-active-class="transition duration-200 ease-out"
                enter-from-class="opacity-0 -translate-y-2"
                enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition duration-150 ease-in"
                leave-from-class="opacity-100 translate-y-0"
                leave-to-class="opacity-0 -translate-y-2"
            >
                <div v-if="showVariantForm" class="bg-white rounded-xl shadow-sm border border-indigo-200 p-6">
                    <h4 class="text-base font-semibold text-gray-900 mb-5 flex items-center gap-2">
                        <Icon icon="mdi:tag-multiple" class="w-5 h-5 text-indigo-600" />
                        {{ editingVariantId ? 'Editar Variante' : 'Nueva Variante' }}
                    </h4>

                    <div class="space-y-5">
                        <!-- Attributes -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-3">Atributos</label>
                            <div v-if="loadingAttributes" class="flex items-center gap-2 text-sm text-gray-500">
                                <span class="w-4 h-4 border-2 border-indigo-300 border-t-indigo-600 rounded-full animate-spin"></span>
                                Cargando atributos...
                            </div>
                            <div v-else-if="allAttributes.length === 0" class="text-sm text-gray-500 italic">
                                No hay atributos disponibles. Crea atributos desde el módulo de Atributos.
                            </div>
                            <div v-else class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                                <div
                                    v-for="attr in allAttributes"
                                    :key="attr.id"
                                    class="border border-gray-200 rounded-lg p-3 bg-gray-50"
                                >
                                    <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">
                                        {{ attr.name }}
                                    </label>
                                    <select
                                        v-model="variantForm.attribute_values[attr.id]"
                                        class="w-full px-3 py-1.5 text-sm border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-white"
                                    >
                                        <option :value="null">— Sin seleccionar —</option>
                                        <option
                                            v-for="val in attr.values"
                                            :key="val.id"
                                            :value="val.id"
                                        >
                                            {{ val.value }}
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Divider -->
                        <div class="border-t border-gray-200"></div>

                        <!-- SKU & Name -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    SKU <span class="text-red-500">*</span>
                                </label>
                                <input
                                    v-model="variantForm.sku"
                                    type="text"
                                    placeholder="AUTO-GENERADO"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                                    :class="{ 'border-red-300': variantErrors.sku }"
                                >
                                <p v-if="variantErrors.sku" class="mt-1 text-xs text-red-600">{{ variantErrors.sku }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Nombre <span class="text-red-500">*</span>
                                </label>
                                <input
                                    v-model="variantForm.name"
                                    type="text"
                                    placeholder="AUTO-GENERADO"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                                    :class="{ 'border-red-300': variantErrors.name }"
                                >
                                <p v-if="variantErrors.name" class="mt-1 text-xs text-red-600">{{ variantErrors.name }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Código de Barras</label>
                                <input
                                    v-model="variantForm.barcode"
                                    type="text"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                                >
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Peso (kg)</label>
                                <input
                                    v-model.number="variantForm.weight"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                                >
                            </div>
                        </div>

                        <!-- Active toggle -->
                        <div class="flex items-center gap-3">
                            <SwitchGroup as="div" class="flex items-center">
                                <Switch
                                    v-model="variantForm.is_active"
                                    :class="[variantForm.is_active ? 'bg-indigo-600' : 'bg-gray-200', 'relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2']"
                                >
                                    <span aria-hidden="true" :class="[variantForm.is_active ? 'translate-x-5' : 'translate-x-0', 'pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out']" />
                                </Switch>
                                <SwitchLabel as="span" class="ml-3 cursor-pointer">
                                    <span class="text-sm font-medium text-gray-700">{{ variantForm.is_active ? 'Activa' : 'Inactiva' }}</span>
                                </SwitchLabel>
                            </SwitchGroup>
                        </div>

                        <!-- Actions -->
                        <div class="flex justify-end gap-3 pt-2">
                            <button
                                type="button"
                                @click="cancelVariantForm"
                                class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 text-sm transition-colors"
                            >
                                Cancelar
                            </button>
                            <button
                                type="button"
                                @click="saveVariant"
                                :disabled="savingVariant"
                                class="inline-flex items-center gap-2 px-5 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:opacity-50 text-sm font-medium transition-colors"
                            >
                                <span v-if="savingVariant" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                                {{ savingVariant ? 'Guardando...' : (editingVariantId ? 'Actualizar' : 'Guardar Variante') }}
                            </button>
                        </div>
                    </div>
                </div>
            </transition>

            <!-- Variants Table -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div v-if="loadingVariants" class="flex items-center justify-center py-12 text-gray-500">
                    <span class="w-6 h-6 border-2 border-indigo-300 border-t-indigo-600 rounded-full animate-spin mr-3"></span>
                    Cargando variantes...
                </div>

                <div v-else-if="productVariants.length === 0" class="flex flex-col items-center justify-center py-16 text-gray-400">
                    <Icon icon="mdi:tag-multiple-outline" class="w-14 h-14 mb-3 opacity-40" />
                    <p class="text-base font-medium">Sin variantes todavía</p>
                    <p class="text-sm mt-1">Haz clic en "Nueva Variante" para agregar la primera.</p>
                </div>

                <table v-else class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Atributos</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">SKU</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Nombre</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider text-indigo-600">Stock</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Estado</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        <tr
                            v-for="variant in productVariants"
                            :key="variant.id"
                            class="hover:bg-gray-50 transition-colors"
                        >
                            <!-- Attribute Values Badges -->
                            <td class="px-6 py-4">
                                <div class="flex flex-wrap gap-1.5">
                                    <template v-if="variant.attribute_values && variant.attribute_values.length > 0">
                                        <span
                                            v-for="av in variant.attribute_values"
                                            :key="av.id"
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800"
                                        >
                                            <span class="text-indigo-500 mr-1">{{ av.attribute ? av.attribute.name : '' }}:</span>
                                            {{ av.value }}
                                        </span>
                                    </template>
                                    <span v-else class="text-xs text-gray-400 italic">Sin atributos</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900 font-mono">{{ variant.sku }}</td>
                            <td class="px-6 py-4 text-sm text-gray-700">{{ variant.name }}</td>
                            <td class="px-6 py-4 text-right">
                                <span class="text-sm font-bold" :class="variant.total_stock > 0 ? 'text-gray-900' : 'text-red-500'">
                                    {{ variant.total_stock || 0 }}
                                </span>
                                <span class="text-[10px] text-gray-400 uppercase ml-1">{{ form.unit || 'Pcs' }}</span>
                            </td>
                            <!-- Status Badge -->
                            <td class="px-6 py-4">
                                <span
                                    :class="variant.is_active
                                        ? 'bg-green-100 text-green-700'
                                        : 'bg-gray-100 text-gray-500'"
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                >
                                    <span
                                        :class="variant.is_active ? 'bg-green-400' : 'bg-gray-400'"
                                        class="w-1.5 h-1.5 rounded-full mr-1.5"
                                    ></span>
                                    {{ variant.is_active ? 'Activa' : 'Inactiva' }}
                                </span>
                            </td>
                            <!-- Actions -->
                            <td class="px-6 py-4">
                                <div class="flex justify-end items-center gap-2">
                                    <router-link 
                                        :to="{ name: 'InventoryEntry', query: { product_id: product_id, product_variant_id: variant.id } }"
                                        title="Recibir Stock"
                                        class="p-1.5 text-emerald-600 hover:bg-emerald-50 rounded-md transition-colors"
                                    >
                                        <Icon icon="mdi:package-variant-plus" class="w-4 h-4" />
                                    </router-link>
                                    <button
                                        type="button"
                                        @click="editVariant(variant)"
                                        title="Editar"
                                        class="p-1.5 text-gray-500 hover:text-indigo-600 hover:bg-indigo-50 rounded-md transition-colors"
                                    >
                                        <Icon icon="mdi:pencil" class="w-4 h-4" />
                                    </button>
                                    <button
                                        type="button"
                                        @click="toggleVariantStatus(variant)"
                                        :title="variant.is_active ? 'Desactivar' : 'Activar'"
                                        class="p-1.5 rounded-md transition-colors"
                                        :class="variant.is_active
                                            ? 'text-yellow-600 hover:bg-yellow-50'
                                            : 'text-green-600 hover:bg-green-50'"
                                    >
                                        <Icon :icon="variant.is_active ? 'mdi:eye-off' : 'mdi:eye'" class="w-4 h-4" />
                                    </button>
                                    <button
                                        type="button"
                                        @click="deleteVariant(variant.id)"
                                        title="Eliminar"
                                        class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-md transition-colors"
                                    >
                                        <Icon icon="mdi:trash-can-outline" class="w-4 h-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
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

// ============================================================
// VARIATIONS
// ============================================================

const allAttributes = ref([]);
const productVariants = ref([]);
const loadingAttributes = ref(false);
const loadingVariants = ref(false);
const showVariantForm = ref(false);
const editingVariantId = ref(null);
const savingVariant = ref(false);
const variantErrors = ref({});

const variantForm = reactive({
    sku: '',
    name: '',
    barcode: '',
    weight: null,
    is_active: true,
    attribute_values: {}, // { attrId: valueId | null }
});

// Auto-generate SKU and Name when attribute values change
watch(
    () => ({ ...variantForm.attribute_values }),
    () => {
        const selectedValues = Object.entries(variantForm.attribute_values)
            .filter(([, valueId]) => valueId !== null)
            .map(([attrId, valueId]) => {
                const attr = allAttributes.value.find(a => a.id == attrId);
                const val = attr?.values?.find(v => v.id == valueId);
                return val?.value ?? '';
            })
            .filter(Boolean);

        if (selectedValues.length > 0) {
            const baseSku = form.sku || 'SKU';
            variantForm.sku = (baseSku + '-' + selectedValues.join('-')).toUpperCase();
            variantForm.name = (form.name || '') + ' - ' + selectedValues.join(' / ');
        }
    },
    { deep: true }
);

const fetchAllAttributes = async () => {
    loadingAttributes.value = true;
    try {
        const response = await api.get('/attributes/all');
        allAttributes.value = response.data.data || [];
        // Initialize attribute_values with null for each attribute
        resetAttributeValues();
    } catch (error) {
        console.error('Error fetching attributes:', error);
    } finally {
        loadingAttributes.value = false;
    }
};

const resetAttributeValues = () => {
    const values = {};
    allAttributes.value.forEach(attr => {
        values[attr.id] = null;
    });
    Object.assign(variantForm.attribute_values, values);
};

const fetchProductVariants = async () => {
    if (!isEditing.value) return;
    loadingVariants.value = true;
    try {
        const response = await api.get(`/products/${route.params.id}/variants`);
        productVariants.value = response.data.data || [];
    } catch (error) {
        console.error('Error fetching variants:', error);
    } finally {
        loadingVariants.value = false;
    }
};

const openNewVariantForm = () => {
    editingVariantId.value = null;
    variantErrors.value = {};
    variantForm.sku = '';
    variantForm.name = '';
    variantForm.barcode = '';
    variantForm.weight = null;
    variantForm.is_active = true;
    resetAttributeValues();
    showVariantForm.value = true;
    // Scroll to the form
    setTimeout(() => {
        document.querySelector('[data-variant-form]')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }, 100);
};

const cancelVariantForm = () => {
    showVariantForm.value = false;
    editingVariantId.value = null;
    variantErrors.value = {};
};

const editVariant = (variant) => {
    editingVariantId.value = variant.id;
    variantErrors.value = {};
    variantForm.sku = variant.sku;
    variantForm.name = variant.name;
    variantForm.barcode = variant.barcode || '';
    variantForm.weight = variant.weight || null;
    variantForm.is_active = variant.is_active;

    // Reset then fill attribute values
    resetAttributeValues();
    if (variant.attribute_values) {
        variant.attribute_values.forEach(av => {
            if (av.attribute) {
                variantForm.attribute_values[av.attribute.id] = av.id;
            }
        });
    }
    showVariantForm.value = true;
};

const saveVariant = async () => {
    savingVariant.value = true;
    variantErrors.value = {};

    // Collect only selected (non-null) attribute value ids
    const selectedAttributeValues = Object.values(variantForm.attribute_values)
        .filter(v => v !== null && v !== undefined);

    const payload = {
        product_id: route.params.id,
        sku: variantForm.sku,
        name: variantForm.name,
        barcode: variantForm.barcode || null,
        weight: variantForm.weight || null,
        is_active: variantForm.is_active,
        attribute_values: selectedAttributeValues,
    };

    try {
        if (editingVariantId.value) {
            await api.put(`/variants/${editingVariantId.value}`, payload);
            swal.success('Variante actualizada correctamente');
        } else {
            await api.post('/variants', payload);
            swal.success('Variante creada correctamente');
        }
        showVariantForm.value = false;
        editingVariantId.value = null;
        await fetchProductVariants();
    } catch (error) {
        if (error.response?.status === 422) {
            const apiErrors = error.response.data.errors || {};
            Object.keys(apiErrors).forEach(key => {
                variantErrors.value[key] = apiErrors[key][0];
            });
            if (error.response.data.message && !Object.keys(apiErrors).length) {
                swal.error(error.response.data.message);
            }
        } else {
            swal.error('Error al guardar la variante');
        }
    } finally {
        savingVariant.value = false;
    }
};

const toggleVariantStatus = async (variant) => {
    try {
        await api.put(`/variants/${variant.id}`, { is_active: !variant.is_active });
        variant.is_active = !variant.is_active;
    } catch (error) {
        swal.error('Error al cambiar el estado de la variante');
    }
};

const deleteVariant = async (id) => {
    const result = await swal.confirm(
        'Esta acción no se puede deshacer.',
        '¿Eliminar variante?'
    );
    if (!result.isConfirmed) return;

    try {
        await api.delete(`/variants/${id}`);
        productVariants.value = productVariants.value.filter(v => v.id !== id);
        swal.success('Variante eliminada');
    } catch (error) {
        swal.error('Error al eliminar la variante');
    }
};

// Load variants when switching to variations tab
watch(activeTab, (newTab) => {
    if (newTab === 'variations' && isEditing.value) {
        if (productVariants.value.length === 0 && !loadingVariants.value) {
            fetchProductVariants();
        }
        if (allAttributes.value.length === 0 && !loadingAttributes.value) {
            fetchAllAttributes();
        }
    }
});

// ============================================================

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
