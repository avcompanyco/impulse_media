<script setup lang="ts">
import { ref, reactive } from 'vue';
import UserDashboardLayout from '@/layouts/UserDashboardLayout.vue';
import { router, Link, usePage } from '@inertiajs/vue3';
import type { User } from '@/types';

import MovieCard from '../channel/partials/MovieCard.vue';
import SerieCard from '../channel/partials/SerieCard.vue';
import ShortCard from '../channel/partials/ShortCard.vue';

import AddToFollowController from '@/actions/App/Http/Controllers/Follow/AddToFollowController';
import RemoveToFollowController from '@/actions/App/Http/Controllers/Follow/RemoveToFollowController';
import ShowMovieController from '@/actions/App/Http/Controllers/Movie/ShowMovieController';
import ShowSerieController from '@/actions/App/Http/Controllers/Serie/ShowSerieController';

const props = defineProps<{
    creator: User & { bio?: string | null; external_link?: string | null };
    movies: any[];
    series: any[];
    shorts: any[];
}>();

const page = usePage();

// Make creator reactive so is_followed updates in the UI
const creatorData = reactive({ ...props.creator });

const activeTab = ref<'movies' | 'series' | 'shorts'>('movies');
const followButtonLoading = ref(false);
const unfollowButtonLoading = ref(false);

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

function addToFollow(userId: number) {
    followButtonLoading.value = true;
    router.post(AddToFollowController({ user: userId }), {}, {
        preserveScroll: true,
        onSuccess: () => {
            creatorData.is_followed = true;
            creatorData.followers_count = (creatorData.followers_count || 0) + 1;
        },
        onFinish: () => {
            followButtonLoading.value = false;
        },
    });
}

function removeFromFollow(userId: number) {
    unfollowButtonLoading.value = true;
    router.post(RemoveToFollowController({ user: userId }), {}, {
        preserveScroll: true,
        onSuccess: () => {
            creatorData.is_followed = false;
            creatorData.followers_count = Math.max(0, (creatorData.followers_count || 1) - 1);
        },
        onFinish: () => {
            unfollowButtonLoading.value = false;
        },
    });
}

function formatExternalLink(link: string): string {
    if (!link.startsWith('http://') && !link.startsWith('https://')) {
        return 'https://' + link;
    }
    return link;
}

function displayExternalLink(link: string): string {
    return link.replace(/^https?:\/\//, '').replace(/\/$/, '');
}

// Instant Smooth Scroll helper
function scrollCreatorRow(tab: 'movies' | 'series' | 'shorts', direction: 'left' | 'right') {
    const container = document.getElementById(`creator-row-${tab}`);
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
        :title="`${creator.name} - ${$page.props.name || 'Impulsemedia'}`" 
        :headerTitle="`${creator.name} - ${$page.props.name || 'Impulsemedia'}`">

        <div class="creator-profile-container">
            <!-- Creator Profile Header -->
            <section class="creator-header">
                <div class="creator-info-card">
                    <img :src="creatorData.image_url" alt="Creator Avatar" class="creator-avatar">
                    <div class="creator-details">
                        <h1 class="creator-name">{{ creatorData.name }}</h1>
                        <p class="creator-username">@{{ creatorData.username }}</p>
                        <div class="creator-stats">
                            {{ creatorData.followers_count }} Followers &bull; 
                            {{ creatorData.followings_count }} Following &bull; 
                            {{ creatorData.content_count }} Videos
                        </div>
                    </div>
                    <template v-if="creatorData.id !== $page.props.auth.user.id">
                        <button 
                            v-if="!creatorData.is_followed" 
                            class="creator-action-btn" 
                            @click="addToFollow(creatorData.id)"
                            :disabled="followButtonLoading">
                            <i class="fa-solid fa-circle-notch fa-spin" v-if="followButtonLoading"></i>
                            Follow
                        </button>
                        <button 
                            v-else 
                            class="creator-action-btn following" 
                            @click="removeFromFollow(creatorData.id)"
                            :disabled="unfollowButtonLoading">
                            <i class="fa-solid fa-circle-notch fa-spin" v-if="unfollowButtonLoading"></i>
                            <span class="following-text">Following</span>
                            <span class="unfollow-text">Unfollow</span>
                        </button>
                    </template>
                </div>

                <!-- Bio & External Link -->
                <div class="creator-bio-section" v-if="creatorData.bio || creatorData.external_link">
                    <p class="creator-bio" v-if="creatorData.bio">{{ creatorData.bio }}</p>
                    <a 
                        v-if="creatorData.external_link" 
                        :href="formatExternalLink(creatorData.external_link)" 
                        target="_blank" 
                        rel="noopener noreferrer" 
                        class="creator-external-link">
                        <i class="fa-solid fa-link"></i>
                        {{ displayExternalLink(creatorData.external_link) }}
                    </a>
                </div>
            </section>

            <!-- Content Tabs -->
            <nav class="creator-tabs">
                <button 
                    class="tab-btn" 
                    :class="{ active: activeTab === 'movies' }" 
                    @click="activeTab = 'movies'">
                    Movies ({{ movies.length }})
                </button>
                <button 
                    class="tab-btn" 
                    :class="{ active: activeTab === 'series' }" 
                    @click="activeTab = 'series'">
                    Series ({{ series.length }})
                </button>
                <button 
                    class="tab-btn" 
                    :class="{ active: activeTab === 'shorts' }" 
                    @click="activeTab = 'shorts'">
                    Shorts ({{ shorts.length }})
                </button>
            </nav>

            <!-- Content Rows with SVG Navigation -->
            <main class="creator-content" style="margin-bottom: 80px;">
                <!-- Movies Row -->
                <div v-if="activeTab === 'movies'" class="tab-content-wrapper">
                    <div v-if="movies.length > 0" class="slider-wrapper">
                        <button 
                            type="button" 
                            class="slider-arrow prev-arrow" 
                            aria-label="Scroll left"
                            @click="scrollCreatorRow('movies', 'left')"
                        >
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M15 18l-6-6 6-6" />
                            </svg>
                        </button>

                        <div id="creator-row-movies" class="movies-row">
                            <div v-for="movie in movies" :key="`movie_${movie.id}`" class="movie-card">
                                <Link :href="ShowMovieController.url({ movie: movie.contentable.id })">
                                    <div v-if="movie.contentable?.content || movie.content" class="card-ppv-badge" :class="{ 'ppv-paid': isPpvPaid(movie.contentable?.content || movie.content) }">
                                        {{ getBadgeText(movie.contentable?.content || movie.content) }}
                                    </div>
                                    <img :src="movie.contentable.vertical_image_url || movie.contentable.horizontal_image_url || '/images/default_poster.webp'" alt="Movie Poster" loading="lazy">
                                </Link>
                            </div>
                        </div>

                        <button 
                            type="button" 
                            class="slider-arrow next-arrow" 
                            aria-label="Scroll right"
                            @click="scrollCreatorRow('movies', 'right')"
                        >
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M9 18l6-6-6-6" />
                            </svg>
                        </button>
                    </div>
                    <p v-else class="empty-message">No movies uploaded yet.</p>
                </div>

                <!-- Series Row -->
                <div v-if="activeTab === 'series'" class="tab-content-wrapper">
                    <div v-if="series.length > 0" class="slider-wrapper">
                        <button 
                            type="button" 
                            class="slider-arrow prev-arrow" 
                            aria-label="Scroll left"
                            @click="scrollCreatorRow('series', 'left')"
                        >
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M15 18l-6-6 6-6" />
                            </svg>
                        </button>

                        <div id="creator-row-series" class="movies-row">
                            <div v-for="serie in series" :key="`serie_${serie.id}`" class="movie-card">
                                <Link :href="ShowSerieController.url({ serie: serie.contentable.id })">
                                    <div v-if="serie.contentable?.content || serie.content" class="card-ppv-badge" :class="{ 'ppv-paid': isPpvPaid(serie.contentable?.content || serie.content) }">
                                        {{ getBadgeText(serie.contentable?.content || serie.content) }}
                                    </div>
                                    <img :src="serie.contentable.vertical_image_url || serie.contentable.horizontal_image_url || '/images/default_poster.webp'" alt="Serie Poster" loading="lazy">
                                </Link>
                            </div>
                        </div>

                        <button 
                            type="button" 
                            class="slider-arrow next-arrow" 
                            aria-label="Scroll right"
                            @click="scrollCreatorRow('series', 'right')"
                        >
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M9 18l6-6-6-6" />
                            </svg>
                        </button>
                    </div>
                    <p v-else class="empty-message">No series uploaded yet.</p>
                </div>

                <!-- Shorts Row -->
                <div v-if="activeTab === 'shorts'" class="tab-content-wrapper">
                    <div v-if="shorts.length > 0" class="slider-wrapper">
                        <button 
                            type="button" 
                            class="slider-arrow prev-arrow" 
                            aria-label="Scroll left"
                            @click="scrollCreatorRow('shorts', 'left')"
                        >
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M15 18l-6-6 6-6" />
                            </svg>
                        </button>

                        <div id="creator-row-shorts" class="movies-row">
                            <div v-for="short in shorts" :key="`short_${short.id}`" class="movie-card">
                                <ShortCard :short="short.contentable" />
                            </div>
                        </div>

                        <button 
                            type="button" 
                            class="slider-arrow next-arrow" 
                            aria-label="Scroll right"
                            @click="scrollCreatorRow('shorts', 'right')"
                        >
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M9 18l6-6-6-6" />
                            </svg>
                        </button>
                    </div>
                    <p v-else class="empty-message">No shorts uploaded yet.</p>
                </div>
            </main>
        </div>
    </UserDashboardLayout>
</template>

<style scoped>
.creator-profile-container {
    padding-bottom: 80px;
}

/* Creator Profile Header */
.creator-header {
    padding: 2rem 1rem 1rem 1rem;
}

.creator-info-card {
    background-color: rgba(255, 255, 255, 0.04);
    backdrop-filter: blur(12px);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 20px;
    padding: 1.5rem;
    display: flex;
    align-items: center;
    gap: 1.25rem;
}

.creator-avatar {
    width: 84px;
    height: 84px;
    border-radius: 50%;
    object-fit: cover;
    flex-shrink: 0;
    border: 3px solid #e8445a;
    box-shadow: 0 4px 14px rgba(232, 68, 90, 0.4);
}

.creator-details {
    flex-grow: 1;
}

.creator-name {
    font-size: 1.75rem;
    font-weight: 800;
    margin: 0 0 0.1rem 0;
    color: #ffffff;
}

.creator-username {
    font-size: 0.95rem;
    color: rgba(255, 255, 255, 0.6);
    margin: 0 0 0.4rem 0;
}

.creator-stats {
    color: rgba(255, 255, 255, 0.75);
    font-size: 0.85rem;
    font-weight: 500;
}

.creator-action-btn {
    background: #e8445a;
    color: white;
    border: none;
    padding: 0.65rem 1.4rem;
    border-radius: 24px;
    font-weight: 700;
    cursor: pointer;
    flex-shrink: 0;
    margin-left: auto;
    transition: all 0.2s ease;
    box-shadow: 0 4px 12px rgba(232, 68, 90, 0.4);
}

.creator-action-btn:hover {
    transform: scale(1.05);
    background: #ff4d67;
}

.creator-action-btn.following {
    background-color: transparent;
    border: 2px solid #e8445a;
    color: #e8445a;
    box-shadow: none;
}

.creator-action-btn.following .unfollow-text {
    display: none;
}

.creator-action-btn.following:hover {
    background-color: rgba(220, 53, 69, 0.15);
    border-color: #dc3545;
    color: #dc3545;
}

.creator-action-btn.following:hover .following-text {
    display: none;
}

.creator-action-btn.following:hover .unfollow-text {
    display: inline;
}

.creator-action-btn:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}

/* Bio Section */
.creator-bio-section {
    background-color: rgba(255, 255, 255, 0.03);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 14px;
    padding: 1rem 1.5rem;
    margin-top: 1rem;
}

.creator-bio {
    color: rgba(255, 255, 255, 0.85);
    font-size: 0.95rem;
    line-height: 1.5;
    margin: 0 0 0.75rem 0;
    white-space: pre-line;
}

.creator-external-link {
    color: #e8445a;
    text-decoration: none;
    font-size: 0.9rem;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    transition: opacity 0.2s;
}

.creator-external-link:hover {
    opacity: 0.8;
    text-decoration: underline;
}

/* Tabs */
.creator-tabs {
    display: flex;
    justify-content: flex-start;
    gap: 1.5rem;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    margin: 0 1rem 2rem 1rem;
    overflow-x: auto;
}

.creator-tabs::-webkit-scrollbar {
    display: none;
}

.tab-btn {
    background: none;
    border: none;
    color: rgba(255, 255, 255, 0.6);
    font-size: 1.05rem;
    font-weight: 600;
    padding: 0.75rem 0.5rem;
    cursor: pointer;
    border-bottom: 3px solid transparent;
    transition: all 0.2s;
    white-space: nowrap;
}

.tab-btn:hover {
    color: #ffffff;
}

.tab-btn.active {
    color: #ffffff;
    border-bottom-color: #e8445a;
}

/* Content Slider Layout */
.creator-content {
    padding: 0 1rem;
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

/* Disney+ / Netflix Arrow Buttons */
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

.empty-message {
    text-align: center;
    color: rgba(255, 255, 255, 0.5);
    font-size: 1.1rem;
    padding: 3rem 0;
    background: rgba(255, 255, 255, 0.02);
    border-radius: 16px;
    border: 1px dashed rgba(255, 255, 255, 0.1);
}

@media (min-width: 1200px) {
    .creator-header {
        padding: 2rem 4rem 1rem 4rem;
    }

    .creator-avatar {
        width: 110px;
        height: 110px;
    }

    .creator-name {
        font-size: 2rem;
    }

    .creator-username {
        font-size: 1.05rem;
    }

    .creator-stats {
        font-size: 0.95rem;
    }

    .creator-tabs {
        margin: 0 4rem 2rem 4rem;
    }

    .creator-content {
        padding: 0 4rem;
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
