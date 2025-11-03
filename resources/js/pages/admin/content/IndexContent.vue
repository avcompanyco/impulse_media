<script setup lang="ts">
import { ref } from 'vue';
import CreateCategoryModal from './partials/CreateCategoryModal.vue';
import EditCategoryModal from './partials/EditCategoryModal.vue';
import DeleteCategoryModal from './partials/DeleteCategoryModal.vue';
import CreateSubcategoryModal from './partials/CreateSubcategoryModal.vue';
import EditSubcategoryModal from './partials/EditSubcategoryModal.vue';
import DeleteSubcategoryModal from './partials/DeleteSubcategoryModal.vue';
import AdminDashboardLayout from '@/layouts/AdminDashboardLayout.vue';
import { Category, Subcategory } from '@/types';
import ShowContentDatatable from './partials/ShowContentDatatable.vue';

const props = defineProps<{
    categories: Category[];
}>();

</script>

<template>  
    <AdminDashboardLayout 
        :title="`Manage Categories and Content - ${$page.props.name || 'Impulsemedia'}`" 
        :headerTitle="`Manage Categories and Content - ${$page.props.name || 'Impulsemedia'}`">
        <template #header-actions>
            <CreateCategoryModal />
        </template>
        
        <div class="dashboard-module">
            <div class="categories-container" id="categoriesContainer">
                <div class="category-card" v-for="category in categories" :key="category.id">
                    <div class="category-card-header">
                        <h3 class="category-card-title">{{ category.name }}</h3>
                        <div class="item-actions">
                            <EditCategoryModal :category="category" />
                            <DeleteCategoryModal :category="category" />
                        </div>
                    </div>
                    <div class="category-card-body">
                        <div class="category-image-container">
                            <img :src="category.image_url" alt="Action">
                        </div>
                        <div class="subcategories-container">
                            <div class="list-header">
                                <h4 class="list-title">Subcategories</h4>
                                <CreateSubcategoryModal :category="category" />
                            </div>
                            <div class="subcategory-list">
                                <div v-for="subcategory in category.subcategories" :key="subcategory.id"
                                    class="subcategory-item">
                                    <span>{{ subcategory.name }}</span>
                                    <div class="item-actions">
                                        <EditSubcategoryModal :subcategory="subcategory" />
                                        <DeleteSubcategoryModal :subcategory="subcategory" />
                                    </div>
                                </div>
                                <div v-if="category.subcategories.length === 0">
                                    No subcategories yet.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <br />
            <Suspense>
                <ShowContentDatatable />
            </Suspense>
        </div>

    </AdminDashboardLayout>
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

.action-button:hover {
    background-color: var(--primary-color-hover);
    transform: translateY(-1px);
}

/* --- NUEVO DISEÑO DE CATEGORÍAS --- */
.categories-container {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.category-card {
    background-color: rgba(0, 0, 0, 0.2);
    padding: 1.5rem;
    border-radius: var(--border-radius-sm);
    border: 1px solid var(--border-color);
}

.category-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid var(--border-color);
    padding-bottom: 1rem;
    margin-bottom: 1rem;
}

.category-card-title {
    font-size: 1.5rem;
    font-weight: 600;
    margin: 0;
    color: var(--text-headings);
}

.category-card-body {
    display: grid;
    grid-template-columns: 200px 1fr;
    gap: 1.5rem;
}

.category-image-container img {
    width: 100%;
    height: auto;
    object-fit: cover;
    border-radius: var(--border-radius-sm);
}

.subcategories-container .list-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.subcategories-container .list-title {
    font-size: 1.1rem;
    color: var(--text-muted);
}

.subcategory-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.6rem;
    border-radius: 4px;
}

.subcategory-item:hover {
    background-color: rgba(255, 255, 255, 0.05);
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

.item-actions .btn-delete {
    background-color: var(--error-color);
}

/* --- FIN NUEVO DISEÑO --- */
</style>