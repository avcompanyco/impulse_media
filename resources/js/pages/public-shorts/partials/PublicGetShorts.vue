<script setup lang="ts">
import { ref, onMounted, onUnmounted, nextTick, watch } from 'vue';

interface User {
    id: number;
    name: string;
    username: string;
    image_url: string;
}

interface Short {
    id: number;
    short_video_url: string;
    text_caption: string;
    user: User;
}

const emit = defineEmits<{
    requireLogin: [];
}>();

// Reactive data
const shorts = ref<Short[]>([]);
const currentShortIndex = ref(-1);
const isLoadingMoreShorts = ref(false);
const hasInitialShorts = ref(false);

// Transition state
const transitionDirection = ref<'up' | 'down' | null>(null);
const isTransitioning = ref(false);
const previousShort = ref<Short | null>(null);
const nextShortPreview = ref<Short | null>(null);

async function getNextTenShorts() {
    if (isLoadingMoreShorts.value) return;

    isLoadingMoreShorts.value = true;
    try {
        const response = await fetch('/public/shorts/random', {
            method: 'GET',
            headers: { 'Accept': 'application/json' },
        }).then(r => r.json());

        if (response.shorts && response.shorts.length > 0) {
            const newShorts = response.shorts;
            shorts.value.push(...newShorts);

            if (currentShortIndex.value === -1 && newShorts.length > 0) {
                currentShortIndex.value = 0;
                updateCurrentShort();
                hasInitialShorts.value = true;
            }
        }
    } catch (error) {
        console.error('Error loading public shorts:', error);
    } finally {
        isLoadingMoreShorts.value = false;
    }
}

watch(shorts, (newShorts) => {
    if (newShorts.length > 0 && currentShortIndex.value === -1) {
        currentShortIndex.value = 0;
        updateCurrentShort();
        hasInitialShorts.value = true;
    }
}, { deep: true });

function checkLoadMoreShorts() {
    const remaining = shorts.value.length - currentShortIndex.value - 1;
    if (remaining <= 3 && !isLoadingMoreShorts.value) {
        getNextTenShorts();
    }
}

const currentShort = ref<Short | null>(null);

function updateCurrentShort() {
    if (
        shorts.value.length > 0 &&
        currentShortIndex.value >= 0 &&
        currentShortIndex.value < shorts.value.length
    ) {
        currentShort.value = shorts.value[currentShortIndex.value];
        previousShort.value = currentShortIndex.value > 0 ? shorts.value[currentShortIndex.value - 1] : null;
        nextShortPreview.value =
            currentShortIndex.value < shorts.value.length - 1 ? shorts.value[currentShortIndex.value + 1] : null;
        resetVideo();
    }
}

const nextShort = () => {
    if (isTransitioning.value) return;

    if (shorts.value.length === 0) {
        getNextTenShorts();
        return;
    }

    const isLast = currentShortIndex.value === shorts.value.length - 1;

    if (!isLast) {
        transitionDirection.value = 'up';
        isTransitioning.value = true;
        setTimeout(() => {
            currentShortIndex.value++;
            updateCurrentShort();
            checkLoadMoreShorts();
            setTimeout(() => {
                transitionDirection.value = null;
                isTransitioning.value = false;
            }, 400);
        }, 100);
    } else {
        getNextTenShorts().then(() => {
            if (currentShortIndex.value < shorts.value.length - 1) {
                transitionDirection.value = 'up';
                isTransitioning.value = true;
                setTimeout(() => {
                    currentShortIndex.value++;
                    updateCurrentShort();
                    setTimeout(() => {
                        transitionDirection.value = null;
                        isTransitioning.value = false;
                    }, 400);
                }, 100);
            }
        });
    }
};

const prevShort = () => {
    if (isTransitioning.value || shorts.value.length === 0) return;

    if (currentShortIndex.value > 0) {
        transitionDirection.value = 'down';
        isTransitioning.value = true;
        setTimeout(() => {
            currentShortIndex.value--;
            updateCurrentShort();
            setTimeout(() => {
                transitionDirection.value = null;
                isTransitioning.value = false;
            }, 400);
        }, 100);
    }
};

function resetVideo() {
    if (videoPlayer.value) {
        videoPlayer.value.currentTime = 0;
        currentTime.value = 0;
        isPlaying.value = false;
    }
    showControls.value = true;
    showControlsTemporarily();

    nextTick(() => {
        setTimeout(() => {
            if (videoPlayer.value && currentShort.value) {
                videoPlayer.value.play().then(() => {
                    isPlaying.value = true;
                }).catch(() => {
                    isPlaying.value = false;
                });
            }
        }, 300);
    });
}

// Player refs
const videoContainer = ref<HTMLDivElement>();
const videoPlayer = ref<HTMLVideoElement>();
const isPlaying = ref(false);
const showControls = ref(true);
const isMuted = ref(false);
const currentTime = ref(0);
const duration = ref(0);

// Touch handling
const touchStartY = ref(0);
const touchStartX = ref(0);
const touchEndY = ref(0);
const touchEndX = ref(0);
const isDragging = ref(false);
const minSwipeDistance = 50;

let controlsTimeout: number;

const togglePlayPause = (event?: Event) => {
    if (event) { event.stopPropagation(); event.preventDefault(); }
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
    if (event) { event.stopPropagation(); event.preventDefault(); }
    if (!videoPlayer.value) return;
    videoPlayer.value.muted = !videoPlayer.value.muted;
    isMuted.value = videoPlayer.value.muted;
    showControlsTemporarily();
};

const showControlsTemporarily = () => {
    showControls.value = true;
    clearTimeout(controlsTimeout);
    controlsTimeout = setTimeout(() => {
        if (isPlaying.value) showControls.value = false;
    }, 3000);
};

const handleKeyPress = (event: KeyboardEvent) => {
    switch (event.code) {
        case 'Space': event.preventDefault(); togglePlayPause(); break;
        case 'ArrowUp': event.preventDefault(); prevShort(); break;
        case 'ArrowDown': event.preventDefault(); nextShort(); break;
        case 'ArrowLeft':
            event.preventDefault();
            if (videoPlayer.value) videoPlayer.value.currentTime = Math.max(0, videoPlayer.value.currentTime - 10);
            break;
        case 'ArrowRight':
            event.preventDefault();
            if (videoPlayer.value) videoPlayer.value.currentTime = Math.min(videoPlayer.value.duration, videoPlayer.value.currentTime + 10);
            break;
        case 'KeyM': event.preventDefault(); toggleMute(); break;
    }
};

const handleTouchStart = (event: TouchEvent) => {
    const target = event.target as HTMLElement;
    if (target.closest('.video-controls') || target.closest('.video-overlay')) return;
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
    if (isVertical && Math.abs(deltaY) > minSwipeDistance) {
        if (deltaY > 0) prevShort(); else nextShort();
    }
    isDragging.value = false;
    touchStartY.value = touchStartX.value = touchEndY.value = touchEndX.value = 0;
};

const handleVideoLoaded = () => {
    if (videoPlayer.value && currentShort.value) {
        duration.value = videoPlayer.value.duration;
        if (videoPlayer.value.paused) {
            videoPlayer.value.play().then(() => {
                isPlaying.value = true;
                showControlsTemporarily();
            }).catch(() => { isPlaying.value = false; });
        }
    }
};

const handleVideoEnded = () => {
    isPlaying.value = false;
    setTimeout(() => nextShort(), 1500);
};

const handleVideoClick = (event: MouseEvent) => {
    event.stopPropagation();
    event.preventDefault();
    const target = event.target as HTMLElement;
    if (target === videoPlayer.value || target.classList.contains('main-video')) togglePlayPause();
};

const handleContainerClick = (event: MouseEvent) => {
    const target = event.target as HTMLElement;
    if (!target.closest('.video-controls') && !target.closest('.video-overlay')) showControlsTemporarily();
};

const updateProgress = () => {
    if (videoPlayer.value) currentTime.value = videoPlayer.value.currentTime;
};

const handleProgressClick = (event: MouseEvent) => {
    event.stopPropagation();
    event.preventDefault();
    if (!videoPlayer.value) return;
    const rect = (event.target as HTMLElement).getBoundingClientRect();
    videoPlayer.value.currentTime = ((event.clientX - rect.left) / rect.width) * videoPlayer.value.duration;
    showControlsTemporarily();
};

const formatTime = (seconds: number): string => {
    const m = Math.floor(seconds / 60);
    const s = Math.floor(seconds % 60);
    return `${m}:${s.toString().padStart(2, '0')}`;
};

// Intercept interactions — emit up to parent so it can show login modal
const handleInteraction = (event: Event) => {
    event.stopPropagation();
    event.preventDefault();
    emit('requireLogin');
};

onMounted(() => {
    getNextTenShorts();
    document.addEventListener('keydown', handleKeyPress);
    const container = document.getElementById('publicShortsContainer');
    if (container) {
        container.addEventListener('touchstart', handleTouchStart, { passive: true });
        container.addEventListener('touchmove', handleTouchMove, { passive: false });
        container.addEventListener('touchend', handleTouchEnd, { passive: true });
    }
});

onUnmounted(() => {
    document.removeEventListener('keydown', handleKeyPress);
    const container = document.getElementById('publicShortsContainer');
    if (container) {
        container.removeEventListener('touchstart', handleTouchStart);
        container.removeEventListener('touchmove', handleTouchMove);
        container.removeEventListener('touchend', handleTouchEnd);
    }
    clearTimeout(controlsTimeout);
});
</script>

<template>
    <div class="shorts-container" id="publicShortsContainer" @click="handleContainerClick"
        style="height: 100%; touch-action: pan-y; position: relative;">
        <div class="short-video-slide">

            <!-- Loading state -->
            <div v-if="!hasInitialShorts && isLoadingMoreShorts"
                style="height: 100vh; display: flex; align-items: center; justify-content: center;">
                <div style="color: white; text-align: center;">
                    <i class="fa-solid fa-circle-notch fa-spin" style="font-size: 2rem; margin-bottom: 1rem;"></i>
                    <p>Loading...</p>
                </div>
            </div>

            <!-- Main content when shorts are loaded -->
            <div v-else-if="currentShort" class="shorts-viewport">
                <!-- Previous short preview -->
                <div v-if="previousShort && transitionDirection === 'down'"
                    class="short-player preview-previous"
                    style="width: 100%; height: 100vh; background-color: #000; position: absolute; top: 50%;">
                    <video class="main-video" :src="previousShort.short_video_url" preload="metadata"
                        style="width: 100%; height: 100%; object-fit: cover;" />
                </div>

                <!-- Current short -->
                <div class="short-player current-short"
                    :class="{ 'slide-out-up': transitionDirection === 'up', 'slide-out-down': transitionDirection === 'down' }"
                    style="width: 100%; height: 100vh; background-color: #000; position: relative;">

                    <video ref="videoPlayer" class="main-video" :src="currentShort.short_video_url"
                        preload="metadata" loop :muted="isMuted"
                        @click="handleVideoClick"
                        @loadeddata="handleVideoLoaded"
                        @ended="handleVideoEnded"
                        @play="isPlaying = true"
                        @pause="isPlaying = false"
                        @timeupdate="updateProgress"
                        style="width: 100%; height: 100%; object-fit: cover;" />

                    <!-- Video controls -->
                    <div v-show="showControls" class="video-controls" @click.stop style="z-index: 99999;">
                        <button @click="togglePlayPause($event)" class="control-btn">
                            <i v-if="isPlaying" class="fas fa-pause"></i>
                            <i v-else class="fas fa-play"></i>
                        </button>
                        <div class="progress-container" @click="handleProgressClick($event)">
                            <div class="progress-track">
                                <div class="progress-fill"
                                    :style="{ width: duration > 0 ? (currentTime / duration) * 100 + '%' : '0%' }"></div>
                            </div>
                        </div>
                        <div class="time-display">
                            <span>{{ formatTime(currentTime) }} / {{ formatTime(duration) }}</span>
                        </div>
                        <button @click="toggleMute($event)" class="control-btn">
                            <i v-if="isMuted" class="fas fa-volume-mute"></i>
                            <i v-else class="fas fa-volume-high"></i>
                        </button>
                    </div>

                    <!-- Video overlay with user info -->
                    <div class="video-overlay" @click.stop>
                        <div class="user-info">
                            <!-- Profile picture / username — clicking prompts login -->
                            <div class="user-avatar-container" @click.stop="handleInteraction($event)">
                                <img :src="currentShort.user.image_url" alt="User Avatar" class="user-avatar">
                            </div>
                            <span class="username" @click.stop="handleInteraction($event)">
                                @{{ currentShort.user.username }}
                            </span>
                            <!-- Follow button — clicking prompts login -->
                            <button class="follow-btn" @click.stop="handleInteraction($event)">
                                Follow
                            </button>
                        </div>
                        <p class="video-description">{{ currentShort.text_caption }}</p>
                    </div>

                    <!-- Loading more indicator -->
                    <div v-if="isLoadingMoreShorts && hasInitialShorts"
                        style="position: absolute; bottom: 100px; left: 50%; transform: translateX(-50%); background: rgba(0,0,0,0.7); color: white; padding: 10px; border-radius: 5px;">
                        <i class="fa-solid fa-circle-notch fa-spin"></i>
                    </div>
                </div>

                <!-- Next short preview -->
                <div v-if="nextShortPreview && transitionDirection === 'up'"
                    class="short-player preview-next"
                    style="width: 100%; height: 100vh; background-color: #000; position: absolute; top: 50%;">
                    <video class="main-video" :src="nextShortPreview.short_video_url" preload="metadata"
                        style="width: 100%; height: 100%; object-fit: cover;" />
                </div>
            </div>

            <!-- Empty state -->
            <div v-else-if="shorts.length === 0 && !isLoadingMoreShorts" class="short-player"
                style="width: 100%; height: 100vh; background-color: #000; display: flex; align-items: center; justify-content: center;">
                <div style="color: white; text-align: center;">
                    <p style="margin-bottom: 1rem;">No shorts available right now.</p>
                    <button @click="getNextTenShorts"
                        style="padding: 0.5rem 1rem; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer;">
                        Retry
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation arrows (desktop) -->
    <button class="shorts-nav-arrow up" aria-label="Previous short" @click.stop="prevShort"
        :disabled="!currentShort || currentShortIndex === 0">
        <i class="fa-solid fa-chevron-up"></i>
    </button>
    <button class="shorts-nav-arrow down" aria-label="Next short" @click.stop="nextShort"
        :disabled="!currentShort" style="margin-bottom: -20px;">
        <i class="fa-solid fa-chevron-down"></i>
    </button>
</template>

<style scoped>
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

@keyframes slideOutUp {
    from { transform: translateY(0); opacity: 1; }
    to { transform: translateY(-100%); opacity: 0.5; }
}
@keyframes slideOutDown {
    from { transform: translateY(0); opacity: 1; }
    to { transform: translateY(100%); opacity: 0.5; }
}
@keyframes slideInFromBottom {
    from { transform: translateY(100%); opacity: 0.5; }
    to { transform: translateY(0); opacity: 1; }
}
@keyframes slideInFromTop {
    from { transform: translateY(-100%); opacity: 0.5; }
    to { transform: translateY(0); opacity: 1; }
}

.current-short { z-index: 10; }
.slide-out-up { animation: slideOutUp 0.4s ease-out forwards; }
.slide-out-down { animation: slideOutDown 0.4s ease-out forwards; }
.preview-next { z-index: 5; animation: slideInFromBottom 0.4s ease-out forwards; display: flex; justify-content: center; align-items: center; }
.preview-previous { z-index: 5; animation: slideInFromTop 0.4s ease-out forwards; display: flex; justify-content: center; align-items: center; }

.video-controls { z-index: 30; pointer-events: auto; }
.video-overlay { z-index: 25; pointer-events: auto; }
.control-btn, .follow-btn { min-height: 44px; min-width: 44px; display: flex; align-items: center; justify-content: center; pointer-events: auto; }
.progress-container { pointer-events: auto; }
.user-avatar-container, .username { cursor: pointer; pointer-events: auto; }

.shorts-container {
    flex-grow: 1;
    width: 100%;
    overflow-y: scroll;
    scroll-snap-type: y mandatory;
    -ms-overflow-style: none;
    scrollbar-width: none;
}
.shorts-container::-webkit-scrollbar { display: none; }

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
.short-player video { width: 100%; height: 100%; object-fit: cover; }

.video-overlay {
    position: absolute;
    bottom: 0; left: 0;
    width: 100%;
    padding: 1rem;
    padding-bottom: 45px;
    color: #fff;
    background: linear-gradient(to top, rgba(0,0,0,0.7), transparent);
    box-sizing: border-box;
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.user-info { display: flex; align-items: center; gap: 0.75rem; }
.user-avatar { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; }
.username { font-weight: 600; font-size: 1rem; color: #fff; }

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
}
.follow-btn:hover { background-color: #d84373; }

.video-description { font-size: 0.9rem; margin: 0; line-height: 1.4; color: #fff; }

.video-controls {
    position: absolute;
    bottom: 10px; left: 10px; right: 10px;
    display: flex;
    align-items: center;
    gap: 10px;
    z-index: 20;
}
.control-btn { background: none; border: none; color: #fff; font-size: 1.2rem; cursor: pointer; padding: 5px; flex-shrink: 0; }
.progress-container { flex-grow: 1; height: 4px; background-color: rgba(255,255,255,0.3); border-radius: 2px; cursor: pointer; position: relative; }
.progress-track { width: 100%; height: 100%; background-color: rgba(255,255,255,0.3); border-radius: 2px; }
.progress-fill { height: 100%; background-color: #e8445a; border-radius: 2px; transition: width 0.1s ease; }
.time-display { flex-shrink: 0; color: #fff; font-size: 0.8rem; font-weight: 500; min-width: 80px; text-align: center; }

@media (max-width: 768px) {
    .shorts-nav-arrow { display: none; }
    .control-btn { padding: 12px; font-size: 1.4rem; }
    .follow-btn { padding: 8px 16px; font-size: 0.9rem; }
    .user-avatar { width: 44px; height: 44px; }
}
@media (min-width: 769px) {
    .shorts-nav-arrow {
        display: block;
        position: fixed;
        right: 20px;
        background: rgba(255,255,255,0.2);
        border: none;
        border-radius: 50%;
        width: 50px; height: 50px;
        color: white;
        font-size: 1.2rem;
        cursor: pointer;
        z-index: 1000;
        transition: background 0.3s ease;
    }
    .shorts-nav-arrow:hover { background: rgba(255,255,255,0.3); }
    .shorts-nav-arrow.up { top: 50%; transform: translateY(-100%); }
    .shorts-nav-arrow.down { bottom: 50%; transform: translateY(100%); }
    .shorts-nav-arrow:disabled { opacity: 0.3; cursor: not-allowed; }
}
</style>
