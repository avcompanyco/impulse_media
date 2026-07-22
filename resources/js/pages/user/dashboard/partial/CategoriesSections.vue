<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import ShowMovieController from '@/actions/App/Http/Controllers/Movie/ShowMovieController';
import ShowSerieController from '@/actions/App/Http/Controllers/Serie/ShowSerieController';

const props = defineProps<{
    categories: any[];
}>();

const page = usePage();

function getCategoryUrl(category: any) {
    if (typeof category === 'object' && category.id) {
        if (category.id === 'popular') return '/category/popular';
        if (category.id === 'pay_per_view') return '/category/pay_per_view';
        if (category.id === 'all_movies') return '/movies';
        if (category.id === 'all_series') return '/series';
        if (category.id === 'documentary') return '/category/documentary';
        return `/category/${category.id}`;
    }
    return `/category/${category}`;
}

function mergeMoviesAndSeries(category: any) {
    const movies = category.movies || [];
    const series = category.series || [];
    return [...movies, ...series];
}

function hasMoviesOrSeries(category: any) {
    return (category.movies && category.movies.length > 0) || (category.series && category.series.length > 0);
}

function isUserMember() {
    return Boolean((page.props.auth as any)?.user?.is_member);
}

function getBadgeText(content: any) {
    if (!content) return '';
    const price = Number(content.ppv_price || 0);
    const allowMembership = Boolean(content.allow_membership);

    if (allowMembership && (price <= 0 || isUserMember())) {
        return 'INCLUDED';
    }
    if (price > 0) {
        return `PPV $${price.toFixed(2)}`;
    }
    return 'INCLUDED';
}

function isPpvPaid(content: any) {
    if (!content) return false;
    const price = Number(content.ppv_price || 0);
    const allowMembership = Boolean(content.allow_membership);

    if (allowMembership && isUserMember()) {
        return false;
    }
    return price > 0 || !allowMembership;
}

</script>

<template>
    <div v-for="category in categories" :key="category.id">
        <div v-if="hasMoviesOrSeries(category)" class="movies-section">
            <div class="section-header">
                <Link :href="getCategoryUrl(category)" class="section-title">{{ category.name }}</Link>
                <Link :href="getCategoryUrl(category)" class="view-all-btn">View All</Link>
            </div>
            <div class="movies-row" data-slider="action">
                <div v-for="(item, index) in mergeMoviesAndSeries(category)"
                    :key="`content_${item.id}_card_${index + 1}`" class="movie-card">
                    <Link v-if="item.movie_video || item.duration !== undefined || item.video_url !== undefined" :href="ShowMovieController.url(item)">
                        <div v-if="item.content" class="card-ppv-badge" :class="{ 'ppv-paid': isPpvPaid(item.content) }">
                            {{ getBadgeText(item.content) }}
                        </div>
                        <img :src="item.vertical_image_url || item.horizontal_image_url || '/images/default_poster.webp'" alt="Poster" loading="lazy">
                    </Link>
                    <Link v-else :href="ShowSerieController.url(item)">
                        <div v-if="item.content" class="card-ppv-badge" :class="{ 'ppv-paid': isPpvPaid(item.content) }">
                            {{ getBadgeText(item.content) }}
                        </div>
                        <img :src="item.vertical_image_url || item.horizontal_image_url || '/images/default_poster.webp'" alt="Poster" loading="lazy">
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
    transition: color 0.2s;
}

.section-title:hover {
    color: #e8445a;
}

.view-all-btn {
    background-color: rgba(255, 255, 255, 0.1);
    color: var(--text-light);
    text-decoration: none;
    padding: 0.3rem 0.85rem;
    border-radius: 12px;
    font-size: 0.85rem;
    font-weight: 600;
    transition: all 0.2s ease;
}

.view-all-btn:hover {
    background-color: rgba(232, 68, 90, 0.8);
    color: #ffffff;
}

.movies-row {
    position: relative;
    display: flex;
    gap: 0.85rem;
    overflow-x: auto;
    padding: 0.5rem 0.5rem 1rem 0.5rem;
    scroll-snap-type: x mandatory;
    -ms-overflow-style: auto;
    scrollbar-width: thin;
    scrollbar-color: rgba(255, 255, 255, 0.2) transparent;
    cursor: grab;
    user-select: none;
    scroll-behavior: smooth;
    -webkit-overflow-scrolling: touch;
}

.movies-row::-webkit-scrollbar {
    height: 6px;
}

.movies-row::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.2);
    border-radius: 4px;
}

.movies-row.grabbing {
    cursor: grabbing;
}

.movie-card {
    flex: 0 0 auto;
    width: 125px;
    aspect-ratio: 2/3;
    border-radius: 12px;
    overflow: hidden;
    transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.3s;
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

.movie-card:hover {
    transform: translateY(-4px) scale(1.03);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.4);
    z-index: 10;
}

/* Desktop Styles */
@media (min-width: 1200px) {
    .movies-section {
        padding: 0;
        margin: 0 60px 3rem;
    }

    .movie-card {
        width: 180px;
    }
}

.card-ppv-badge {
    position: absolute;
    top: 6px;
    right: 6px;
    background: rgba(0, 0, 0, 0.75);
    color: #48bb78;
    font-size: 0.65rem;
    font-weight: 700;
    padding: 2px 6px;
    border-radius: 6px;
    z-index: 10;
    backdrop-filter: blur(4px);
    text-transform: uppercase;
    letter-spacing: 0.03em;
    border: 1px solid rgba(72, 187, 120, 0.3);
}

.card-ppv-badge.ppv-paid {
    background: rgba(232, 68, 90, 0.85);
    color: #ffffff;
    border-color: rgba(255, 255, 255, 0.4);
}
</style>