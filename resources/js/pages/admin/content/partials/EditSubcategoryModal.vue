<script setup lang="ts">
import { ref } from 'vue';
import { Form } from '@inertiajs/vue3';
import UpdateSubcategoryController from '@/actions/App/Http/Controllers/Subcategory/UpdateSubcategoryController';
import InputCustomField from '@/components/form/InputCustomField.vue';
import ActionButton from '@/components/app/ActionButton.vue';
import { Subcategory } from '@/types';

interface Props {
    subcategory: Subcategory | null;
}

const isOpen = ref(false);
const props = defineProps<Props>();

const closeModal = () => {
    isOpen.value = false;
};
</script>

<template>
    <button class="chip-action-btn chip-edit-btn" @click="isOpen = true" title="Edit Subcategory">
        <i class="fa-solid fa-pen-to-square"></i> Edit
    </button>
    <teleport to="body">
        <div id="editSubcategoryModal" class="modal" :class="{ 'active': isOpen }">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title">Edit Subcategory</h2>
                    <button class="close-modal-btn" @click="closeModal">&times;</button>
                </div>
                <Form
                    v-bind="UpdateSubcategoryController.form({ category: subcategory.category_id, subcategory: subcategory.id })"
                    v-slot="{ errors, processing }"
                    @success="closeModal" v-if="subcategory"
                    :options="{ preserveScroll: true }">


                    <InputCustomField name="name" label="Subcategory Name" placeholder="Enter Subcategory Name"
                        type="text" required autocomplete="off" :error="errors.name" :value="subcategory?.name" />

                    <div class="modal-actions">
                        <ActionButton type="button" class="action-button" @click="closeModal"
                            style="background-color: #6c757d;">
                            Cancel
                        </ActionButton>

                        <ActionButton type="submit" class="action-button" :processing="processing">
                            <i class="fa-solid fa-circle-notch fa-spin" v-if="processing"></i>
                            Update Subcategory
                        </ActionButton>
                    </div>
                </Form>
            </div>
        </div>
    </teleport>
</template>

<style scoped>
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
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.action-button:hover {
    background-color: var(--primary-color-hover);
    transform: translateY(-1px);
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

.item-actions .btn-edit {
    background-color: var(--secondary-color);
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

.modal-actions {
    margin-top: 2rem;
    display: flex;
    justify-content: flex-end;
    gap: 1rem;
}

.category-info {
    margin-bottom: 1.5rem;
}

.form-label-custom {
    font-size: 0.95rem;
    font-weight: 500;
    margin-bottom: 0.4rem;
    color: var(--text-muted);
    display: block;
}

.category-display {
    background: var(--input-bg);
    border: 1px solid var(--border-color);
    color: var(--text-light);
    padding: 0.8rem 1rem;
    border-radius: var(--border-radius-sm);
    font-size: 0.95rem;
    font-weight: 500;
}
.chip-action-btn.chip-edit-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    background: rgba(59, 130, 246, 0.25);
    color: #60a5fa;
    border: 1px solid rgba(59, 130, 246, 0.5);
    padding: 0.25rem 0.6rem;
    font-weight: 700;
    font-size: 0.75rem;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.chip-action-btn.chip-edit-btn:hover {
    background: #2563eb;
    color: #ffffff;
    border-color: #3b82f6;
}
</style>
