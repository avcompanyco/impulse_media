<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import UserDashboardLayout from '@/layouts/UserDashboardLayout.vue';
import VideoPlayer from '@/components/VideoPlayer.vue';
import ShowSerieController from '@/actions/App/Http/Controllers/Serie/ShowSerieController';
import ShowCreatorProfileController from '@/actions/App/Http/Controllers/CreatorProfile/ShowCreatorProfileController';

const props = defineProps<{
    serie: any;
    season: any;
    chapter: any;
    hasFullAccess: boolean;
    ppvPrice: number;
    rawPpvPrice: number;
    isMember: boolean;
    allowMembership: boolean;
    freePreviewSeconds: number;
}>();

const goToSerie = () => {
    router.get(ShowSerieController.url({ serie: props.serie.id }));
};

const findNextChapter = () => {
    const currentSeason = props.serie.seasons.find((s: any) => s.id === props.season.id);
    if (!currentSeason) return null;

    const currentChapterIndex = currentSeason.chapters.findIndex((c: any) => c.id === props.chapter.id);

    // Try next chapter in same season
    if (currentChapterIndex < currentSeason.chapters.length - 1) {
        return {
            season: currentSeason,
            chapter: currentSeason.chapters[currentChapterIndex + 1],
        };
    }

    // Try first chapter of next season
    const currentSeasonIndex = props.serie.seasons.findIndex((s: any) => s.id === props.season.id);
    if (currentSeasonIndex < props.serie.seasons.length - 1) {
        const nextSeason = props.serie.seasons[currentSeasonIndex + 1];
        if (nextSeason.chapters.length > 0) {
            return {
                season: nextSeason,
                chapter: nextSeason.chapters[0],
            };
        }
    }

    return null;
};

const playNextChapter = () => {
    const next = findNextChapter();
    if (next) {
        router.get(`/serie/${props.serie.id}/season/${next.season.id}/chapter/${next.chapter.id}/player`);
    }
};

const handleVideoEnded = () => {
    const next = findNextChapter();
    if (next) {
        setTimeout(playNextChapter, 3000);
    }
};
</script>

<template>
    <UserDashboardLayout 
        :title="`${chapter.title} - ${serie.title} - ${$page.props.name || 'Impulsemedia'}`" 
        :headerTitle="`${chapter.title} - ${serie.title} - ${$page.props.name || 'Impulsemedia'}`">
        <template #main-content>
            <div style="margin-bottom: 80px;">
                <VideoPlayer
                    :video-src="chapter.chapter_video_url"
                    :title="serie.title"
                    :subtitle="`Temporada ${season.id} • Episodio ${chapter.chapter_number}: ${chapter.title}`"
                    :poster-url="chapter.thumbnail_url"
                    :show-ads="$page.props.show_ads"
                    @ended="handleVideoEnded"
                    :content-id="serie.content.id"
                    :has-full-access="hasFullAccess"
                    :ppv-price="ppvPrice"
                    :raw-ppv-price="rawPpvPrice"
                    :is-member="isMember"
                    :allow-membership="allowMembership"
                    :free-preview-seconds="freePreviewSeconds"
                >
                    <template #back-button>
                        <button @click="goToSerie" class="vp-back-btn" title="Volver a la serie">
                            ←
                        </button>
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
                            <span class="vp-chip-tag">Episode</span>
                        </div>
                    </template>

                    <template #extra-controls>
                        <button
                            v-if="findNextChapter()"
                            class="vp-next-btn"
                            @click.stop="playNextChapter"
                        >
                            Siguiente Episodio →
                        </button>
                    </template>
                </VideoPlayer>
            </div>
            <br />
        </template>
    </UserDashboardLayout>
</template>