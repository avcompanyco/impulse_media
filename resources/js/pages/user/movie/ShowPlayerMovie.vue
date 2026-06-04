<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import UserDashboardLayout from '@/layouts/UserDashboardLayout.vue';
import VideoPlayer from '@/components/VideoPlayer.vue';
import ShowMovieController from '@/actions/App/Http/Controllers/Movie/ShowMovieController';
import ShowCreatorProfileController from '@/actions/App/Http/Controllers/CreatorProfile/ShowCreatorProfileController';

const props = defineProps<{
    movie: any;
    hasFullAccess: boolean;
    ppvPrice: number;
    rawPpvPrice: number;
    isMember: boolean;
    allowMembership: boolean;
}>();
</script>

<template>
    <UserDashboardLayout 
        :title="`${movie.title} - ${$page.props.name || 'Impulsemedia'}`" 
        :headerTitle="`${movie.title} - ${$page.props.name || 'Impulsemedia'}`">
        <div>
            <!-- Clickable Creator Details Card (A3) -->
            <div class="creator-card-container" v-if="movie.user">
                <div class="creator-card" @click="router.visit(ShowCreatorProfileController({ user: movie.user.username }))">
                    <img :src="movie.user.image_url || '/images/default-avatar.png'" alt="Creator Avatar" class="creator-avatar">
                    <div class="creator-info">
                        <span class="creator-name">{{ movie.user.name }}</span>
                        <span class="creator-handle">@{{ movie.user.username }}</span>
                    </div>
                    <div class="divider-dot"></div>
                    <div class="content-meta">
                        <span class="content-title">{{ movie.title }}</span>
                        <span class="content-tag">Movie</span>
                    </div>
                </div>
            </div>

            <VideoPlayer
                :video-src="movie.movie_video_url"
                :title="movie.title || 'Video'"
                :show-ads="$page.props.show_ads"
                :content-id="movie.content.id"
                :has-full-access="hasFullAccess"
                :ppv-price="ppvPrice"
                :raw-ppv-price="rawPpvPrice"
                :is-member="isMember"
                :allow-membership="allowMembership"
            >
                <template #back-button>
                    <Link 
                        :href="ShowMovieController.url(movie)" 
                        class="vp-back-btn"
                        title="Volver"
                    >
                        ←
                    </Link>
                </template>
            </VideoPlayer>
        </div>
    </UserDashboardLayout>
</template>

<style scoped>
.creator-card-container {
    margin-bottom: 1.5rem;
}

.creator-card {
    display: flex;
    align-items: center;
    gap: 1.25rem;
    background: rgba(255, 255, 255, 0.03);
    backdrop-filter: blur(12px);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 16px;
    padding: 0.75rem 1.25rem;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
}

.creator-card:hover {
    background: rgba(255, 255, 255, 0.07);
    border-color: rgba(255, 255, 255, 0.15);
    transform: translateY(-2px);
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.3);
}

.creator-avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #e8445a;
    box-shadow: 0 0 10px rgba(232, 68, 90, 0.2);
}

.creator-info {
    display: flex;
    flex-direction: column;
    gap: 0.15rem;
}

.creator-name {
    font-weight: 700;
    font-size: 0.95rem;
    color: #fff;
}

.creator-handle {
    font-size: 0.8rem;
    color: #a0aec0;
}

.divider-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background-color: rgba(255, 255, 255, 0.2);
    margin: 0 0.5rem;
}

.content-meta {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex-wrap: wrap;
}

.content-title {
    font-weight: 600;
    font-size: 1rem;
    color: #fff;
}

.content-tag {
    background: rgba(232, 68, 90, 0.15);
    color: #e8445a;
    font-size: 0.75rem;
    font-weight: 700;
    padding: 0.2rem 0.6rem;
    border-radius: 20px;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

@media (max-width: 640px) {
    .creator-card {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.75rem;
        padding: 1rem;
    }
    
    .divider-dot {
        display: none;
    }
    
    .content-meta {
        width: 100%;
        border-top: 1px solid rgba(255, 255, 255, 0.08);
        padding-top: 0.75rem;
    }
}
</style>