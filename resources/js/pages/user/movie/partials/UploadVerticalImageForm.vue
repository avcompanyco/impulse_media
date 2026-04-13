<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { Form, router } from '@inertiajs/vue3';
import UploadVerticalImageMovieController from '@/actions/App/Http/Controllers/Movie/UploadVerticalImageMovieController';
import DeleteVerticalImageMovieController from '@/actions/App/Http/Controllers/Movie/DeleteVerticalImageMovieController';
import ErrorLabel from '@/components/form/ErrorLabel.vue';

const movie = defineModel<any>();

const isDisable = defineModel<boolean>('disable', { default: false });

const form = ref(null);
const isDragOver = ref(false);
const previewImage = ref<string | null>(null);
const isUploading = ref(false);

const isDisableUpload = computed(() => {
    return isDisable.value || isUploading.value;
});

const hasImage = computed(() => {
    return movie.value.vertical_image || previewImage.value;
});

const imageUrl = computed(() => {
    return previewImage.value || movie.value.vertical_image_url;
});

// Watch isUploading to update isDisable
watch(isUploading, (newValue) => {
    isDisable.value = newValue;
});

const handleImageChange = (event: Event) => {
    if (isDisableUpload.value) return;
    
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0];
    if (file) {
        processImage(file);
    }
};

const handleDrop = (event: DragEvent) => {
    event.preventDefault();
    isDragOver.value = false;
    
    if (isDisableUpload.value) return;
    
    const files = event.dataTransfer?.files;
    if (files && files.length > 0) {
        // set the file to the input
        const input = document.getElementById('movieImageVertical') as HTMLInputElement;
        if (input) {
            input.files = files;
        }
        // trigger the change event
        const changeEvent = new Event('change');
        input.dispatchEvent(changeEvent);
    }
};

const handleDragOver = (event: DragEvent) => {
    event.preventDefault();
    if (!isDisableUpload.value) {
        isDragOver.value = true;
    }
};

const handleDragLeave = (event: DragEvent) => {
    event.preventDefault();
    isDragOver.value = false;
};

const processImage = (file: File) => {
    if (isDisableUpload.value) return;
    
    isUploading.value = true;
    
    const reader = new FileReader();
    reader.onload = (e) => {
        previewImage.value = e.target?.result as string;
    };
    reader.readAsDataURL(file);

    // Simulate form submit
    if (form.value) {
        // @ts-ignore
        form.value.submit();
    }
};

function removeImage () {
    if (isDisableUpload.value) return;
    
    router.delete(DeleteVerticalImageMovieController.url(movie.value), {
        preserveScroll: true,
        onSuccess: () => {
            previewImage.value = null;
            const fileInput = document.getElementById('movieImageVertical') as HTMLInputElement;
            if (fileInput) {
                fileInput.value = '';
            }
        }
    });
}

const triggerFileInput = () => {
    if (isDisableUpload.value) return;
    
    const fileInput = document.getElementById('movieImageVertical') as HTMLInputElement;
    fileInput?.click();
};

function successForm() {
    if (form.value) {
        // @ts-ignore
        form.value.reset();
        // Keep previewImage as visual fallback until server URL loads
        // previewImage will be overridden by server URL via imageUrl computed
    }
    isUploading.value = false;
}


</script>
<template>
    <Form ref="form" v-bind="UploadVerticalImageMovieController.form(movie)" :reset-on-success="['vertical_image']"
        v-slot="{ errors, processing }" @success="successForm" :options="{ preserveScroll: true }">
        <div class="form-section">
            <label class="form-label">Promotional Image (Vertical)</label>
            <div 
                class="upload-box"
                :class="{ 
                    'drag-over': isDragOver, 
                    'has-image': hasImage,
                    'uploading': isUploading,
                    'disabled': isDisableUpload
                }"
                @click="triggerFileInput"
                @drop="handleDrop"
                @dragover="handleDragOver"
                @dragleave="handleDragLeave"
            >
                <div v-if="!hasImage && !isUploading" class="upload-content">
                    <i class="fa-solid fa-image"></i>
                    <p>Select the vertical poster</p>
                    <small>or drag and drop an image here</small>
                </div>

                <div v-else-if="isUploading" class="upload-progress">
                    <i class="fa-solid fa-upload"></i>
                    <p>Uploading image...</p>
                </div>
                
                <div v-else class="image-preview-container">
                    <img :src="imageUrl" alt="Vertical Image" class="image-preview"/>
                    <div class="image-overlay">
                        <div class="image-actions">
                            <button 
                                type="button" 
                                @click.stop="triggerFileInput" 
                                class="action-btn edit-btn"
                                :disabled="isDisableUpload"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" 
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" 
                                    stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-edit"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path></svg>
                            </button>
                            <button 
                                type="button" 
                                @click.stop="removeImage" 
                                class="action-btn delete-btn"
                                :disabled="isDisableUpload"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-trash-2"><path d="M3 6h18"></path><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><path d="M10 11v6"></path><path d="M14 11v6"></path></svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <input 
                type="file" 
                name="vertical_image" 
                id="movieImageVertical" 
                accept="image/*" 
                @change="handleImageChange"
                :disabled="isDisableUpload"
            >
            <ErrorLabel :error="errors.vertical_image" />
        </div>
    </Form>
</template>

<style scoped>
.upload-form {
    display: none;
}

.upload-form.active {
    display: block;
}

.form-section {
    margin-bottom: 1.5rem;
}

.form-label {
    font-size: 1.1rem;
    font-weight: 500;
    margin-bottom: 0.75rem;
    display: block;
}

.form-control {
    background: var(--input-bg);
    border: none;
    color: var(--text-dark);
    padding: 0.9rem;
    border-radius: 12px;
    font-size: 1rem;
    width: 100%;
    box-sizing: border-box;
}

.upload-box {
    border: 2px dashed #555;
    background-color: var(--card-bg);
    border-radius: 15px;
    padding: 1.5rem;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
    position: relative;
    min-height: 120px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.upload-box:hover {
    border-color: var(--primary-color);
}

.upload-box.drag-over {
    border-color: var(--primary-color);
    background-color: rgba(var(--primary-color-rgb), 0.1);
}

.upload-box.uploading {
    cursor: not-allowed;
    border-color: var(--primary-color);
}

.upload-box.disabled {
    cursor: not-allowed;
    opacity: 0.6;
    pointer-events: none;
}

.upload-box.has-image {
    padding: 0;
    border: none;
    background: transparent;
}

.upload-content i {
    font-size: 2rem;
    color: #888;
    margin-bottom: 0.75rem;
    display: block;
}

.upload-content p {
    color: var(--text-light);
    font-size: 1rem;
    font-weight: 500;
    margin: 0 0 0.5rem 0;
}

.upload-content small {
    color: #888;
    font-size: 0.85rem;
}

.upload-progress {
    width: 100%;
}

.upload-progress i {
    font-size: 2rem;
    color: var(--primary-color);
    margin-bottom: 0.75rem;
    animation: pulse 2s infinite;
}

.upload-progress p {
    color: var(--text-light);
    font-size: 1rem;
    font-weight: 500;
    margin: 0;
}

.image-preview-container {
    position: relative;
    width: 100%;
    border-radius: 15px;
    overflow: hidden;
}

.image-preview {
    width: 100%;
    height: auto;
    max-height: 200px;
    object-fit: cover;
    display: block;
    border-radius: 15px;
}

.image-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s ease;
    border-radius: 15px;
    opacity: 0.5;
}

.image-preview-container:hover .image-overlay {
    opacity: 1;
}

.image-actions {
    display: flex;
    gap: 0.75rem;
}

.action-btn {
    background: rgba(255, 255, 255, 0.9);
    border: none;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 0.9rem;
}

.action-btn:hover:not(:disabled) {
    /* background: white; */
    transform: scale(1.1);
}

.action-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.edit-btn, .delete-btn {
    color: var(--text-light);
}

.edit-btn {
    background: var(--secondary-color);
}

.delete-btn {
    background: var(--danger-color);
}

input[type="file"] {
    display: none;
}

@keyframes pulse {
    0% {
        opacity: 1;
    }
    50% {
        opacity: 0.5;
    }
    100% {
        opacity: 1;
    }
}
</style>