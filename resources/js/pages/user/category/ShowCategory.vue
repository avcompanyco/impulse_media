<script setup lang="ts">
import UserDashboardLayout from '@/layouts/UserDashboardLayout.vue';
import { Link, router } from '@inertiajs/vue3';

import ShowMovieController from '@/actions/App/Http/Controllers/Movie/ShowMovieController';
import ShowSerieController from '@/actions/App/Http/Controllers/Serie/ShowSerieController';
import ShowSubcategoryController from '@/actions/App/Http/Controllers/Subcategory/ShowSubcategoryController';

const props = defineProps<{
    category: any;
    subcategories: any[];
}>();


function mergeInRandomOrderMoviesAndSeries(subcategory: any) {
    subcategory.movies = subcategory.movies || [];
    subcategory.series = subcategory.series || [];
    return [...subcategory.movies, ...subcategory.series].sort(() => Math.random() - 0.5);
}

function goTuSubcategory(subcategoryId: number) {
    router.visit(ShowSubcategoryController({subcategory: subcategoryId}))
}

</script>

<template>
    <UserDashboardLayout 
        :title="`${category.name} - ${$page.props.name || 'Impulsemedia'}`" 
        :headerTitle="`${category.name} - ${$page.props.name || 'Impulsemedia'}`">
        <h1 
            class="page-title" 
            style="cursor:pointer"
            @click="goTuSubcategory(category.id)"
            >{{ category.name }}</h1>
        <div 
            v-for="subcategory in subcategories" 
            :key="`subcategory_${subcategory.id}_card`" 
            class="movies-section"
            style="margin-bottom: 80px;"
        >
            <template v-if="subcategory.movies.length > 0 || subcategory.series.length > 0">
                <h2 class="section-title">{{ subcategory.name }}</h2>
                <div class="movies-row" data-slider="slasher">
                    <div v-for="(item, index) in mergeInRandomOrderMoviesAndSeries(subcategory)"
                        :key="`movie_${item.id}_card_${index + 1}`" class="movie-card">
                        <Link 
                            v-if="item.content.type == 'movies'"
                            :href="ShowMovieController({ movie: item.id })">
                            <img :src="item.vertical_image_url" alt="Movie Poster">
                        </Link>
                        <Link 
                            v-else
                            :href="ShowSerieController({ serie: item.id })">
                            <img :src="item.vertical_image_url" alt="Movie Poster">
                        </Link>
                    </div>
                </div>
            </template>
        </div>
        <br />
    </UserDashboardLayout>
</template>

<style scoped>
/* Page Specific Styles */
.page-title {
    font-size: 2.5rem;
    font-weight: 700;
    margin: 1.5rem 1rem;
    text-align: center;
}

/* Movie Sections */
.movies-section {
    position: relative;
    padding: 0 1rem;
    margin-bottom: 2rem;
    overflow: visible;
}

.section-title {
    font-size: 1.75rem;
    font-weight: 600;
    margin-bottom: 1rem;
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

/* Navigation Arrows Style */
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
    opacity: 0;
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

/* Bottom Navigation */
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
    transition: all 0.3s var(--transition-bezier);
}

.nav-item.active {
    color: var(--gradient-start);
}

.nav-icon {
    width: 24px;
    height: 24px;
}

/* Desktop Styles */
@media (min-width: 1200px) {
    .app-wrapper {
        max-width: 1600px;
        margin: 0 auto;
        padding: 0 4rem 80px;
    }

    .page-title {
        margin: 2rem 0 3rem;
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
        transition: all 0.3s ease;
        transform-origin: center center;
    }

    .movies-row:hover .movie-card {
        transform-origin: center left;
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