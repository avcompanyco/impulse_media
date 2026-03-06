<script setup lang="ts">
import { ref } from 'vue';
import ShortsPlayer from '@/components/ShortsPlayer.vue';
import type { ShortItem } from '@/components/ShortsPlayer.vue';

const emit = defineEmits<{
    requireLogin: [];
}>();

// State
const shorts = ref<ShortItem[]>([]);
const isLoadingMoreShorts = ref(false);
const hasInitialShorts = ref(false);
const playerRef = ref<InstanceType<typeof ShortsPlayer>>();

// Load shorts
async function getNextTenShorts() {
    if (isLoadingMoreShorts.value) return;

    isLoadingMoreShorts.value = true;
    try {
        const response = await fetch('/public/shorts/random', {
            method: 'GET',
            headers: { 'Accept': 'application/json' },
        }).then((r) => r.json());

        if (response.shorts && response.shorts.length > 0) {
            shorts.value.push(...response.shorts);
            if (!hasInitialShorts.value) {
                hasInitialShorts.value = true;
            }
        }
    } catch (error) {
        console.error('Error loading public shorts:', error);
    } finally {
        isLoadingMoreShorts.value = false;
    }
}

// Initial load
getNextTenShorts();

// Intercept interactions to prompt login
function handleInteraction(event: Event) {
    event.stopPropagation();
    event.preventDefault();
    emit('requireLogin');
}
</script>

<template>
    <div
        style="margin-top: 80px;"
        >
        <ShortsPlayer
            ref="playerRef"
            :shorts="shorts"
            :is-loading-more="isLoadingMoreShorts"
            :has-initial-shorts="hasInitialShorts"
            container-id="publicShortsContainer"
            loading-text="Loading..."
            empty-text="No shorts available right now."
            @load-more="getNextTenShorts"
            @retry="getNextTenShorts"
        >
            <template #overlay="{ short }">
                <div class="user-info">
                    <div class="user-avatar-container" @click.stop="handleInteraction($event)">
                        <img :src="short.user.image_url" alt="User Avatar" class="user-avatar" loading="lazy">
                    </div>
                    <span class="username" @click.stop="handleInteraction($event)">
                        @{{ short.user.username }}
                    </span>
                    <button class="follow-btn" @click.stop="handleInteraction($event)">
                        Follow
                    </button>
                </div>
                <p class="video-description">{{ short.text_caption }}</p>
            </template>
        </ShortsPlayer>
    </div>
</template>

<style scoped>
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

@media (max-width: 768px) {
    .follow-btn {
        padding: 8px 16px;
        font-size: 0.9rem;
    }

    .user-avatar {
        width: 44px;
        height: 44px;
    }
}
</style>