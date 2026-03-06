<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import UserDashboardLayout from '@/layouts/UserDashboardLayout.vue';
import VideoPlayer from '@/components/VideoPlayer.vue';
import ShowSerieController from '@/actions/App/Http/Controllers/Serie/ShowSerieController';

const props = defineProps<{
    serie: any;
}>();
</script>

<template>
    <UserDashboardLayout 
        :title="`${serie.title} - ${$page.props.name || 'Impulsemedia'}`" 
        :headerTitle="`${serie.title} - ${$page.props.name || 'Impulsemedia'}`">
        <div style="margin-bottom: 80px;">
            <VideoPlayer
                :video-src="serie.trailer_video_url"
                :title="serie.title || 'Serie'"
            >
                <template #back-button>
                    <Link 
                        :href="ShowSerieController.url({ serie: serie.id })" 
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