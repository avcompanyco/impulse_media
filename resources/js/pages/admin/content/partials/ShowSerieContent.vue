<script setup lang="ts">
import { ref, computed } from 'vue';

interface Chapter {
    id: number;
    chapter_number: number;
    title: string;
    thumbnail: string;
    chapter_video: string;
    status: string;
    season_id: number;
    user_id: number;
    created_at: string;
    updated_at: string;
    chapter_video_url: string;
    thumbnail_url: string;
}

interface Season {
    id: number;
    serie_id: number;
    user_id: number;
    created_at: string;
    updated_at: string;
    chapters: Chapter[];
}

interface Serie {
    id: number;
    title: string;
    description: string;
    vertical_image: string;
    horizontal_image: string;
    trailer_video: string;
    user_id: number;
    category_id: number;
    subcategory_id: number;
    created_at: string;
    updated_at: string;
    horizontal_image_url: string;
    vertical_image_url: string;
    trailer_video_url: string;
    url_path: string;
    seasons: Season[];
}

const props = defineProps<{
    serie: Serie;
}>();

const activeVideoTab = ref<'trailer'>('trailer');
const showTrailerVideo = ref(false);
const activeChapterVideos = ref<Set<number>>(new Set());

// Ordenar temporadas por ID de forma ascendente
const sortedSeasons = computed(() => {
    if (!props.serie.seasons) return [];
    return [...props.serie.seasons].sort((a, b) => a.id - b.id);
});

// Función para ordenar capítulos por chapter_number
const getSortedChapters = (chapters: Chapter[]) => {
    if (!chapters) return [];
    return [...chapters].sort((a, b) => a.chapter_number - b.chapter_number);
};

// Función para mostrar el video del trailer
const playTrailer = () => {
    showTrailerVideo.value = true;
};

// Función para mostrar el video de un capítulo
const playChapter = (chapterId: number) => {
    activeChapterVideos.value.add(chapterId);
};

// Función para verificar si un capítulo está activo
const isChapterVideoActive = (chapterId: number) => {
    return activeChapterVideos.value.has(chapterId);
};
</script>

<template>
    <div class="serie-content-container">
        <!-- Sección de información básica -->
        <div class="info-section">
            <h2 class="serie-title">{{ serie.title }}</h2>
            <p class="serie-description">{{ serie.description }}</p>
        </div>

        <!-- Sección de imágenes -->
        <div class="images-section">
            <div class="image-container">
                <h3>Imagen Vertical</h3>
                <img 
                    :src="serie.vertical_image_url" 
                    :alt="`${serie.title} - Vertical`"
                    class="vertical-image"
                />
            </div>
            <div class="image-container" style="margin-top: 20px;">
                <h3>Imagen Horizontal</h3>
                <figure style="max-width: 500px; height: 100%;">
                    <img 
                        :src="serie.horizontal_image_url" 
                        :alt="`${serie.title} - Horizontal`"
                        class="horizontal-image"
                    />
                </figure>
            </div>
        </div>

        <!-- Sección de trailer -->
        <div class="videos-section">
            <div class="video-tabs">
                <button 
                    @click="activeVideoTab = 'trailer'"
                    :class="{ active: activeVideoTab === 'trailer' }"
                    class="tab-button"
                >
                    Trailer
                </button>
            </div>

            <div class="video-player-container">
                <div v-if="!showTrailerVideo" class="video-placeholder">
                    <button @click="playTrailer" class="play-button">
                        <i class="fas fa-play"></i>
                        <span>Reproducir Trailer</span>
                    </button>
                </div>
                <video 
                    v-else
                    :src="serie.trailer_video_url"
                    controls
                    preload="metadata"
                    class="video-player"
                    style="max-width: 500px;"
                    autoplay
                >
                    Tu navegador no soporta la etiqueta de video HTML5.
                </video>
            </div>
        </div>

        <!-- Sección de temporadas y episodios -->
        <div v-if="sortedSeasons.length > 0" class="seasons-section">
            <h2 class="section-title">Temporadas y Episodios</h2>
            
            <div 
                v-for="(season, seasonIndex) in sortedSeasons" 
                :key="season.id" 
                class="season-container"
            >
                <h3 class="season-title">Temporada {{ seasonIndex + 1 }}</h3>
                
                <div v-if="season.chapters && season.chapters.length > 0" class="episodes-grid">
                    <div 
                        v-for="chapter in getSortedChapters(season.chapters)" 
                        :key="chapter.id"
                        class="episode-card"
                    >
                        <div class="episode-thumbnail">
                            <img 
                                :src="chapter.thumbnail_url" 
                                :alt="`Episode ${chapter.chapter_number}`"
                                class="episode-image"
                            />
                            <div class="episode-number">{{ chapter.chapter_number }}</div>
                        </div>
                        <div class="episode-info">
                            <h4 class="episode-title">{{ chapter.title }}</h4>
                            <p class="episode-meta">Episodio {{ chapter.chapter_number }}</p>
                            <div class="episode-video">
                                <div v-if="!isChapterVideoActive(chapter.id)" class="video-placeholder">
                                    <button @click="playChapter(chapter.id)" class="play-button">
                                        <i class="fas fa-play"></i>
                                        <span>Reproducir Episodio</span>
                                    </button>
                                </div>
                                <video 
                                    v-else
                                    :src="chapter.chapter_video_url"
                                    controls
                                    preload="metadata"
                                    class="chapter-video-player"
                                    autoplay
                                >
                                    Tu navegador no soporta la etiqueta de video HTML5.
                                </video>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div v-else class="no-episodes">
                    <p>No hay episodios disponibles para esta temporada.</p>
                </div>
            </div>
        </div>

        <div v-else class="no-seasons">
            <p>No hay temporadas disponibles para esta serie.</p>
        </div>
    </div>
</template>

<style scoped>
.serie-content-container {
    padding: 20px;
    max-width: 1200px;
    margin: 0 auto;
}

.info-section {
    margin-bottom: 30px;
}

.serie-title {
    font-size: 2rem;
    font-weight: 600;
    margin-bottom: 15px;
    color: var(--text-color, #f6f6f6);
}

.serie-description {
    font-size: 1rem;
    line-height: 1.6;
    color: var(--text-secondary, #ffffff);
}

.images-section {
    margin-bottom: 40px;
}

.image-container h3 {
    font-size: 1.2rem;
    font-weight: 500;
    margin-bottom: 15px;
    color: var(--text-color, #ffffff);
}

.vertical-image {
    width: 100%;
    max-width: 300px;
    height: auto;
    border-radius: 8px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.horizontal-image {
    width: 100%;
    height: auto;
    border-radius: 8px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.videos-section {
    margin-top: 40px;
    margin-bottom: 40px;
}

.video-tabs {
    display: flex;
    gap: 10px;
    margin-bottom: 20px;
}

.tab-button {
    padding: 10px 20px;
    border: none;
    background-color: var(--bg-secondary, #f5f5f5);
    color: var(--text-color, #333);
    font-size: 1rem;
    font-weight: 500;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.tab-button:hover {
    background-color: var(--bg-hover, #e0e0e0);
}

.tab-button.active {
    background-color: var(--primary-color, #F06292);
    color: white;
}

.video-player-container {
    width: 100%;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.video-player {
    width: 100%;
    height: auto;
    display: block;
}

.video-placeholder {
    width: 100%;
    max-width: 500px;
    aspect-ratio: 16 / 9;
    background-color: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
}

.play-button {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 10px;
    background-color: var(--primary-color, #F06292);
    color: white;
    border: none;
    padding: 20px 40px;
    border-radius: 50px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
}

.play-button:hover {
    background-color: var(--primary-color-hover, #e91e63);
    transform: scale(1.05);
}

.play-button i {
    font-size: 2rem;
}

.seasons-section {
    margin-top: 40px;
}

.section-title {
    font-size: 1.8rem;
    font-weight: 600;
    margin-bottom: 25px;
    color: var(--text-color, #f6f6f6);
}

.season-container {
    margin-bottom: 40px;
}

.season-title {
    font-size: 1.5rem;
    font-weight: 600;
    margin-bottom: 20px;
    color: var(--text-color, #f6f6f6);
}

.episodes-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 25px;
}

.episode-card {
    background-color: var(--card-bg, #1a1a2e);
    border-radius: 12px;
    overflow: hidden;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.episode-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
}

.episode-thumbnail {
    position: relative;
    width: 100%;
    padding-top: 56.25%;
    overflow: hidden;
}

.episode-image {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.episode-number {
    position: absolute;
    top: 10px;
    left: 10px;
    background-color: rgba(0, 0, 0, 0.7);
    color: white;
    padding: 5px 10px;
    border-radius: 5px;
    font-weight: 600;
    font-size: 0.9rem;
}

.episode-info {
    padding: 15px;
}

.episode-title {
    font-size: 1.1rem;
    font-weight: 600;
    margin-bottom: 8px;
    color: var(--text-color, #f6f6f6);
}

.episode-meta {
    font-size: 0.9rem;
    color: var(--text-secondary, #b0b0b0);
    margin-bottom: 15px;
}

.episode-video {
    margin-top: 15px;
}

.episode-video .video-placeholder {
    max-width: 100%;
    aspect-ratio: 16 / 9;
}

.episode-video .play-button {
    padding: 15px 30px;
    font-size: 0.9rem;
}

.episode-video .play-button i {
    font-size: 1.5rem;
}

.chapter-video-player {
    width: 100%;
    height: auto;
    border-radius: 8px;
}

.no-episodes,
.no-seasons {
    text-align: center;
    padding: 40px 20px;
    color: var(--text-secondary, #b0b0b0);
    font-size: 1.1rem;
}

@media (max-width: 768px) {
    .serie-title {
        font-size: 1.5rem;
    }

    .episodes-grid {
        grid-template-columns: 1fr;
    }

    .video-tabs {
        flex-direction: column;
    }

    .tab-button {
        width: 100%;
    }
}
</style>
