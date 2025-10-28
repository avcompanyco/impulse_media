<script setup lang="ts">
import { ref } from 'vue';
import { Form } from '@inertiajs/vue3';
import ActionButton from '@/components/app/ActionButton.vue';
import InputCustomField from '@/components/form/InputCustomField.vue';
import ImageCustomField from '@/components/form/ImageCustomField.vue';
import { Category } from '@/types';
import UpdateCategoryController from '@/actions/App/Http/Controllers/Category/UpdateCategoryController';

interface Props {
    category: Category | null;
}

const props = defineProps<Props>();

const titleRef = ref(props.category?.name);
const imageRef = ref(null);
const isOpen = ref(false);

function onSuccess ()
{
    isOpen.value = false;
    if (imageRef.value) {
        // @ts-ignore
        imageRef.value?.resetInput();
    }
}

</script>

<template>
    <button class="btn btn-edit" @click="isOpen = true">Edit</button>
    <teleport to="body">
        <div id="editCategoryModal" class="modal" :class="{ 'active': isOpen }">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title">Edit Category</h2>
                    <button class="close-modal-btn" @click="isOpen = false">&times;</button>
                </div>
                <Form v-bind="UpdateCategoryController.form({ category: category.id })" method="post"
                    v-slot="{ errors, processing }" @success="onSuccess" v-if="category"
                    :options="{ preserveScroll: true }"
                    >

                    <InputCustomField 
                        name="name" label="Category Name" placeholder="Enter Category Name" type="text"
                        required autocomplete="off" :error="errors.name" v-model="titleRef" />

                    <ImageCustomField 
                        ref="imageRef"
                        name="image" 
                        label="Featured Image" 
                        placeholder="Select Featured Image"
                        type="file" autocomplete="off" :error="errors.image" />

                    <div class="current-image" v-if="category?.image_url">
                        <label class="form-label-custom">Current Image</label>
                        <div class="image-preview">
                            <img :src="category.image_url" :alt="category.name" class="preview-img">
                        </div>
                    </div>

                    <div class="modal-actions">
                        <ActionButton type="button" class="action-button" @click="isOpen = false"
                            style="background-color: #6c757d;">
                            Cancel
                        </ActionButton>

                        <ActionButton type="submit" class="action-button" :processing="processing">
                            <i class="fa-solid fa-circle-notch fa-spin" v-if="processing"></i>
                            Update Category
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

.current-image {
    margin-bottom: 1.5rem;
}

.form-label-custom {
    font-size: 0.95rem;
    font-weight: 500;
    margin-bottom: 0.4rem;
    color: var(--text-muted);
    display: block;
}

.image-preview {
    border: 1px solid var(--border-color);
    border-radius: var(--border-radius-sm);
    padding: 0.5rem;
    background-color: var(--input-bg);
}

.preview-img {
    max-width: 100%;
    max-height: 200px;
    object-fit: cover;
    border-radius: var(--border-radius-sm);
}
</style>
