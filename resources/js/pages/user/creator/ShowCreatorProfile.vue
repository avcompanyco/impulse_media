<script setup lang="ts">
import { ref, reactive } from 'vue';
import UserDashboardLayout from '@/layouts/UserDashboardLayout.vue';
import { router, Link } from '@inertiajs/vue3';
import type { User } from '@/types';

import MovieCard from '../channel/partials/MovieCard.vue';
import SerieCard from '../channel/partials/SerieCard.vue';
import ShortCard from '../channel/partials/ShortCard.vue';

import AddToFollowController from '@/actions/App/Http/Controllers/Follow/AddToFollowController';
import RemoveToFollowController from '@/actions/App/Http/Controllers/Follow/RemoveToFollowController';

const props = defineProps<{
    creator: User & { bio?: string | null; external_link?: string | null };
    movies: any[];
    series: any[];
    shorts: any[];
}>();

// Make creator reactive so is_followed updates in the UI
const creatorData = reactive({ ...props.creator });

const activeTab = ref<'movies' | 'series' | 'shorts'>('movies');
const followButtonLoading = ref(false);
const unfollowButtonLoading = ref(false);

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
</script>

<template>
    <UserDashboardLayout 
        :title="`${creator.name} - ${$page.props.name || 'Impulsemedia'}`" 
        :headerTitle="`${creator.name} - ${$page.props.name || 'Impulsemedia'}`">

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

        <!-- Content Grids -->
        <main class="creator-content" style="margin-bottom: 80px;">
            <!-- Movies -->
            <div v-if="activeTab === 'movies'" class="content-grid active">
                <MovieCard v-for="movie in movies" :key="`movie_${movie.id}`" :movie="movie.contentable" />
                <p v-if="movies.length === 0" class="empty-message">No movies uploaded yet.</p>
            </div>

            <!-- Series -->
            <div v-if="activeTab === 'series'" class="content-grid active">
                <SerieCard v-for="serie in series" :key="`serie_${serie.id}`" :serie="serie.contentable" />
                <p v-if="series.length === 0" class="empty-message">No series uploaded yet.</p>
            </div>

            <!-- Shorts -->
            <div v-if="activeTab === 'shorts'" class="content-grid active">
                <ShortCard v-for="short in shorts" :key="`short_${short.id}`" :short="short.contentable" />
                <p v-if="shorts.length === 0" class="empty-message">No shorts uploaded yet.</p>
            </div>
        </main>
        <br />
    </UserDashboardLayout>
</template>

<style scoped>
/* Creator Profile Header */
.creator-header {
    padding: 2rem 1rem 1rem 1rem;
}

.creator-info-card {
    background-color: var(--card-bg);
    border-radius: 16px;
    padding: 1.5rem;
    display: flex;
    align-items: center;
    gap: 1rem;
}

.creator-avatar {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    object-fit: cover;
    flex-shrink: 0;
    border: 3px solid var(--primary-color);
}

.creator-details {
    flex-grow: 1;
}

.creator-name {
    font-size: 1.5rem;
    font-weight: 700;
    margin: 0 0 0.1rem 0;
}

.creator-username {
    font-size: 0.95rem;
    color: #aaa;
    margin: 0 0 0.25rem 0;
}

.creator-stats {
    color: #ccc;
    font-size: 0.85rem;
}

.creator-action-btn {
    background-color: var(--primary-color);
    color: white;
    border: none;
    padding: 0.6rem 1.2rem;
    border-radius: 20px;
    font-weight: 500;
    cursor: pointer;
    flex-shrink: 0;
    margin-left: auto;
    transition: background-color 0.2s ease;
}

.creator-action-btn.following {
    background-color: transparent;
    border: 2px solid var(--primary-color);
    color: var(--primary-color);
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
    background-color: var(--card-bg);
    border-radius: 12px;
    padding: 1rem 1.5rem;
    margin-top: 1rem;
}

.creator-bio {
    color: #ddd;
    font-size: 0.95rem;
    line-height: 1.5;
    margin: 0 0 0.75rem 0;
    white-space: pre-line;
}

.creator-external-link {
    color: var(--primary-color);
    text-decoration: none;
    font-size: 0.9rem;
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
    gap: 1rem;
    border-bottom: 1px solid #333;
    margin: 0 1rem 2rem 1rem;
    overflow-x: auto;
}

.creator-tabs::-webkit-scrollbar {
    display: none;
}

.tab-btn {
    background: none;
    border: none;
    color: #aaa;
    font-size: 1rem;
    font-weight: 500;
    padding: 0.75rem 0.5rem;
    cursor: pointer;
    border-bottom: 3px solid transparent;
    transition: all 0.2s;
    white-space: nowrap;
}

.tab-btn.active {
    color: white;
    border-bottom-color: var(--primary-color);
}

/* Content Grid */
.creator-content {
    padding: 0 1rem;
}

.content-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 1rem;
}

.content-grid.active {
    display: grid;
}

.empty-message {
    grid-column: 1 / -1;
    text-align: center;
    color: #888;
    font-size: 1rem;
    padding: 3rem 0;
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
        justify-content: center;
        margin: 0 4rem 2rem 4rem;
    }

    .content-grid {
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    }

    .creator-content {
        padding: 0 4rem;
    }

    .creator-bio-section {
        margin-left: 0;
        margin-right: 0;
    }
}
</style>
