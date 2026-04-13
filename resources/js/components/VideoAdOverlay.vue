<script setup lang="ts">
import { ref, onMounted, onUnmounted, watch, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const adCampaigns = computed(() => (page.props as any).ad_campaigns || []);

const props = withDefaults(defineProps<{
    /** Show ads? (based on user's plan) */
    showAds: boolean;
    /** Duration in seconds the ad overlay is shown (for images) */
    adDuration?: number;
    /** Video duration in seconds (to calculate mid-roll schedule) */
    videoDuration?: number;
    /** Current playback time in seconds */
    currentTime?: number;
}>(), {
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
const canSkip = ref(false);
let countdownTimer: ReturnType<typeof setInterval> | null = null;

// ─── Custom Campaign State ───
const currentAd = ref<any>(null);
const customVideoRef = ref<HTMLVideoElement | null>(null);
const customVideoEnded = ref(false);

// ─── Mid-roll schedule ───
const MID_ROLL_INTERVAL = 30 * 60; // 30 minutes
const triggeredMidrolls = new Set<number>();

// ─── Rotation tracking for equal distribution ───
let shownAdIndexes: number[] = [];

function getRandomAd() {
    const ads = adCampaigns.value;
    if (!ads || ads.length === 0) return null;
    
    // If all ads have been shown, reset the tracking
    if (shownAdIndexes.length >= ads.length) {
        shownAdIndexes = [];
    }
    
    // Get indexes that haven't been shown yet
    const availableIndexes = ads.map((_: any, i: number) => i).filter((i: number) => !shownAdIndexes.includes(i));
    
    // Pick random from available
    const randomIdx = availableIndexes[Math.floor(Math.random() * availableIndexes.length)];
    shownAdIndexes.push(randomIdx);
    
    return ads[randomIdx];
}

// ─── Show Ad ───
function showAd(type: 'preroll' | 'midroll') {
    if (!props.showAds) return;

    adType.value = type;
    customVideoEnded.value = false;
    canSkip.value = false;

    const ad = getRandomAd();

    if (!ad) {
        // No campaigns available, skip ad
        if (type === 'preroll') {
            prerollComplete.value = true;
            emit('preroll-complete');
        }
        return;
    }

    currentAd.value = ad;
    isAdVisible.value = true;

    if (ad.media_type === 'image') {
        countdown.value = props.adDuration;
        startCountdown();
    } else {
        // Video: managed by video events
        countdown.value = 0;
    }

    emit('ad-start');
}

function startCountdown() {
    countdownTimer = setInterval(() => {
        countdown.value--;
        if (countdown.value <= 0) {
            hideAd();
        }
    }, 1000);
}

function onCustomVideoEnded() {
    customVideoEnded.value = true;
    canSkip.value = true;
}

function onCustomVideoTimeUpdate() {
    if (customVideoRef.value && customVideoRef.value.currentTime >= 5) {
        canSkip.value = true;
    }
}

function skipAd() {
    if (canSkip.value || customVideoEnded.value) {
        hideAd();
    }
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
    if (props.videoDuration < MID_ROLL_INTERVAL) return;
    if (isAdVisible.value) return;

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

defineExpose({ prerollComplete, isAdVisible, showAd });
</script>

<template>
    <Teleport to="body">
        <Transition name="ad-fade">
            <div v-if="isAdVisible && currentAd" class="ad-overlay">
                <div class="ad-container">
                    <!-- Ad Label -->
                    <div class="ad-header">
                        <span class="ad-badge">AD</span>
                        <span class="ad-type">{{ adType === 'preroll' ? 'Pre-roll' : 'Ad Break' }}</span>
                        <span v-if="currentAd.company_name" class="ad-sponsor">
                            Sponsored by {{ currentAd.company_name }}
                        </span>
                        <span class="ad-countdown" v-if="countdown > 0">
                            Resuming in {{ countdown }}s
                        </span>
                        <button
                            v-if="currentAd.media_type === 'video' && canSkip"
                            class="skip-btn"
                            @click="skipAd"
                        >
                            Skip Ad →
                        </button>
                    </div>

                    <!-- Campaign: Image -->
                    <div v-if="currentAd.media_type === 'image'" class="ad-content custom-ad">
                        <img :src="currentAd.media_url" alt="Advertisement" class="campaign-image" />
                    </div>

                    <!-- Campaign: Video -->
                    <div v-else class="ad-content custom-ad">
                        <video
                            ref="customVideoRef"
                            :src="currentAd.media_url"
                            class="campaign-video"
                            autoplay
                            playsinline
                            @ended="onCustomVideoEnded"
                            @timeupdate="onCustomVideoTimeUpdate"
                        ></video>
                    </div>

                    <!-- Progress bar -->
                    <div class="ad-progress-container" v-if="countdown > 0">
                        <div class="ad-progress-bar" :style="{ width: `${((adDuration - countdown) / adDuration) * 100}%` }"></div>
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
    flex-wrap: wrap;
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

.ad-sponsor {
    color: rgba(255, 255, 255, 0.5);
    font-size: 0.8rem;
    font-style: italic;
}

.ad-countdown {
    margin-left: auto;
    color: rgba(255, 255, 255, 0.6);
    font-size: 0.85rem;
    font-family: monospace;
}

.skip-btn {
    margin-left: auto;
    background: rgba(255, 255, 255, 0.15);
    border: 1px solid rgba(255, 255, 255, 0.3);
    color: white;
    padding: 0.4rem 1rem;
    border-radius: 6px;
    font-size: 0.85rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}
.skip-btn:hover {
    background: rgba(255, 255, 255, 0.25);
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

.ad-content.custom-ad {
    min-height: auto;
}

.campaign-image {
    max-width: 100%;
    max-height: 400px;
    object-fit: contain;
    border-radius: 12px;
}

.campaign-video {
    width: 100%;
    max-height: 400px;
    border-radius: 12px;
    object-fit: contain;
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
    .campaign-image,
    .campaign-video {
        max-height: 300px;
    }
}
</style>
