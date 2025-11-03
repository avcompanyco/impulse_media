<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import IndexShortController from '@/actions/App/Http/Controllers/Short/IndexShortController';

const props = defineProps<{
    short: any;
}>();

const videoRef = ref<HTMLVideoElement | null>(null);
const isHovering = ref(false);
const previewTimer = ref<ReturnType<typeof setTimeout> | null>(null);
const PREVIEW_DURATION = 3000; // 3 segundos

const handleMouseEnter = () => {
    isHovering.value = true;
    if (videoRef.value) {
        videoRef.value.play();
        
        // Configurar timer para pausar después de 3 segundos
        previewTimer.value = setTimeout(() => {
            if (videoRef.value && isHovering.value) {
                videoRef.value.pause();
                videoRef.value.currentTime = 0;
            }
        }, PREVIEW_DURATION);
    }
};

const handleMouseLeave = () => {
    isHovering.value = false;
    
    // Limpiar timer si existe
    if (previewTimer.value) {
        clearTimeout(previewTimer.value);
        previewTimer.value = null;
    }
    
    if (videoRef.value) {
        videoRef.value.pause();
        videoRef.value.currentTime = 0;
    }
};

onUnmounted(() => {
    // Limpiar timer al desmontar componente
    if (previewTimer.value) {
        clearTimeout(previewTimer.value);
    }
    
    if (videoRef.value) {
        videoRef.value.pause();
    }
});
</script>

<template>
    <div class="content-card" @mouseenter="handleMouseEnter" @mouseleave="handleMouseLeave">
        <Link :href="IndexShortController({ query: { short: short.id }})">
            <video 
                ref="videoRef"
                :src="short.short_video_url" 
                muted
                loop
                preload="metadata"
                :class="{ 'video-preview': true, 'playing': isHovering }"
            />
            <div class="video-overlay">
                <div class="play-icon" v-if="!isHovering">
                    <i class="fa-solid fa-play"></i>
                </div>
                <div class="video-caption" v-if="short.text_caption">
                    {{ short.text_caption }}
                </div>
            </div>
        </Link>
    </div>
</template>

<style scoped>
/* Contenido del Canal */
.channel-content {
    padding: 0 1rem;
}

.content-grid {
    display: none;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 1rem;
}

.content-grid.active {
    display: grid;
}

.content-card {
    position: relative;
    aspect-ratio: 2/3;
    border-radius: 12px;
    overflow: hidden;
    background-color: #000;
}

.content-card img,
.content-card video {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

.content-card a {
    display: block;
    width: 100%;
    height: 100%;
}

.options-menu-btn {
    position: absolute;
    top: 8px;
    right: 8px;
    background: rgba(0, 0, 0, 0.6);
    color: white;
    border: none;
    border-radius: 50%;
    width: 30px;
    height: 30px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10;
}

.options-dropdown {
    display: none;
    position: absolute;
    top: 40px;
    right: 8px;
    background-color: #333;
    border-radius: 8px;
    overflow: hidden;
    z-index: 11;
}

.options-dropdown button {
    display: block;
    width: 100%;
    background: none;
    border: none;
    color: white;
    padding: 0.75rem 1.5rem;
    text-align: left;
    white-space: nowrap;
    cursor: pointer;
}

.options-dropdown button:hover {
    background-color: var(--primary-color);
}
</style>