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

                <!-- Floating creator chip (top-right, shows/hides with controls) -->
                <template #creator-chip v-if="movie.user">
                    <div 
                        class="vp-creator-chip"
                        @click.stop="router.visit(ShowCreatorProfileController({ user: movie.user.username }))"
                    >
                        <img :src="movie.user.image_url || '/images/default-avatar.png'" alt="" class="vp-chip-avatar">
                        <div class="vp-chip-text">
                            <span class="vp-chip-name">{{ movie.user.name }}</span>
                            <span class="vp-chip-handle">@{{ movie.user.username }}</span>
                        </div>
                        <span class="vp-chip-tag">Movie</span>
                    </div>
                </template>
            </VideoPlayer>
        </div>
    </UserDashboardLayout>
</template>