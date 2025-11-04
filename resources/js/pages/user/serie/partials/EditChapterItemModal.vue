<script setup lang="ts">

import { ref, onMounted, watch } from 'vue';
import UploadEpisodeForm from './UploadEpisodeForm.vue';
import UploadThumbnailForm from './UploadThumbnailForm.vue';
import UpdateChapterController from '@/actions/App/Http/Controllers/Serie/UpdateChapterController';

import { Form } from '@inertiajs/vue3';

const props = defineProps<{
    serie: number,
    season: number,
    chapter: any,
}>();

const chapterRef = ref(props.chapter);
watch(chapterRef, (newVal) => {
    chapterRef.value = newVal;
});

const isOpen = ref(false);

const emits = defineEmits<{
    (e: 'chapter-updated'): void;
}>();

function openModal() {
    isOpen.value = true;
}

const isLoading = ref(false);
const episodeForm = ref<any>(null);

function updateEpisode() {
    if (episodeForm.value) {
        episodeForm.value.submit();
    }
}

function closeModal() {
    isOpen.value = false;
}

const isUploadingSomething = ref(false);

</script>

<template>
    <button type="button" class="edit-btn" @click="openModal">
        <i class="fa-solid fa-edit"></i>
    </button>
    <teleport to="body">
        <div class="modal" :class="{ 'active': isOpen }" id="editEpisodeModal">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title" id="editEpisodeModalTitle">Edit Episode</h2>
                    <button class="close-modal-btn" id="closeEditEpisodeModalBtn" @click="closeModal">&times;</button>
                </div>
                <Form ref="episodeForm" id="editEpisodeForm" v-bind="UpdateChapterController.form({
                    serie: props.serie,
                    season: props.season,
                    chapter: chapterRef.id,
                })" v-on:start="isLoading = true"
                :options="{
                    preserveScroll: true,
                }"
                v-on:finish="async () => {isLoading = false; closeModal(); emits('chapter-updated')}" v-slot="{ errors, processing, form }">
                    <div class="form-section">
                        <label for="editEpisodeNumber" class="form-label">Episode Number</label>
                        <input name="chapter_number" type="number" id="editEpisodeNumber" class="form-control" 
                               :value="chapterRef.value.chapter_number" placeholder="e.g., 1">
                    </div>
                    <div class="form-section">
                        <label for="editEpisodeTitle" class="form-label">Episode Title</label>
                        <input name="title" type="text" id="editEpisodeTitle" class="form-control" 
                               :value="chapterRef.value.title" placeholder="e.g., The Beginning">
                    </div>
                </Form>
                <UploadThumbnailForm :serie="serie" :season="season" :chapter="chapterRef" v-model:disable="isUploadingSomething" />
                <UploadEpisodeForm :serie="serie" :season="season" v-model="chapterRef" v-model:disable="isUploadingSomething" />

                <div class="modal-actions">
                    <button type="button" class="action-button" style="background-color:#6c757d;" @click="closeModal">Cancel</button>
                    <button type="button" class="action-button" @click="updateEpisode">
                        <i class="fa-solid fa-circle-notch fa-spin" v-if="isLoading"></i>
                        Update Episode
                    </button>
                </div>
            </div>
        </div>
    </teleport>
</template>

<style scoped>
.edit-btn {
    background: transparent;
    color: inherit;
    border: none;
    padding: 0.4rem 0.8rem;
    border-radius: 8px;
    cursor: pointer;
    font-size: 0.8rem;
}

.edit-btn:hover {
    color: #fff;
}

.action-button {
    background-color: var(--primary-color);
    color: white;
    padding: 0.7rem 1.4rem;
    border-radius: var(--border-radius-sm);
    text-decoration: none;
    font-weight: 500;
    font-size: 0.95rem;
    transition: background-color 0.2s ease, transform 0.2s ease;
    border: none;
    cursor: pointer;
}

.action-button:hover {
    background-color: var(--primary-color-hover);
    transform: translateY(-1px);
}

.modal {
    display: none;
    position: fixed;
    z-index: 1001;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(10, 10, 35, 0.8);
    align-items: flex-start;
    justify-content: center;
    padding-top: 5vh;
}

.modal.active {
    display: flex;
}

.modal-content {
    background-color: var(--sidebar-bg);
    color: var(--text-light);
    padding: 2rem;
    border: 1px solid var(--border-color);
    border-radius: var(--border-radius-md);
    width: 90%;
    max-width: 700px;
    box-shadow: var(--shadow-md);
    position: relative;
    animation: slideInModalPlatform 0.3s ease-out;
}

@keyframes slideInModalPlatform {
    from {
        opacity: 0;
        transform: scale(0.95) translateY(-20px);
    }

    to {
        opacity: 1;
        transform: scale(1) translateY(0);
    }
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid var(--border-color);
}

.modal-title {
    font-size: 1.5rem;
    font-weight: 600;
    margin: 0;
    color: var(--text-headings);
}

.close-modal-btn {
    background: none;
    border: none;
    font-size: 1.8rem;
    color: var(--text-muted);
    cursor: pointer;
    line-height: 1;
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

.modal-actions {
    margin-top: 2rem;
    display: flex;
    justify-content: flex-end;
    gap: 1rem;
}
</style>
