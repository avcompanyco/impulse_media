<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import UserDashboardLayout from '@/layouts/UserDashboardLayout.vue';
import ShowMovieController from '@/actions/App/Http/Controllers/Movie/ShowMovieController';

const props = defineProps<{
    movie: any;
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
        const videoTitle = props.movie.title || "Video";
        
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
            updatePlayPauseIcon();
        }).catch(error => {
            console.error("Error al intentar reproducir el video:", error);
            updatePlayPauseIcon();
        });
    } else {
        customPlayer.value.pause();
    }
};

const updatePlayPauseIcon = () => {
    if (!playPauseBtn.value || !customPlayer.value) return;
    playPauseBtn.value.textContent = (customPlayer.value.paused || customPlayer.value.ended) ? '►' : '❚❚';
};

const updateVolumeUI = () => {
    if (!volumeBtn.value || !volumeSlider.value || !customPlayer.value) return;
    
    if (customPlayer.value.muted || customPlayer.value.volume === 0) {
        volumeBtn.value.textContent = '🔇';
        volumeSlider.value.value = '0';
    } else {
        volumeBtn.value.textContent = '🔊';
        volumeSlider.value.value = customPlayer.value.volume.toString();
    }
};

const updateProgress = () => {
    if (!customPlayer.value || !progressBar.value || !timeDisplay.value) return;
    
    const progressPercent = (customPlayer.value.currentTime / customPlayer.value.duration) * 100;
    progressBar.value.style.width = `${progressPercent}%`;
    timeDisplay.value.textContent = `${formatTime(customPlayer.value.currentTime)} / ${formatTime(customPlayer.value.duration || 0)}`;
};

const formatTime = (timeInSeconds: number): string => {
    const minutes = Math.floor(timeInSeconds / 60);
    const seconds = Math.floor(timeInSeconds % 60);
    return `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
};

const handleProgressClick = (e: MouseEvent) => {
    if (!progressBarContainer.value || !customPlayer.value) return;
    
    const rect = progressBarContainer.value.getBoundingClientRect();
    const clickPosition = e.clientX - rect.left;
    const newTime = (clickPosition / rect.width) * customPlayer.value.duration;
    customPlayer.value.currentTime = newTime;
};

const toggleMute = () => {
    if (!customPlayer.value) return;
    
    customPlayer.value.muted = !customPlayer.value.muted;
    if (!customPlayer.value.muted && customPlayer.value.volume === 0) {
        customPlayer.value.volume = 0.75;
    }
    updateVolumeUI();
};

const handleVolumeChange = (e: Event) => {
    if (!customPlayer.value) return;
    
    const target = e.target as HTMLInputElement;
    customPlayer.value.volume = parseFloat(target.value);
    customPlayer.value.muted = customPlayer.value.volume === 0;
};

const toggleFullscreen = () => {
    if (!playerContainer.value) return;
    
    if (!document.fullscreenElement) {
        playerContainer.value.requestFullscreen().catch(err => {
            console.warn(`Error al activar pantalla completa: ${err.message}`);
        });
    } else {
        document.exitFullscreen();
    }
};

const showControls = () => {
    if (!customControls.value || !backButtonContainer.value || !playerContainer.value || !customPlayer.value) return;
    
    customControls.value.style.opacity = '1';
    customControls.value.style.pointerEvents = 'auto';
    backButtonContainer.value.style.opacity = '1';
    backButtonContainer.value.style.pointerEvents = 'auto';
    playerContainer.value.style.cursor = 'default';
    clearTimeout(controlsTimeout);
    
    if (!customPlayer.value.paused && !customPlayer.value.ended) {
        controlsTimeout = setTimeout(() => {
            if (customControls.value && backButtonContainer.value && playerContainer.value) {
                customControls.value.style.opacity = '0';
                customControls.value.style.pointerEvents = 'none';
                backButtonContainer.value.style.opacity = '0';
                backButtonContainer.value.style.pointerEvents = 'none';
                playerContainer.value.style.cursor = 'none';
            }
        }, 3000);
    }
};

const handleMouseLeave = () => {
    if (!customPlayer.value) return;
    
    if (!customPlayer.value.paused && !customPlayer.value.ended) {
        clearTimeout(controlsTimeout);
        controlsTimeout = setTimeout(() => {
            if (customControls.value && backButtonContainer.value && playerContainer.value) {
                customControls.value.style.opacity = '0';
                customControls.value.style.pointerEvents = 'none';
                backButtonContainer.value.style.opacity = '0';
                backButtonContainer.value.style.pointerEvents = 'none';
                playerContainer.value.style.cursor = 'none';
            }
        }, 500);
    }
};

const handleFullscreenChange = () => {
    if (!fullscreenBtn.value) return;
    fullscreenBtn.value.textContent = document.fullscreenElement ? '↙↗' : '⛶';
};

onMounted(() => {
    if (!customPlayer.value) return;
    
    // Event listeners para el video
    customPlayer.value.addEventListener('play', () => {
        updatePlayPauseIcon();
        showControls();
        showTitleOverlay();
    });
    
    customPlayer.value.addEventListener('pause', () => {
        updatePlayPauseIcon();
        clearTimeout(controlsTimeout);
        if (customControls.value && backButtonContainer.value && playerContainer.value) {
            customControls.value.style.opacity = '1';
            customControls.value.style.pointerEvents = 'auto';
            backButtonContainer.value.style.opacity = '1';
            backButtonContainer.value.style.pointerEvents = 'auto';
            playerContainer.value.style.cursor = 'default';
        }
    });
    
    customPlayer.value.addEventListener('ended', updatePlayPauseIcon);
    
    customPlayer.value.addEventListener('loadedmetadata', () => {
        updateProgress();
        updatePlayPauseIcon();
        updateVolumeUI();
        if (customPlayer.value && (customPlayer.value.autoplay && !customPlayer.value.muted)) {
            showTitleOverlay();
        }
    });
    
    customPlayer.value.addEventListener('timeupdate', updateProgress);
    customPlayer.value.addEventListener('volumechange', updateVolumeUI);
    
    // Event listeners para controles
    playPauseBtn.value?.addEventListener('click', togglePlayPause);
    progressBarContainer.value?.addEventListener('click', handleProgressClick);
    volumeBtn.value?.addEventListener('click', toggleMute);
    volumeSlider.value?.addEventListener('input', handleVolumeChange);
    fullscreenBtn.value?.addEventListener('click', toggleFullscreen);
    
    // Event listeners para el contenedor
    playerContainer.value?.addEventListener('mousemove', showControls);
    playerContainer.value?.addEventListener('mouseleave', handleMouseLeave);
    
    // Event listener para fullscreen
    document.addEventListener('fullscreenchange', handleFullscreenChange);
    
    // Estado inicial
    showControls();
    updatePlayPauseIcon();
    updateVolumeUI();
    
    // Intento de autoplay
    customPlayer.value.play().then(() => {
        console.log("Autoplay con sonido iniciado.");
    }).catch(error => {
        console.warn("Autoplay con sonido fue bloqueado por el navegador. El usuario debe iniciar la reproducción.", error);
        updatePlayPauseIcon();
    });
});

onUnmounted(() => {
    clearTimeout(titleTimeout);
    clearTimeout(controlsTimeout);
    document.removeEventListener('fullscreenchange', handleFullscreenChange);
});

</script>
<template>
    <UserDashboardLayout 
        :title="`${movie.title} - ${$page.props.name || 'Impulsemedia'}`" 
        :headerTitle="`${movie.title} - ${$page.props.name || 'Impulsemedia'}`">
        <div 
            ref="playerContainer"
            class="player-container" 
            id="playerContainer"
            style="margin-bottom: 80px;"
        >
            <video 
                ref="customPlayer"
                id="customPlayer" 
                preload="auto" 
                poster="/images/video_poster_placeholder.jpg" 
                autoplay
            >
                <source :src="movie.trailer_video_url">
                Tu navegador no soporta la etiqueta de video HTML5.
            </video>

            <div 
                ref="titleOverlay"
                class="title-overlay" 
                id="titleOverlay"
            >
            </div>

            <!-- Botón de regreso -->
            <div 
                ref="backButtonContainer"
                class="back-button-container"
            >
                <Link 
                    :href="ShowMovieController.url(movie)" 
                    class="back-button"
                    title="Volver"
                >
                    ←
                </Link>
            </div>

            <div 
                ref="customControls"
                class="custom-controls" 
                id="customControls"
            >
                <button 
                    ref="playPauseBtn"
                    id="playPauseBtn" 
                    title="Reproducir/Pausar"
                >►</button>
                <div 
                    ref="progressBarContainer"
                    class="progress-bar-container" 
                    id="progressBarContainer"
                >
                    <div 
                        ref="progressBar"
                        class="progress-bar" 
                        id="progressBar"
                    ></div>
                </div>
                <div 
                    ref="timeDisplay"
                    class="time-display" 
                    id="timeDisplay"
                >00:00 / 00:00</div>
                <div class="volume-container">
                    <button 
                        ref="volumeBtn"
                        id="volumeBtn" 
                        title="Silenciar/Sonido"
                    >🔊</button> 
                    <input 
                        ref="volumeSlider"
                        type="range" 
                        id="volumeSlider"
                        class="volume-slider" 
                        min="0" 
                        max="1" 
                        step="0.05" 
                        value="1"
                    >
                </div>
                <button 
                    ref="fullscreenBtn"
                    id="fullscreenBtn" 
                    title="Pantalla Completa"
                >⛶</button>
            </div>
        </div>
        <br />
    </UserDashboardLayout>
</template>

<style scoped>
:root {
    --main-bg: #0A0A23;
    --primary-color: #F06292;
    --controls-bg: rgba(10, 10, 35, 0.75);
    --text-light: #FFFFFF;
    --icon-color: #FFFFFF;
    --icon-hover-color: var(--primary-color);
    --slider-track-color: rgba(255, 255, 255, 0.3);
    --slider-thumb-color: var(--primary-color);
    --title-overlay-bg: rgba(10, 10, 35, 0.85);
}

.player-container {
    width: 100%;
    height: 100vh;
    position: relative;
    display: flex;
    justify-content: center;
    align-items: center;
    background-color: #000;
    overflow: hidden;
}

.player-container video {
    width: 100%;
    height: 100%;
    display: block;
    object-fit: contain;
}

.title-overlay {
    position: absolute;
    top: 5%;
    left: 50%;
    transform: translateX(-50%);
    background-color: var(--title-overlay-bg);
    color: var(--text-light);
    padding: 15px 30px;
    border-radius: 10px;
    font-size: 1.8rem;
    font-weight: 600;
    text-align: center;
    opacity: 0;
    transition: opacity 0.5s ease-in-out, visibility 0.5s ease-in-out;
    z-index: 20;
    pointer-events: none;
    visibility: hidden;
}

.title-overlay.visible {
    opacity: 1;
    visibility: visible;
}

.back-button-container {
    position: absolute;
    top: 20px;
    left: 20px;
    z-index: 22;
    opacity: 1;
    transition: opacity 0.3s ease-in-out;
}

.back-button {
    background-color: var(--controls-bg);
    color: var(--text-light);
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

.back-button:hover {
    background-color: var(--primary-color);
    color: var(--text-light);
    text-decoration: none;
}

.custom-controls {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background-color: var(--controls-bg);
    padding: 10px 15px;
    display: flex;
    align-items: center;
    gap: 15px;
    opacity: 1;
    transition: opacity 0.3s ease-in-out;
    z-index: 21;
}

.player-container.controls-hidden .custom-controls {
    opacity: 0;
    pointer-events: none;
}

.custom-controls button {
    background: none;
    border: none;
    color: var(--icon-color);
    font-size: 1.5rem;
    cursor: pointer;
    padding: 5px;
    line-height: 1;
}

.custom-controls button:hover {
    color: var(--icon-hover-color);
}

.progress-bar-container {
    flex-grow: 1;
    height: 8px;
    background-color: var(--slider-track-color);
    border-radius: 4px;
    cursor: pointer;
    position: relative;
}

.progress-bar {
    height: 100%;
    width: 0%;
    background-color: var(--primary-color);
    border-radius: 4px;
}

.time-display {
    font-size: 0.9rem;
    min-width: 90px;
    text-align: center;
}

.volume-container {
    display: flex;
    align-items: center;
}

.volume-slider {
    width: 80px;
    height: 6px;
    background-color: var(--slider-track-color);
    border-radius: 3px;
    -webkit-appearance: none;
    appearance: none;
    cursor: pointer;
    margin-left: 8px;
}

.volume-slider::-webkit-slider-thumb {
    -webkit-appearance: none;
    appearance: none;
    width: 14px;
    height: 14px;
    background: var(--slider-thumb-color);
    border-radius: 50%;
    cursor: pointer;
}

.volume-slider::-moz-range-thumb {
    width: 14px;
    height: 14px;
    background: var(--slider-thumb-color);
    border-radius: 50%;
    border: none;
    cursor: pointer;
}
</style>