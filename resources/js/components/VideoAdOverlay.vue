<script setup lang="ts">
import { ref, onMounted, onUnmounted, watch, nextTick, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const adCampaigns = computed(() => (page.props as any).ad_campaigns || []);

const props = withDefaults(defineProps<{
    /** Show ads? (based on user's plan) */
    showAds: boolean;
    /** Google AdSense client ID */
    adClient?: string;
    /** Google AdSense slot ID */
    adSlot?: string;
    /** Duration in seconds the ad overlay is shown (for images) */
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
const canSkip = ref(false);
let countdownTimer: ReturnType<typeof setInterval> | null = null;

// ─── Custom Campaign State ───
const currentCampaign = ref<any>(null);
const isCustomAd = ref(false);
const customVideoRef = ref<HTMLVideoElement | null>(null);
const customVideoEnded = ref(false);

// ─── Mid-roll schedule ───
const MID_ROLL_INTERVAL = 30 * 60; // 30 minutes in seconds
const triggeredMidrolls = new Set<number>();

// ─── Get Random Campaign ───
function getRandomCampaign() {
    const campaigns = adCampaigns.value;
    if (!campaigns || campaigns.length === 0) return null;
    // Pick a random campaign (equitable rotation via random)
    const idx = Math.floor(Math.random() * campaigns.length);
    return campaigns[idx];
}

// ─── Show Ad ───
function showAd(type: 'preroll' | 'midroll') {
    if (!props.showAds) return;

    adType.value = type;
    isAdVisible.value = true;
    canSkip.value = false;
    customVideoEnded.value = false;

    // Try custom campaign first
    const campaign = getRandomCampaign();

    if (campaign) {
        // Custom campaign ad
        currentCampaign.value = campaign;
        isCustomAd.value = true;

        if (campaign.media_type === 'image') {
            // Image: show for adDuration seconds
            countdown.value = props.adDuration;
            startCountdown();
        } else {
            // Video: wait until video ends, then allow skip
            countdown.value = 0;
            // Video countdown will be managed by the video player events
        }
    } else {
        // Fallback to AdSense
        currentCampaign.value = null;
        isCustomAd.value = false;
        countdown.value = props.adDuration;

        nextTick(() => {
            try {
                if (typeof window !== 'undefined' && (window as any).adsbygoogle) {
                    (window as any).adsbygoogle.push({});
                }
            } catch (e) {
                console.warn('AdSense push failed:', e);
            }
        });

        startCountdown();
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
    // Allow skip after 5 seconds of video
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
                        <span v-if="currentCampaign?.company_name" class="ad-sponsor">
                            Sponsored by {{ currentCampaign.company_name }}
                        </span>
                        <span class="ad-countdown" v-if="countdown > 0">
                            Resuming in {{ countdown }}s
                        </span>
                        <button
                            v-if="isCustomAd && currentCampaign?.media_type === 'video' && canSkip"
                            class="skip-btn"
                            @click="skipAd"
                        >
                            Skip Ad →
                        </button>
                    </div>

                    <!-- Custom Campaign: Image -->
                    <div v-if="isCustomAd && currentCampaign?.media_type === 'image'" class="ad-content custom-ad">
                        <img :src="currentCampaign.media_url" alt="Advertisement" class="campaign-image" />
                    </div>

                    <!-- Custom Campaign: Video -->
                    <div v-else-if="isCustomAd && currentCampaign?.media_type === 'video'" class="ad-content custom-ad">
                        <video
                            ref="customVideoRef"
                            :src="currentCampaign.media_url"
                            class="campaign-video"
                            autoplay
                            playsinline
                            @ended="onCustomVideoEnded"
                            @timeupdate="onCustomVideoTimeUpdate"
                        ></video>
                    </div>

                    <!-- Google AdSense Fallback -->
                    <div v-else class="ad-content">
                        <ins class="adsbygoogle"
                            style="display:block; width:100%; min-height:250px;"
                            :data-ad-client="adClient"
                            :data-ad-slot="adSlot"
                            data-ad-format="auto"
                            data-full-width-responsive="true">
                        </ins>
                        
                        <div class="ad-fallback">
                            <div class="ad-fallback-icon"><i class="fas fa-bullhorn"></i></div>
                            <p class="ad-fallback-text">Advertisement</p>
                            <p class="ad-fallback-subtext">Loading ad...</p>
                        </div>
                    </div>

                    <!-- Progress bar -->
                    <div class="ad-progress-container" v-if="countdown > 0">
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
    .campaign-image,
    .campaign-video {
        max-height: 300px;
    }
}
</style>
