<script setup lang="ts">
import { ref, onMounted, nextTick } from 'vue';

interface Short {
    id: number;
    short_video_url: string;
    text_caption: string;
}

const props = defineProps<{
    short: Short;
}>();

// Reactive refs
const videoPlayer = ref<HTMLVideoElement>();
const isPlaying = ref(false);
const showControls = ref(true);
const isMuted = ref(false);
const currentTime = ref(0);
const duration = ref(0);

// Auto-hide controls timeout
let controlsTimeout: number;

// Functions
const togglePlayPause = (event?: Event) => {
    if (event) {
        event.stopPropagation();
        event.preventDefault();
    }
    
    if (!videoPlayer.value) return;

    if (videoPlayer.value.paused) {
        videoPlayer.value.play();
        isPlaying.value = true;
    } else {
        videoPlayer.value.pause();
        isPlaying.value = false;
    }
    showControlsTemporarily();
};

const toggleMute = (event?: Event) => {
    if (event) {
        event.stopPropagation();
        event.preventDefault();
    }
    
    if (!videoPlayer.value) return;

    videoPlayer.value.muted = !videoPlayer.value.muted;
    isMuted.value = videoPlayer.value.muted;
    showControlsTemporarily();
};

const showControlsTemporarily = () => {
    showControls.value = true;
    clearTimeout(controlsTimeout);

    controlsTimeout = setTimeout(() => {
        if (isPlaying.value) {
            showControls.value = false;
        }
    }, 3000);
};

const handleVideoLoaded = () => {
    if (videoPlayer.value) {
        duration.value = videoPlayer.value.duration;
        showControlsTemporarily();
    }
};

const handleVideoClick = (event: MouseEvent) => {
    event.stopPropagation();
    event.preventDefault();
    
    const target = event.target as HTMLElement;
    
    if (target === videoPlayer.value || target.classList.contains('main-video')) {
        togglePlayPause();
    }
};

const updateProgress = () => {
    if (videoPlayer.value) {
        currentTime.value = videoPlayer.value.currentTime;
    }
};

const handleProgressClick = (event: MouseEvent) => {
    event.stopPropagation();
    event.preventDefault();
    
    if (!videoPlayer.value) return;

    const rect = (event.target as HTMLElement).getBoundingClientRect();
    const clickX = event.clientX - rect.left;
    const width = rect.width;
    const duration = videoPlayer.value.duration;

    videoPlayer.value.currentTime = (clickX / width) * duration;
    showControlsTemporarily();
};

const formatTime = (seconds: number): string => {
    const minutes = Math.floor(seconds / 60);
    const remainingSeconds = Math.floor(seconds % 60);
    return `${minutes}:${remainingSeconds.toString().padStart(2, '0')}`;
};

onMounted(() => {
    nextTick(() => {
        if (videoPlayer.value) {
            videoPlayer.value.play().then(() => {
                isPlaying.value = true;
            }).catch(() => {
                isPlaying.value = false;
            });
        }
    });
});
</script>

<template>
    <div class="short-container">
        <div class="short-player">
            <!-- Main video -->
            <video 
                ref="videoPlayer" 
                class="main-video" 
                :src="short.short_video_url" 
                preload="metadata" 
                loop
                :muted="isMuted" 
                @click="handleVideoClick" 
                @loadeddata="handleVideoLoaded"
                @play="isPlaying = true" 
                @pause="isPlaying = false" 
                @timeupdate="updateProgress"
            />

            <!-- Video overlay with caption -->
            <div class="video-overlay">
                <div class="video-description-container">
                    <p class="video-description">{{ short.text_caption }}</p>
                </div>
            </div>

            <!-- Video controls -->
            <div v-show="showControls" class="video-controls" @click.stop>
                <button @click="togglePlayPause($event)" class="control-btn">
                    <i v-if="isPlaying" class="fas fa-pause"></i>
                    <i v-else class="fas fa-play"></i>
                </button>

                <div class="progress-container" @click="handleProgressClick($event)">
                    <div class="progress-track">
                        <div class="progress-fill" :style="{ width: `${(currentTime / duration) * 100}%` }"></div>
                    </div>
                </div>

                <button @click="toggleMute($event)" class="control-btn">
                    <i v-if="isMuted" class="fas fa-volume-mute"></i>
                    <i v-else class="fas fa-volume-up"></i>
                </button>

                <span class="time-display">
                    {{ formatTime(currentTime) }} / {{ formatTime(duration) }}
                </span>
            </div>
        </div>
    </div>
</template>

<style scoped>
.short-container {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #000;
}

.short-player {
    position: relative;
    width: 100%;
    max-width: 400px;
    height: 100%;
    max-height: calc(100vh - 160px);
    background-color: #000;
    border-radius: 16px;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
}

.main-video {
    width: 100%;
    height: 100%;
    object-fit: cover;
    cursor: pointer;
}

.video-overlay {
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    padding: 1rem;
    color: var(--text-light);
    background: linear-gradient(to top, rgba(0, 0, 0, 0.7), transparent);
    box-sizing: border-box;
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    padding-bottom: 60px;
    pointer-events: none;
}

.video-description-container {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.video-description {
    font-size: 0.9rem;
    margin: 0;
    line-height: 1.4;
    color: white;
}

.video-controls {
    position: absolute;
    bottom: 10px;
    left: 10px;
    right: 10px;
    display: flex;
    align-items: center;
    gap: 10px;
    z-index: 20;
    pointer-events: auto;
}

.control-btn {
    background: none;
    border: none;
    color: var(--text-light);
    font-size: 1.2rem;
    cursor: pointer;
    padding: 5px;
    flex-shrink: 0;
    min-height: 44px;
    min-width: 44px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.progress-container {
    flex-grow: 1;
    height: 4px;
    background-color: rgba(255, 255, 255, 0.3);
    border-radius: 2px;
    cursor: pointer;
    position: relative;
    pointer-events: auto;
}

.progress-track {
    width: 100%;
    height: 100%;
    background-color: rgba(255, 255, 255, 0.3);
    border-radius: 2px;
}

.progress-fill {
    height: 100%;
    background-color: var(--primary-color);
    border-radius: 2px;
    transition: width 0.1s ease;
}

.time-display {
    flex-shrink: 0;
    color: var(--text-light);
    font-size: 0.8rem;
    font-weight: 500;
    min-width: 80px;
    text-align: center;
}

@media (max-width: 768px) {
    .control-btn {
        padding: 12px;
        font-size: 1.4rem;
    }
}
</style>
