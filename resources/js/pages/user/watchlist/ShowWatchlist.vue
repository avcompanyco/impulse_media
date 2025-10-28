<script setup lang="ts">
import { computed, ref, watch, onMounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import UserDashboardLayout from '@/layouts/UserDashboardLayout.vue';

import ShowMovieController from '@/actions/App/Http/Controllers/Movie/ShowMovieController';
import ShowSerieController from '@/actions/App/Http/Controllers/Serie/ShowSerieController';
import ShowCategoryController from '@/actions/App/Http/Controllers/Category/ShowCategoryController';

const props = defineProps<{
    watchlist: any;
}>();


</script>
<template>
    <UserDashboardLayout title="Dashboard" headerTitle="Dashboard">
        <h1 class="page-title">Watchlist</h1>

        <template v-for="(content, categoryName) in watchlist" :key="`name_${content.category.id}`">
            <div v-if="content.items.length > 0" class="movies-section" style="margin-bottom: 80px;">
                <div class="section-header">
                    <Link :href="ShowCategoryController({ category: content.category.id })" class="section-title">{{
                    categoryName }}</Link>
                </div>
                <div class="movies-row">

                    <div v-for="item in content.items" class="movie-card">
                        <Link v-if="item.watchlistable.content.type == 'movies'"
                            :href="ShowMovieController({ movie: item.watchlistable.id })">
                        <img :src="item.watchlistable.vertical_image_url" alt="Movie Poster" />
                        </Link>
                        <Link v-if="item.watchlistable.content.type == 'series'"
                            :href="ShowSerieController({ serie: item.watchlistable.id })">
                        <img :src="item.watchlistable.vertical_image_url" alt="Serie Poster" />
                        </Link>
                    </div>
                </div>
            </div>
            <br />
        </template>
    </UserDashboardLayout>
</template>

<style scoped>
/* Título de la página */
.page-title {
    font-size: 2rem;
    font-weight: 600;
    padding: 1rem;
    margin: 1rem 0 0.5rem 0;
}

/* Secciones de películas (Estilo dashboard.html) */
.movies-section {
    position: relative;
    padding: 0 1rem;
    margin-bottom: 2rem;
    overflow: visible;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.section-title {
    font-size: 1.75rem;
    font-weight: 600;
    margin-bottom: 0;
    color: inherit;
    text-decoration: none;
}

.movies-row {
    position: relative;
    display: flex;
    gap: 0.75rem;
    overflow-x: auto;
    padding: 1rem;
    margin: -1rem;
    scroll-snap-type: x mandatory;
    -ms-overflow-style: none;
    scrollbar-width: none;
    cursor: grab;
    user-select: none;
    scroll-behavior: smooth;
    -webkit-overflow-scrolling: touch;
}

.movies-row::-webkit-scrollbar {
    display: none;
}

.movies-row.grabbing {
    cursor: grabbing;
}

.movie-card {
    flex: 0 0 auto;
    width: 110px;
    aspect-ratio: 2/3;
    border-radius: 12px;
    overflow: hidden;
    transition: all 0.3s var(--transition-bezier);
    scroll-snap-align: start;
    position: relative;
    display: block;
    text-decoration: none;
}

.movie-card a {
    display: block;
    width: 100%;
    height: 100%;
}

.movie-card img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.slider-arrow {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    width: 40px;
    height: 40px;
    background: rgba(128, 128, 128, 0.3);
    border-radius: 50%;
    display: none;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    z-index: 99;
    border: none;
    color: white;
    transition: all 0.2s ease;
}

.slider-arrow::after {
    content: '›';
    font-size: 28px;
    font-weight: 400;
    line-height: 1;
}

.slider-arrow.prev::after {
    content: '‹';
}

.slider-arrow:hover {
    background: rgba(128, 128, 128, 0.5);
}

/* Navegación Inferior (Estilo dashboard.html) */
.bottom-nav {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    background: var(--main-bg);
    padding: 1rem;
    display: flex;
    justify-content: space-around;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    z-index: 1000;
}

.nav-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    color: white;
    text-decoration: none;
    font-size: 0.8rem;
    gap: 4px;
}

.nav-item.active {
    color: var(--gradient-start);
}

.nav-icon {
    width: 24px;
    height: 24px;
}

/* Estilos Desktop (Estilo dashboard.html) */
@media (min-width: 1200px) {
    .app-wrapper {
        max-width: 1600px;
        margin: 0 auto;
        padding: 0 4rem 80px;
    }

    .header {
        justify-content: space-between;
    }

    .page-title {
        font-size: 2.5rem;
        padding: 1rem 0;
    }

    .movies-section {
        padding: 0;
        margin: 0 60px 3rem;
    }

    .movies-row {
        padding: 2rem;
        margin: -2rem;
    }

    .movie-card {
        width: 180px;
    }

    .movie-card:hover {
        transform: scale(1.2);
        z-index: 20;
    }

    .movie-card:hover~.movie-card {
        transform: translateX(30px);
    }

    .slider-arrow {
        display: flex;
    }

    .slider-arrow.prev {
        left: -80px;
    }

    .slider-arrow.next {
        right: -80px;
    }
}
</style>