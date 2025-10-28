<script setup lang="ts">
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import DestroyChapterController from '@/actions/App/Http/Controllers/Serie/DestroyChapterController';

const props = defineProps<{
    chapter: any;
    season: number;
    serie: number;
}>();

const isOpen = ref(false);
const isLoading = ref(false);

const emits = defineEmits<{
    (e: 'chapter-deleted'): void;
}>();

async function deleteChapter() {
    isLoading.value = true;
    router.delete(DestroyChapterController.url({
        serie: props.serie,
        season: props.season,
        chapter: props.chapter.id,
    }), {
        preserveScroll: true,
        onSuccess: async (response: any) => {
            emits('chapter-deleted');
            isOpen.value = false;
        },
        onError: async (response: any) => {
            isLoading.value = false;
            isOpen.value = false;
        },
    });
}

function openModal() {
    isOpen.value = true;
}

function closeModal() {
    isOpen.value = false;
}
</script>

<template>
    <button type="button" class="delete-btn" @click="openModal" style="z-index: 1000;">
        <i class="fa-solid fa-trash-can"></i>
    </button>

    <teleport to="body">
        <div class="modal" :class="{ 'active': isOpen }">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title">Delete Episode</h2>
                    <button class="close-modal-btn" @click="closeModal">&times;</button>
                </div>

                <div class="modal-body">
                    <p>Are you sure you want to delete "{{ chapter.title }}"?</p>
                    <p class="warning-text">This action cannot be undone.</p>
                </div>

                <div class="modal-actions">
                    <button type="button" class="action-button cancel-btn" @click="closeModal">Cancel</button>
                    <button type="button" class="action-button delete-btn-modal" @click="deleteChapter"
                        :disabled="isLoading">
                        <i class="fa-solid fa-circle-notch fa-spin" v-if="isLoading"></i>
                        Delete Episode
                    </button>
                </div>
            </div>
        </div>
    </teleport>
</template>

<style scoped>
.item-list>.list-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: rgba(0, 0, 0, 0.2);
    padding: 0.75rem;
    border-radius: 8px;
    margin-bottom: 0.5rem;
}

.list-item>span {
    flex-grow: 1;
    cursor: pointer;
}

.item-list>.list-item:hover {
    background: rgba(255, 255, 255, 0.1);
}

.item-list>.list-item.selected {
    background: var(--primary-color);
}

.list-item-actions button {
    background: none;
    border: none;
    color: #ccc;
    cursor: pointer;
    padding: 0.25rem;
    font-size: 1rem;
    transition: color 0.2s;
}

.list-item-actions button.delete-btn:hover {
    color: var(--danger-color);
}

.list-item-actions button.edit-btn:hover {
    color: white;
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
    max-width: 500px;
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

.modal-body {
    margin-bottom: 2rem;
}

.modal-body p {
    margin-bottom: 1rem;
    font-size: 1.1rem;
}

.warning-text {
    color: var(--danger-color);
    font-size: 0.9rem;
    font-style: italic;
}

.modal-actions {
    display: flex;
    justify-content: flex-end;
    gap: 1rem;
}

.action-button {
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
    transform: translateY(-1px);
}

.cancel-btn {
    background-color: #6c757d;
}

.cancel-btn:hover {
    background-color: #5a6268;
}

.delete-btn-modal {
    background-color: var(--danger-color);
}

.delete-btn-modal:hover {
    background-color: #c82333;
}

.delete-btn-modal:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}
</style>