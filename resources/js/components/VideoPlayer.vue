<script setup lang="ts">
import { useVideoPlayer, type UseVideoPlayerOptions } from '@/composables/useVideoPlayer';
import VideoAdOverlay from '@/components/VideoAdOverlay.vue';
import { ref, onUnmounted, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';

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
    
    // Monetization Props (B2)
    contentId?: number;
    hasFullAccess?: boolean;
    ppvPrice?: number;
    rawPpvPrice?: number;
    isMember?: boolean;
    allowMembership?: boolean;
}

const props = withDefaults(defineProps<VideoPlayerProps>(), {
    posterUrl: '/images/video_poster_placeholder.jpg',
    autoplay: true,
    keyboardControls: true,
    subtitle: '',
    showAds: false,
    hasFullAccess: true,
    ppvPrice: 0,
    rawPpvPrice: 0,
    isMember: false,
    allowMembership: false,
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
    seekRelative,
} = useVideoPlayer({
    autoplay: shouldAutoplay,
    keyboardControls: props.keyboardControls,
    onEnded: () => emit('ended'),
});

// Heartbeat watch time logging
let watchInterval: ReturnType<typeof setInterval> | null = null;
let lastHeartbeatTime = Date.now();

function startHeartbeat() {
    if (watchInterval) return;
    lastHeartbeatTime = Date.now();
    watchInterval = setInterval(() => {
        const now = Date.now();
        const elapsedSeconds = Math.round((now - lastHeartbeatTime) / 1000);
        
        if (elapsedSeconds >= 1 && props.contentId) {
            axios.post(`/content/${props.contentId}/watch-log`, {
                duration_seconds: elapsedSeconds
            }).catch(err => {
                console.warn('Failed to log watch time:', err);
            });
        }
        lastHeartbeatTime = now;
    }, 10000);
}

function stopHeartbeat() {
    if (watchInterval) {
        const now = Date.now();
        const elapsedSeconds = Math.round((now - lastHeartbeatTime) / 1000);
        if (elapsedSeconds >= 1 && props.contentId) {
            axios.post(`/content/${props.contentId}/watch-log`, {
                duration_seconds: elapsedSeconds
            }).catch(() => {});
        }
        clearInterval(watchInterval);
        watchInterval = null;
    }
}

watch(isPlaying, (playing) => {
    if (playing) {
        startHeartbeat();
    } else {
        stopHeartbeat();
    }
});

// Enforce 5-minute preview limit (300 seconds - temporarily 3 seconds for testing)
watch(currentTime, (newVal) => {
    if (!props.hasFullAccess && newVal >= 3) {
        pause();
        if (videoEl.value) {
            videoEl.value.currentTime = 3;
        }
        currentTime.value = 3;
        stopHeartbeat();
    }
});

watch(isPlaying, (playing) => {
    if (playing && !props.hasFullAccess && currentTime.value >= 3) {
        pause();
        if (videoEl.value) {
            videoEl.value.currentTime = 3;
        }
        currentTime.value = 3;
    }
});

onUnmounted(() => {
    stopHeartbeat();
});

function handleCheckout() {
    if (!props.contentId) return;
    router.post(`/ppv/checkout/${props.contentId}`, {}, {
        onError: (err: any) => {
            alert(err.message || 'Checkout failed');
        }
    });
}

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

        <!-- Floating Creator Chip (top-right, shows/hides with controls) -->
        <div
            class="vp-creator-chip-container"
            :class="{ 'vp-visible': controlsVisible }"
        >
            <slot name="creator-chip" />
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

                    <!-- Skip Backward 10s -->
                    <button
                        class="vp-btn vp-skip-btn"
                        title="Retroceder 10s"
                        @click.stop="seekRelative(-10)"
                    >
                        <i class="fa-solid fa-backward"></i>
                        <span class="vp-skip-label">10</span>
                    </button>

                    <!-- Skip Forward 10s -->
                    <button
                        class="vp-btn vp-skip-btn"
                        title="Adelantar 10s"
                        @click.stop="seekRelative(10)"
                    >
                        <i class="fa-solid fa-forward"></i>
                        <span class="vp-skip-label">10</span>
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

        <!-- 5-Minute Preview Paywall Overlay (B2 - temporarily 3 seconds for testing) -->
        <div 
            v-if="!hasFullAccess && currentTime >= 3"
            class="vp-paywall-overlay"
        >
            <div class="vp-paywall-content">
                <div class="vp-paywall-lock-icon">
                    <i class="fa-solid fa-lock"></i>
                </div>
                <h2 class="vp-paywall-title">Preview ended</h2>
                <p class="vp-paywall-text">
                    Unlock full access to continue watching this content.
                </p>
                
                <div class="vp-paywall-pricing">
                    <span class="vp-price-tag">${{ Number(ppvPrice).toFixed(2) }} USD</span>
                    <span v-if="isMember && rawPpvPrice !== ppvPrice" class="vp-discount-badge">
                        10% Member Discount Applied
                    </span>
                </div>

                <div class="vp-paywall-actions">
                    <button 
                        class="vp-paywall-btn primary"
                        @click="handleCheckout"
                    >
                        <i class="fa-solid fa-credit-card"></i> Buy Full Access
                    </button>
                    
                    <a 
                        v-if="allowMembership"
                        href="/subscription"
                        class="vp-paywall-btn secondary"
                    >
                        <i class="fa-solid fa-crown"></i> Subscribe to Membership
                    </a>
                </div>

                <p v-if="!isMember && allowMembership" class="vp-paywall-promo">
                    Impulse members save 10% on PPV purchases!
                </p>
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

/* === Floating Creator Chip (top-right) === */
.vp-creator-chip-container {
    position: absolute;
    top: 20px;
    right: 20px;
    z-index: 22;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.4s ease-in-out;
}

.vp-creator-chip-container.vp-visible {
    opacity: 1;
    pointer-events: auto;
}

.vp-container :deep(.vp-creator-chip) {
    display: flex;
    align-items: center;
    gap: 10px;
    background: rgba(0, 0, 0, 0.55);
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 40px;
    padding: 6px 14px 6px 6px;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
    color: #fff;
    max-width: 340px;
}

.vp-container :deep(.vp-creator-chip:hover) {
    background: rgba(0, 0, 0, 0.7);
    border-color: rgba(232, 68, 90, 0.4);
    transform: scale(1.03);
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.4);
}

.vp-container :deep(.vp-chip-avatar) {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #e8445a;
    flex-shrink: 0;
}

.vp-container :deep(.vp-chip-text) {
    display: flex;
    flex-direction: column;
    gap: 1px;
    min-width: 0;
}

.vp-container :deep(.vp-chip-name) {
    font-size: 0.8rem;
    font-weight: 600;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    line-height: 1.2;
}

.vp-container :deep(.vp-chip-handle) {
    font-size: 0.65rem;
    color: rgba(255, 255, 255, 0.55);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    line-height: 1.2;
}

.vp-container :deep(.vp-chip-tag) {
    background: rgba(232, 68, 90, 0.2);
    color: #e8445a;
    font-size: 0.6rem;
    font-weight: 700;
    padding: 2px 7px;
    border-radius: 12px;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    flex-shrink: 0;
    margin-left: auto;
}

@media (max-width: 640px) {
    .vp-creator-chip-container {
        top: 14px;
        right: 14px;
    }
    .vp-container :deep(.vp-creator-chip) {
        max-width: 220px;
        padding: 5px 10px 5px 5px;
        gap: 8px;
    }
    .vp-container :deep(.vp-chip-avatar) {
        width: 26px;
        height: 26px;
    }
    .vp-container :deep(.vp-chip-name) {
        font-size: 0.7rem;
    }
    .vp-container :deep(.vp-chip-handle) {
        display: none;
    }
    .vp-container :deep(.vp-chip-tag) {
        display: none;
    }
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

/* === Skip Buttons === */
.vp-skip-btn {
    position: relative;
    font-size: 1rem;
}

.vp-skip-label {
    position: absolute;
    bottom: 2px;
    right: 4px;
    font-size: 0.55rem;
    font-weight: 800;
    color: var(--vp-icon-color);
    pointer-events: none;
    line-height: 1;
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

/* === Paywall Overlay === */
.vp-paywall-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(10, 10, 15, 0.85);
    backdrop-filter: blur(25px);
    -webkit-backdrop-filter: blur(25px);
    z-index: 30;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    box-sizing: border-box;
}

.vp-paywall-content {
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 24px;
    padding: 40px;
    max-width: 480px;
    width: 100%;
    text-align: center;
    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5);
    animation: vp-fade-in-up 0.5s cubic-bezier(0.25, 0.8, 0.25, 1) forwards;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.vp-paywall-lock-icon {
    width: 70px;
    height: 70px;
    background: rgba(232, 68, 90, 0.1);
    border: 1px solid rgba(232, 68, 90, 0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 24px;
    color: #e8445a;
    font-size: 2rem;
    box-shadow: 0 0 20px rgba(232, 68, 90, 0.15);
    animation: vp-pulse 2s infinite;
}

.vp-paywall-title {
    font-size: 1.8rem;
    font-weight: 700;
    margin: 0 0 12px 0;
    color: #fff;
}

.vp-paywall-text {
    font-size: 0.95rem;
    color: #a0aec0;
    margin: 0 0 24px 0;
    line-height: 1.5;
}

.vp-paywall-pricing {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    margin-bottom: 30px;
}

.vp-price-tag {
    font-size: 2.25rem;
    font-weight: 800;
    color: #fff;
}

.vp-discount-badge {
    background: rgba(72, 187, 120, 0.15);
    color: #48bb78;
    font-size: 0.75rem;
    font-weight: 700;
    padding: 4px 10px;
    border-radius: 20px;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.vp-paywall-actions {
    display: flex;
    flex-direction: column;
    gap: 12px;
    width: 100%;
    margin-bottom: 20px;
}

.vp-paywall-btn {
    border: none;
    border-radius: 12px;
    padding: 14px 24px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: all 0.3s ease;
    text-decoration: none;
    width: 100%;
    box-sizing: border-box;
}

.vp-paywall-btn.primary {
    background: linear-gradient(135deg, #e8445a 0%, #b82337 100%);
    color: #fff;
    box-shadow: 0 4px 15px rgba(232, 68, 90, 0.3);
}

.vp-paywall-btn.primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(232, 68, 90, 0.5);
    background: linear-gradient(135deg, #f8546a 0%, #c83347 100%);
}

.vp-paywall-btn.secondary {
    background: rgba(255, 255, 255, 0.08);
    color: #fff;
    border: 1px solid rgba(255, 255, 255, 0.15);
}

.vp-paywall-btn.secondary:hover {
    background: rgba(255, 255, 255, 0.15);
    transform: translateY(-2px);
}

.vp-paywall-promo {
    font-size: 0.8rem;
    color: #718096;
    margin: 0;
}

@keyframes vp-fade-in-up {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes vp-pulse {
    0% {
        box-shadow: 0 0 0 0 rgba(232, 68, 90, 0.4);
    }
    70% {
        box-shadow: 0 0 0 15px rgba(232, 68, 90, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(232, 68, 90, 0);
    }
}

@media (max-width: 640px) {
    .vp-paywall-content {
        padding: 30px 20px;
    }
    .vp-paywall-title {
        font-size: 1.5rem;
    }
    .vp-price-tag {
        font-size: 1.8rem;
    }
}
</style>
