import { ref, computed, onMounted, onUnmounted, type Ref } from 'vue';

export interface UseVideoPlayerOptions {
    /** Autoplay on mount (default: true) */
    autoplay?: boolean;
    /** Delay in ms before hiding controls (default: 3000) */
    controlsHideDelay?: number;
    /** Delay in ms before hiding title overlay (default: 4000) */
    titleOverlayDuration?: number;
    /** Enable keyboard shortcuts (default: true) */
    keyboardControls?: boolean;
    /** Seek step in seconds for arrow keys (default: 10) */
    seekStep?: number;
    /** Volume step for arrow keys (default: 0.1) */
    volumeStep?: number;
    /** Callback when video ends */
    onEnded?: () => void;
}

export function useVideoPlayer(options: UseVideoPlayerOptions = {}) {
    const {
        autoplay = true,
        controlsHideDelay = 3000,
        titleOverlayDuration = 4000,
        keyboardControls = true,
        seekStep = 10,
        volumeStep = 0.1,
        onEnded,
    } = options;

    // --- Refs ---
    const videoEl = ref<HTMLVideoElement | null>(null);
    const containerEl = ref<HTMLDivElement | null>(null);

    // --- Reactive state ---
    const isPlaying = ref(false);
    const isMuted = ref(false);
    const volume = ref(1);
    const currentTime = ref(0);
    const duration = ref(0);
    const progress = ref(0);
    const isFullscreen = ref(false);
    const controlsVisible = ref(true);
    const titleVisible = ref(false);
    const isBuffering = ref(false);

    // --- Internal timers ---
    let controlsTimer: ReturnType<typeof setTimeout> | null = null;
    let titleTimer: ReturnType<typeof setTimeout> | null = null;
    let touchStartedOnControls = false;

    // --- Computed ---
    const formattedCurrentTime = computed(() => formatTime(currentTime.value));
    const formattedDuration = computed(() => formatTime(duration.value));
    const timeDisplayText = computed(() => `${formattedCurrentTime.value} / ${formattedDuration.value}`);

    // --- Helpers ---
    function formatTime(seconds: number): string {
        if (!isFinite(seconds) || isNaN(seconds)) return '00:00';
        const totalSeconds = Math.floor(seconds);
        const h = Math.floor(totalSeconds / 3600);
        const m = Math.floor((totalSeconds % 3600) / 60);
        const s = totalSeconds % 60;

        if (h > 0) {
            return `${h}:${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
        }
        return `${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
    }

    // --- Controls visibility ---
    function showControls() {
        controlsVisible.value = true;
        clearControlsTimer();

        if (isPlaying.value) {
            controlsTimer = setTimeout(() => {
                controlsVisible.value = false;
            }, controlsHideDelay);
        }
    }

    function hideControls() {
        if (isPlaying.value) {
            controlsVisible.value = false;
        }
    }

    function clearControlsTimer() {
        if (controlsTimer !== null) {
            clearTimeout(controlsTimer);
            controlsTimer = null;
        }
    }

    // --- Title overlay ---
    function showTitle() {
        clearTitleTimer();
        titleVisible.value = true;
        titleTimer = setTimeout(() => {
            titleVisible.value = false;
        }, titleOverlayDuration);
    }

    function clearTitleTimer() {
        if (titleTimer !== null) {
            clearTimeout(titleTimer);
            titleTimer = null;
        }
    }

    // --- Playback controls ---
    function togglePlayPause() {
        const video = videoEl.value;
        if (!video) return;

        if (video.paused || video.ended) {
            video.play().catch((err) => {
                console.warn('Play was prevented:', err);
            });
        } else {
            video.pause();
        }
    }

    function play() {
        videoEl.value?.play().catch((err) => {
            console.warn('Play was prevented:', err);
        });
    }

    function pause() {
        videoEl.value?.pause();
    }

    function seek(time: number) {
        const video = videoEl.value;
        if (!video) return;
        video.currentTime = Math.max(0, Math.min(time, video.duration || 0));
    }

    function seekRelative(delta: number) {
        const video = videoEl.value;
        if (!video) return;
        seek(video.currentTime + delta);
    }

    // --- Progress bar ---
    function handleProgressSeek(event: MouseEvent | TouchEvent) {
        const video = videoEl.value;
        if (!video) return;

        const target = event.currentTarget as HTMLElement;
        if (!target) return;

        const rect = target.getBoundingClientRect();
        let clientX: number;

        if ('touches' in event) {
            clientX = event.touches[0]?.clientX ?? event.changedTouches[0]?.clientX ?? 0;
        } else {
            clientX = event.clientX;
        }

        const position = Math.max(0, Math.min(clientX - rect.left, rect.width));
        const percentage = position / rect.width;
        video.currentTime = percentage * (video.duration || 0);
    }

    // --- Volume ---
    function setVolume(val: number) {
        const video = videoEl.value;
        if (!video) return;

        const clampedVolume = Math.max(0, Math.min(1, val));
        video.volume = clampedVolume;
        video.muted = clampedVolume === 0;
        volume.value = clampedVolume;
        isMuted.value = video.muted;
    }

    function toggleMute() {
        const video = videoEl.value;
        if (!video) return;

        video.muted = !video.muted;
        if (!video.muted && video.volume === 0) {
            video.volume = 0.75;
        }
        isMuted.value = video.muted;
        volume.value = video.muted ? 0 : video.volume;
    }

    function handleVolumeInput(event: Event) {
        const target = event.target as HTMLInputElement;
        setVolume(parseFloat(target.value));
    }

    // --- Fullscreen ---
    function toggleFullscreen() {
        const container = containerEl.value;
        if (!container) return;

        if (!document.fullscreenElement) {
            // Try standard API first, then webkit fallback for Safari/iOS
            if (container.requestFullscreen) {
                container.requestFullscreen().catch((err) => {
                    console.warn('Fullscreen request failed:', err);
                });
            } else if ((container as any).webkitRequestFullscreen) {
                (container as any).webkitRequestFullscreen();
            } else if ((container as any).msRequestFullscreen) {
                (container as any).msRequestFullscreen();
            }
        } else {
            if (document.exitFullscreen) {
                document.exitFullscreen();
            } else if ((document as any).webkitExitFullscreen) {
                (document as any).webkitExitFullscreen();
            } else if ((document as any).msExitFullscreen) {
                (document as any).msExitFullscreen();
            }
        }
    }

    // --- Video event handlers ---
    function onVideoPlay() {
        isPlaying.value = true;
        showControls();
        showTitle();
    }

    function onVideoPause() {
        isPlaying.value = false;
        clearControlsTimer();
        controlsVisible.value = true;
    }

    function onVideoEnded() {
        isPlaying.value = false;
        clearControlsTimer();
        controlsVisible.value = true;
        onEnded?.();
    }

    function onTimeUpdate() {
        const video = videoEl.value;
        if (!video) return;
        currentTime.value = video.currentTime;
        if (video.duration && isFinite(video.duration)) {
            progress.value = (video.currentTime / video.duration) * 100;
        }
    }

    function onLoadedMetadata() {
        const video = videoEl.value;
        if (!video) return;
        duration.value = video.duration || 0;
        currentTime.value = video.currentTime;
        volume.value = video.volume;
        isMuted.value = video.muted;
        isPlaying.value = !video.paused;
    }

    function onVolumeChange() {
        const video = videoEl.value;
        if (!video) return;
        volume.value = video.muted ? 0 : video.volume;
        isMuted.value = video.muted;
    }

    function onWaiting() {
        isBuffering.value = true;
    }

    function onCanPlay() {
        isBuffering.value = false;
    }

    // --- Container event handlers ---
    function onContainerMouseMove() {
        showControls();
    }

    function onContainerMouseLeave() {
        if (isPlaying.value) {
            clearControlsTimer();
            controlsTimer = setTimeout(() => {
                controlsVisible.value = false;
            }, 500);
        }
    }

    function onContainerTouchStart(event: TouchEvent) {
        // Check if touch started on a control element
        const target = event.target as HTMLElement;
        touchStartedOnControls = target.closest('.custom-controls, .back-button-container, .control-btn, .volume-slider, .progress-bar-container') !== null;
    }

    function onContainerTouchEnd(event: TouchEvent) {
        // Only toggle play/pause for taps on the video area (not on controls)
        if (!touchStartedOnControls) {
            if (controlsVisible.value) {
                // If controls are visible, toggle play/pause
                togglePlayPause();
            } else {
                // If controls are hidden, just show them
                showControls();
            }
        }
        touchStartedOnControls = false;
    }

    function onContainerClick(event: MouseEvent) {
        const target = event.target as HTMLElement;
        // Only toggle play/pause when clicking on the video or the container itself
        if (target.closest('.custom-controls, .back-button-container, .back-button')) return;
        togglePlayPause();
    }

    // --- Fullscreen change handler ---
    function onFullscreenChange() {
        isFullscreen.value = !!document.fullscreenElement || !!(document as any).webkitFullscreenElement;
    }

    // --- Keyboard handler ---
    function onKeyDown(event: KeyboardEvent) {
        if (!keyboardControls) return;

        // Don't handle keys if user is typing in an input
        const target = event.target as HTMLElement;
        if (target.tagName === 'INPUT' || target.tagName === 'TEXTAREA' || target.isContentEditable) return;

        const video = videoEl.value;
        if (!video) return;

        switch (event.code) {
            case 'Space':
                event.preventDefault();
                togglePlayPause();
                showControls();
                break;
            case 'ArrowLeft':
                event.preventDefault();
                seekRelative(-seekStep);
                showControls();
                break;
            case 'ArrowRight':
                event.preventDefault();
                seekRelative(seekStep);
                showControls();
                break;
            case 'ArrowUp':
                event.preventDefault();
                setVolume(video.volume + volumeStep);
                showControls();
                break;
            case 'ArrowDown':
                event.preventDefault();
                setVolume(video.volume - volumeStep);
                showControls();
                break;
            case 'KeyF':
                event.preventDefault();
                toggleFullscreen();
                break;
            case 'KeyM':
                event.preventDefault();
                toggleMute();
                showControls();
                break;
        }
    }

    // --- Lifecycle ---
    function init() {
        const video = videoEl.value;
        const container = containerEl.value;
        if (!video) return;

        // Video events
        video.addEventListener('play', onVideoPlay);
        video.addEventListener('pause', onVideoPause);
        video.addEventListener('ended', onVideoEnded);
        video.addEventListener('timeupdate', onTimeUpdate);
        video.addEventListener('loadedmetadata', onLoadedMetadata);
        video.addEventListener('volumechange', onVolumeChange);
        video.addEventListener('waiting', onWaiting);
        video.addEventListener('canplay', onCanPlay);

        // Container events
        if (container) {
            container.addEventListener('mousemove', onContainerMouseMove);
            container.addEventListener('mouseleave', onContainerMouseLeave);
            container.addEventListener('touchstart', onContainerTouchStart, { passive: true });
            container.addEventListener('touchend', onContainerTouchEnd, { passive: true });
            container.addEventListener('click', onContainerClick);
        }

        // Document events
        document.addEventListener('fullscreenchange', onFullscreenChange);
        document.addEventListener('webkitfullscreenchange', onFullscreenChange);

        if (keyboardControls) {
            document.addEventListener('keydown', onKeyDown);
        }

        // Initial state
        showControls();

        // Autoplay
        if (autoplay) {
            video.play().then(() => {
                // Autoplay started successfully
            }).catch(() => {
                // Autoplay blocked, try muted autoplay
                video.muted = true;
                isMuted.value = true;
                video.play().catch(() => {
                    // Even muted autoplay failed, user must interact
                });
            });
        }
    }

    function destroy() {
        const video = videoEl.value;
        const container = containerEl.value;

        if (video) {
            video.removeEventListener('play', onVideoPlay);
            video.removeEventListener('pause', onVideoPause);
            video.removeEventListener('ended', onVideoEnded);
            video.removeEventListener('timeupdate', onTimeUpdate);
            video.removeEventListener('loadedmetadata', onLoadedMetadata);
            video.removeEventListener('volumechange', onVolumeChange);
            video.removeEventListener('waiting', onWaiting);
            video.removeEventListener('canplay', onCanPlay);
        }

        if (container) {
            container.removeEventListener('mousemove', onContainerMouseMove);
            container.removeEventListener('mouseleave', onContainerMouseLeave);
            container.removeEventListener('touchstart', onContainerTouchStart);
            container.removeEventListener('touchend', onContainerTouchEnd);
            container.removeEventListener('click', onContainerClick);
        }

        document.removeEventListener('fullscreenchange', onFullscreenChange);
        document.removeEventListener('webkitfullscreenchange', onFullscreenChange);

        if (keyboardControls) {
            document.removeEventListener('keydown', onKeyDown);
        }

        clearControlsTimer();
        clearTitleTimer();
    }

    onMounted(init);
    onUnmounted(destroy);

    return {
        // Template refs (bind these in the template)
        videoEl,
        containerEl,

        // Reactive state
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

        // Computed
        formattedCurrentTime,
        formattedDuration,
        timeDisplayText,

        // Methods
        togglePlayPause,
        play,
        pause,
        seek,
        seekRelative,
        handleProgressSeek,
        setVolume,
        toggleMute,
        handleVolumeInput,
        toggleFullscreen,
        showControls,
        hideControls,
        showTitle,
        formatTime,
    };
}
