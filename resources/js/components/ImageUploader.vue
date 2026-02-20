<template>
  <div class="space-y-3">
    <div v-if="modelValue" class="relative">
      <img :src="modelValue" :alt="alt" class="w-full rounded-lg border border-gray-300 object-cover h-48" @error="onImageError" />
      <button @click="clear" type="button" class="absolute top-2 right-2 bg-red-500 text-white rounded-full p-2 hover:bg-red-600 transition-colors">
        <i class="fas fa-trash text-sm"></i>
      </button>
    </div>

    <div class="flex-1">
        <input
          v-model="localUrl"
          type="text"
          placeholder="Paste image URL"
          @input="onUrlInput"
          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
        />
    </div>
    <!-- Drag & drop area + file input -->
    <div
      class="flex gap-2"
      @dragover.prevent="dragOver = true"
      @dragleave.prevent="dragOver = false"
      @drop.prevent="onDrop"
    >
      <div :class="['flex-1 p-2 rounded-lg transition', dragOver ? 'border-2 border-dashed border-indigo-400 bg-indigo-50' : '']">
        <label class="block text-sm text-gray-600 mb-2">Drop image here or choose a file</label>
        <input
          type="file"
          :accept="accept"
          @change="onFileChange"
          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-white"
        />
      </div>
      
    </div>

    <p class="text-xs text-gray-500">Upload an image or paste a URL (JPEG, PNG, GIF, max {{ maxSizeMB }}MB)</p>

    <!-- Crop modal -->
    <div v-if="showCropper" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
      <div class="bg-white rounded-lg p-4 w-[90%] max-w-3xl">
        <div class="flex justify-between items-center mb-3">
          <h3 class="text-lg font-semibold">Crop image</h3>
          <button @click="cancelCrop" class="text-sm text-gray-500">Cancelar</button>
        </div>
        <div class="overflow-hidden bg-gray-100 rounded">
          <img ref="cropperImage" :src="tempImage" alt="To crop" class="w-full max-h-[60vh] object-contain" />
        </div>
        <div class="mt-3 flex justify-end gap-2">
          <button @click="cancelCrop" class="px-4 py-2 border rounded">Cancelar</button>
          <button @click="cropAndUpload" class="px-4 py-2 bg-indigo-600 text-white rounded">Crop & upload</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, computed, nextTick } from 'vue';
import Cropper from 'cropperjs';
import 'cropperjs/dist/cropper.css';
import api from '../axios';

const props = defineProps({
  modelValue: { type: String, default: '' },
  folder: { type: String, required: true },
  accept: { type: String, default: 'image/*' },
  maxSize: { type: Number, default: 2 * 1024 * 1024 },
  alt: { type: String, default: '' },
  // new props for cropping behavior
  crop: { type: Boolean, default: true },
  aspectRatio: { type: Number, default: 1 }
});
const emit = defineEmits(['update:modelValue']);

const localUrl = ref(props.modelValue || '');
watch(() => props.modelValue, (v) => (localUrl.value = v || ''));

const maxSizeMB = computed(() => (props.maxSize / 1024 / 1024).toFixed(1));

// drag / crop state
const dragOver = ref(false);
const showCropper = ref(false);
const tempImage = ref(null); // data URL for cropper
const cropperInstance = ref(null);
const cropperImage = ref(null);

// common file handling: used by file input and drop
const handleFile = async (file) => {
  if (!file) return;
  if (!file.type.startsWith('image/')) {
    alert('Please select a valid image file');
    return;
  }
  if (file.size > props.maxSize) {
    alert(`Image must be less than ${maxSizeMB.value} MB`);
    return;
  }

  if (props.crop) {
    // show cropper modal
    tempImage.value = await fileToDataUrl(file);
    showCropper.value = true;
    await nextTick();
    initCropper();
  } else {
    await uploadFile(file);
  }
};

const onFileChange = async (event) => {
  const file = event.target.files?.[0];
  await handleFile(file);
  event.target.value = '';
};

const onDrop = async (event) => {
  dragOver.value = false;
  const file = event.dataTransfer?.files?.[0];
  await handleFile(file);
};

const fileToDataUrl = (file) => new Promise((res, rej) => {
  const reader = new FileReader();
  reader.onload = () => res(reader.result);
  reader.onerror = rej;
  reader.readAsDataURL(file);
});

const initCropper = () => {
  if (cropperInstance.value) {
    cropperInstance.value.destroy();
    cropperInstance.value = null;
  }
  const img = cropperImage.value;
  if (!img) return;
  cropperInstance.value = new Cropper(img, {
    aspectRatio: props.aspectRatio,
    viewMode: 1,
    background: false,
    autoCropArea: 1,
    responsive: true,
    movable: true,
    zoomable: true,
    scalable: false,
    rotatable: false
  });
};

const cropAndUpload = () => {
  if (!cropperInstance.value) return;
  const canvas = cropperInstance.value.getCroppedCanvas({
    width: 800,
    height: 800,
    imageSmoothingQuality: 'high'
  });
  canvas.toBlob(async (blob) => {
    if (!blob) {
      alert('Failed to crop image');
      return;
    }
    await uploadBlob(blob);
    // cleanup
    destroyCropper();
    showCropper.value = false;
    tempImage.value = null;
  }, 'image/jpeg', 0.92);
};

const cancelCrop = () => {
  destroyCropper();
  showCropper.value = false;
  tempImage.value = null;
};

const destroyCropper = () => {
  if (cropperInstance.value) {
    cropperInstance.value.destroy();
    cropperInstance.value = null;
  }
};

const uploadFile = async (file) => {
  const formData = new FormData();
  formData.append('file', file);
  formData.append('folder', props.folder);
  try {
    const res = await api.post('/upload', formData, { headers: { 'Content-Type': 'multipart/form-data' } });
    if (res.data.success) emit('update:modelValue', res.data.url);
  } catch (err) {
    console.error('Upload error', err);
    alert('Failed to upload image');
  }
};

const uploadBlob = async (blob) => {
  // give a filename for the blob
  const file = new File([blob], `image_${Date.now()}.jpg`, { type: blob.type || 'image/jpeg' });
  await uploadFile(file);
};

const onUrlInput = () => emit('update:modelValue', localUrl.value);
const clear = () => {
  emit('update:modelValue', '');
  localUrl.value = '';
};

const onImageError = (event) => {
  event.target.src = 'data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 24 24%22 fill=%22%23d1d5db%22%3E%3Cpath d=%22M21 19V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z%22/%3E%3C/svg%3E';
};
</script>
