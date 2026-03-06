<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import UserDashboardLayout from '@/layouts/UserDashboardLayout.vue';
import VideoPlayer from '@/components/VideoPlayer.vue';
import ShowSerieController from '@/actions/App/Http/Controllers/Serie/ShowSerieController';

const props = defineProps<{
    serie: any;
    season: any;
    chapter: any;
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
                    @ended="handleVideoEnded"
                >
                    <template #back-button>
                        <button @click="goToSerie" class="vp-back-btn" title="Volver a la serie">
                            ←
                        </button>
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