<script setup lang="ts">
import { ref, onMounted } from 'vue';
import UserDashboardLayout from '@/layouts/UserDashboardLayout.vue';
import { router, Link } from '@inertiajs/vue3';
import CategorySection from '../movie/partials/CategorySection.vue';

import ShowPlayerSerieController from '@/actions/App/Http/Controllers/Serie/ShowPlayerSerieController';
import ShowPlayerTrailerSerieController from '@/actions/App/Http/Controllers/Serie/ShowPlayerTrailerSerieController';
import ShowPlayerChapterController from '@/actions/App/Http/Controllers/Serie/ShowPlayerChapterController';
import AddToWatchlistController from '@/actions/App/Http/Controllers/Watchlist/AddToWatchlistController';
import RemoveToWatchlistController from '@/actions/App/Http/Controllers/Watchlist/RemoveToWatchlistController';
import AddToFollowController from '@/actions/App/Http/Controllers/Follow/AddToFollowController';
import RemoveToFollowController from '@/actions/App/Http/Controllers/Follow/RemoveToFollowController';
import ShowCategoryController from '@/actions/App/Http/Controllers/Category/ShowCategoryController';
import ShowCreatorProfileController from '@/actions/App/Http/Controllers/CreatorProfile/ShowCreatorProfileController';

const props = defineProps<{
    serie: any;
}>();

// Helper function to generate route for show category
const showCategoryRoute = (categoryId: number) => ShowCategoryController.url({ category: categoryId });

const followButtonLoading = ref(false);
const unfollowButtonLoading = ref(false);
const watchlistButtonLoading = ref(false);
const unwatchlistButtonLoading = ref(false);

function addToWatchlist() {
    watchlistButtonLoading.value = true;
    router.post(AddToWatchlistController.url({
        id: props.serie.id,
        type: 'serie',
    }), {}, {
        preserveScroll: true,
        onSuccess: () => {
            watchlistButtonLoading.value = false;
        },
    });
}

function removeFromWatchlist() {
    unwatchlistButtonLoading.value = true;
    router.post(RemoveToWatchlistController.url({
        id: props.serie.id,
        type: 'serie',
    }), {}, {
        preserveScroll: true,
        onSuccess: () => {
            unwatchlistButtonLoading.value = false;
        },
    });
}

function playSerie() {
    router.get(ShowPlayerSerieController.url({
        serie: props.serie.id,
    }));
}

function playTrailerSerie() {
    router.get(ShowPlayerTrailerSerieController.url({
        serie: props.serie.id,
    }));
}   

function followUser(userId: number) {
    followButtonLoading.value = true;
    router.post(AddToFollowController.url({ user: userId }), {}, {
        preserveScroll: true,
        onSuccess: () => {
            followButtonLoading.value = false;
        },
    });
}

function unfollowUser(userId: number) {
    unfollowButtonLoading.value = true;
    router.post(RemoveToFollowController.url({ user: userId }), {}, {
        preserveScroll: true,
        onSuccess: () => {
            unfollowButtonLoading.value = false;
        },
    });
}

function playChapter(seasonId: number, chapterId: number) {
    router.visit(ShowPlayerChapterController.url({
        serie: props.serie.id,
        season: seasonId,
        chapter: chapterId,
    }));
}

// Track view on page load
onMounted(async () => {
    try {
        const contentId = props.serie?.content?.id;
        if (!contentId) return;
        await fetch(`/content/${contentId}/view`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-XSRF-TOKEN': decodeURIComponent(document.cookie.match(/XSRF-TOKEN=([^;]+)/)?.[1] || ''),
            },
            credentials: 'same-origin',
        });
    } catch (e) {
        // Silent fail
    }
});
</script>

<template>
    <UserDashboardLayout 
        :title="`${serie.title} - ${$page.props.name || 'Impulsemedia'}`" 
        :headerTitle="`${serie.title} - ${$page.props.name || 'Impulsemedia'}`">
        <template #main-content>
            <div class="serie-banner"
                :style="{ background: `url(${serie.horizontal_image_url}) no-repeat`, backgroundPosition: 'center center', backgroundSize: 'cover' }"
                >
                <div class="header-gradient"></div>
                <div class="banner-content">
                    <div class="serie-title-container">
                        <h1 class="serie-title">{{ serie.title }}</h1>
                        <Link :href="showCategoryRoute(serie.category.id)" style="text-decoration: none;">
                            <span class="category-tag">{{ serie.category.name }}</span>
                        </Link>
                    </div>
                    <div class="uploader-info">
                        <img :src="serie.user.image_url" alt="Uploader Avatar" class="uploader-avatar" style="cursor: pointer;" @click="router.visit(ShowCreatorProfileController({ user: serie.user.username }))">
                        <span class="uploader-username" style="cursor: pointer;" @click="router.visit(ShowCreatorProfileController({ user: serie.user.username }))">@{{ serie.user.username }}</span>
                        <template v-if="serie.user.id !== $page.props.auth.user.id">
                            <button
                                v-if="!serie.user.is_followed"
                                @click="followUser(serie.user.id)"
                                :disabled="followButtonLoading"
                                class="follow-btn" id="followButton">
                                <i class="fa-solid fa-circle-notch fa-spin" v-if="followButtonLoading"></i>
                                Follow
                            </button>
                            <button
                                v-else
                                @click="unfollowUser(serie.user.id)"
                                :disabled="unfollowButtonLoading"
                                class="follow-btn" id="followButton"
                                style="background-color: var(--destructive-action-bg);"
                                >
                                <i class="fa-solid fa-circle-notch fa-spin"
                                v-if="unfollowButtonLoading"></i>
                                Unfollow
                            </button>
                        </template>
                    </div>
                    <div class="action-buttons-group">
                        <button v-if="!serie.watchlist" class="watchlist-button" id="watchlistBtn" @click="addToWatchlist" :disabled="watchlistButtonLoading">
                            <i class="fa-solid fa-circle-notch fa-spin"
                                v-if="watchlistButtonLoading"></i>
                            + Watchlist
                        </button>
                        <button v-else class="watchlist-button added" id="watchlistBtn" @click="removeFromWatchlist" :disabled="unwatchlistButtonLoading">
                            <i class="fa-solid fa-circle-notch fa-spin"
                                v-if="unwatchlistButtonLoading"></i>
                            ✓ Added
                        </button>
                    </div>
                </div>
            </div>
            <section class="content-section">
                <h2 class="description-title">Description</h2>
                <p class="description">
                    {{ serie.description }}
                </p>
                <button class="trailer-button" @click="playTrailerSerie">Trailer</button>
            </section>
    
            <!-- Seasons and Episodes Section -->
            <section v-if="serie.seasons && serie.seasons.length > 0" class="episodes-section">
                <h2 class="section-title">Episodes</h2>
                
                <div v-for="(season, seasonIndex) in serie.seasons" :key="season.id" class="season-container">
                    <h3 class="season-title">Season {{ seasonIndex + 1 }}</h3>
                    
                    <div v-if="season.chapters && season.chapters.length > 0" class="episodes-grid">
                        <div 
                            v-for="chapter in season.chapters" 
                            :key="chapter.id"
                            class="episode-card"
                            @click="playChapter(season.id, chapter.id)"
                        >
                            <div class="episode-thumbnail">
                                <img 
                                    :src="chapter.thumbnail_url" 
                                    :alt="`Episode ${chapter.chapter_number}`"
                                    class="episode-image"
                                />
                                <div class="play-overlay">
                                    <div class="play-button">▶</div>
                                </div>
                                <div class="episode-number">{{ chapter.chapter_number }}</div>
                            </div>
                            <div class="episode-info">
                                <h4 class="episode-title">{{ chapter.title }}</h4>
                                <p class="episode-meta">Episode {{ chapter.chapter_number }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div v-else class="no-episodes">
                        <p>No episodes available for this season.</p>
                    </div>
                </div>
            </section>
    
            <div class="series-section" style="margin-bottom: 80px;">
                <div class="section-header">
                    <div class="section-title-container">
                        <a href="javascript:void(0)" class="section-title">Suggested</a>
                        <a href="javascript:void(0)" style="text-decoration: none;">
                            <span class="category-tag category-tag-highlight">{{ serie.category.name }}</span>
                        </a>
                    </div>
                </div>
                <div class="series-row" data-slider="suggested">
                    <CategorySection :category="serie.category" />
                </div>
            </div>
            <br />
        </template>
    </UserDashboardLayout>
</template>

<style scoped>
.menu-section-title {
    color: #aaa;
    font-weight: 500;
    font-size: 0.9rem;
    text-transform: uppercase;
    padding: 0.5rem 0;
    margin-top: 1rem;
    border-top: 1px solid #444;
}

.menu-section-title:first-child {
    margin-top: 0;
    border-top: none;
}

.menu-item,
.category-item summary {
    display: flex;
    align-items: center;
    gap: 1rem;
    color: var(--text-light);
    text-decoration: none;
    padding: 0.8rem;
    border-radius: 8px;
    font-weight: 500;
    transition: background-color 0.2s;
}

.menu-item:hover,
.category-item summary:hover {
    background-color: rgba(255, 255, 255, 0.1);
}

.menu-item .menu-profile-icon {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    object-fit: cover;
}

.category-item summary {
    cursor: pointer;
    list-style: none;
    justify-content: space-between;
}

.category-item summary::-webkit-details-marker {
    display: none;
}

.category-item summary::after {
    content: '›';
    transform: rotate(90deg);
    transition: transform 0.2s;
}

.category-item[open]>summary::after {
    transform: rotate(-90deg);
}

.subcategory-list {
    padding-left: 1.5rem;
    list-style: none;
}

.subcategory-list .menu-item {
    padding-left: 0.5rem;
}

.subscription-item img {
    width: 32px;
    height: 32px;
    border-radius: 50%;
}

.menu-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.6);
    z-index: 1000;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.3s ease-in-out;
}

.menu-overlay.active {
    opacity: 1;
    pointer-events: auto;
}

.serie-banner {
    position: relative;
    width: 100%;
    height: 300px;
    background-size: cover;
    margin: 0;
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
    padding-bottom: 2rem;
}

.banner-content {
    position: relative;
    z-index: 2;
    padding: 0 4rem;

}

.serie-title-container {
    display: flex;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
    margin-bottom: 1rem;
}

.serie-title {
    font-size: 2.5rem;
    font-weight: 600;
    margin: 0;
}

.category-tag {
    background: rgba(255, 255, 255, 0.15);
    color: white;
    padding: 0.3rem 0.8rem;
    border-radius: 8px;
    font-size: 0.9rem;
    font-weight: 500;
    display: inline-block;
    text-decoration: none;
}

.category-tag-highlight {
    background-color: var(--primary-color);
    font-weight: 600;
    color: white;
}

.uploader-info {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 1.5rem;
}

.uploader-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
}

.uploader-username {
    font-size: 1.1rem;
    font-weight: 500;
}

.follow-btn {
    background: rgba(255, 255, 255, 0.2);
    color: white;
    border: none;
    border-radius: 15px;
    padding: 0.4rem 1rem;
    font-size: 0.8rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s var(--transition-bezier);
}

.follow-btn:hover {
    background: rgba(255, 255, 255, 0.3);
}

.header-gradient {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(to bottom, rgba(10, 10, 35, 0.3), var(--main-bg));
}

.action-buttons-group {
    display: flex;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
}

.play-button {
    background-color: var(--primary-color);
    color: white;
    border: none;
    border-radius: 25px;
    padding: 0.75rem 2rem;
    font-size: 1.1rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s var(--transition-bezier);
}

.play-button:hover {
    transform: scale(1.05);
}

.watchlist-button {
    background: rgba(255, 255, 255, 0.2);
    color: white;
    border: none;
    border-radius: 25px;
    padding: 0.75rem 1.5rem;
    font-size: 1rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s var(--transition-bezier);
}

.watchlist-button:hover {
    background: rgba(255, 255, 255, 0.3);
}

.watchlist-button.added {
    background-color: #4CAF50;
    /* Verde para indicar que fue agregado */
    color: white;
}

.content-section {
    margin-top: 2rem;
    margin-bottom: 3rem;
    padding: 0 4rem;
}

.description-title {
    font-size: 1.75rem;
    font-weight: 600;
    margin-bottom: 1rem;
    text-align: left;
}

.description {
    line-height: 1.6;
    margin-bottom: 1.5rem;
}

.trailer-button {
    background-color: transparent;
    border: 2px solid var(--primary-color);
    color: var(--primary-color);
    border-radius: 20px;
    padding: 0.5rem 2rem;
    cursor: pointer;
    transition: transform 0.3s var(--transition-bezier);
}

.trailer-button:hover {
    transform: scale(1.1);
}

.series-section {
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

.section-title-container {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.section-title {
    font-size: 1.75rem;
    font-weight: 600;
    margin-bottom: 0;
    color: inherit;
    text-decoration: none;
}

.series-row {
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

.series-row::-webkit-scrollbar {
    display: none;
}

.series-row.grabbing {
    cursor: grabbing;
}

.serie-card {
    flex: 0 0 auto;
    width: 110px;
    aspect-ratio: 2/3;
    border-radius: 12px;
    overflow: hidden;
    transition: all 0.3s var(--transition-bezier);
    scroll-snap-align: start;
    position: relative;
}

.serie-card img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.slider-arrow {
    position: absolute;
    top: 50%;
    transform: translateY(calc(-50% + 0.5rem));
    width: 40px;
    height: 40px;
    background: rgba(128, 128, 128, 0.3);
    border-radius: 50%;
    display: none;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    z-index: 100;
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
    color: var(--primary-color);
}

.nav-icon {
    width: 24px;
    height: 24px;
}

.nav-icon.profile {
    border-radius: 50%;
}

@media (min-width: 1200px) {
    :root {
        --container-padding: 4rem;
    }

    .app-wrapper {
        max-width: 100%;
        margin: 0;
        padding: 0 0 80px;
    }

    .header {
        max-width: 1600px;
        margin: 0 auto;
        padding: 0.75rem 4rem;
        box-sizing: border-box;
    }

    .logo-icon {
        position: absolute;
        left: 50%;
        transform: translateX(-50%);
    }

    .serie-banner {
        height: 400px;
        width: 100%;
    }

    .series-section {
        padding: 0;
        margin: 3rem 80px;
    }

    .series-row {
        padding: 2rem;
        margin: -2rem;
    }

    .serie-card {
        width: 180px;
    }

    .serie-card:hover {
        transform: scale(1.2);
        z-index: 20;
    }

    .serie-card:hover~.serie-card {
        transform: translateX(30px);
    }

    .slider-arrow {
        display: flex;
    }

    .slider-arrow.prev {
        left: -60px;
    }

    .slider-arrow.next {
        right: -60px;
    }
}

@media (max-width: 768px) {
    :root {
        --container-padding: 1.5rem;
    }

    .serie-banner {
        height: 250px;
    }

    .serie-title {
        font-size: 2rem;
    }

    .content-section {
        padding: 0 1.5rem;
    }

    .banner-content {
        padding: 0 1.5rem;
    }

    .series-section {
        padding: 0 1.5rem;
    }
}

/* Episodes Section Styles */
.episodes-section {
    margin: 3rem 4rem;
}

.episodes-section .section-title {
    font-size: 2rem;
    font-weight: 600;
    margin-bottom: 2rem;
    color: white;
}

.season-container {
    margin-bottom: 3rem;
}

.season-title {
    font-size: 1.5rem;
    font-weight: 600;
    margin-bottom: 1.5rem;
    color: white;
    border-bottom: 2px solid var(--primary-color);
    padding-bottom: 0.5rem;
    display: inline-block;
}

.episodes-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1.5rem;
}

.episode-card {
    background: rgba(255, 255, 255, 0.05);
    border-radius: 12px;
    overflow: hidden;
    cursor: pointer;
    transition: all 0.3s var(--transition-bezier);
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.episode-card:hover {
    transform: translateY(-5px);
    background: rgba(255, 255, 255, 0.1);
    border-color: var(--primary-color);
}

.episode-thumbnail {
    position: relative;
    width: 100%;
    aspect-ratio: 16/9;
    overflow: hidden;
}

.episode-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.episode-card:hover .episode-image {
    transform: scale(1.05);
}

.play-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.episode-card:hover .play-overlay {
    opacity: 1;
}

.play-button {
    width: 60px;
    height: 60px;
    background: var(--primary-color);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 24px;
    transform: scale(0.8);
    transition: transform 0.3s ease;
}

.episode-card:hover .play-button {
    transform: scale(1);
}

.episode-number {
    position: absolute;
    top: 12px;
    left: 12px;
    background: rgba(0, 0, 0, 0.8);
    color: white;
    padding: 4px 8px;
    border-radius: 6px;
    font-size: 0.85rem;
    font-weight: 600;
}

.episode-info {
    padding: 1rem;
}

.episode-title {
    font-size: 1.1rem;
    font-weight: 600;
    color: white;
    margin: 0 0 0.5rem 0;
    line-height: 1.4;
}

.episode-meta {
    font-size: 0.9rem;
    color: rgba(255, 255, 255, 0.7);
    margin: 0;
}

.no-episodes {
    text-align: center;
    padding: 2rem;
    color: rgba(255, 255, 255, 0.6);
    font-style: italic;
}

/* Mobile responsive for episodes */
@media (max-width: 768px) {
    .episodes-section {
        margin: 2rem 1.5rem;
    }
    
    .episodes-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .episode-card {
        margin-bottom: 0;
    }
    
    .episode-thumbnail {
        aspect-ratio: 16/9;
    }
}
</style>