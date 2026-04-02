<script setup lang="ts">
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import ShowSerieController from '@/actions/App/Http/Controllers/Serie/ShowSerieController';
import ShowEditSerieController from '@/actions/App/Http/Controllers/Serie/ShowEditSerieController';
import DeleteSerieModal from './DeleteSerieModal.vue';

const props = defineProps<{
    serie: any;
    viewsCount?: number;
}>();

const isDropdownOpen = ref(false);

function toggleDropdown() {
    isDropdownOpen.value = !isDropdownOpen.value;
}

function goToEditSerie() {
    router.visit(ShowEditSerieController({serie: props.serie.id}));
    isDropdownOpen.value = false;
}

function formatViews(count: number): string {
    if (count >= 1000000) return (count / 1000000).toFixed(1) + 'M';
    if (count >= 1000) return (count / 1000).toFixed(1) + 'K';
    return count.toString();
}
</script>

<template>
    <div class="content-card">
        <Link :href="ShowSerieController({serie: serie.id})">
            <img :src="serie.vertical_image_url" alt="Content" />
            <div class="views-badge">
                <i class="fa-solid fa-eye"></i>
                <span>{{ formatViews(viewsCount ?? 0) }}</span>
            </div>
        </Link>
        <button class="options-menu-btn" @click="toggleDropdown">
            <i class="fa-solid fa-ellipsis-vertical"></i>
        </button>
        <div class="options-dropdown" :class="{ 'show': isDropdownOpen }">
            <button @click="goToEditSerie">Edit</button>
            <DeleteSerieModal :serie="serie" />
        </div>
    </div>
</template>

<style scoped>
/* Contenido del Canal */
.channel-content {
    padding: 0 1rem;
}

.content-grid {
    display: none;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 1rem;
}

.content-grid.active {
    display: grid;
}

.content-card {
    position: relative;
    aspect-ratio: 2/3;
    border-radius: 12px;
    overflow: hidden;
    background-color: #000;
}

.content-card img,
.content-card video {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

.content-card a {
    display: block;
    width: 100%;
    height: 100%;
}

.options-menu-btn {
    position: absolute;
    top: 8px;
    right: 8px;
    background: rgba(0, 0, 0, 0.6);
    color: white;
    border: none;
    border-radius: 50%;
    width: 30px;
    height: 30px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10;
}

.options-dropdown {
    display: none;
    position: absolute;
    top: 40px;
    right: 8px;
    background-color: #333;
    border-radius: 8px;
    overflow: hidden;
    z-index: 11;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
}

.options-dropdown.show {
    display: block;
}

.options-dropdown button {
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

.options-dropdown button:hover {
    background-color: var(--primary-color);
}

.views-badge {
    position: absolute;
    bottom: 8px;
    left: 8px;
    background: rgba(0, 0, 0, 0.7);
    color: #fff;
    border-radius: 6px;
    padding: 3px 8px;
    font-size: 0.75rem;
    display: flex;
    align-items: center;
    gap: 4px;
    z-index: 5;
    pointer-events: none;
}

.views-badge i {
    font-size: 0.65rem;
}
</style>