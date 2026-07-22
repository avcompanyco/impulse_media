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
const PREVIEW_DURATION = 3000;

const handleMouseEnter = () => {
    isHovering.value = true;
    if (videoRef.value) {
        videoRef.value.play().catch(() => {});
        
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
                preload="auto"
                playsinline
                class="video-preview"
                :class="{ 'playing': isHovering }"
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
.content-card {
    position: relative;
    width: 100%;
    height: 100%;
    border-radius: 14px;
    overflow: hidden;
    background-color: #000000;
}

.content-card a {
    display: block;
    width: 100%;
    height: 100%;
}

.video-preview {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    transition: transform 0.3s ease;
}

.content-card:hover .video-preview {
    transform: scale(1.06);
}

.video-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(to top, rgba(0, 0, 0, 0.7) 0%, transparent 60%);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    padding: 10px;
    pointer-events: none;
}

.play-icon {
    align-self: center;
    margin-top: auto;
    margin-bottom: auto;
    background: rgba(15, 23, 42, 0.75);
    backdrop-filter: blur(6px);
    width: 38px;
    height: 38px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #ffffff;
    font-size: 0.9rem;
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.video-caption {
    color: #ffffff;
    font-size: 0.75rem;
    font-weight: 600;
    line-height: 1.2;
    overflow: hidden;
    text-overflow: ellipsis;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
}
</style>