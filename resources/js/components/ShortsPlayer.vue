<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, nextTick, watch, type Ref } from 'vue';

// ─── Types ───────────────────────────────────────────────
export interface ShortUser {
    id: number;
    name: string;
    username: string;
    image_url: string;
    is_followed?: boolean;
}

export interface ShortItem {
    id: number;
    short_video_url: string;
    text_caption: string;
    user: ShortUser;
}

// ─── Props ───────────────────────────────────────────────
const props = withDefaults(
    defineProps<{
        /** All loaded shorts */
        shorts: ShortItem[];
        /** Whether more shorts are currently loading */
        isLoadingMore: boolean;
        /** Whether initial shorts have been loaded at least once */
        hasInitialShorts: boolean;
        /** Unique container id (to avoid collisions when multiple instances exist) */
        containerId?: string;
        /** Text to show during initial load */
        loadingText?: string;
        /** Text to show when no shorts are available */
        emptyText?: string;
        /** Auto-play video after transition */
        autoPlay?: boolean;
        /** Controls auto-hide delay in ms */
        controlsHideDelay?: number;
        /** Seconds to seek with arrow keys */
        seekStep?: number;
        /** Delay before auto-advancing after video ends (ms) */
        autoAdvanceDelay?: number;
    }>(),
    {
        containerId: 'shortsPlayerContainer',
        loadingText: 'Loading...',
        emptyText: 'No shorts available right now.',
        autoPlay: true,
        controlsHideDelay: 3000,
        seekStep: 10,
        autoAdvanceDelay: 1500,
    },
);

// ─── Emits ───────────────────────────────────────────────
const emit = defineEmits<{
    /** Request more shorts to be loaded */
    loadMore: [];
    /** User clicked on user avatar / username */
    userClick: [event: Event, user: ShortUser];
    /** User clicked follow / unfollow */
    followClick: [event: Event, user: ShortUser];
    /** Generic interaction intercepted (e.g. public mode login prompt) */
    interaction: [event: Event];
    /** Retry loading after empty state */
    retry: [];
    /** Current short changed */
    change: [short: ShortItem, index: number];
}>();

// ─── Navigation state ────────────────────────────────────
const currentShortIndex = ref(-1);
const currentShort = ref<ShortItem | null>(null);
const previousShort = ref<ShortItem | null>(null);
const nextShortPreview = ref<ShortItem | null>(null);

// Transition state
const transitionDirection = ref<'up' | 'down' | null>(null);
const isTransitioning = ref(false);

// ─── Player state ────────────────────────────────────────
const containerRef = ref<HTMLDivElement>();
const playerRef = ref<HTMLDivElement>();
const videoPlayer = ref<HTMLVideoElement>();
const isPlaying = ref(false);
const showControls = ref(true);
const isMuted = ref(true); // Start muted so autoplay works on all browsers
const currentTime = ref(0);
const duration = ref(0);
const isFullscreen = ref(false);

// Touch / swipe state
const touchStartY = ref(0);
const touchStartX = ref(0);
const touchEndY = ref(0);
const touchEndX = ref(0);
const isDragging = ref(false);
const MIN_SWIPE_DISTANCE = 50;

let controlsTimeout: ReturnType<typeof setTimeout> | null = null;
let rafId: number | null = null;

// ─── Computed ────────────────────────────────────────────
const progressPercent = computed(() =>
    duration.value > 0 ? (currentTime.value / duration.value) * 100 : 0,
);

const canGoPrev = computed(() => currentShortIndex.value > 0);
const canGoNext = computed(() => currentShort.value !== null);

// ─── Watch for external shorts changes ──────────────────
watch(
    () => props.shorts,
    (newShorts) => {
        if (newShorts.length > 0 && currentShortIndex.value === -1) {
            currentShortIndex.value = 0;
            syncCurrentShort();
        }
    },
    { deep: true },
);

// ─── Navigation ──────────────────────────────────────────
function syncCurrentShort() {
    const list = props.shorts;
    const idx = currentShortIndex.value;

    if (list.length > 0 && idx >= 0 && idx < list.length) {
        currentShort.value = list[idx];
        previousShort.value = idx > 0 ? list[idx - 1] : null;
        nextShortPreview.value = idx < list.length - 1 ? list[idx + 1] : null;
        resetVideo();
        emit('change', list[idx], idx);
    }
}

function checkLoadMore() {
    const remaining = props.shorts.length - currentShortIndex.value - 1;
    if (remaining <= 3 && !props.isLoadingMore) {
        emit('loadMore');
    }
}

const nextShort = () => {
    if (isTransitioning.value) return;

    if (props.shorts.length === 0) {
        emit('loadMore');
        return;
    }

    const isLast = currentShortIndex.value === props.shorts.length - 1;

    if (!isLast) {
        performTransition('up', () => {
            currentShortIndex.value++;
            syncCurrentShort();
            checkLoadMore();
        });
    } else {
        emit('loadMore');
    }
};

const prevShort = () => {
    if (isTransitioning.value || props.shorts.length === 0) return;

    if (currentShortIndex.value > 0) {
        performTransition('down', () => {
            currentShortIndex.value--;
            syncCurrentShort();
        });
    }
};

function performTransition(direction: 'up' | 'down', onMid: () => void) {
    transitionDirection.value = direction;
    isTransitioning.value = true;

    setTimeout(() => {
        onMid();
        setTimeout(() => {
            transitionDirection.value = null;
            isTransitioning.value = false;
        }, 400);
    }, 100);
}

// ─── Video controls ──────────────────────────────────────
function resetVideo() {
    cancelRaf();

    if (videoPlayer.value) {
        videoPlayer.value.currentTime = 0;
        currentTime.value = 0;
        isPlaying.value = false;
    }

    showControls.value = true;
    showControlsTemporarily();

    if (props.autoPlay) {
        nextTick(() => {
            setTimeout(() => {
                if (videoPlayer.value && currentShort.value) {
                    videoPlayer.value
                        .play()
                        .then(() => {
                            isPlaying.value = true;
                            startProgressLoop();
                        })
                        .catch(() => {
                            isPlaying.value = false;
                        });
                }
            }, 300);
        });
    }
}

const togglePlayPause = (event?: Event) => {
    if (event) {
        event.stopPropagation();
        event.preventDefault();
    }
    if (!videoPlayer.value) return;

    if (videoPlayer.value.paused) {
        videoPlayer.value.play();
        isPlaying.value = true;
        startProgressLoop();
    } else {
        videoPlayer.value.pause();
        isPlaying.value = false;
        cancelRaf();
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
    if (controlsTimeout) clearTimeout(controlsTimeout);
    controlsTimeout = setTimeout(() => {
        if (isPlaying.value) showControls.value = false;
    }, props.controlsHideDelay);
};

// ─── Progress with requestAnimationFrame ─────────────────
function startProgressLoop() {
    cancelRaf();
    const tick = () => {
        if (videoPlayer.value) {
            currentTime.value = videoPlayer.value.currentTime;
        }
        rafId = requestAnimationFrame(tick);
    };
    rafId = requestAnimationFrame(tick);
}

function cancelRaf() {
    if (rafId !== null) {
        cancelAnimationFrame(rafId);
        rafId = null;
    }
}

// ─── Video event handlers ────────────────────────────────
const handleVideoLoaded = () => {
    if (videoPlayer.value && currentShort.value) {
        duration.value = videoPlayer.value.duration;
        if (videoPlayer.value.paused && props.autoPlay) {
            videoPlayer.value
                .play()
                .then(() => {
                    isPlaying.value = true;
                    startProgressLoop();
                    showControlsTemporarily();
                })
                .catch(() => {
                    isPlaying.value = false;
                });
        }
    }
};

const handleVideoEnded = () => {
    isPlaying.value = false;
    cancelRaf();
    setTimeout(() => nextShort(), props.autoAdvanceDelay);
};

const handleVideoPlay = () => {
    isPlaying.value = true;
    startProgressLoop();
};

const handleVideoPause = () => {
    isPlaying.value = false;
    cancelRaf();
};

const handleVideoClick = (event: MouseEvent) => {
    event.stopPropagation();
    event.preventDefault();
    const target = event.target as HTMLElement;
    if (target === videoPlayer.value || target.classList.contains('main-video')) {
        togglePlayPause();
    }
};

const handleContainerClick = (event: MouseEvent) => {
    const target = event.target as HTMLElement;
    if (
        !target.closest('.video-controls') &&
        !target.closest('.follow-btn') &&
        !target.closest('.user-info') &&
        !target.closest('.video-overlay')
    ) {
        showControlsTemporarily();
    }
};

const handleProgressClick = (event: MouseEvent) => {
    event.stopPropagation();
    event.preventDefault();
    if (!videoPlayer.value) return;

    const rect = (event.currentTarget as HTMLElement).getBoundingClientRect();
    const ratio = Math.max(0, Math.min(1, (event.clientX - rect.left) / rect.width));
    videoPlayer.value.currentTime = ratio * videoPlayer.value.duration;
    currentTime.value = videoPlayer.value.currentTime;
    showControlsTemporarily();
};

// ─── Keyboard ────────────────────────────────────────────
const handleKeyPress = (event: KeyboardEvent) => {
    switch (event.code) {
        case 'Space':
            event.preventDefault();
            togglePlayPause();
            break;
        case 'ArrowUp':
            event.preventDefault();
            prevShort();
            break;
        case 'ArrowDown':
            event.preventDefault();
            nextShort();
            break;
        case 'ArrowLeft':
            event.preventDefault();
            if (videoPlayer.value) {
                videoPlayer.value.currentTime = Math.max(0, videoPlayer.value.currentTime - props.seekStep);
            }
            break;
        case 'ArrowRight':
            event.preventDefault();
            if (videoPlayer.value) {
                videoPlayer.value.currentTime = Math.min(
                    videoPlayer.value.duration,
                    videoPlayer.value.currentTime + props.seekStep,
                );
            }
            break;
        case 'KeyM':
            event.preventDefault();
            toggleMute();
            break;
        case 'KeyF':
            event.preventDefault();
            toggleFullscreen();
            break;
    }
};

// ─── Touch / swipe ───────────────────────────────────────
const handleTouchStart = (event: TouchEvent) => {
    const target = event.target as HTMLElement;
    if (
        target.closest('.video-controls') ||
        target.closest('.follow-btn') ||
        target.closest('.user-info') ||
        target.closest('.video-overlay')
    ) {
        return;
    }
    touchStartY.value = event.touches[0].clientY;
    touchStartX.value = event.touches[0].clientX;
    isDragging.value = true;
    showControlsTemporarily();
};

const handleTouchMove = (event: TouchEvent) => {
    if (!isDragging.value) return;
    touchEndY.value = event.touches[0].clientY;
    touchEndX.value = event.touches[0].clientX;
    const deltaY = touchEndY.value - touchStartY.value;
    if (Math.abs(deltaY) > 10) event.preventDefault();
};

const handleTouchEnd = (event: TouchEvent) => {
    if (!isDragging.value) return;

    const endY = touchEndY.value || event.changedTouches[0].clientY;
    const endX = touchEndX.value || event.changedTouches[0].clientX;
    const deltaY = endY - touchStartY.value;
    const deltaX = endX - touchStartX.value;
    const isVertical = Math.abs(deltaY) > Math.abs(deltaX);

    if (isVertical && Math.abs(deltaY) > MIN_SWIPE_DISTANCE) {
        if (deltaY > 0) prevShort();
        else nextShort();
    }

    isDragging.value = false;
    touchStartY.value = touchStartX.value = touchEndY.value = touchEndX.value = 0;
};

// ─── Fullscreen ──────────────────────────────────────────
const toggleFullscreen = async (event?: Event) => {
    if (event) {
        event.stopPropagation();
        event.preventDefault();
    }
    if (!playerRef.value) return;

    try {
        if (!document.fullscreenElement) {
            await playerRef.value.requestFullscreen();
        } else {
            await document.exitFullscreen();
        }
    } catch (err) {
        console.warn('Fullscreen not supported:', err);
    }
};

function onFullscreenChange() {
    isFullscreen.value = !!document.fullscreenElement;
}

// ─── Helpers ─────────────────────────────────────────────
function formatTime(seconds: number): string {
    const m = Math.floor(seconds / 60);
    const s = Math.floor(seconds % 60);
    return `${m}:${s.toString().padStart(2, '0')}`;
}

// ─── Public API (expose for parent access) ───────────────
defineExpose({
    nextShort,
    prevShort,
    togglePlayPause,
    toggleMute,
    toggleFullscreen,
    currentShort,
    currentShortIndex,
    isPlaying,
    isMuted,
    isFullscreen,
});

// ─── Lifecycle ───────────────────────────────────────────
onMounted(() => {
    document.addEventListener('keydown', handleKeyPress);
    document.addEventListener('fullscreenchange', onFullscreenChange);

    nextTick(() => {
        const container = containerRef.value;
        if (container) {
            container.addEventListener('touchstart', handleTouchStart, { passive: true });
            container.addEventListener('touchmove', handleTouchMove, { passive: false });
            container.addEventListener('touchend', handleTouchEnd, { passive: true });
        }
    });
});

onUnmounted(() => {
    document.removeEventListener('keydown', handleKeyPress);
    document.removeEventListener('fullscreenchange', onFullscreenChange);

    const container = containerRef.value;
    if (container) {
        container.removeEventListener('touchstart', handleTouchStart);
        container.removeEventListener('touchmove', handleTouchMove);
        container.removeEventListener('touchend', handleTouchEnd);
    }

    if (controlsTimeout) clearTimeout(controlsTimeout);
    cancelRaf();
});
</script>

<template>
    <div
        ref="containerRef"
        class="shorts-container"
        :id="containerId"
        @click="handleContainerClick"
    >
        <div class="short-video-slide">
            <!-- ── Loading state ── -->
            <div
                v-if="!hasInitialShorts && isLoadingMore"
                class="shorts-loading-state"
            >
                <div class="shorts-loading-content">
                    <i class="fa-solid fa-circle-notch fa-spin shorts-loading-icon"></i>
                    <p>{{ loadingText }}</p>
                </div>
            </div>

            <!-- ── Main content ── -->
            <div v-else-if="currentShort" class="shorts-viewport">
                <!-- Previous short preview -->
                <div
                    v-if="previousShort && transitionDirection === 'down'"
                    class="short-player preview-previous"
                >
                    <video
                        class="main-video"
                        :src="previousShort.short_video_url"
                        preload="metadata"
                        
                    />
                </div>

                <!-- Current short -->
                <div
                    ref="playerRef"
                    class="short-player current-short"
                    :class="{
                        'slide-out-up': transitionDirection === 'up',
                        'slide-out-down': transitionDirection === 'down',
                        'is-fullscreen': isFullscreen,
                    }"
                >
                    <video
                        ref="videoPlayer"
                        class="main-video"
                        :src="currentShort.short_video_url"
                        preload="metadata"
                        loop
                        :muted="isMuted"
                        autoplay
                        playsinline
                        @click="handleVideoClick"
                        @loadeddata="handleVideoLoaded"
                        @ended="handleVideoEnded"
                        @play="handleVideoPlay"
                        @pause="handleVideoPause"
                    />

                    <!-- Video controls -->
                    <div v-show="showControls" class="video-controls" @click.stop>
                        <button @click="togglePlayPause($event)" class="control-btn" aria-label="Play / Pause">
                            <i :class="isPlaying ? 'fas fa-pause' : 'fas fa-play'"></i>
                        </button>

                        <div class="progress-container" @click="handleProgressClick($event)">
                            <div class="progress-track">
                                <div class="progress-fill" :style="{ width: progressPercent + '%' }"></div>
                            </div>
                        </div>

                        <div class="time-display">
                            {{ formatTime(currentTime) }} / {{ formatTime(duration) }}
                        </div>

                        <button @click="toggleMute($event)" class="control-btn" aria-label="Mute / Unmute">
                            <i :class="isMuted ? 'fas fa-volume-mute' : 'fas fa-volume-high'"></i>
                        </button>

                        <button @click="toggleFullscreen($event)" class="control-btn" aria-label="Fullscreen">
                            <i :class="isFullscreen ? 'fas fa-compress' : 'fas fa-expand'"></i>
                        </button>
                    </div>

                    <!-- Overlay slot — parent provides user info / follow / description etc. -->
                    <div class="video-overlay" @click.stop>
                        <slot
                            name="overlay"
                            :short="currentShort"
                            :isPlaying="isPlaying"
                        >
                            <!-- Default overlay if no slot is provided -->
                            <div class="user-info">
                                <div class="user-avatar-container">
                                    <img
                                        :src="currentShort.user.image_url"
                                        :alt="currentShort.user.username"
                                        class="user-avatar"
                                        loading="lazy"
                                    />
                                </div>
                                <span class="username">@{{ currentShort.user.username }}</span>
                            </div>
                            <p v-if="currentShort.text_caption" class="video-description">
                                {{ currentShort.text_caption }}
                            </p>
                        </slot>
                    </div>

                    <!-- Loading more indicator -->
                    <div v-if="isLoadingMore && hasInitialShorts" class="loading-more-indicator">
                        <i class="fa-solid fa-circle-notch fa-spin"></i>
                    </div>
                </div>

                <!-- Next short preview -->
                <div
                    v-if="nextShortPreview && transitionDirection === 'up'"
                    class="short-player preview-next"
                >
                    <video
                        class="main-video"
                        :src="nextShortPreview.short_video_url"
                        preload="metadata"
                    />
                </div>
            </div>

            <!-- ── Empty state ── -->
            <div
                v-else-if="shorts.length === 0 && !isLoadingMore"
                class="short-player shorts-empty-state"
            >
                <div class="shorts-empty-content">
                    <p>{{ emptyText }}</p>
                    <button class="shorts-retry-btn" @click="emit('retry')">
                        Retry
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation arrows (desktop) -->
    <button
        class="shorts-nav-arrow up"
        aria-label="Previous short"
        @click.stop="prevShort"
        :disabled="!canGoPrev"
    >
        <i class="fa-solid fa-chevron-up"></i>
    </button>
    <button
        class="shorts-nav-arrow down"
        aria-label="Next short"
        @click.stop="nextShort"
        :disabled="!canGoNext"
    >
        <i class="fa-solid fa-chevron-down"></i>
    </button>
</template>

<style scoped>
/* ──────────────────────────────────────
   Container
────────────────────────────────────── */
.shorts-container {
    flex-grow: 1;
    width: 100%;
    height: 100%;
    overflow-y: scroll;
    scroll-snap-type: y mandatory;
    -ms-overflow-style: none;
    scrollbar-width: none;
    touch-action: pan-y;
    position: relative;
}

.shorts-container::-webkit-scrollbar {
    display: none;
}

/* ──────────────────────────────────────
   Slide wrapper
────────────────────────────────────── */
.short-video-slide {
    width: 100%;
    height: 100%;
    position: relative;
    scroll-snap-align: start;
    display: flex;
    align-items: center;
    justify-content: center;
    box-sizing: border-box;
    padding: 1.5rem;
}

/* ──────────────────────────────────────
   Viewport (transition container)
────────────────────────────────────── */
.shorts-viewport {
    position: relative;
    width: 100%;
    height: 100%;
    overflow: hidden;
    flex: 1;
    display: flex;
    justify-content: center;
    align-items: center;
    padding-bottom: 80px;
}

/* ──────────────────────────────────────
   Player — 9:16 aspect ratio
────────────────────────────────────── */
.short-player {
    position: relative;
    width: 100%;
    max-width: 400px;
    height: 100%;
    max-height: calc(100vh - 160px);
    aspect-ratio: 9 / 16;
    background-color: #000;
    border-radius: 16px;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
}

.short-player.is-fullscreen {
    max-width: 100vw;
    max-height: 100vh;
    border-radius: 0;
    aspect-ratio: auto;
}

.short-player video {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

/* ──────────────────────────────────────
   Transition animations
────────────────────────────────────── */
@keyframes slideOutUp {
    from {
        transform: translateY(0);
        opacity: 1;
    }
    to {
        transform: translateY(-100%);
        opacity: 0.5;
    }
}

@keyframes slideOutDown {
    from {
        transform: translateY(0);
        opacity: 1;
    }
    to {
        transform: translateY(100%);
        opacity: 0.5;
    }
}

@keyframes slideInFromBottom {
    from {
        transform: translateY(100%);
        opacity: 0.5;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

@keyframes slideInFromTop {
    from {
        transform: translateY(-100%);
        opacity: 0.5;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

.current-short {
    z-index: 10;
    will-change: transform, opacity;
}

.slide-out-up {
    animation: slideOutUp 0.4s ease-out forwards;
}

.slide-out-down {
    animation: slideOutDown 0.4s ease-out forwards;
}

.preview-next {
    z-index: 5;
    position: absolute;
    top: 50%;
    width: 100%;
    animation: slideInFromBottom 0.4s ease-out forwards;
    display: flex;
    justify-content: center;
    align-items: center;
    will-change: transform, opacity;
}

.preview-previous {
    z-index: 5;
    position: absolute;
    top: 50%;
    width: 100%;
    animation: slideInFromTop 0.4s ease-out forwards;
    display: flex;
    justify-content: center;
    align-items: center;
    will-change: transform, opacity;
}

/* ──────────────────────────────────────
   Video overlay
────────────────────────────────────── */
.video-overlay {
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    padding: 1rem;
    padding-bottom: 45px;
    color: #fff;
    background: linear-gradient(to top, rgba(0, 0, 0, 0.7), transparent);
    box-sizing: border-box;
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    z-index: 25;
    pointer-events: auto;
}

.user-info {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.user-avatar-container,
.username {
    cursor: pointer;
    pointer-events: auto;
}

.user-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
}

.username {
    font-weight: 600;
    font-size: 1rem;
    color: #fff;
}

.follow-btn {
    background-color: #e8445a;
    color: #fff;
    border: none;
    border-radius: 8px;
    padding: 0.3rem 0.8rem;
    font-size: 0.8rem;
    font-weight: 600;
    cursor: pointer;
    transition: background-color 0.2s;
    min-height: 44px;
    min-width: 44px;
    display: flex;
    align-items: center;
    justify-content: center;
    pointer-events: auto;
}

.follow-btn:hover {
    background-color: #d84373;
}

.video-description {
    font-size: 0.9rem;
    margin: 0;
    line-height: 1.4;
    color: #fff;
}

/* ──────────────────────────────────────
   Video controls
────────────────────────────────────── */
.video-controls {
    position: absolute;
    bottom: 10px;
    left: 10px;
    right: 10px;
    display: flex;
    align-items: center;
    gap: 10px;
    z-index: 30;
    pointer-events: auto;
}

.control-btn {
    background: none;
    border: none;
    color: #fff;
    font-size: 1.2rem;
    cursor: pointer;
    padding: 5px;
    flex-shrink: 0;
    min-height: 44px;
    min-width: 44px;
    display: flex;
    align-items: center;
    justify-content: center;
    pointer-events: auto;
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
    background-color: #e8445a;
    border-radius: 2px;
    transition: width 0.1s linear;
}

.time-display {
    flex-shrink: 0;
    color: #fff;
    font-size: 0.8rem;
    font-weight: 500;
    min-width: 80px;
    text-align: center;
}

/* ──────────────────────────────────────
   Loading / empty states
────────────────────────────────────── */
.shorts-loading-state {
    height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
}

.shorts-loading-content {
    color: #fff;
    text-align: center;
}

.shorts-loading-icon {
    font-size: 2rem;
    margin-bottom: 1rem;
}

.shorts-empty-state {
    width: 100%;
    height: 100vh;
    background-color: #000;
    display: flex;
    align-items: center;
    justify-content: center;
}

.shorts-empty-content {
    color: #fff;
    text-align: center;
}

.shorts-retry-btn {
    margin-top: 1rem;
    padding: 0.5rem 1rem;
    background: #007bff;
    color: #fff;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    transition: background 0.2s;
}

.shorts-retry-btn:hover {
    background: #0069d9;
}

.loading-more-indicator {
    position: absolute;
    bottom: 100px;
    left: 50%;
    transform: translateX(-50%);
    background: rgba(0, 0, 0, 0.7);
    color: #fff;
    padding: 10px;
    border-radius: 5px;
    z-index: 35;
}

/* ──────────────────────────────────────
   Navigation arrows
────────────────────────────────────── */
@media (max-width: 768px) {
    .shorts-nav-arrow {
        display: none;
    }

    .control-btn {
        padding: 12px;
        font-size: 1.4rem;
    }

    .follow-btn {
        padding: 8px 16px;
        font-size: 0.9rem;
    }

    .user-avatar {
        width: 44px;
        height: 44px;
    }
}

@media (min-width: 769px) {
    .shorts-nav-arrow {
        display: block;
        position: fixed;
        right: 20px;
        background: rgba(255, 255, 255, 0.2);
        border: none;
        border-radius: 50%;
        width: 50px;
        height: 50px;
        color: #fff;
        font-size: 1.2rem;
        cursor: pointer;
        z-index: 1000;
        transition: background 0.3s ease;
    }

    .shorts-nav-arrow:hover {
        background: rgba(255, 255, 255, 0.3);
    }

    .shorts-nav-arrow.up {
        top: 50%;
        transform: translateY(-100%);
    }

    .shorts-nav-arrow.down {
        bottom: 50%;
        transform: translateY(100%);
        margin-bottom: -20px;
    }

    .shorts-nav-arrow:disabled {
        opacity: 0.3;
        cursor: not-allowed;
    }
}
</style>
