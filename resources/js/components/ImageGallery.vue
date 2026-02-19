<template>
    <div class="space-y-3">
        <!-- Image Gallery Grid -->
        <div v-if="images && images.length > 0" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-2 gap-3">
            <div 
                v-for="(image, index) in images" 
                :key="index"
                draggable="!readonly"
                @dragstart="onDragStart($event, index)"
                @dragover.prevent="onDragOver($event, index)"
                @drop.prevent="onDrop($event, index)"
                @dragleave="onDragLeave"
                class="relative group rounded-lg overflow-hidden border-2 transition-all cursor-move"
                :class="{ 
                    'border-indigo-500 shadow-lg': image.is_default,
                    'border-gray-200 hover:border-gray-300': !image.is_default,
                    'opacity-50 bg-indigo-50 border-dashed border-indigo-400': dragOverIndex === index
                }"
            >
                <!-- Image -->
                <img 
                    :src="image.url" 
                    :alt="`Image ${index + 1}`"
                    class="h-24 w-full object-cover transition-opacity"
                    @error="onImageError"
                >

                <!-- Toolbar al pie de la imagen -->
                <div v-if="!readonly" class="absolute bottom-0 left-0 w-full bg-gradient-to-t from-black to-transparent flex justify-around items-center px-1 py-2 gap-1 z-10">
                    <!-- Set Default -->
                    <button 
                        v-if="!image.is_default"
                        @click="emit('set-default', index)"
                        class="text-white hover:text-slate-200 transition-colors cursor-pointer"
                        title="Set as default"
                    >
                        <Icon icon="material-symbols:star" class="w-5 h-5" />
                    </button>
                    <!-- Zoom -->
                    <button 
                        @click="emit('view', image.url)"
                        class="text-white hover:text-slate-200 transition-colors cursor-pointer"
                        title="Zoom">
                        <Icon icon="material-symbols:zoom-in" class="w-5 h-5" />
                    </button>
                    <!-- Crop/Edit -->
                    <button 
                        @click="emit('edit', index)"
                        class="text-white hover:text-slate-200 transition-colors cursor-pointer"
                        title="Crop/Edit"
                    >
                        <Icon icon="material-symbols:crop" class="w-5 h-5" />
                    </button>
                    <!-- Delete -->
                    <button 
                        @click="emit('delete', index)"
                        class="text-white hover:text-slate-200 transition-colors cursor-pointer"
                        title="Delete"
                    >
                        <Icon icon="material-symbols:delete" class="w-5 h-5" />
                    </button>
                </div>


                

            </div>
        </div>

        <!-- Empty State -->
        <div v-else class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center text-gray-500">
            <Icon icon="material-symbols:image" class="w-12 h-12 mx-auto text-gray-300 mb-2" />
            <p class="text-sm">{{ emptyMessage }}</p>
        </div>

        <!-- Image Preview Modal -->
        <Teleport to="body" v-if="previewImage">
            <div class="fixed inset-0 bg-black bg-opacity-70 flex items-center justify-center z-50 p-4" @click="previewImage = null">
                <div class="max-w-4xl max-h-96 bg-white rounded-lg overflow-hidden" @click.stop>
                    <img :src="previewImage" :alt="previewMessage" class="w-full h-full object-contain">
                    <button 
                        @click="previewImage = null"
                        class="absolute top-2 right-2 bg-gray-800 text-white rounded-full p-2 hover:bg-gray-900"
                    >
                        <Icon icon="material-symbols:close" class="w-5 h-5" />
                    </button>
                </div>
            </div>
        </Teleport>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { Icon } from '@iconify/vue';

defineProps({
    images: {
        type: Array,
        required: true,
        default: () => []
    },
    readonly: {
        type: Boolean,
        default: false
    },
    emptyMessage: {
        type: String,
        default: 'No images available'
    }
});

const emit = defineEmits(['set-default', 'delete', 'view', 'edit', 'reorder']);

const previewImage = ref(null);
const dragOverIndex = ref(null);
const draggedIndex = ref(null);

const onDragStart = (event, index) => {
    draggedIndex.value = index;
    event.dataTransfer.effectAllowed = 'move';
};

const onDragOver = (event, index) => {
    event.preventDefault();
    dragOverIndex.value = index;
    event.dataTransfer.dropEffect = 'move';
};

const onDragLeave = () => {
    dragOverIndex.value = null;
};

const onDrop = (event, dropIndex) => {
    event.preventDefault();
    dragOverIndex.value = null;

    if (draggedIndex.value !== null && draggedIndex.value !== dropIndex) {
        emit('reorder', {
            fromIndex: draggedIndex.value,
            toIndex: dropIndex
        });
    }
    draggedIndex.value = null;
};

const onImageError = (event) => {
    event.target.src = 'data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 24 24%22 fill=%22%23d1d5db%22%3E%3Cpath d=%22M21 19V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z%22/%3E%3C/svg%3E';
};
</script>

<style scoped>
img::-webkit-image-set {
    @apply object-cover;
}
</style>
