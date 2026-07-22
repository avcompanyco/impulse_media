<script setup lang="ts">
import UserDashboardLayout from '@/layouts/UserDashboardLayout.vue';
import { Link, usePage } from '@inertiajs/vue3';
import ShowMovieController from '@/actions/App/Http/Controllers/Movie/ShowMovieController';
import ShowSerieController from '@/actions/App/Http/Controllers/Serie/ShowSerieController';

const props = defineProps<{
    subcategory: any;
    subcategories: any[];
}>();

const page = usePage();

function mergeMoviesAndSeries(subcat: any) {
    if (!subcat) return [];
    const movies = subcat.movies || [];
    const series = subcat.series || [];
    return [...movies, ...series];
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

// Instant Smooth Scroll helper
function scrollSubcategoryRow(rowId: string, direction: 'left' | 'right') {
    const container = document.getElementById(`subcat-row-${rowId}`);
    if (container) {
        const scrollAmount = container.clientWidth * 0.75;
        container.scrollBy({
            left: direction === 'left' ? -scrollAmount : scrollAmount,
            behavior: 'smooth'
        });
    }
}

</script>

<template>
    <UserDashboardLayout 
        :title="`${subcategory.name} - ${$page.props.name || 'Impulsemedia'}`" 
        :headerTitle="`${subcategory.name} - ${$page.props.name || 'Impulsemedia'}`">
        
        <div class="subcategory-page-container">
            <h1 class="page-title">{{ subcategory.name }}</h1>

            <!-- Main Subcategory Section -->
            <div class="movies-section" style="margin-bottom: 40px;">
                <div class="slider-wrapper">
                    <!-- Native Instant SVG Arrow Left -->
                    <button 
                        type="button" 
                        class="slider-arrow prev-arrow" 
                        aria-label="Scroll left"
                        @click="scrollSubcategoryRow('main', 'left')"
                    >
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M15 18l-6-6 6-6" />
                        </svg>
                    </button>

                    <div id="subcat-row-main" class="movies-row" data-slider="slasher">
                        <div v-for="(item, index) in mergeMoviesAndSeries(subcategory)"
                            :key="`movie_${item.id}_card_${index + 1}`" class="movie-card">
                            <Link v-if="item.movie_video || item.duration !== undefined || item.video_url !== undefined" :href="ShowMovieController.url(item)">
                                <div v-if="item.content" class="card-ppv-badge" :class="{ 'ppv-paid': isPpvPaid(item.content) }">
                                    {{ getBadgeText(item.content) }}
                                </div>
                                <img :src="item.vertical_image_url || item.horizontal_image_url || '/images/default_poster.webp'" alt="Movie Poster" loading="lazy">
                            </Link>
                            <Link v-else :href="ShowSerieController.url(item)">
                                <div v-if="item.content" class="card-ppv-badge" :class="{ 'ppv-paid': isPpvPaid(item.content) }">
                                    {{ getBadgeText(item.content) }}
                                </div>
                                <img :src="item.vertical_image_url || item.horizontal_image_url || '/images/default_poster.webp'" alt="Series Poster" loading="lazy">
                            </Link>
                        </div>
                    </div>

                    <!-- Native Instant SVG Arrow Right -->
                    <button 
                        type="button" 
                        class="slider-arrow next-arrow" 
                        aria-label="Scroll right"
                        @click="scrollSubcategoryRow('main', 'right')"
                    >
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9 18l6-6-6-6" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Related Subcategories -->
            <div v-for="subcat in subcategories" :key="`subcat_${subcat.id}`" class="movies-section" style="margin-bottom: 80px;">
                <h2 class="section-title">{{ subcat.name }}</h2>
                <div class="slider-wrapper">
                    <!-- Native Instant SVG Arrow Left -->
                    <button 
                        type="button" 
                        class="slider-arrow prev-arrow" 
                        aria-label="Scroll left"
                        @click="scrollSubcategoryRow(String(subcat.id), 'left')"
                    >
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M15 18l-6-6 6-6" />
                        </svg>
                    </button>

                    <div :id="`subcat-row-${subcat.id}`" class="movies-row" data-slider="slasher">
                        <div v-for="(item, index) in mergeMoviesAndSeries(subcat)"
                            :key="`movie_${item.id}_card_${index + 1}`" class="movie-card">
                            <Link v-if="item.movie_video || item.duration !== undefined || item.video_url !== undefined" :href="ShowMovieController.url(item)">
                                <div v-if="item.content" class="card-ppv-badge" :class="{ 'ppv-paid': isPpvPaid(item.content) }">
                                    {{ getBadgeText(item.content) }}
                                </div>
                                <img :src="item.vertical_image_url || item.horizontal_image_url || '/images/default_poster.webp'" alt="Movie Poster" loading="lazy">
                            </Link>
                            <Link v-else :href="ShowSerieController.url(item)">
                                <div v-if="item.content" class="card-ppv-badge" :class="{ 'ppv-paid': isPpvPaid(item.content) }">
                                    {{ getBadgeText(item.content) }}
                                </div>
                                <img :src="item.vertical_image_url || item.horizontal_image_url || '/images/default_poster.webp'" alt="Series Poster" loading="lazy">
                            </Link>
                        </div>
                    </div>

                    <!-- Native Instant SVG Arrow Right -->
                    <button 
                        type="button" 
                        class="slider-arrow next-arrow" 
                        aria-label="Scroll right"
                        @click="scrollSubcategoryRow(String(subcat.id), 'right')"
                    >
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9 18l6-6-6-6" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </UserDashboardLayout>
</template>

<style scoped>
.subcategory-page-container {
    padding-bottom: 80px;
}

.page-title {
    font-size: 2.25rem;
    font-weight: 800;
    margin: 1.5rem 1rem 2rem 1rem;
    text-align: center;
    background: linear-gradient(135deg, #ffffff 0%, #a0aec0 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.movies-section {
    position: relative;
    padding: 0 1rem;
    margin-bottom: 2.5rem;
    overflow: visible;
}

.section-title {
    font-size: 1.5rem;
    font-weight: 700;
    margin-bottom: 1rem;
    color: #ffffff;
}

.slider-wrapper {
    position: relative;
    display: flex;
    align-items: center;
    width: 100%;
}

.movies-row {
    position: relative;
    display: flex;
    gap: 1rem;
    overflow-x: auto;
    padding: 0.5rem 0.25rem 1rem 0.25rem;
    width: 100%;
    scroll-snap-type: x mandatory;
    -ms-overflow-style: none;
    scrollbar-width: none;
    scroll-behavior: smooth;
    -webkit-overflow-scrolling: touch;
}

.movies-row::-webkit-scrollbar {
    display: none;
}

.movie-card {
    flex: 0 0 auto;
    width: 130px;
    aspect-ratio: 2/3;
    border-radius: 14px;
    overflow: hidden;
    transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.3s;
    scroll-snap-align: start;
    position: relative;
    display: block;
    text-decoration: none;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
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
    transition: transform 0.3s ease;
}

.movie-card:hover {
    transform: translateY(-5px) scale(1.04);
    box-shadow: 0 12px 24px rgba(0, 0, 0, 0.5);
    z-index: 10;
}

.movie-card:hover img {
    transform: scale(1.06);
}

/* Disney+ / Netflix Instant SVG Arrow Buttons */
.slider-arrow {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: rgba(15, 23, 42, 0.88);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.22);
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    z-index: 25;
    transition: transform 0.2s ease, background-color 0.2s ease, border-color 0.2s ease;
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.5);
    opacity: 1;
}

.slider-arrow svg {
    display: block;
    transition: transform 0.2s ease;
}

.slider-arrow:hover {
    background: #e8445a;
    border-color: #e8445a;
    transform: translateY(-50%) scale(1.12);
    box-shadow: 0 8px 20px rgba(232, 68, 90, 0.6);
}

.prev-arrow {
    left: -18px;
}

.next-arrow {
    right: -18px;
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

@media (min-width: 1200px) {
    .movies-section {
        padding: 0;
        margin: 0 60px 3rem;
    }

    .movie-card {
        width: 185px;
    }

    .prev-arrow {
        left: -24px;
    }

    .next-arrow {
        right: -24px;
    }
}

@media (max-width: 768px) {
    .slider-arrow {
        width: 36px;
        height: 36px;
    }

    .prev-arrow {
        left: -10px;
    }

    .next-arrow {
        right: -10px;
    }
}
</style>