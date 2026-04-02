<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import UserDashboardLayout from '@/layouts/UserDashboardLayout.vue';
import VideoPlayer from '@/components/VideoPlayer.vue';
import ShowMovieController from '@/actions/App/Http/Controllers/Movie/ShowMovieController';

const props = defineProps<{
    movie: any;
}>();
</script>

<template>
    <UserDashboardLayout 
        :title="`${movie.title} - ${$page.props.name || 'Impulsemedia'}`" 
        :headerTitle="`${movie.title} - ${$page.props.name || 'Impulsemedia'}`">
        <div style="margin-bottom: 80px;">
            <VideoPlayer
                :video-src="movie.movie_video_url"
                :title="movie.title || 'Video'"
                :show-ads="$page.props.show_ads"
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
        <br />
    </UserDashboardLayout>
</template>