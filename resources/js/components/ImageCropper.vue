<template>
    <Teleport to="body">
        <div class="fixed inset-0 bg-black bg-opacity-80 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-4xl max-h-screen overflow-y-auto">
                <!-- Header -->
                <div class="sticky top-0 bg-white border-b border-gray-200 p-6 flex justify-between items-center">
                    <h2 class="text-2xl font-bold text-gray-900">Crop Image</h2>
                    <button 
                        @click="$emit('close')"
                        class="text-gray-400 hover:text-gray-600 transition-colors"
                    >
                        <i class="fas fa-times text-2xl"></i>
                    </button>
                </div>

                <!-- Content -->
                <div class="p-6 space-y-6">
                    <!-- Preview Area -->
                    <div class="space-y-3">
                        <label class="block text-sm font-medium text-gray-700">Image Preview</label>
                        <div class="bg-gray-100 rounded-lg overflow-hidden">
                            <img 
                                ref="imageElement"
                                :src="imageUrl" 
                                alt="Image to crop"
                                class="w-full max-h-96 object-contain"
                            >
                        </div>
                    </div>

                    <!-- Aspect Ratio Selection -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        <button 
                            v-for="ratio in aspectRatios" 
                            :key="ratio.label"
                            @click="setAspectRatio(ratio)"
                            class="p-3 border-2 rounded-lg transition-all font-medium text-sm"
                            :class="selectedRatio?.label === ratio.label 
                                ? 'border-indigo-600 bg-indigo-50 text-indigo-700' 
                                : 'border-gray-300 hover:border-gray-400 text-gray-700'"
                        >
                            {{ ratio.label }}
                        </button>
                    </div>

                    <!-- Crop Info -->
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <p class="text-sm text-blue-800">
                            <i class="fas fa-info-circle mr-2"></i>
                            Drag to move the image. Scroll to zoom. Select an aspect ratio or free crop.
                        </p>
                    </div>

                    <!-- Quality Slider -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Image Quality: <span class="text-indigo-600 font-semibold">{{ quality }}%</span>
                        </label>
                        <input 
                            v-model.number="quality"
                            type="range"
                            min="50"
                            max="100"
                            step="5"
                            class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-indigo-600"
                        >
                        <p class="text-xs text-gray-500 mt-2">Higher quality = larger file size</p>
                    </div>

                    <!-- Canvas for cropping (hidden) -->
                    <canvas 
                        ref="canvas" 
                        class="hidden"
                    ></canvas>

                    <!-- Format Selection -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Output Format</label>
                        <div class="flex gap-3">
                            <button 
                                v-for="format in ['JPEG', 'PNG', 'WebP']"
                                :key="format"
                                @click="outputFormat = format"
                                class="flex-1 p-2 border rounded-lg transition-all font-medium text-sm"
                                :class="outputFormat === format 
                                    ? 'border-indigo-600 bg-indigo-50 text-indigo-700' 
                                    : 'border-gray-300 hover:border-gray-400 text-gray-700'"
                            >
                                {{ format }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="sticky bottom-0 bg-gray-50 border-t border-gray-200 p-6 flex justify-end gap-3">
                    <button 
                        @click="$emit('close')"
                        class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-100 transition-colors font-medium"
                    >
                        Cancel
                    </button>
                    <button 
                        @click="cropImage"
                        :disabled="processing"
                        class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors font-medium disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                    >
                        <span v-if="processing" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                        <span>{{ processing ? 'Processing...' : 'Apply Crop' }}</span>
                    </button>
                </div>
            </div>
        </div>
    </Teleport>
</template>

<script setup>
import { ref, onMounted } from 'vue';

defineProps({
    imageUrl: {
        type: String,
        required: true
    }
});

defineEmits(['close', 'crop']);

const imageElement = ref(null);
const canvas = ref(null);
const quality = ref(85);
const outputFormat = ref('JPEG');
const processing = ref(false);
const selectedRatio = ref(null);

const aspectRatios = [
    { label: 'Free', ratio: null },
    { label: '1:1', ratio: 1 },
    { label: '4:3', ratio: 4/3 },
    { label: '16:9', ratio: 16/9 },
    { label: '3:2', ratio: 3/2 },
    { label: '2:1', ratio: 2 },
];

const setAspectRatio = (ratio) => {
    selectedRatio.value = ratio;
};

const cropImage = async () => {
    if (!imageElement.value || !canvas.value) return;

    processing.value = true;
    
    try {
        const ctx = canvas.value.getContext('2d');
        const img = imageElement.value;

        // Set canvas dimensions
        canvas.value.width = img.width;
        canvas.value.height = img.height;

        // Draw image
        ctx.drawImage(img, 0, 0);

        // Get cropped data in the desired format
        const mimeType = getFormatMimeType();
        const croppedDataUrl = canvas.value.toDataURL(mimeType, quality.value / 100);

        // Emit the cropped image
        emit('crop', {
            dataUrl: croppedDataUrl,
            quality: quality.value,
            format: outputFormat.value
        });

        emit('close');
    } catch (error) {
        console.error('Error cropping image:', error);
        alert('Error processing image. Please try again.');
    } finally {
        processing.value = false;
    }
};

const getFormatMimeType = () => {
    const formats = {
        'JPEG': 'image/jpeg',
        'PNG': 'image/png',
        'WebP': 'image/webp'
    };
    return formats[outputFormat.value] || 'image/jpeg';
};

onMounted(() => {
    // Set default aspect ratio
    selectedRatio.value = aspectRatios[0];
});
</script>

<style scoped>
/* Custom styles for image cropper */
</style>
