<script setup lang="ts">
import { ref } from 'vue';
import AdminDashboardLayout from '@/layouts/AdminDashboardLayout.vue';
import ShowShortContent from './partials/ShowShortContent.vue';
import ShowMovieContent from './partials/ShowMovieContent.vue';
import ShowSerieContent from './partials/ShowSerieContent.vue';

const props = defineProps<{
    content: any;
}>();

</script>

<template>  
    <AdminDashboardLayout 
        :title="`View Content - ${$page.props.name || 'Impulsemedia'}`" 
        :headerTitle="`View Content - ${$page.props.name || 'Impulsemedia'}`">

        <div class="" style="display: flex; gap: 1rem;">
            <div style="width: 25%;">
                <!-- User Information -->
                <div style="background-color: var(--section-bg); border-radius: var(--border-radius-md); padding: 1.5rem; border: 1px solid var(--border-color);">
                    <h3 style="margin-top: 0; margin-bottom: 1rem; color: var(--text-headings); font-size: 1.25rem;">User Information</h3>
                    <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
                        <img :src="content.user.image_url" :alt="content.user.name" style="width: 60px; height: 60px; border-radius: 50%; object-fit: cover;">
                        <div>
                            <div style="font-weight: 600; font-size: 1.1rem; color: var(--text-headings);">{{ content.user.name }}</div>
                            <div style="color: var(--text-muted); font-size: 0.9rem;">@{{ content.user.username }}</div>
                        </div>
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                        <div style="display: flex; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid var(--border-color);">
                            <span style="color: var(--text-muted);">Email:</span>
                            <span style="color: var(--text-headings);">{{ content.user.email }}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid var(--border-color);">
                            <span style="color: var(--text-muted);">Status:</span>
                            <span :style="`color: ${content.user.status === 'active' ? 'var(--success-color)' : 'var(--error-color)'};`">
                                {{ content.user.status.charAt(0).toUpperCase() + content.user.status.slice(1) }}
                            </span>
                        </div>
                        <div style="display: flex; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid var(--border-color);">
                            <span style="color: var(--text-muted);">Followers:</span>
                            <span style="color: var(--text-headings);">{{ content.user.followers_count }}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid var(--border-color);">
                            <span style="color: var(--text-muted);">Following:</span>
                            <span style="color: var(--text-headings);">{{ content.user.followings_count }}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid var(--border-color);">
                            <span style="color: var(--text-muted);">Content Count:</span>
                            <span style="color: var(--text-headings);">{{ content.user.content_count }}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; padding: 0.5rem 0;">
                            <span style="color: var(--text-muted);">Member Since:</span>
                            <span style="color: var(--text-headings);">{{ new Date(content.user.created_at).toLocaleDateString() }}</span>
                        </div>
                    </div>
                </div>
            </div>
            <div style="width: 75%;">
                <!-- Content Information -->
                <ShowShortContent v-if="content.type == 'shorts'" :short="content.contentable" />
                <ShowMovieContent v-if="content.type == 'movies'" :movie="content.contentable" />
                <ShowSerieContent v-if="content.type == 'series'" :serie="content.contentable" />
            </div>
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