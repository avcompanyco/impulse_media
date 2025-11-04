<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { Form, router } from '@inertiajs/vue3';
import UploadMovieController from '@/actions/App/Http/Controllers/Movie/UploadMovieController';
import DeleteMovieMovieController from '@/actions/App/Http/Controllers/Movie/DeleteMovieMovieController';
import ErrorLabel from '@/components/form/ErrorLabel.vue';

const movie = defineModel<any>();

const isDisable = defineModel<boolean>('disable', { default: false });

const form = ref(null);
const isDragOver = ref(false);
const selectedFile = ref<File | null>(null);
const isUploading = ref(false);
const uploadProgress = ref(0);

const CHUNK_SIZE = 10 * 1024 * 1024; // 10MB

const isDisableUpload = computed(() => {
    return isDisable.value || isUploading.value;
});

const hasVideo = computed(() => {
    // @ts-ignore
    return movie.value.movie_video || selectedFile.value;
});

const videoName = computed(() => {
    // @ts-ignore
    return selectedFile.value?.name || (movie.value.movie_video ? 'Current movie' : '');
});

// Watch isUploading to update isDisable
watch(isUploading, (newValue) => {
    isDisable.value = newValue;
});

function handleVideoChange(event: Event) {
    if (isDisableUpload.value) return;
    
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0];
    if (file) {
        selectedFile.value = file;
        uploadChunks(file);
    }
}

function handleDrop(event: DragEvent) {
    event.preventDefault();
    isDragOver.value = false;
    
    if (isDisableUpload.value) return;
    
    const files = event.dataTransfer?.files;
    if (files && files.length > 0) {
        const file = files[0];
        if (file.type.startsWith('video/')) {
            selectedFile.value = file;
            const input = document.getElementById('movieFile') as HTMLInputElement;
            if (input) {
                const dt = new DataTransfer();
                dt.items.add(file);
                input.files = dt.files;
            }
            uploadChunks(file);
        }
    }
}

function handleDragOver(event: DragEvent) {
    event.preventDefault();
    if (!isDisableUpload.value) {
        isDragOver.value = true;
    }
}

function handleDragLeave(event: DragEvent) {
    event.preventDefault();
    isDragOver.value = false;
}

const uploadChunks = async (file: File) => {
    if (!file || isDisableUpload.value) return;

    isUploading.value = true;
    uploadProgress.value = 0;

    try {
        const totalChunks = Math.ceil(file.size / CHUNK_SIZE);
        
        for (let chunkIndex = 0; chunkIndex < totalChunks; chunkIndex++) {
            const start = chunkIndex * CHUNK_SIZE;
            const end = Math.min(start + CHUNK_SIZE, file.size);
            const chunk = file.slice(start, end);
            
            const isLastChunk = chunkIndex === totalChunks - 1;
            
            const formData = new FormData();
            formData.append('movie_video', chunk, file.name);
            formData.append('is_last_chunk', isLastChunk ? '1' : '0');
            
            await new Promise((resolve, reject) => {
                router.post(UploadMovieController.url(movie.value), formData, {
                    preserveScroll: true,
                    forceFormData: true,
                    onSuccess: (page) => {
                        uploadProgress.value = Math.round(((chunkIndex + 1) / totalChunks) * 100);
                        // @ts-ignore
                        if (page.props.flash?.complete) {
                            isUploading.value = false;
                            uploadProgress.value = 100;
                            selectedFile.value = null;
                        }
                        resolve(void 0);
                    },
                    onError: (errors) => {
                        reject(new Error(Object.values(errors)[0] as string));
                    }
                });
            });
        }
    } catch (error) {
        console.error('Upload failed:', error);
        isUploading.value = false;
        uploadProgress.value = 0;
        selectedFile.value = null;
        
        const fileInput = document.getElementById('movieFile') as HTMLInputElement;
        if (fileInput) {
            fileInput.value = '';
        }
    }
};

function triggerFileInput() {
    if (isDisableUpload.value) return;
    const fileInput = document.getElementById('movieFile') as HTMLInputElement;
    fileInput?.click();
}

function removeVideo() {
    if (isDisableUpload.value) return;
    
    router.delete(DeleteMovieMovieController.url(movie.value), {
        preserveScroll: true,
        onSuccess: (page) => {
            selectedFile.value = null;
            uploadProgress.value = 0;
        }
    });
}

</script>
<template>
    <Form ref="form" v-bind="UploadMovieController.form(movie)" :reset-on-success="[]"
        v-slot="{ errors, processing }" :options="{ preserveScroll: true }">
        <div class="form-section">
            <label class="form-label">Movie File</label>
            <div 
                class="upload-box"
                :class="{ 
                    'drag-over': isDragOver, 
                    'has-video': hasVideo, 
                    'uploading': isUploading,
                    'disabled': isDisableUpload
                }"
                @click="triggerFileInput"
                @drop="handleDrop"
                @dragover="handleDragOver"
                @dragleave="handleDragLeave"
            >
                <div v-if="!hasVideo && !isUploading" class="upload-content">
                    <i class="fa-solid fa-film"></i>
                    <p>Select your movie video</p>
                    <small>or drag and drop a video here</small>
                </div>

                <div v-else-if="isUploading" class="upload-progress">
                    <i class="fa-solid fa-upload"></i>
                    <p>Uploading movie...</p>
                    <div class="progress-bar">
                        <div class="progress-fill" :style="{ width: uploadProgress + '%' }"></div>
                    </div>
                    <span class="progress-text">{{ uploadProgress }}%</span>
                </div>
                
                <div v-else class="video-info">
                    <i class="fa-solid fa-film"></i>
                    <p class="video-name">
                        {{ videoName }}
                        <!-- <video :src="movie.movie_video_url" controls class="video-preview"></video> -->
                    </p>
                    <div class="video-actions">
                        <button 
                            type="button" 
                            @click.stop="triggerFileInput" 
                            class="action-btn edit-btn"
                            :disabled="isDisableUpload"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" 
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" 
                                stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-edit">
                                <path d="M12 20h9"></path>
                                <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path>
                            </svg>
                        </button>
                        <button 
                            type="button" 
                            @click.stop="removeVideo" 
                            class="action-btn delete-btn"
                            :disabled="isDisableUpload"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" 
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" 
                                stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-trash-2">
                                <path d="M3 6h18"></path>
                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                <path d="M10 11v6"></path>
                                <path d="M14 11v6"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
            <input 
                type="file" 
                name="movie_video" 
                id="movieFile" 
                accept="video/*" 
                @change="handleVideoChange"
                :disabled="isDisableUpload"
            >
            <ErrorLabel :error="errors.movie_video" />
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
    flex-direction: column;
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

.upload-box.has-video {
    padding: 1.5rem;
    border-color: var(--success-color);
    background-color: rgba(var(--success-color-rgb), 0.1);
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
    margin: 0 0 1rem 0;
}

.progress-bar {
    width: 100%;
    height: 8px;
    background-color: rgba(255, 255, 255, 0.1);
    border-radius: 4px;
    overflow: hidden;
    margin-bottom: 0.5rem;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
    border-radius: 4px;
    transition: width 0.3s ease;
}

.progress-text {
    color: var(--primary-color);
    font-weight: 600;
    font-size: 0.9rem;
}

.video-info {
    width: 100%;
}

.video-info i {
    font-size: 2rem;
    color: var(--success-color);
    margin-bottom: 0.75rem;
}

.video-name {
    color: var(--text-light);
    font-size: 1rem;
    font-weight: 500;
    margin: 0 0 1rem 0;
    word-break: break-word;
}

.video-preview {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.video-preview video {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.video-actions {
    display: flex;
    gap: 0.75rem;
    justify-content: center;
}

.action-btn {
    background: rgba(255, 255, 255, 0.9);
    border: none;
    border-radius: 50%;
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s ease;
}

.action-btn:hover:not(:disabled) {
    transform: scale(1.1);
}

.action-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.edit-btn {
    background: var(--secondary-color);
    color: white;
}

.delete-btn {
    background: var(--danger-color);
    color: white;
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