<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import UserDashboardLayout from '@/layouts/UserDashboardLayout.vue';
import ShowSerieController from '@/actions/App/Http/Controllers/Serie/ShowSerieController';

const props = defineProps<{
    serie: any;
    season: any;
    chapter: any;
}>();

// Refs para elementos del DOM
const playerContainer = ref<HTMLDivElement>();
const customPlayer = ref<HTMLVideoElement>();
const titleOverlay = ref<HTMLDivElement>();
const customControls = ref<HTMLDivElement>();
const backButtonContainer = ref<HTMLDivElement>();
const playPauseBtn = ref<HTMLButtonElement>();
const progressBarContainer = ref<HTMLDivElement>();
const progressBar = ref<HTMLDivElement>();
const timeDisplay = ref<HTMLDivElement>();
const volumeBtn = ref<HTMLButtonElement>();
const volumeSlider = ref<HTMLInputElement>();
const fullscreenBtn = ref<HTMLButtonElement>();

// Variables reactivas
let titleTimeout: number;
let controlsTimeout: number;

// Funciones del reproductor
const showTitleOverlay = () => {
    clearTimeout(titleTimeout);
    if (customPlayer.value && (customPlayer.value.currentSrc || customPlayer.value.src)) {
        const videoTitle = `${props.serie.title} - S${props.season.id}E${props.chapter.chapter_number}: ${props.chapter.title}`;
        
        if (titleOverlay.value) {
            titleOverlay.value.textContent = videoTitle;
            titleOverlay.value.classList.add('visible');
            
            titleTimeout = setTimeout(() => {
                titleOverlay.value?.classList.remove('visible');
            }, 4000);
        }
    }
};

const togglePlayPause = () => {
    if (!customPlayer.value) return;
    
    if (customPlayer.value.paused || customPlayer.value.ended) {
        customPlayer.value.play().then(() => {
            showTitleOverlay();
        }).catch(console.error);
    } else {
        customPlayer.value.pause();
    }
};

const showCustomControls = () => {
    clearTimeout(controlsTimeout);
    
    if (customControls.value && backButtonContainer.value) {
        customControls.value.classList.add('visible');
        backButtonContainer.value.classList.add('visible');
        
        controlsTimeout = setTimeout(() => {
            if (customPlayer.value && !customPlayer.value.paused) {
                customControls.value?.classList.remove('visible');
                backButtonContainer.value?.classList.remove('visible');
            }
        }, 3000);
    }
};

const hideCustomControls = () => {
    if (customControls.value && backButtonContainer.value) {
        customControls.value.classList.remove('visible');
        backButtonContainer.value.classList.remove('visible');
    }
};

const updatePlayPauseButton = () => {
    if (!playPauseBtn.value || !customPlayer.value) return;
    
    if (customPlayer.value.paused || customPlayer.value.ended) {
        playPauseBtn.value.innerHTML = '▶';
    } else {
        playPauseBtn.value.innerHTML = '⏸';
    }
};

const updateProgressBar = () => {
    if (!progressBar.value || !customPlayer.value) return;
    
    const progress = (customPlayer.value.currentTime / customPlayer.value.duration) * 100;
    progressBar.value.style.width = `${progress}%`;
};

const updateTimeDisplay = () => {
    if (!timeDisplay.value || !customPlayer.value) return;
    
    const formatTime = (seconds: number) => {
        const hours = Math.floor(seconds / 3600);
        const minutes = Math.floor((seconds % 3600) / 60);
        const secs = Math.floor(seconds % 60);
        
        if (hours > 0) {
            return `${hours}:${minutes.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
        }
        return `${minutes}:${secs.toString().padStart(2, '0')}`;
    };
    
    const current = formatTime(customPlayer.value.currentTime);
    const duration = formatTime(customPlayer.value.duration);
    timeDisplay.value.textContent = `${current} / ${duration}`;
};

const handleProgressBarClick = (event: MouseEvent) => {
    if (!progressBarContainer.value || !customPlayer.value) return;
    
    const rect = progressBarContainer.value.getBoundingClientRect();
    const clickX = event.clientX - rect.left;
    const barWidth = rect.width;
    const percentage = clickX / barWidth;
    
    customPlayer.value.currentTime = percentage * customPlayer.value.duration;
};

const toggleMute = () => {
    if (!customPlayer.value || !volumeBtn.value) return;
    
    customPlayer.value.muted = !customPlayer.value.muted;
    volumeBtn.value.innerHTML = customPlayer.value.muted ? '🔇' : '🔊';
    
    if (volumeSlider.value) {
        volumeSlider.value.value = customPlayer.value.muted ? '0' : String(customPlayer.value.volume);
    }
};

const handleVolumeChange = () => {
    if (!volumeSlider.value || !customPlayer.value) return;
    
    const volume = Number(volumeSlider.value.value);
    customPlayer.value.volume = volume;
    customPlayer.value.muted = volume === 0;
    
    if (volumeBtn.value) {
        volumeBtn.value.innerHTML = volume === 0 ? '🔇' : '🔊';
    }
};

const toggleFullscreen = () => {
    if (!playerContainer.value) return;
    
    if (!document.fullscreenElement) {
        playerContainer.value.requestFullscreen().catch(console.error);
    } else {
        document.exitFullscreen().catch(console.error);
    }
};

const handleKeyPress = (event: KeyboardEvent) => {
    if (!customPlayer.value) return;
    
    switch (event.code) {
        case 'Space':
            event.preventDefault();
            togglePlayPause();
            break;
        case 'ArrowLeft':
            event.preventDefault();
            customPlayer.value.currentTime = Math.max(0, customPlayer.value.currentTime - 10);
            break;
        case 'ArrowRight':
            event.preventDefault();
            customPlayer.value.currentTime = Math.min(customPlayer.value.duration, customPlayer.value.currentTime + 10);
            break;
        case 'ArrowUp':
            event.preventDefault();
            customPlayer.value.volume = Math.min(1, customPlayer.value.volume + 0.1);
            if (volumeSlider.value) {
                volumeSlider.value.value = String(customPlayer.value.volume);
            }
            break;
        case 'ArrowDown':
            event.preventDefault();
            customPlayer.value.volume = Math.max(0, customPlayer.value.volume - 0.1);
            if (volumeSlider.value) {
                volumeSlider.value.value = String(customPlayer.value.volume);
            }
            break;
        case 'KeyF':
            event.preventDefault();
            toggleFullscreen();
            break;
        case 'KeyM':
            event.preventDefault();
            toggleMute();
            break;
    }
};

const goToSerie = () => {
    router.get(ShowSerieController.url({ serie: props.serie.id }));
};

const findNextChapter = () => {
    const currentSeason = props.serie.seasons.find((s: any) => s.id === props.season.id);
    if (!currentSeason) return null;
    
    // Find current chapter index
    const currentChapterIndex = currentSeason.chapters.findIndex((c: any) => c.id === props.chapter.id);
    
    // Try next chapter in same season
    if (currentChapterIndex < currentSeason.chapters.length - 1) {
        return {
            season: currentSeason,
            chapter: currentSeason.chapters[currentChapterIndex + 1]
        };
    }
    
    // Try first chapter of next season
    const currentSeasonIndex = props.serie.seasons.findIndex((s: any) => s.id === props.season.id);
    if (currentSeasonIndex < props.serie.seasons.length - 1) {
        const nextSeason = props.serie.seasons[currentSeasonIndex + 1];
        if (nextSeason.chapters.length > 0) {
            return {
                season: nextSeason,
                chapter: nextSeason.chapters[0]
            };
        }
    }
    
    return null;
};

const playNextChapter = () => {
    const next = findNextChapter();
    if (next) {
        router.get(`/serie/${props.serie.id}/season/${next.season.id}/chapter/${next.chapter.id}/player`);
    }
};

onMounted(() => {
    if (customPlayer.value) {
        // Event listeners del reproductor
        customPlayer.value.addEventListener('loadedmetadata', showTitleOverlay);
        customPlayer.value.addEventListener('play', () => {
            updatePlayPauseButton();
            showCustomControls();
        });
        customPlayer.value.addEventListener('pause', updatePlayPauseButton);
        customPlayer.value.addEventListener('timeupdate', () => {
            updateProgressBar();
            updateTimeDisplay();
        });
        customPlayer.value.addEventListener('ended', () => {
            updatePlayPauseButton();
            // Auto-play next chapter if available
            const next = findNextChapter();
            if (next) {
                setTimeout(playNextChapter, 3000); // 3 second delay
            }
        });
        
        // Event listeners del contenedor
        if (playerContainer.value) {
            playerContainer.value.addEventListener('click', togglePlayPause);
            playerContainer.value.addEventListener('mousemove', showCustomControls);
            playerContainer.value.addEventListener('mouseleave', hideCustomControls);
        }
        
        // Event listeners de los controles
        if (progressBarContainer.value) {
            progressBarContainer.value.addEventListener('click', handleProgressBarClick);
        }
        
        if (volumeSlider.value) {
            volumeSlider.value.addEventListener('input', handleVolumeChange);
        }
        
        // Keyboard controls
        document.addEventListener('keydown', handleKeyPress);
        
        // Inicializar controles
        showCustomControls();
        
        // Auto-play el video
        if (customPlayer.value.readyState >= 1) {
            customPlayer.value.play().catch(console.error);
        } else {
            customPlayer.value.addEventListener('loadeddata', () => {
                customPlayer.value?.play().catch(console.error);
            });
        }
    }
});

onUnmounted(() => {
    if (customPlayer.value) {
        customPlayer.value.removeEventListener('loadedmetadata', showTitleOverlay);
        customPlayer.value.removeEventListener('play', updatePlayPauseButton);
        customPlayer.value.removeEventListener('pause', updatePlayPauseButton);
        customPlayer.value.removeEventListener('timeupdate', updateProgressBar);
        customPlayer.value.removeEventListener('ended', updatePlayPauseButton);
    }
    
    if (playerContainer.value) {
        playerContainer.value.removeEventListener('click', togglePlayPause);
        playerContainer.value.removeEventListener('mousemove', showCustomControls);
        playerContainer.value.removeEventListener('mouseleave', hideCustomControls);
    }
    
    if (progressBarContainer.value) {
        progressBarContainer.value.removeEventListener('click', handleProgressBarClick);
    }
    
    if (volumeSlider.value) {
        volumeSlider.value.removeEventListener('input', handleVolumeChange);
    }
    
    document.removeEventListener('keydown', handleKeyPress);
    
    clearTimeout(titleTimeout);
    clearTimeout(controlsTimeout);
});
</script>

<template>
    <UserDashboardLayout :title="`${chapter.title} - ${serie.title}`" headerTitle="Chapter Player">
        <template #main-content>
            <div class="player-container" ref="playerContainer" style="margin-bottom: 80px;">
                <video
                    ref="customPlayer"
                    class="custom-player"
                    :src="chapter.chapter_video_url"
                    :poster="chapter.thumbnail_url"
                    preload="metadata"
                >
                    Your browser does not support the video tag.
                </video>
                
                <!-- Title Overlay -->
                <div ref="titleOverlay" class="title-overlay">
                    {{ serie.title }} - S{{ season.id }}E{{ chapter.chapter_number }}: {{ chapter.title }}
                </div>
                
                <!-- Back Button Container -->
                <div ref="backButtonContainer" class="back-button-container">
                    <button @click="goToSerie" class="back-button">
                        ← Back to Serie
                    </button>
                </div>
                
                <!-- Custom Controls -->
                <div ref="customControls" class="custom-controls">
                    <div class="controls-top">
                        <div class="video-info">
                            <h3>{{ serie.title }}</h3>
                            <p>Season {{ season.id }} • Episode {{ chapter.chapter_number }}: {{ chapter.title }}</p>
                        </div>
                    </div>
                    
                    <div class="controls-bottom">
                        <div class="progress-container" ref="progressBarContainer">
                            <div class="progress-bar" ref="progressBar"></div>
                        </div>
                        
                        <div class="controls-row">
                            <div class="controls-left">
                                <button ref="playPauseBtn" @click="togglePlayPause" class="control-btn">▶</button>
                                <button ref="volumeBtn" @click="toggleMute" class="control-btn">🔊</button>
                                <input
                                    ref="volumeSlider"
                                    type="range"
                                    min="0"
                                    max="1"
                                    step="0.1"
                                    value="1"
                                    class="volume-slider"
                                />
                                <div ref="timeDisplay" class="time-display">0:00 / 0:00</div>
                            </div>
                            
                            <div class="controls-right">
                                <button v-if="findNextChapter()" @click="playNextChapter" class="control-btn next-btn">
                                    Next Episode →
                                </button>
                                <button ref="fullscreenBtn" @click="toggleFullscreen" class="control-btn">⛶</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <br />
        </template>
    </UserDashboardLayout>
</template>

<style scoped>
.player-container {
    position: relative;
    width: 100%;
    height: 100vh;
    background: #000;
    overflow: hidden;
    cursor: none;
}

.custom-player {
    width: 100%;
    height: 100%;
    object-fit: contain;
}

.title-overlay {
    position: absolute;
    top: 2rem;
    left: 2rem;
    background: rgba(0, 0, 0, 0.8);
    color: white;
    padding: 1rem 2rem;
    border-radius: 8px;
    font-size: 1.5rem;
    font-weight: 600;
    opacity: 0;
    transform: translateY(-20px);
    transition: all 0.3s ease;
    pointer-events: none;
    z-index: 10;
}

.title-overlay.visible {
    opacity: 1;
    transform: translateY(0);
}

.back-button-container {
    position: absolute;
    top: 2rem;
    right: 2rem;
    opacity: 0;
    transform: translateY(-20px);
    transition: all 0.3s ease;
    z-index: 10;
}

.back-button-container.visible {
    opacity: 1;
    transform: translateY(0);
}

.back-button {
    background: rgba(0, 0, 0, 0.8);
    color: white;
    border: none;
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    cursor: pointer;
    font-size: 1rem;
    font-weight: 500;
    transition: background 0.2s ease;
}

.back-button:hover {
    background: rgba(0, 0, 0, 0.9);
}

.custom-controls {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: linear-gradient(transparent, rgba(0, 0, 0, 0.8));
    padding: 2rem;
    opacity: 0;
    transform: translateY(20px);
    transition: all 0.3s ease;
    z-index: 10;
}

.custom-controls.visible {
    opacity: 1;
    transform: translateY(0);
}

.controls-top {
    margin-bottom: 1rem;
}

.video-info h3 {
    color: white;
    font-size: 1.5rem;
    font-weight: 600;
    margin: 0 0 0.5rem 0;
}

.video-info p {
    color: rgba(255, 255, 255, 0.8);
    font-size: 1rem;
    margin: 0;
}

.progress-container {
    width: 100%;
    height: 6px;
    background: rgba(255, 255, 255, 0.3);
    border-radius: 3px;
    margin-bottom: 1rem;
    cursor: pointer;
    position: relative;
}

.progress-bar {
    height: 100%;
    background: var(--primary-color);
    border-radius: 3px;
    width: 0%;
    transition: width 0.1s ease;
}

.controls-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.controls-left,
.controls-right {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.control-btn {
    background: none;
    border: none;
    color: white;
    font-size: 1.2rem;
    cursor: pointer;
    padding: 0.5rem;
    border-radius: 4px;
    transition: background 0.2s ease;
}

.control-btn:hover {
    background: rgba(255, 255, 255, 0.2);
}

.next-btn {
    background: var(--primary-color);
    padding: 0.5rem 1rem;
    font-weight: 500;
}

.next-btn:hover {
    background: var(--primary-color-dark);
}

.volume-slider {
    width: 80px;
    height: 4px;
    background: rgba(255, 255, 255, 0.3);
    outline: none;
    border-radius: 2px;
}

.time-display {
    color: white;
    font-size: 0.9rem;
    font-family: monospace;
    min-width: 120px;
}

.player-container:hover {
    cursor: default;
}

/* Responsive */
@media (max-width: 768px) {
    .title-overlay {
        top: 1rem;
        left: 1rem;
        font-size: 1.2rem;
        padding: 0.75rem 1.5rem;
    }
    
    .back-button-container {
        top: 1rem;
        right: 1rem;
    }
    
    .custom-controls {
        padding: 1rem;
    }
    
    .controls-row {
        flex-direction: column;
        gap: 1rem;
        align-items: stretch;
    }
    
    .controls-left,
    .controls-right {
        justify-content: center;
    }
    
    .volume-slider {
        width: 60px;
    }
}
</style>