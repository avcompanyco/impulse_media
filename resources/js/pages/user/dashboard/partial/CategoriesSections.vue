<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import ShowCategoryController from '@/actions/App/Http/Controllers/Category/ShowCategoryController';
import ShowMovieController from '@/actions/App/Http/Controllers/Movie/ShowMovieController';
import ShowSerieController from '@/actions/App/Http/Controllers/Serie/ShowSerieController';

const props = defineProps<{
    categories: any[];
}>();

function mergeInRandomOrderMoviesAndSeries(category: any) {
    return [...category.movies, ...category.series].sort(() => Math.random() - 0.5);
}

function hasMoviesOrSeries(category: any) {
    return category.movies.length > 0 || category.series.length > 0;
}

</script>

<template>
    <div v-for="category in categories" :key="category.id">
        <div v-if="hasMoviesOrSeries(category)" class="movies-section">
            <div class="section-header">
                <Link :href="ShowCategoryController(category)" class="section-title">{{ category.name }}</Link>
                <Link :href="ShowCategoryController(category)" class="view-all-btn">View All</Link>
            </div>
            <div class="movies-row" data-slider="action">
                <div v-for="(movie, index) in mergeInRandomOrderMoviesAndSeries(category)"
                    :key="`movie_${movie.id}_card_${index + 1}`" class="movie-card">
                    <Link v-if="movie.movie_video" :href="ShowMovieController.url(movie)">
                        <img :src="movie.vertical_image_url" alt="Movie Poster" loading="lazy">
                    </Link>
                    <Link v-else :href="ShowSerieController.url(movie)">
                        <img :src="movie.vertical_image_url" alt="Movie Poster" loading="lazy">
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Movie Sections */
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

.view-all-btn {
    background-color: rgba(255, 255, 255, 0.1);
    color: var(--text-light);
    text-decoration: none;
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    font-size: 0.8rem;
    font-weight: 500;
    transition: background-color 0.2s;
}

.view-all-btn:hover {
    background-color: rgba(255, 255, 255, 0.2);
    color: var(--text-light);
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

    .header {
        justify-content: space-between;
    }

    .logo-icon {
        position: absolute;
        left: 50%;
        transform: translateX(-50%);
    }

    .carousel-container {
        padding: 0;
    }

    .carousel-content {
        aspect-ratio: 21/9;
    }

    .mobile-image {
        display: none;
    }

    .desktop-image {
        display: block;
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

    .movies-row .movie-card:first-child {
        transform-origin: left center;
    }

    .movies-row .movie-card:last-child {
        transform-origin: right center;
    }

    .movie-title {
        font-size: 2.5rem;
        max-width: 800px;
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