<script setup lang="ts">
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { AlertTriangle } from 'lucide-vue-next';
import { Category } from '@/types';
import DeleteCategoryController from '@/actions/App/Http/Controllers/Category/DestroyCategoryController';

interface Props {
    category: Category | null;
}

const props = defineProps<Props>();

const processing = ref(false);

const isOpen = ref(false);
const deleteCategory = () => {
    if (props.category && !processing.value) {
        processing.value = true;
        router.delete(DeleteCategoryController.url({ category: props.category.id }), {
            preserveScroll: true,
            onSuccess: () => {
                processing.value = false;
                isOpen.value = false;
            },
            onError: () => {
                processing.value = false;
            }
        });
    }
};
</script>

<template>
    <button class="btn btn-delete" @click="isOpen = true">Delete</button>
    <teleport to="body">
        <div id="deleteCategoryModal" class="modal" :class="{ 'active': isOpen }">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title">Delete Category</h2>
                    <button class="close-modal-btn" @click="isOpen = false">&times;</button>
                </div>

                <div class="delete-confirmation">
                    <div class="warning-icon">
                        <AlertTriangle class="h-12 w-12 text-red-500" />
                    </div>

                    <div class="confirmation-content">
                        <h3 class="confirmation-title">Are you sure you want to delete this category?</h3>
                        <p class="confirmation-message">
                            This action will permanently delete the category
                            <strong>"{{ category?.name }}"</strong> and all its subcategories.
                        </p>
                        <div v-if="category?.subcategories && category.subcategories.length > 0"
                            class="subcategories-warning">
                            <p class="warning-text">
                                ⚠️ This category contains <strong>{{ category.subcategories.length }}</strong>
                                subcategory(ies) that will also be deleted:
                            </p>
                            <ul class="subcategories-list">
                                <li v-for="subcategory in category.subcategories" :key="subcategory.id">
                                    {{ subcategory.name }}
                                </li>
                            </ul>
                        </div>
                        <p class="danger-text">
                            This action cannot be undone. All content associated with this category will be affected.
                        </p>
                    </div>
                </div>

                <div class="modal-actions">
                    <button type="button" class="action-button secondary-button" @click="isOpen = false">
                        Cancel
                    </button>
                    <button type="button" class="action-button danger-button" @click="deleteCategory"
                        :disabled="processing">
                        <i class="fa-solid fa-circle-notch fa-spin" v-if="processing"></i>
                        Delete Category
                    </button>
                </div>
            </div>
        </div>
    </teleport>
</template>

<style scoped>
.action-button {
    padding: 0.7rem 1.4rem;
    border-radius: var(--border-radius-sm);
    text-decoration: none;
    font-weight: 500;
    font-size: 0.95rem;
    transition: background-color 0.2s ease, transform 0.2s ease;
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.action-button:hover {
    transform: translateY(-1px);
}

.action-button:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
}

.secondary-button {
    background-color: #6c757d;
    color: white;
}

.secondary-button:hover {
    background-color: #5a6268;
}

.danger-button {
    background-color: #dc3545;
    color: white;
}

.danger-button:hover {
    background-color: #c82333;
}

.item-actions .btn {
    font-size: 0.8rem;
    padding: 0.2rem 0.5rem;
    margin-left: 0.5rem;
    border: none;
    color: white;
    cursor: pointer;
    border-radius: 4px;
}

.item-actions .btn-delete {
    background-color: var(--error-color);
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
    max-width: 600px;
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

.delete-confirmation {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    margin-bottom: 2rem;
}

.warning-icon {
    margin-bottom: 1rem;
}

.confirmation-content {
    max-width: 500px;
}

.confirmation-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--text-headings);
    margin-bottom: 1rem;
}

.confirmation-message {
    font-size: 1rem;
    color: var(--text-light);
    margin-bottom: 1rem;
    line-height: 1.5;
}

.confirmation-message strong {
    color: var(--text-headings);
    font-weight: 600;
}

.subcategories-warning {
    background-color: rgba(251, 191, 36, 0.1);
    border: 1px solid #fbbf24;
    border-radius: var(--border-radius-sm);
    padding: 1rem;
    margin: 1rem 0;
    text-align: left;
}

.warning-text {
    font-size: 0.9rem;
    color: #fbbf24;
    margin-bottom: 0.5rem;
}

.subcategories-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.subcategories-list li {
    padding: 0.25rem 0;
    color: var(--text-light);
    font-size: 0.9rem;
}

.subcategories-list li:before {
    content: "• ";
    color: #fbbf24;
    margin-right: 0.5rem;
}

.danger-text {
    font-size: 0.9rem;
    color: #ef4444;
    background-color: rgba(239, 68, 68, 0.1);
    padding: 0.75rem;
    border-radius: var(--border-radius-sm);
    border-left: 3px solid #ef4444;
    margin-top: 1rem;
}

.modal-actions {
    display: flex;
    justify-content: flex-end;
    gap: 1rem;
}

/* Responsive design */
@media (max-width: 768px) {
    .modal-content {
        width: 95%;
        margin: 1rem;
        padding: 1.5rem;
    }

    .modal-actions {
        flex-direction: column-reverse;
    }

    .action-button {
        width: 100%;
        justify-content: center;
    }
}
</style>
