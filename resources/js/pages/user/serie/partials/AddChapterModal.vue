<script setup lang="ts">

import { ref, onMounted, onUnmounted, watch } from 'vue';
import CreateChapterController from '@/actions/App/Http/Controllers/Serie/CreateChapterController';
import UploadEpisodeForm from './UploadEpisodeForm.vue';
import UploadThumbnailForm from './UploadThumbnailForm.vue';
import PublishChapterController from '@/actions/App/Http/Controllers/Serie/PublishChapterController';

import { Form } from '@inertiajs/vue3';

const props = defineProps<{
    serie: number,
    season: number,
}>();

const chapter = ref<any>(null);
const isOpen = ref(false);

const emits = defineEmits<{
    (e: 'chapter-added'): void;
}>();

async function getChapter() {
    try {
        const response = await fetch(CreateChapterController.url({
            serie: props.serie,
            season: props.season,
        }), {
            method: 'GET',
        }).then(response => response.json());
        chapter.value = response;
    } catch (error) {
        console.log(error);
    }
}

watch(() => chapter, async () => {
    await getChapter();
});

async function addNewChapter() {
    await getChapter();
    isOpen.value = true;
}

const isLoading = ref(false);
const episodeForm = ref<any>(null);

function publishEpisode() {
    if (episodeForm.value) {
        episodeForm.value.submit();
    }
}

function closeModal() {
    isOpen.value = false;
    chapter.value = null;
}

const isUploadingSomething = ref(false);
</script>

<template>
    <button type="button" id="addEpisodeBtn" class="add-btn" @click="addNewChapter">+ Add</button>
    <teleport to="body">
        <div class="modal" :class="{ 'active': isOpen }" id="episodeModal">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title" id="episodeModalTitle">Add Episode</h2>
                    <button class="close-modal-btn" id="closeEpisodeModalBtn" @click="closeModal">&times;</button>
                </div>
                <Form v-if="chapter" ref="episodeForm" id="episodeForm" v-bind="PublishChapterController.form({
                    serie: props.serie,
                    season: props.season,
                    chapter: chapter?.id,
                })" v-on:start="isLoading = true"
                :options="{
                    preserveScroll: true,
                }"
                v-on:finish="async () => {isLoading = false; closeModal(); await getChapter(); emits('chapter-added')}" v-slot="{ errors, processing, form }">
                    <input type="hidden" id="episodeSeasonIndex">
                    <div class="form-section">
                        <label for="episodeNumber" class="form-label">Episode Number</label>
                        <input name="chapter_number" type="number" id="episodeNumber" class="form-control" placeholder="e.g., 1">
                    </div>
                    <div class="form-section">
                        <label for="episodeTitle" class="form-label">Episode Title</label>
                        <input name="title" type="text" id="episodeTitle" class="form-control" placeholder="e.g., The Beginning">
                    </div>
                </Form>
                <UploadThumbnailForm v-if="chapter" :serie="serie" :season="season" :chapter="chapter" v-model:disable="isUploadingSomething" />
                <UploadEpisodeForm v-if="chapter" :serie="serie" :season="season" v-model="chapter" v-model:disable="isUploadingSomething" />

                <div class="modal-actions">
                    <button type="button" class="action-button" style="background-color:#6c757d;" @click="closeModal">Cancel</button>
                    <button type="button" class="action-button" @click="publishEpisode">
                        <i class="fa-solid fa-circle-notch fa-spin" v-if="isLoading"></i>
                        Save Episode
                    </button>
                </div>
            </div>
        </div>
    </teleport>
</template>

<style scoped>
.add-btn {
    background: var(--primary-color);
    color: white;
    border: none;
    padding: 0.4rem 0.8rem;
    border-radius: 8px;
    cursor: pointer;
    font-size: 0.8rem;
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