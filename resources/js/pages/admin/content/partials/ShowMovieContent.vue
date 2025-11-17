<script setup lang="ts">
import { ref } from 'vue';

const props = defineProps<{
    movie: any;
}>();

const activeVideoTab = ref<'trailer' | 'movie'>('trailer');
</script>

<template>
    <div class="movie-content-container">
        <!-- Sección de información básica -->
        <div class="info-section">
            <h2 class="movie-title">{{ movie.title }}</h2>
            <p class="movie-description">{{ movie.description }}</p>
        </div>

        <!-- Sección de imágenes -->
        <div class="images-section">
            <div class="image-container">
                <h3>Imagen Vertical</h3>
                <img 
                    :src="movie.vertical_image_url" 
                    :alt="`${movie.title} - Vertical`"
                    class="vertical-image"
                />
            </div>
            <div class="image-container" style="margin-top: 20px;">
                <h3>Imagen Horizontal</h3>
                <figure style="max-width: 500px; height: 100%;">
                    <img 
                        :src="movie.horizontal_image_url" 
                        :alt="`${movie.title} - Horizontal`"
                        class="horizontal-image"
                    />
                </figure>
            </div>
        </div>

        <!-- Sección de videos -->
        <div class="videos-section">
            <div class="video-tabs">
                <button 
                    @click="activeVideoTab = 'trailer'"
                    :class="{ active: activeVideoTab === 'trailer' }"
                    class="tab-button"
                >
                    Trailer
                </button>
                <button 
                    @click="activeVideoTab = 'movie'"
                    :class="{ active: activeVideoTab === 'movie' }"
                    class="tab-button"
                >
                    Película
                </button>
            </div>

            <div class="video-player-container">
                <video 
                    v-if="activeVideoTab === 'trailer'"
                    :src="movie.trailer_video_url"
                    controls
                    preload="metadata"
                    class="video-player"
                    style="max-width: 500px;"
                >
                    Tu navegador no soporta la etiqueta de video HTML5.
                </video>
                <video 
                    v-else
                    :src="movie.movie_video_url"
                    controls
                    preload="metadata"
                    class="video-player"
                    style="max-width: 500px;"
                >
                    Tu navegador no soporta la etiqueta de video HTML5.
                </video>
            </div>
        </div>
    </div>
</template>

<style scoped>
.movie-content-container {
    padding: 20px;
    max-width: 1200px;
    margin: 0 auto;
}

.info-section {
    margin-bottom: 30px;
}

.movie-title {
    font-size: 2rem;
    font-weight: 600;
    margin-bottom: 15px;
    color: var(--text-color, #f6f6f6);
}

.movie-description {
    font-size: 1rem;
    line-height: 1.6;
    color: var(--text-secondary, #ffffff);
}

.images-section {
    /* display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 30px; */
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
    /* background-color: #000; */
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.video-player {
    width: 100%;
    height: auto;
    display: block;
}

@media (max-width: 768px) {
    .movie-title {
        font-size: 1.5rem;
    }

    .images-section {
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
