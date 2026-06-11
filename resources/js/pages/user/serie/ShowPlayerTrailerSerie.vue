<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import UserDashboardLayout from '@/layouts/UserDashboardLayout.vue';
import VideoPlayer from '@/components/VideoPlayer.vue';
import ShowSerieController from '@/actions/App/Http/Controllers/Serie/ShowSerieController';
import ShowCreatorProfileController from '@/actions/App/Http/Controllers/CreatorProfile/ShowCreatorProfileController';

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
                :show-ads="$page.props.show_ads"
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

                <!-- Floating creator chip (top-right, shows/hides with controls) -->
                <template #creator-chip>
                    <div 
                        v-if="serie.user"
                        class="vp-creator-chip"
                        @click.stop="router.visit(ShowCreatorProfileController({ user: serie.user.username }))"
                    >
                        <img :src="serie.user.image_url || '/images/default-avatar.png'" alt="" class="vp-chip-avatar">
                        <div class="vp-chip-text">
                            <span class="vp-chip-name">{{ serie.user.name }}</span>
                            <span class="vp-chip-handle">@{{ serie.user.username }}</span>
                        </div>
                        <span class="vp-chip-tag">Trailer</span>
                    </div>
                </template>
            </VideoPlayer>
        </div>
        <br />
    </UserDashboardLayout>
</template>