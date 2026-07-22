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
                    <!-- Category Header -->
                    <div class="category-card-header">
                        <div class="category-info">
                            <div class="category-image-thumb">
                                <img :src="category.image_url" :alt="category.name">
                            </div>
                            <div>
                                <h3 class="category-card-title">{{ category.name }}</h3>
                                <span class="subcategory-count-badge">
                                    {{ category.subcategories?.length || 0 }} {{ category.subcategories?.length === 1 ? 'Subcategory' : 'Subcategories' }}
                                </span>
                            </div>
                        </div>
                        <div class="category-actions">
                            <CreateSubcategoryModal :category="category" />
                            <EditCategoryModal :category="category" />
                            <DeleteCategoryModal :category="category" />
                        </div>
                    </div>

                    <!-- Subcategories Responsive Grid -->
                    <div class="subcategories-section">
                        <div v-if="category.subcategories && category.subcategories.length > 0" class="subcategories-grid">
                            <div 
                                v-for="subcategory in category.subcategories" 
                                :key="subcategory.id"
                                class="subcategory-chip"
                            >
                                <span class="subcategory-name" :title="subcategory.name">{{ subcategory.name }}</span>
                                <div class="chip-actions">
                                    <EditSubcategoryModal :subcategory="subcategory" />
                                    <DeleteSubcategoryModal :subcategory="subcategory" />
                                </div>
                            </div>
                        </div>
                        <div v-else class="empty-subcategories">
                            <i class="fa-solid fa-folder-open"></i>
                            <span>No subcategories created yet in this category.</span>
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

/* --- OPTIMIZED RESPONSIVE CATEGORIES DESIGN --- */
.categories-container {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.category-card {
    background: rgba(18, 18, 26, 0.6);
    backdrop-filter: blur(12px);
    padding: 1.5rem;
    border-radius: 16px;
    border: 1px solid rgba(255, 255, 255, 0.08);
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25);
    transition: all 0.3s ease;
}

.category-card:hover {
    border-color: rgba(255, 255, 255, 0.15);
    box-shadow: 0 6px 24px rgba(0, 0, 0, 0.35);
}

.category-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    padding-bottom: 1rem;
    margin-bottom: 1.25rem;
    flex-wrap: wrap;
    gap: 1rem;
}

.category-info {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.category-image-thumb {
    width: 56px;
    height: 56px;
    border-radius: 12px;
    overflow: hidden;
    flex-shrink: 0;
    border: 1px solid rgba(255, 255, 255, 0.15);
    background: rgba(255, 255, 255, 0.05);
}

.category-image-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.category-card-title {
    font-size: 1.4rem;
    font-weight: 700;
    margin: 0 0 0.25rem 0;
    color: #ffffff;
    letter-spacing: -0.01em;
}

.subcategory-count-badge {
    display: inline-block;
    font-size: 0.75rem;
    font-weight: 600;
    color: var(--primary-color, #e8445a);
    background: rgba(232, 68, 90, 0.12);
    border: 1px solid rgba(232, 68, 90, 0.25);
    padding: 0.15rem 0.6rem;
    border-radius: 20px;
}

.category-actions {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    flex-wrap: wrap;
}

.subcategories-section {
    width: 100%;
}

.subcategories-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(210px, 1fr));
    gap: 0.75rem;
}

.subcategory-chip {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: rgba(255, 255, 255, 0.04);
    border: 1px solid rgba(255, 255, 255, 0.07);
    border-radius: 10px;
    padding: 0.55rem 0.85rem;
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
}

.subcategory-chip:hover {
    background: rgba(255, 255, 255, 0.08);
    border-color: rgba(255, 255, 255, 0.15);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}

.subcategory-name {
    font-size: 0.88rem;
    font-weight: 600;
    color: rgba(255, 255, 255, 0.9);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    margin-right: 0.5rem;
}

.chip-actions {
    display: flex;
    align-items: center;
    gap: 0.3rem;
    flex-shrink: 0;
}

.empty-subcategories {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 1.25rem;
    background: rgba(255, 255, 255, 0.02);
    border: 1px dashed rgba(255, 255, 255, 0.1);
    border-radius: 12px;
    color: rgba(255, 255, 255, 0.4);
    font-size: 0.9rem;
}

@media (max-width: 768px) {
    .subcategories-grid {
        grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
    }
    .category-card-header {
        flex-direction: column;
        align-items: flex-start;
    }
}
</style>