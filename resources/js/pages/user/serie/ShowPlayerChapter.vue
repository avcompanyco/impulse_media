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
                <!-- Clickable Creator Details Card (A3) -->
                <div class="creator-card-container" v-if="serie.user">
                    <div class="creator-card" @click="router.visit(ShowCreatorProfileController({ user: serie.user.username }))">
                        <img :src="serie.user.image_url || '/images/default-avatar.png'" alt="Creator Avatar" class="creator-avatar">
                        <div class="creator-info">
                            <span class="creator-name">{{ serie.user.name }}</span>
                            <span class="creator-handle">@{{ serie.user.username }}</span>
                        </div>
                        <div class="divider-dot"></div>
                        <div class="content-meta">
                            <span class="content-title">{{ chapter.title }} ({{ serie.title }})</span>
                            <span class="content-tag">Episode</span>
                        </div>
                    </div>
                </div>

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