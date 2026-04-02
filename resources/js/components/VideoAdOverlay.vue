<script setup lang="ts">
import { ref, onMounted, onUnmounted, watch, nextTick } from 'vue';

const props = withDefaults(defineProps<{
    /** Show ads? (based on user's plan) */
    showAds: boolean;
    /** Google AdSense client ID */
    adClient?: string;
    /** Google AdSense slot ID */
    adSlot?: string;
    /** Duration in seconds the ad overlay is shown */
    adDuration?: number;
    /** Video duration in seconds (to calculate mid-roll schedule) */
    videoDuration?: number;
    /** Current playback time in seconds */
    currentTime?: number;
}>(), {
    adClient: 'ca-pub-4197071521851440',
    adSlot: '2560635252',
    adDuration: 10,
    videoDuration: 0,
    currentTime: 0,
});

const emit = defineEmits<{
    /** Emitted when an ad starts — parent should pause video */
    'ad-start': [];
    /** Emitted when an ad ends — parent should resume video */
    'ad-end': [];
    /** Emitted when pre-roll is complete — parent can start video */
    'preroll-complete': [];
}>();

// ─── State ───
const isAdVisible = ref(false);
const countdown = ref(0);
const adType = ref<'preroll' | 'midroll'>('preroll');
const prerollComplete = ref(false);
const adContainerRef = ref<HTMLElement | null>(null);
let countdownTimer: ReturnType<typeof setInterval> | null = null;

// ─── Mid-roll schedule ───
// For videos >= 30 minutes, show ads every 30 minutes
const MID_ROLL_INTERVAL = 30 * 60; // 30 minutes in seconds
const triggeredMidrolls = new Set<number>();

// ─── Show Ad ───
function showAd(type: 'preroll' | 'midroll') {
    if (!props.showAds) return;

    adType.value = type;
    isAdVisible.value = true;
    countdown.value = props.adDuration;

    emit('ad-start');

    // Try to load AdSense ad
    nextTick(() => {
        try {
            if (typeof window !== 'undefined' && (window as any).adsbygoogle) {
                (window as any).adsbygoogle.push({});
            }
        } catch (e) {
            console.warn('AdSense push failed:', e);
        }
    });

    // Start countdown
    countdownTimer = setInterval(() => {
        countdown.value--;
        if (countdown.value <= 0) {
            hideAd();
        }
    }, 1000);
}

function hideAd() {
    if (countdownTimer) {
        clearInterval(countdownTimer);
        countdownTimer = null;
    }
    isAdVisible.value = false;

    if (adType.value === 'preroll') {
        prerollComplete.value = true;
        emit('preroll-complete');
    }

    emit('ad-end');
}

// ─── Pre-roll: show ad on mount ───
onMounted(() => {
    if (props.showAds) {
        // Small delay to let the player initialize
        setTimeout(() => {
            showAd('preroll');
        }, 500);
    } else {
        prerollComplete.value = true;
        emit('preroll-complete');
    }
});

// ─── Mid-roll: watch currentTime for trigger points ───
watch(() => props.currentTime, (time) => {
    if (!props.showAds || !prerollComplete.value) return;
    if (props.videoDuration < MID_ROLL_INTERVAL) return; // Only for videos >= 30 min
    if (isAdVisible.value) return; // Don't trigger if ad is already showing

    // Check if we've passed a mid-roll point
    const midrollPoint = Math.floor(time / MID_ROLL_INTERVAL) * MID_ROLL_INTERVAL;
    if (midrollPoint > 0 && !triggeredMidrolls.has(midrollPoint) && time >= midrollPoint && time < midrollPoint + 2) {
        triggeredMidrolls.add(midrollPoint);
        showAd('midroll');
    }
});

onUnmounted(() => {
    if (countdownTimer) {
        clearInterval(countdownTimer);
    }
});

// Expose for parent to check state
defineExpose({ prerollComplete, isAdVisible, showAd });
</script>

<template>
    <Teleport to="body">
        <Transition name="ad-fade">
            <div v-if="isAdVisible" class="ad-overlay">
                <div class="ad-container" ref="adContainerRef">
                    <!-- Ad Label -->
                    <div class="ad-header">
                        <span class="ad-badge">AD</span>
                        <span class="ad-type">{{ adType === 'preroll' ? 'Pre-roll' : 'Ad Break' }}</span>
                        <span class="ad-countdown">
                            Resuming in {{ countdown }}s
                        </span>
                    </div>

                    <!-- Google AdSense Ad Unit -->
                    <div class="ad-content">
                        <ins class="adsbygoogle"
                            style="display:block; width:100%; min-height:250px;"
                            :data-ad-client="adClient"
                            :data-ad-slot="adSlot"
                            data-ad-format="auto"
                            data-full-width-responsive="true">
                        </ins>
                        
                        <!-- Fallback for dev/testing (AdSense won't render on localhost) -->
                        <div class="ad-fallback">
                            <div class="ad-fallback-icon"><i class="fas fa-bullhorn"></i></div>
                            <p class="ad-fallback-text">Advertisement</p>
                            <p class="ad-fallback-subtext">Google Ads will appear here in production</p>
                        </div>
                    </div>

                    <!-- Progress bar -->
                    <div class="ad-progress-container">
                        <div class="ad-progress-bar" :style="{ width: `${((props.adDuration - countdown) / props.adDuration) * 100}%` }"></div>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
.ad-overlay {
    position: fixed;
    inset: 0;
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(0, 0, 0, 0.92);
    backdrop-filter: blur(8px);
}

.ad-container {
    width: 100%;
    max-width: 728px;
    padding: 1rem;
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.ad-header {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.ad-badge {
    background: #f5c518;
    color: #000;
    font-size: 0.7rem;
    font-weight: 800;
    padding: 0.2rem 0.5rem;
    border-radius: 4px;
    letter-spacing: 1px;
}

.ad-type {
    color: rgba(255, 255, 255, 0.7);
    font-size: 0.85rem;
    font-weight: 500;
}

.ad-countdown {
    margin-left: auto;
    color: rgba(255, 255, 255, 0.6);
    font-size: 0.85rem;
    font-family: monospace;
}

.ad-content {
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 12px;
    min-height: 250px;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    overflow: hidden;
}

.ad-content .adsbygoogle {
    position: relative;
    z-index: 2;
}

.ad-fallback {
    position: absolute;
    inset: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    z-index: 1;
}

.ad-fallback-icon {
    font-size: 3rem;
    margin-bottom: 0.75rem;
    opacity: 0.5;
}

.ad-fallback-text {
    color: rgba(255, 255, 255, 0.5);
    font-size: 1rem;
    font-weight: 600;
}

.ad-fallback-subtext {
    color: rgba(255, 255, 255, 0.3);
    font-size: 0.8rem;
    margin-top: 0.25rem;
}

.ad-progress-container {
    width: 100%;
    height: 4px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 2px;
    overflow: hidden;
}

.ad-progress-bar {
    height: 100%;
    background: linear-gradient(90deg, #e8445a, #f5c518);
    border-radius: 2px;
    transition: width 1s linear;
}

/* Transition */
.ad-fade-enter-active,
.ad-fade-leave-active {
    transition: opacity 0.4s ease;
}
.ad-fade-enter-from,
.ad-fade-leave-to {
    opacity: 0;
}

/* Responsive */
@media (max-width: 768px) {
    .ad-container {
        max-width: 100%;
        padding: 0.75rem;
    }
    .ad-content {
        min-height: 200px;
    }
}
</style>
