<script setup lang="ts">
import { ref } from 'vue';
import StoreSubcategoryController from '@/actions/App/Http/Controllers/Subcategory/StoreSubcategoryController';
import InputCustomField from '@/components/form/InputCustomField.vue';
import ActionButton from '@/components/app/ActionButton.vue';
import { Form } from '@inertiajs/vue3';
import { Category } from '@/types';

const props = defineProps<{
    category: Category;
}>();

const isOpen = ref(false);
</script>

<template>
    <button class="action-button add-subcategory-btn" data-category-id="cat_1"
        style="padding:0.3rem 0.6rem; font-size:0.8rem;" @click="isOpen = true">+ Add</button>
    <div id="subcategoryModal" class="modal" :class="{ 'active': isOpen }">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="subcategoryModalTitle"></h2><button class="close-modal-btn"
                    id="closeSubcategoryModal" @click="isOpen = false">&times;</button>
            </div>
            <Form v-bind="StoreSubcategoryController.form({ category: category.id })" method="post" :reset-on-success="['name']"
                v-slot="{ errors, processing }" @success="isOpen = false"
                :options="{ preserveScroll: true }"
                >
                <InputCustomField name="name" label="Subcategory Name" placeholder="Enter Subcategory Name" type="text"
                    required autocomplete="off" :error="errors.name" />
                <div class="modal-actions">
                    <ActionButton type="button" class="action-button" id="cancelSubcategoryModal"
                        @click="isOpen = false" style="background-color: #6c757d;">Cancel</ActionButton>
                    <ActionButton type="submit" class="action-button" :processing="processing">
                        <i class="fa-solid fa-circle-notch fa-spin" v-if="processing"></i>
                        Save
                    </ActionButton>
                </div>
            </Form>
        </div>
    </div>
</template>

<style scoped>
.dashboard-module {
    background-color: var(--section-bg);
    border-radius: var(--border-radius-md);
    padding: 1.5rem;
    margin-bottom: 2rem;
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
</style>