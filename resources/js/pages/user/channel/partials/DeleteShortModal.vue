<script setup lang="ts">
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import ActionButton from '@/components/app/ActionButton.vue';
import DeleteShortController from '@/actions/App/Http/Controllers/Short/DeleteShortController';

const props = defineProps<{
    short: any;
}>();

const isOpen = ref(false);
const deleteBtnLoading = ref(false);

function deleteShort() {
    deleteBtnLoading.value = true;
    router.delete(DeleteShortController({ short: props.short.id }), {
        preserveScroll: true,
        onSuccess: () => {
            deleteBtnLoading.value = false;
            isOpen.value = false;
        }
    });
}
</script>

<template>
    <button class="dropdown" @click="isOpen = true">Delete</button>
    <teleport to="body">
        <div id="deleteShortModal" class="modal" :class="{ 'active': isOpen }">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title" id="deleteShortModalTitle">Delete Short</h2>
                    <button class="close-modal-btn" id="closeDeleteShortModal" @click="isOpen = false">&times;</button>
                </div>
                
                <div class="modal-body">
                    <p class="warning-text">
                        Are you sure you want to delete this short?
                    </p>
                    <p class="warning-subtitle">
                        This action cannot be undone. The short will be permanently removed from your channel.
                    </p>
                </div>

                <div class="modal-actions">
                    <ActionButton type="button" class="action-button" id="cancelDeleteShort"
                        @click="isOpen = false" style="background-color: #6c757d;">
                        Cancel
                    </ActionButton>

                    <ActionButton type="submit" class="action-button" :processing="deleteBtnLoading"
                        @click="deleteShort"
                        style="background-color: var(--destructive-action-bg, #dc3545);">
                        <i class="fa-solid fa-circle-notch fa-spin" v-if="deleteBtnLoading"></i>
                        Delete Short
                    </ActionButton>
                </div>
            </div>
        </div>
    </teleport>
</template>

<style scoped>
button.dropdown {
    display: block;
    width: 100%;
    background: none;
    border: none;
    color: white;
    padding: 0.75rem 1.5rem;
    text-align: left;
    white-space: nowrap;
    cursor: pointer;
}

button.dropdown:hover {
    background-color: var(--primary-color);
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
    margin-bottom: 1.5rem;
}

.warning-text {
    font-size: 1rem;
    color: var(--text-light);
    margin-bottom: 0.75rem;
    line-height: 1.5;
}

.warning-subtitle {
    font-size: 0.9rem;
    color: var(--text-muted);
    margin: 0;
    line-height: 1.4;
}

.modal-actions {
    margin-top: 2rem;
    display: flex;
    justify-content: flex-end;
    gap: 1rem;
}
</style>
