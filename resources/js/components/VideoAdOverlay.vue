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
const skipTimerSeconds = ref(15);
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
    
    if (shownAdIndexes.length >= ads.length) {
        shownAdIndexes = [];
    }
    
    const availableIndexes = ads.map((_: any, i: number) => i).filter((i: number) => !shownAdIndexes.includes(i));
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
    skipTimerSeconds.value = 15;

    const ad = getRandomAd();

    if (!ad) {
        if (type === 'preroll') {
            prerollComplete.value = true;
            emit('preroll-complete');
        }
        return;
    }

    currentAd.value = ad;
    isAdVisible.value = true;

    // Track impression
    if (ad.campaign_id) {
        const csrfMeta = document.querySelector('meta[name="csrf-token"]');
        fetch('/ad/impression', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfMeta?.getAttribute('content') || '',
            },
            body: JSON.stringify({ campaign_id: ad.campaign_id }),
        }).catch(() => {});
    }

    if (ad.media_type === 'image') {
        countdown.value = props.adDuration;
        startCountdown();
    } else {
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
    skipTimerSeconds.value = 0;
}

function onCustomVideoTimeUpdate() {
    if (customVideoRef.value) {
        const time = customVideoRef.value.currentTime;
        skipTimerSeconds.value = Math.max(0, Math.ceil(15 - time));
        if (time >= 15) {
            canSkip.value = true;
        }
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
    if (props.videoDuration <= 3600) return;
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
                    <!-- Top Bar Header -->
                    <div class="ad-header">
                        <div class="ad-header-left">
                            <span class="ad-badge">AD</span>
                            <span class="ad-type">{{ adType === 'preroll' ? 'Pre-roll' : 'Ad Break' }}</span>
                        </div>
                        
                        <div v-if="currentAd.company_name" class="ad-sponsor">
                            Sponsored by <strong>{{ currentAd.company_name }}</strong>
                        </div>
                    </div>

                    <!-- Main Ad Player Media Container -->
                    <div class="ad-content-wrapper">
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

                        <!-- Floating YouTube-style 15s Countdown & Skip Button Badge (Bottom Right) -->
                        <div v-if="currentAd.media_type === 'video'" class="ad-skip-pill-container">
                            <div v-if="!canSkip" class="ad-timer-pill">
                                <div class="timer-spinner-icon">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                        <circle cx="12" cy="12" r="9" stroke-opacity="0.3" />
                                        <path d="M12 3a9 9 0 0 1 9 9" stroke="#e8445a" stroke-linecap="round" />
                                    </svg>
                                </div>
                                <span>Skip ad in <strong>{{ skipTimerSeconds }}s</strong></span>
                            </div>

                            <button
                                v-else
                                type="button"
                                class="ad-skip-btn-active"
                                @click="skipAd"
                            >
                                <span>Skip Ad</span>
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M5 12h14" />
                                    <path d="M12 5l7 7-7 7" />
                                </svg>
                            </button>
                        </div>

                        <!-- Image Ad Countdown Badge -->
                        <div v-else-if="countdown > 0" class="ad-skip-pill-container">
                            <div class="ad-timer-pill">
                                <span>Resuming video in <strong>{{ countdown }}s</strong></span>
                            </div>
                        </div>
                    </div>

                    <!-- Bottom Progress bar for image ads -->
                    <div class="ad-progress-container" v-if="currentAd.media_type === 'image' && countdown > 0">
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
    z-index: 99999;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(5, 7, 15, 0.95);
    backdrop-filter: blur(14px);
    padding: 1rem;
}

.ad-container {
    width: 100%;
    max-width: 820px;
    display: flex;
    flex-direction: column;
    gap: 1rem;
    position: relative;
}

.ad-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    padding: 0 0.25rem;
}

.ad-header-left {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.ad-badge {
    background: #f5c518;
    color: #000000;
    font-size: 0.75rem;
    font-weight: 900;
    padding: 0.2rem 0.6rem;
    border-radius: 6px;
    letter-spacing: 1px;
    box-shadow: 0 2px 8px rgba(245, 197, 24, 0.4);
}

.ad-type {
    color: rgba(255, 255, 255, 0.85);
    font-size: 0.9rem;
    font-weight: 600;
}

.ad-sponsor {
    color: rgba(255, 255, 255, 0.65);
    font-size: 0.85rem;
}

.ad-sponsor strong {
    color: #ffffff;
}

.ad-content-wrapper {
    position: relative;
    width: 100%;
    border-radius: 16px;
    overflow: hidden;
    background: #000000;
    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.8), 0 0 0 1px rgba(255, 255, 255, 0.1);
}

.ad-content {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #000000;
}

.campaign-image {
    width: 100%;
    max-height: 480px;
    object-fit: contain;
}

.campaign-video {
    width: 100%;
    max-height: 480px;
    object-fit: contain;
    display: block;
}

/* Floating Skip Pill Positioned on Media Bottom-Right */
.ad-skip-pill-container {
    position: absolute;
    bottom: 20px;
    right: 20px;
    z-index: 30;
}

.ad-timer-pill {
    display: flex;
    align-items: center;
    gap: 8px;
    background: rgba(15, 23, 42, 0.88);
    backdrop-filter: blur(12px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    color: #ffffff;
    padding: 10px 18px;
    border-radius: 30px;
    font-size: 0.9rem;
    font-weight: 500;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.6);
}

.ad-timer-pill strong {
    color: #e8445a;
    font-weight: 700;
}

.timer-spinner-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    animation: spin 1.2s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

/* Active Skip Button */
.ad-skip-btn-active {
    display: flex;
    align-items: center;
    gap: 8px;
    background: #e8445a;
    color: #ffffff;
    border: 1px solid rgba(255, 255, 255, 0.3);
    padding: 10px 22px;
    border-radius: 30px;
    font-size: 0.95rem;
    font-weight: 700;
    cursor: pointer;
    transition: transform 0.2s ease, background-color 0.2s ease, box-shadow 0.2s ease;
    box-shadow: 0 8px 24px rgba(232, 68, 90, 0.5);
}

.ad-skip-btn-active:hover {
    background: #ff4d67;
    transform: scale(1.05);
    box-shadow: 0 10px 28px rgba(232, 68, 90, 0.7);
}

.ad-progress-container {
    width: 100%;
    height: 5px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 4px;
    overflow: hidden;
}

.ad-progress-bar {
    height: 100%;
    background: linear-gradient(90deg, #e8445a, #f5c518);
    border-radius: 4px;
    transition: width 1s linear;
}

/* Transitions */
.ad-fade-enter-active,
.ad-fade-leave-active {
    transition: opacity 0.4s ease;
}
.ad-fade-enter-from,
.ad-fade-leave-to {
    opacity: 0;
}

/* Responsive UI UX Adjustments */
@max-width: 768px {
    .ad-container {
        max-width: 100%;
        gap: 0.75rem;
    }

    .ad-skip-pill-container {
        bottom: 12px;
        right: 12px;
    }

    .ad-timer-pill {
        padding: 8px 14px;
        font-size: 0.8rem;
    }

    .ad-skip-btn-active {
        padding: 8px 16px;
        font-size: 0.85rem;
    }

    .campaign-image,
    .campaign-video {
        max-height: 320px;
    }
}
</style>
