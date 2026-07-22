<script setup lang="ts">
import { ref, onMounted } from 'vue';
import UserDashboardLayout from '@/layouts/UserDashboardLayout.vue';
import { router, Link } from '@inertiajs/vue3';
import CategorySection from './partials/CategorySection.vue';

import AddToWatchlistController from '@/actions/App/Http/Controllers/Watchlist/AddToWatchlistController';
import RemoveToWatchlistController from '@/actions/App/Http/Controllers/Watchlist/RemoveToWatchlistController';
import ShowPlayerMovieController from '@/actions/App/Http/Controllers/Movie/ShowPlayerMovieController';
import ShowPlayerTrailerMovieController from '@/actions/App/Http/Controllers/Movie/ShowPlayerTrailerMovieController';
import AddToFollowController from '@/actions/App/Http/Controllers/Follow/AddToFollowController';
import RemoveToFollowController from '@/actions/App/Http/Controllers/Follow/RemoveToFollowController';
import ShowCategoryController from '@/actions/App/Http/Controllers/Category/ShowCategoryController';
import ShowCreatorProfileController from '@/actions/App/Http/Controllers/CreatorProfile/ShowCreatorProfileController';

const props = defineProps<{
    movie: any;
    hasFullAccess?: boolean;
    isPurchased?: boolean;
    ppvPrice?: number;
}>();

const followButtonLoading = ref(false);
const unfollowButtonLoading = ref(false);
const watchlistButtonLoading = ref(false);
const unwatchlistButtonLoading = ref(false);
const checkoutLoading = ref(false);

function buyPpvAccess() {
    if (!props.movie?.content?.id) return;
    checkoutLoading.value = true;
    router.post(`/ppv/checkout/${props.movie.content.id}`, {}, {
        onFinish: () => {
            checkoutLoading.value = false;
        }
    });
}

function addToWatchlist() {
    watchlistButtonLoading.value = true;
    router.post(AddToWatchlistController.url({
        id: props.movie.id,
        type: 'movie',
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
        id: props.movie.id,
        type: 'movie',
    }), {}, {
        preserveScroll: true,
        onSuccess: () => {
            unwatchlistButtonLoading.value = false;
        },
    });
}

function playMovie() {
    router.get(ShowPlayerMovieController.url({
        movie: props.movie.id,
    }));
}

function playTrailerMovie() {
    router.get(ShowPlayerTrailerMovieController.url({
        movie: props.movie.id,
    }));
}   

function followUser(userId: number) {
    followButtonLoading.value = true;
    router.post(AddToFollowController({user: userId}), {}, {
        preserveScroll: true,
        onSuccess: () => {
            followButtonLoading.value = false;
        },
    });
}

function unfollowUser(userId: number) {
    unfollowButtonLoading.value = true;
    router.post(RemoveToFollowController({user: userId}), {}, {
        preserveScroll: true,
        onSuccess: () => {
            unfollowButtonLoading.value = false;
        },
    });
}

// Track view on page load
onMounted(async () => {
    try {
        const contentId = props.movie?.content?.id;
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
        :title="`${movie.title} - ${$page.props.name || 'Impulsemedia'}`" 
        :headerTitle="`${movie.title} - ${$page.props.name || 'Impulsemedia'}`">
        <template #main-content>
            <div class="movie-banner"
                :style="{ background: `url(${movie.horizontal_image_url}) no-repeat`, backgroundPosition: 'center center', backgroundSize: 'cover' }"
                >
                <div class="header-gradient"></div>
                <div class="banner-content">
                    <div class="movie-title-container">
                        <h1 class="movie-title">{{ movie.title }}</h1>
                        <Link :href="ShowCategoryController({ category: movie.category.id })" style="text-decoration: none;">
                            <span class="category-tag">{{ movie.category.name }}</span>
                        </Link>
                    </div>
                    <div class="uploader-info">
                        <img :src="movie.user.image_url" alt="Uploader Avatar" class="uploader-avatar" style="cursor: pointer;" @click="router.visit(ShowCreatorProfileController({ user: movie.user.username }))">
                        <span class="uploader-username" style="cursor: pointer;" @click="router.visit(ShowCreatorProfileController({ user: movie.user.username }))">@{{ movie.user.username }}</span>
                        <template v-if="movie.user.id !== $page.props.auth.user.id">
                            <button
                                v-if="!movie.user.is_followed"
                                @click="followUser(movie.user.id)"
                                :disabled="followButtonLoading"
                                class="follow-btn" id="followButton">
                                <i class="fa-solid fa-circle-notch fa-spin"
                                    v-if="followButtonLoading" ></i>
                                Follow
                            </button>
                            <button
                                v-else
                                @click="unfollowUser(movie.user.id)"
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
                        <!-- Unlocked Access (Free, Purchased, Member, or Owner/Admin) -->
                        <template v-if="hasFullAccess">
                            <button class="play-button" @click="playMovie">
                                <i class="fa-solid fa-play"></i> Watch Movie
                            </button>
                            <button v-if="movie.trailer_video_url" class="trailer-button-inline" @click="playTrailerMovie">
                                <i class="fa-solid fa-film"></i> Trailer
                            </button>
                            <span v-if="isPurchased" class="unlocked-badge">
                                <i class="fa-solid fa-circle-check"></i> Purchased
                            </span>
                            <span v-else-if="movie.content?.allow_membership" class="unlocked-badge membership">
                                <i class="fa-solid fa-crown"></i> Included
                            </span>
                        </template>

                        <!-- Locked Access (Requires PPV purchase) -->
                        <template v-else>
                            <button 
                                class="ppv-buy-button-primary" 
                                @click="buyPpvAccess"
                                :disabled="checkoutLoading"
                            >
                                <i v-if="checkoutLoading" class="fa-solid fa-circle-notch fa-spin"></i>
                                <i v-else class="fa-solid fa-ticket"></i>
                                Buy PPV ${{ Number(movie.content?.ppv_price || 0).toFixed(2) }}
                            </button>
                            <button class="play-button-preview" @click="playMovie">
                                <i class="fa-solid fa-eye"></i> Free Preview (5 min)
                            </button>
                            <button v-if="movie.trailer_video_url" class="trailer-button-inline" @click="playTrailerMovie">
                                <i class="fa-solid fa-film"></i> Trailer
                            </button>
                        </template>
                        <button v-if="!movie.watchlist" class="watchlist-button" id="watchlistBtn" @click="addToWatchlist" :disabled="watchlistButtonLoading">
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
                    {{ movie.description }}
                </p>
            </section>
    
    
            <div class="movies-section" style="margin-bottom: 80px;">
                <div class="section-header">
                    <div class="section-title-container">
                        <a href="javascript:void(0)" class="section-title">Suggested</a>
                        <a href="javascript:void(0)" style="text-decoration: none;">
                            <span class="category-tag category-tag-highlight">{{ movie.category.name }}</span>
                        </a>
                    </div>
                </div>
                <div class="movies-row" data-slider="suggested">
                    <CategorySection :category="movie.category" />
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

.movie-banner {
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

.movie-title-container {
    display: flex;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
    margin-bottom: 1rem;
}

.movie-title {
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
}

.movie-card img {
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

    .movie-banner {
        height: 400px;
        width: 100%;
    }

    .movies-section {
        padding: 0;
        margin: 3rem 80px;
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
        left: -60px;
    }

    .slider-arrow.next {
        right: -60px;
    }
}

@media (max-width: 768px) {
    :root {
        --container-padding: 1rem;
    }

    .movie-banner {
        height: auto;
        min-height: 320px;
        padding-top: 4.5rem;
        padding-bottom: 1.5rem;
        box-sizing: border-box;
    }

    .banner-content {
        padding: 0 1rem;
    }

    .movie-title-container {
        gap: 0.5rem;
        margin-bottom: 0.5rem;
    }

    .movie-title {
        font-size: 1.5rem;
        line-height: 1.25;
    }

    .category-tag {
        font-size: 0.75rem;
        padding: 0.2rem 0.6rem;
    }

    .uploader-info {
        gap: 0.5rem;
        margin-bottom: 1rem;
    }

    .uploader-avatar {
        width: 32px;
        height: 32px;
    }

    .uploader-username {
        font-size: 0.9rem;
    }

    .follow-btn {
        padding: 0.3rem 0.8rem;
        font-size: 0.75rem;
    }

    .action-buttons-group {
        gap: 0.5rem;
    }

    .play-button,
    .ppv-buy-button-primary,
    .play-button-preview,
    .trailer-button-inline,
    .watchlist-button {
        font-size: 0.85rem;
        padding: 0.6rem 1.1rem;
        border-radius: 20px;
    }

    .content-section {
        padding: 0 1rem;
        margin-top: 1.5rem;
        margin-bottom: 2rem;
    }

    .description-title {
        font-size: 1.3rem;
        margin-bottom: 0.5rem;
    }

    .description {
        font-size: 0.9rem;
        line-height: 1.5;
        margin-bottom: 1rem;
    }

    .movies-section {
        padding: 0 1rem;
    }
}

.trailer-button-inline {
    background: rgba(255, 255, 255, 0.15);
    color: #fff;
    border: 1px solid rgba(255, 255, 255, 0.3);
    padding: 0.75rem 1.5rem;
    border-radius: 24px;
    font-size: 0.95rem;
    font-weight: 600;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.2s;
}
.trailer-button-inline:hover {
    background: rgba(255, 255, 255, 0.25);
}

.ppv-buy-button {
    background: linear-gradient(135deg, #e8445a 0%, #d03050 100%);
    color: #fff;
    border: none;
    padding: 0.75rem 1.5rem;
    border-radius: 24px;
    font-size: 0.95rem;
    font-weight: 700;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    box-shadow: 0 4px 15px rgba(232, 68, 90, 0.4);
    transition: all 0.2s;
}
.ppv-buy-button:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(232, 68, 90, 0.6);
}

.ppv-buy-button-primary {
    background: linear-gradient(135deg, #e8445a 0%, #b81c40 100%);
    color: #ffffff;
    border: none;
    border-radius: 25px;
    padding: 0.75rem 1.75rem;
    font-size: 1.1rem;
    font-weight: 700;
    cursor: pointer;
    box-shadow: 0 4px 15px rgba(232, 68, 90, 0.4);
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.3s ease;
}

.ppv-buy-button-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(232, 68, 90, 0.6);
}

.play-button-preview {
    background: rgba(255, 255, 255, 0.15);
    color: #ffffff;
    border: 1px solid rgba(255, 255, 255, 0.3);
    border-radius: 25px;
    padding: 0.75rem 1.5rem;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    backdrop-filter: blur(8px);
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.3s ease;
}

.play-button-preview:hover {
    background: rgba(255, 255, 255, 0.25);
    transform: translateY(-2px);
}

.unlocked-badge {
    background: rgba(16, 185, 129, 0.2);
    color: #10b981;
    border: 1px solid rgba(16, 185, 129, 0.4);
    border-radius: 20px;
    padding: 0.5rem 1rem;
    font-size: 0.9rem;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
}

.unlocked-badge.membership {
    background: rgba(124, 58, 237, 0.2);
    color: #a78bfa;
    border-color: rgba(124, 58, 237, 0.4);
}
</style>
