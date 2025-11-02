<script setup lang="ts">
import { ref, onMounted, onUnmounted, nextTick } from 'vue';
import UserDashboardLayout from '@/layouts/UserDashboardLayout.vue';
import { router, usePage } from '@inertiajs/vue3';
import IndexShortController from '@/actions/App/Http/Controllers/Short/IndexShortController';
import AddToFollowController from '@/actions/App/Http/Controllers/Follow/AddToFollowController';
import RemoveToFollowController from '@/actions/App/Http/Controllers/Follow/RemoveToFollowController';
import PlaceholderPattern from '@/components/PlaceholderPattern.vue';

interface User {
    id: number;
    name: string;
    username: string;
    image_url: string;
    is_followed?: boolean;
}

interface Short {
    id: number;
    short_video_url: string;
    text_caption: string;
    user: User;
}

interface PageProps {
    user: {
        id: number;
        [key: string]: any;
    };
    [key: string]: any;
}

const props = defineProps<{
    currentShort: Short;
    nextShorts: Short[];
    prevShorts: Short[];
    totalShorts: number;
}>();

const page = usePage();

// Reactive refs
const videoContainer = ref<HTMLDivElement>();
const videoPlayer = ref<HTMLVideoElement>();
const isPlaying = ref(false);
const isLoading = ref(false);
const showControls = ref(true);
const isMuted = ref(false);
const currentTime = ref(0);
const duration = ref(0);

// Touch/swipe handling
const touchStartY = ref(0);
const touchStartX = ref(0);
const isDragging = ref(false);

// Auto-hide controls timeout
let controlsTimeout: number;

const addFollowLoading = ref(false);
const removeFollowLoading = ref(false);

// Functions
const togglePlayPause = () => {
    if (!videoPlayer.value) return;

    if (videoPlayer.value.paused) {
        videoPlayer.value.play();
        isPlaying.value = true;
    } else {
        videoPlayer.value.pause();
        isPlaying.value = false;
    }
};

const toggleMute = () => {
    if (!videoPlayer.value) return;

    videoPlayer.value.muted = !videoPlayer.value.muted;
    isMuted.value = videoPlayer.value.muted;
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

const navigateToShort = (shortId: number) => {
    if (isLoading.value) return;

    isLoading.value = true;
    router.get(IndexShortController.url(),
        { short: shortId },
        {
            preserveState: false,
            preserveScroll: false,
            onFinish: () => {
                isLoading.value = false;
            }
        }
    );
};

const getRandomShort = () => {
    if (isLoading.value) return;

    isLoading.value = true;
    router.get(IndexShortController.url(),
        {},
        {
            preserveState: false,
            preserveScroll: false,
            onFinish: () => {
                isLoading.value = false;
            }
        }
    );
};

const nextShort = () => {
    if (props.nextShorts.length > 0) {
        navigateToShort(props.nextShorts[0].id);
    } else {
        getRandomShort();
    }
};

const prevShort = () => {
    if (props.prevShorts.length > 0) {
        navigateToShort(props.prevShorts[props.prevShorts.length - 1].id);
    } else {
        getRandomShort();
    }
};

// Keyboard navigation
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
                videoPlayer.value.currentTime = Math.max(0, videoPlayer.value.currentTime - 10);
            }
            break;
        case 'ArrowRight':
            event.preventDefault();
            if (videoPlayer.value) {
                videoPlayer.value.currentTime = Math.min(
                    videoPlayer.value.duration,
                    videoPlayer.value.currentTime + 10
                );
            }
            break;
        case 'KeyM':
            event.preventDefault();
            toggleMute();
            break;
    }
};

// Touch/swipe handling
const handleTouchStart = (event: TouchEvent) => {
    touchStartY.value = event.touches[0].clientY;
    touchStartX.value = event.touches[0].clientX;
    isDragging.value = false;
};

const handleTouchMove = (event: TouchEvent) => {
    if (!isDragging.value) {
        const currentY = event.touches[0].clientY;
        const currentX = event.touches[0].clientX;
        const deltaY = Math.abs(currentY - touchStartY.value);
        const deltaX = Math.abs(currentX - touchStartX.value);

        // Only start dragging if vertical movement is greater than horizontal
        if (deltaY > deltaX && deltaY > 10) {
            isDragging.value = true;
        }
    }
};

const handleTouchEnd = (event: TouchEvent) => {
    if (!isDragging.value) return;

    const touchEndY = event.changedTouches[0].clientY;
    const deltaY = touchStartY.value - touchEndY;

    // Minimum swipe distance
    if (Math.abs(deltaY) > 50) {
        if (deltaY > 0) {
            // Swiped up - next short
            nextShort();
        } else {
            // Swiped down - previous short
            prevShort();
        }
    }

    isDragging.value = false;
};

// Video event handlers
const handleVideoLoaded = () => {
    if (videoPlayer.value) {
        duration.value = videoPlayer.value.duration;
        videoPlayer.value.play().then(() => {
            isPlaying.value = true;
            showControlsTemporarily();
        }).catch(() => {
            isPlaying.value = false;
        });
    }
};

const handleVideoEnded = () => {
    isPlaying.value = false;
    // Auto-play next short
    setTimeout(() => {
        nextShort();
    }, 1000);
};

const handleVideoClick = () => {
    togglePlayPause();
    showControlsTemporarily();
};

const handleContainerClick = () => {
    showControlsTemporarily();
};

const updateProgress = () => {
    if (videoPlayer.value) {
        currentTime.value = videoPlayer.value.currentTime;
    }
};

const handleProgressClick = (event: MouseEvent) => {
    if (!videoPlayer.value) return;

    const rect = (event.target as HTMLElement).getBoundingClientRect();
    const clickX = event.clientX - rect.left;
    const width = rect.width;
    const duration = videoPlayer.value.duration;

    videoPlayer.value.currentTime = (clickX / width) * duration;
};

const formatTime = (seconds: number): string => {
    const minutes = Math.floor(seconds / 60);
    const remainingSeconds = Math.floor(seconds % 60);
    return `${minutes}:${remainingSeconds.toString().padStart(2, '0')}`;
};

// Lifecycle
onMounted(() => {
    // Add event listeners
    document.addEventListener('keydown', handleKeyPress);

    if (videoContainer.value) {
        videoContainer.value.addEventListener('touchstart', handleTouchStart, { passive: true });
        videoContainer.value.addEventListener('touchmove', handleTouchMove, { passive: true });
        videoContainer.value.addEventListener('touchend', handleTouchEnd, { passive: true });
    }

    // Auto-play video
    nextTick(() => {
        if (videoPlayer.value) {
            handleVideoLoaded();
        }
    });
});

onUnmounted(() => {
    document.removeEventListener('keydown', handleKeyPress);

    if (videoContainer.value) {
        videoContainer.value.removeEventListener('touchstart', handleTouchStart);
        videoContainer.value.removeEventListener('touchmove', handleTouchMove);
        videoContainer.value.removeEventListener('touchend', handleTouchEnd);
    }

    clearTimeout(controlsTimeout);
});


function addToFollow(userId: number) {
    addFollowLoading.value = true;
    router.post(AddToFollowController({ user: userId }), {}, {
        preserveState: false,
        preserveScroll: false,
        onFinish: () => {
            addFollowLoading.value = false;
        }
    })
}

function removeFromFollow(userId: number) {
    removeFollowLoading.value = true;
    router.post(RemoveToFollowController({ user: userId }), {}, {
        preserveState: false,
        preserveScroll: false,
        onFinish: () => {
            removeFollowLoading.value = false;
        }
    })
}
</script>

<template>
    <UserDashboardLayout 
        :title="`Shorts - ${$page.props.name || 'Impulsemedia'}`" 
        :headerTitle="`Shorts - ${$page.props.name || 'Impulsemedia'}`">
        <template #shorts-content>
            <div class="shorts-container" id="shortsContainer" @click="handleContainerClick" style="height: 100%;">
                <div class="short-video-slide">
                    <!-- Loading overlay -->
                    <div v-if="isLoading" class="loading-short-player">
                        <div class="short-player">
                            <i class="fa-solid fa-circle-notch fa-spin" style="position: absolute;top: 50%;left: 50%;transform: translate(-50%, -50%);" />
                            <PlaceholderPattern />
                        </div>
                    </div>
                    <div class="short-player">
    
                        <!-- Main video -->
                        <video ref="videoPlayer" class="main-video" :src="currentShort.short_video_url"
                            preload="metadata" loop :muted="isMuted" @click="handleVideoClick"
                            @loadeddata="handleVideoLoaded" @ended="handleVideoEnded" @play="isPlaying = true"
                            @pause="isPlaying = false" @timeupdate="updateProgress" />
    
                        <!-- Video controls -->
                        <div v-show="showControls" class="video-controls">
                            <button @click.stop="togglePlayPause" class="control-btn">
                                <i v-if="isPlaying" class="fas fa-pause"></i>
                                <i v-else class="fas fa-play"></i>
                            </button>
    
                            <div class="progress-container" @click="handleProgressClick">
                                <div class="progress-track">
                                    <div class="progress-fill"
                                        :style="{ width: duration > 0 ? (currentTime / duration) * 100 + '%' : '0%' }">
                                    </div>
                                </div>
                            </div>

                            <div class="time-display">
                                <span>{{ formatTime(currentTime) }} / {{ formatTime(duration) }}</span>
                            </div>
    
                            <button @click.stop="toggleMute" class="control-btn">
                                <i v-if="isMuted" class="fas fa-volume-mute"></i>
                                <i v-else class="fas fa-volume-high"></i>
                            </button>
                        </div>
                        <div class="video-overlay">
                            <a href="channel.html" class="user-info-link">
                                <div class="user-info">
                                    <img :src="currentShort.user.image_url" alt="User Avatar" class="user-avatar">
                                    <span class="username">@{{ currentShort.user.username }}</span>

                                    <template v-if="currentShort.user.id !== $page.props.auth.user.id">
                                        <button v-if="!currentShort.user.is_followed" class="follow-btn"
                                            @click="addToFollow(currentShort.user.id)" :disabled="addFollowLoading">
                                            <i class="fa-solid fa-circle-notch fa-spin" v-if="addFollowLoading"></i>
                                            Follow
                                        </button>
                                        <button v-else class="follow-btn"
                                            @click="removeFromFollow(currentShort.user.id)"
                                            :disabled="removeFollowLoading"
                                            style="background-color: var(--destructive-action-bg);">
                                            <i class="fa-solid fa-circle-notch fa-spin" v-if="removeFollowLoading"></i>
                                            Unfollow
                                        </button>
                                    </template>
                                </div>
                            </a>
                            <p class="video-description">{{ currentShort.text_caption }}</p>
                        </div>
                    </div>
                </div>
    
            </div>
    
            <button class="shorts-nav-arrow up" id="shortsUpBtn" aria-label="Previous short" @click.stop="prevShort" :disabled="isLoading"
                v-if="props.prevShorts.length > 0">
                <i class="fa-solid fa-chevron-up"></i>
            </button>
            <button class="shorts-nav-arrow down" id="shortsDownBtn" aria-label="Next short" @click.stop="nextShort" :disabled="isLoading"
                v-if="props.nextShorts.length > 0">
                <i class="fa-solid fa-chevron-down"></i>
            </button>
        </template>
    </UserDashboardLayout>
</template>

<style scoped>
.shorts-container {
    flex-grow: 1;
    width: 100%;
    overflow-y: scroll;
    scroll-snap-type: y mandatory;
    -ms-overflow-style: none;
    scrollbar-width: none;
}

.shorts-container::-webkit-scrollbar {
    display: none;
}

.short-video-slide {
    width: 100%;
    height: 100%;
    position: relative;
    scroll-snap-align: start;
    display: flex;
    align-items: center;
    justify-content: center;
    box-sizing: border-box;
    padding: 20px 0px 80px 0px;
    top: -20px;
    bottom: 80px;
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

.short-player video {
    width: 100%;
    height: 100%;
    object-fit: cover;
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
    padding-bottom: 45px;
}

a.user-info-link {
    text-decoration: none;
    color: inherit;
}

.user-info {
    display: flex;
    align-items: center;
    gap: 0.75rem;
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
}

.follow-btn {
    background-color: var(--primary-color);
    color: var(--text-light);
    border: none;
    border-radius: 8px;
    padding: 0.3rem 0.8rem;
    font-size: 0.8rem;
    font-weight: 600;
    cursor: pointer;
    transition: background-color 0.2s;
}

.follow-btn:hover {
    background-color: #d84373;
}

.video-description {
    font-size: 0.9rem;
    margin: 0;
    line-height: 1.4;
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
}

.control-btn {
    background: none;
    border: none;
    color: var(--text-light);
    font-size: 1.2rem;
    cursor: pointer;
    padding: 5px;
    flex-shrink: 0;
}

.progress-container {
    flex-grow: 1;
    height: 4px;
    background-color: rgba(255, 255, 255, 0.3);
    border-radius: 2px;
    cursor: pointer;
    position: relative;
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

.progress-bar-container {
    flex-grow: 1;
    height: 4px;
    background-color: rgba(255, 255, 255, 0.3);
    border-radius: 2px;
    cursor: pointer;
}

.progress-bar {
    height: 100%;
    width: 0;
    background-color: var(--primary-color);
    border-radius: 2px;
}

.shorts-nav-arrow {
    display: none;
    position: fixed;
    z-index: 500;
    right: 25px;
    width: 50px;
    height: 50px;
    background-color: rgba(255, 255, 255, 0.2);
    color: var(--text-light);
    border: 1px solid rgba(255, 255, 255, 0.3);
    border-radius: 50%;
    font-size: 1.2rem;
    cursor: pointer;
    transition: background-color 0.2s ease;
}

.shorts-nav-arrow:hover {
    background-color: rgba(255, 255, 255, 0.4);
}

.shorts-nav-arrow.up {
    top: 50%;
    transform: translateY(-120%);
}

.shorts-nav-arrow.down {
    top: 50%;
    transform: translateY(20%);
}

@media (min-width: 1024px) {
    .shorts-nav-arrow {
        display: flex;
        align-items: center;
        justify-content: center;
    }
}

.bottom-nav {
    position: relative;
    width: 100%;
    background: var(--main-bg);
    padding: 1rem;
    display: flex;
    justify-content: space-around;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    z-index: 1000;
    flex-shrink: 0;
}

.nav-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    color: white;
    text-decoration: none;
    font-size: 0.8rem;
    gap: 4px;
}

.nav-item.active {
    color: var(--gradient-start);
}

.nav-icon {
    width: 24px;
    height: 24px;
}
</style>