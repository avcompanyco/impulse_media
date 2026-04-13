<script setup lang="ts">
import { useVideoPlayer, type UseVideoPlayerOptions } from '@/composables/useVideoPlayer';
import VideoAdOverlay from '@/components/VideoAdOverlay.vue';
import { ref } from 'vue';

export interface VideoPlayerProps {
    /** Video source URL */
    videoSrc: string;
    /** Title displayed in the overlay */
    title: string;
    /** Optional subtitle (e.g., "Season 1 • Episode 3") */
    subtitle?: string;
    /** Poster/thumbnail image URL */
    posterUrl?: string;
    /** Autoplay on mount */
    autoplay?: boolean;
    /** Enable keyboard shortcuts */
    keyboardControls?: boolean;
    /** Show ads (based on user plan) */
    showAds?: boolean;
}

const props = withDefaults(defineProps<VideoPlayerProps>(), {
    posterUrl: '/images/video_poster_placeholder.jpg',
    autoplay: true,
    keyboardControls: true,
    subtitle: '',
    showAds: false,
});

const emit = defineEmits<{
    ended: [];
    play: [];
    pause: [];
}>();

// When ads are enabled, defer autoplay until preroll is done
const shouldAutoplay = props.showAds ? false : props.autoplay;
const adOverlayRef = ref<InstanceType<typeof VideoAdOverlay>>();

const {
    videoEl,
    containerEl,
    isPlaying,
    isMuted,
    volume,
    currentTime,
    duration,
    progress,
    isFullscreen,
    controlsVisible,
    titleVisible,
    isBuffering,
    timeDisplayText,
    togglePlayPause,
    handleProgressSeek,
    toggleMute,
    handleVolumeInput,
    toggleFullscreen,
    showControls,
    play,
    pause,
} = useVideoPlayer({
    autoplay: shouldAutoplay,
    keyboardControls: props.keyboardControls,
    onEnded: () => emit('ended'),
});

// Ad event handlers
function onAdStart() {
    pause();
}

function onAdEnd() {
    play();
}

function onPrerollComplete() {
    if (props.autoplay) {
        play();
    }
}
</script>

<template>
    <div
        ref="containerEl"
        class="vp-container"
        :class="{
            'vp-controls-hidden': !controlsVisible,
            'vp-playing': isPlaying,
            'vp-fullscreen': isFullscreen,
        }"
    >
        <!-- Video Element -->
        <video
            ref="videoEl"
            class="vp-video"
            :poster="posterUrl"
            preload="auto"
            playsinline
            webkit-playsinline
            x5-playsinline
            @play="emit('play')"
            @pause="emit('pause')"
        >
            <source :src="videoSrc" type="video/mp4" />
            Tu navegador no soporta la etiqueta de video HTML5.
        </video>

        <!-- Ad Overlay -->
        <VideoAdOverlay
            v-if="showAds"
            ref="adOverlayRef"
            :show-ads="showAds"
            :video-duration="duration"
            :current-time="currentTime"
            @ad-start="onAdStart"
            @ad-end="onAdEnd"
            @preroll-complete="onPrerollComplete"
        />

        <!-- Buffering Spinner -->
        <div v-if="isBuffering" class="vp-buffering">
            <div class="vp-spinner"></div>
        </div>

        <!-- Title Overlay -->
        <div
            class="vp-title-overlay"
            :class="{ 'vp-visible': titleVisible }"
        >
            <span class="vp-title-text">{{ title }}</span>
            <span v-if="subtitle" class="vp-subtitle-text">{{ subtitle }}</span>
        </div>

        <!-- Back Button Area (slot) -->
        <div
            class="vp-back-container"
            :class="{ 'vp-visible': controlsVisible }"
        >
            <slot name="back-button" />
        </div>

        <!-- Custom Controls -->
        <div
            class="vp-controls"
            :class="{ 'vp-visible': controlsVisible }"
            @click.stop
        >
            <!-- Extra Info Slot (e.g., episode info above progress bar) -->
            <slot name="controls-info" />

            <!-- Progress Bar -->
            <div
                class="vp-progress-container"
                @click.stop="handleProgressSeek"
                @touchstart.stop
                @touchmove.prevent.stop="handleProgressSeek"
            >
                <div class="vp-progress-bar" :style="{ width: `${progress}%` }"></div>
                <div class="vp-progress-thumb" :style="{ left: `${progress}%` }"></div>
            </div>

            <!-- Controls Row -->
            <div class="vp-controls-row">
                <div class="vp-controls-left">
                    <!-- Play/Pause -->
                    <button
                        class="vp-btn"
                        :title="isPlaying ? 'Pausar' : 'Reproducir'"
                        @click.stop="togglePlayPause"
                    >
                        <i :class="isPlaying ? 'fa-solid fa-pause' : 'fa-solid fa-play'"></i>
                    </button>

                    <!-- Volume -->
                    <div class="vp-volume-container">
                        <button
                            class="vp-btn"
                            title="Silenciar/Sonido"
                            @click.stop="toggleMute"
                        >
                            <i :class="isMuted ? 'fa-solid fa-volume-xmark' : 'fa-solid fa-volume-high'"></i>
                        </button>
                        <input
                            type="range"
                            class="vp-volume-slider"
                            min="0"
                            max="1"
                            step="0.05"
                            :value="volume"
                            @input.stop="handleVolumeInput"
                        />
                    </div>

                    <!-- Time Display -->
                    <div class="vp-time-display">{{ timeDisplayText }}</div>
                </div>

                <div class="vp-controls-right">
                    <!-- Extra Controls Slot (e.g., next episode button) -->
                    <slot name="extra-controls" />

                    <!-- Fullscreen -->
                    <button
                        class="vp-btn"
                        title="Pantalla Completa"
                        @click.stop="toggleFullscreen"
                    >
                        <i :class="isFullscreen ? 'fa-solid fa-compress' : 'fa-solid fa-expand'"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* === CSS Custom Properties === */
.vp-container {
    --vp-primary: #e8445a;
    --vp-primary-glow: rgba(232, 68, 90, 0.4);
    --vp-controls-bg: rgba(0, 0, 0, 0.7);
    --vp-text-light: #FFFFFF;
    --vp-icon-color: rgba(255, 255, 255, 0.9);
    --vp-icon-hover: #e8445a;
    --vp-slider-track: rgba(255, 255, 255, 0.2);
    --vp-slider-thumb: #e8445a;
    --vp-title-overlay-bg: rgba(0, 0, 0, 0.75);
}

/* === Container === */
.vp-container {
    width: 100%;
    height: calc(100vh - 160px);
    max-height: calc(100dvh - 160px);
    position: relative;
    display: flex;
    justify-content: center;
    align-items: center;
    background-color: #000;
    overflow: hidden;
    cursor: default;
    -webkit-user-select: none;
    user-select: none;
}

.vp-container.vp-controls-hidden {
    cursor: none;
}

/* === Video === */
.vp-video {
    width: 100%;
    height: 100%;
    display: block;
    object-fit: contain;
}

/* === Buffering Spinner === */
.vp-buffering {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    z-index: 25;
    pointer-events: none;
}

.vp-spinner {
    width: 48px;
    height: 48px;
    border: 4px solid rgba(255, 255, 255, 0.3);
    border-top-color: var(--vp-primary);
    border-radius: 50%;
    animation: vp-spin 0.8s linear infinite;
}

@keyframes vp-spin {
    to { transform: rotate(360deg); }
}

/* === Title Overlay === */
.vp-title-overlay {
    position: absolute;
    top: 5%;
    left: 50%;
    transform: translateX(-50%);
    background-color: var(--vp-title-overlay-bg);
    color: var(--vp-text-light);
    padding: 15px 30px;
    border-radius: 10px;
    font-size: 1.8rem;
    font-weight: 600;
    text-align: center;
    opacity: 0;
    visibility: hidden;
    transition: opacity 0.5s ease-in-out, visibility 0.5s ease-in-out;
    z-index: 20;
    pointer-events: none;
    display: flex;
    flex-direction: column;
    gap: 4px;
    max-width: 90%;
}

.vp-title-overlay.vp-visible {
    opacity: 1;
    visibility: visible;
}

.vp-title-text {
    display: block;
}

.vp-subtitle-text {
    display: block;
    font-size: 1rem;
    font-weight: 400;
    opacity: 0.8;
}

/* === Back Button Container === */
.vp-back-container {
    position: absolute;
    top: 20px;
    left: 20px;
    z-index: 22;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.3s ease-in-out;
}

.vp-back-container.vp-visible {
    opacity: 1;
    pointer-events: auto;
}

/* Shared back button styles for slotted content */
.vp-container :deep(.vp-back-btn) {
    background-color: var(--vp-controls-bg);
    color: var(--vp-text-light);
    border: none;
    padding: 10px 15px;
    border-radius: 50%;
    font-size: 1.5rem;
    cursor: pointer;
    text-decoration: none;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background-color 0.3s ease, color 0.3s ease;
    width: 45px;
    height: 45px;
}

.vp-container :deep(.vp-back-btn:hover) {
    background-color: var(--vp-primary);
    color: var(--vp-text-light);
    text-decoration: none;
}

/* === Controls === */
.vp-controls {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: linear-gradient(transparent, rgba(0,0,0,0.85));
    padding: 40px 16px 12px 16px;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.3s ease-in-out;
    z-index: 21;
}

.vp-controls.vp-visible {
    opacity: 1;
    pointer-events: auto;
}

/* === Controls Row === */
.vp-controls-row {
    display: flex;
    align-items: center;
    gap: 15px;
}

.vp-controls-left {
    display: flex;
    align-items: center;
    gap: 12px;
    flex: 1;
}

.vp-controls-right {
    display: flex;
    align-items: center;
    gap: 12px;
}

/* === Buttons === */
.vp-btn {
    background: none;
    border: none;
    color: var(--vp-icon-color);
    font-size: 1.2rem;
    cursor: pointer;
    padding: 8px;
    line-height: 1;
    transition: color 0.2s ease, transform 0.15s ease;
    -webkit-tap-highlight-color: transparent;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.vp-btn:hover {
    color: var(--vp-icon-hover);
    transform: scale(1.1);
    background: rgba(255,255,255,0.08);
}

.vp-btn:focus-visible {
    outline: 2px solid var(--vp-primary);
    outline-offset: 2px;
    border-radius: 4px;
}

/* === Progress Bar === */
.vp-progress-container {
    width: 100%;
    height: 8px;
    background-color: var(--vp-slider-track);
    border-radius: 4px;
    cursor: pointer;
    position: relative;
    margin-bottom: 10px;
    touch-action: none;
}

.vp-progress-container:hover {
    height: 12px;
    margin-top: -2px;
    margin-bottom: 8px;
}

.vp-progress-bar {
    height: 100%;
    width: 0%;
    background: linear-gradient(90deg, var(--vp-primary), #ff6b81);
    border-radius: 4px;
    pointer-events: none;
    transition: width 0.1s linear;
    box-shadow: 0 0 6px var(--vp-primary-glow);
}

.vp-progress-thumb {
    position: absolute;
    top: 50%;
    transform: translate(-50%, -50%) scale(0);
    width: 16px;
    height: 16px;
    background-color: var(--vp-primary);
    border-radius: 50%;
    pointer-events: none;
    transition: transform 0.15s ease;
}

.vp-progress-container:hover .vp-progress-thumb {
    transform: translate(-50%, -50%) scale(1);
}

/* === Time Display === */
.vp-time-display {
    font-size: 0.85rem;
    min-width: 90px;
    text-align: center;
    color: rgba(255,255,255,0.75);
    font-family: 'Inter', system-ui, sans-serif;
    font-variant-numeric: tabular-nums;
    white-space: nowrap;
    letter-spacing: 0.3px;
}

/* === Volume === */
.vp-volume-container {
    display: flex;
    align-items: center;
}

.vp-volume-slider {
    width: 80px;
    height: 6px;
    background-color: var(--vp-slider-track);
    border-radius: 3px;
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
    cursor: pointer;
    margin-left: 8px;
    outline: none;
}

.vp-volume-slider::-webkit-slider-thumb {
    -webkit-appearance: none;
    appearance: none;
    width: 14px;
    height: 14px;
    background: var(--vp-slider-thumb);
    border-radius: 50%;
    cursor: pointer;
}

.vp-volume-slider::-moz-range-thumb {
    width: 14px;
    height: 14px;
    background: var(--vp-slider-thumb);
    border-radius: 50%;
    border: none;
    cursor: pointer;
}

.vp-volume-slider::-ms-thumb {
    width: 14px;
    height: 14px;
    background: var(--vp-slider-thumb);
    border-radius: 50%;
    border: none;
    cursor: pointer;
}

.vp-volume-slider::-webkit-slider-runnable-track {
    height: 6px;
    background: var(--vp-slider-track);
    border-radius: 3px;
}

.vp-volume-slider::-moz-range-track {
    height: 6px;
    background: var(--vp-slider-track);
    border-radius: 3px;
    border: none;
}

/* === Extra controls slot (e.g., Next Episode) === */
.vp-container :deep(.vp-next-btn) {
    background: var(--vp-primary);
    color: var(--vp-text-light);
    border: none;
    padding: 6px 14px;
    border-radius: 6px;
    font-size: 0.95rem;
    font-weight: 500;
    cursor: pointer;
    transition: background 0.2s ease, opacity 0.2s ease;
    white-space: nowrap;
}

.vp-container :deep(.vp-next-btn:hover) {
    opacity: 0.85;
}

/* === Responsive === */
@media (max-width: 768px) {
    .vp-title-overlay {
        top: 3%;
        padding: 10px 20px;
        font-size: 1.2rem;
        border-radius: 8px;
    }

    .vp-subtitle-text {
        font-size: 0.85rem;
    }

    .vp-back-container {
        top: 12px;
        left: 12px;
    }

    .vp-container :deep(.vp-back-btn) {
        width: 38px;
        height: 38px;
        font-size: 1.2rem;
        padding: 8px 12px;
    }

    .vp-controls {
        padding: 8px 10px;
    }

    .vp-controls-row {
        gap: 8px;
    }

    .vp-controls-left {
        gap: 6px;
    }

    .vp-btn {
        font-size: 1.3rem;
        padding: 8px;
        min-width: 36px;
        min-height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .vp-volume-slider {
        width: 50px;
    }

    .vp-time-display {
        font-size: 0.75rem;
        min-width: 70px;
    }

    .vp-progress-container {
        height: 10px;
    }
}

@media (max-width: 480px) {
    .vp-volume-slider {
        display: none;
    }

    .vp-time-display {
        min-width: 60px;
        font-size: 0.7rem;
    }
}

/* === Print: hide player === */
@media print {
    .vp-container {
        display: none;
    }
}
</style>
