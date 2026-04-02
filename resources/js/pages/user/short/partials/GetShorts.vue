<script setup lang="ts">
import { ref } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import ShortsPlayer from '@/components/ShortsPlayer.vue';
import type { ShortItem, ShortUser } from '@/components/ShortsPlayer.vue';
import AddToFollowController from '@/actions/App/Http/Controllers/Follow/AddToFollowController';
import RemoveToFollowController from '@/actions/App/Http/Controllers/Follow/RemoveToFollowController';
import RandomShortController from '@/actions/App/Http/Controllers/Short/RandomShortController';
import ShowCreatorProfileController from '@/actions/App/Http/Controllers/CreatorProfile/ShowCreatorProfileController';

// Props
const props = defineProps<{
    initialShort?: any;
}>();

// State
const shorts = ref<ShortItem[]>([]);
const isLoadingMoreShorts = ref(false);
const hasInitialShorts = ref(false);
const playerRef = ref<InstanceType<typeof ShortsPlayer>>();

const addFollowLoading = ref(false);
const removeFollowLoading = ref(false);

const page = usePage();

// Load shorts
async function getNextTenShorts() {
    if (isLoadingMoreShorts.value) return;

    isLoadingMoreShorts.value = true;
    try {
        const response = await fetch(RandomShortController.url(), {
            method: 'GET',
        }).then((r) => r.json());

        if (response.shorts && response.shorts.length > 0) {
            // Deduplicate: don't add shorts that are already in the list
            const existingIds = new Set(shorts.value.map(s => s.id));
            const newShorts = response.shorts.filter((s: any) => !existingIds.has(s.id));
            shorts.value.push(...newShorts);
            if (!hasInitialShorts.value) {
                hasInitialShorts.value = true;
            }
        }
    } catch (error) {
        console.error('Error loading more shorts:', error);
    } finally {
        isLoadingMoreShorts.value = false;
    }
}

// Initial load: if initialShort is provided, seed it first
if (props.initialShort) {
    shorts.value.push(props.initialShort);
    hasInitialShorts.value = true;
}
getNextTenShorts();

// Follow / unfollow
function handleFollowClick(event: Event, user: ShortUser) {
    event.stopPropagation();
    if (user.is_followed) {
        removeFromFollow(user.id);
    } else {
        addToFollow(user.id);
    }
}

function addToFollow(userId: number) {
    addFollowLoading.value = true;
    router.post(AddToFollowController({ user: userId }), {}, {
        preserveState: true,
        preserveScroll: false,
        onFinish: () => {
            addFollowLoading.value = false;
            const idx = shorts.value.findIndex((s) => s.user.id === userId);
            if (idx !== -1) {
                shorts.value[idx].user.is_followed = true;
            }
        },
    });
}

function removeFromFollow(userId: number) {
    removeFollowLoading.value = true;
    router.post(RemoveToFollowController({ user: userId }), {}, {
        preserveState: true,
        preserveScroll: false,
        onFinish: () => {
            removeFollowLoading.value = false;
            const idx = shorts.value.findIndex((s) => s.user.id === userId);
            if (idx !== -1) {
                shorts.value[idx].user.is_followed = false;
            }
        },
    });
}

// Navigate to creator profile
function handleUserClick(event: Event, user: ShortUser) {
    event.stopPropagation();
    router.visit(ShowCreatorProfileController({ user: user.username }));
}

// Track view for the current short
async function trackView(short: ShortItem, index: number) {
    if (!short?.id) return;
    try {
        const contentId = (short as any).content?.id;
        if (!contentId) return;
        await fetch(`/content/${contentId}/view`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-XSRF-TOKEN': decodeURIComponent(document.cookie.match(/XSRF-TOKEN=([^;]+)/)?.[1] || ''),
            },
            credentials: 'same-origin',
        });
    } catch (e) {
        // Silent fail
    }
}
</script>

<template>
    <ShortsPlayer
        ref="playerRef"
        :shorts="shorts"
        :is-loading-more="isLoadingMoreShorts"
        :has-initial-shorts="hasInitialShorts"
        container-id="shortsContainer"
        loading-text="Loading shorts..."
        empty-text="No se pudieron cargar los shorts"
        @load-more="getNextTenShorts"
        @retry="getNextTenShorts"
        @change="trackView"
    >
        <template #overlay="{ short }">
            <div class="user-info">
                <div class="user-avatar-container" @click.stop="handleUserClick($event, short.user)">
                    <img :src="short.user.image_url" alt="User Avatar" class="user-avatar" loading="lazy">
                </div>
                <span class="username" @click.stop="handleUserClick($event, short.user)">
                    @{{ short.user.username }}
                </span>

                <template v-if="short.user.id !== $page.props.auth.user.id">
                    <button
                        v-if="!short.user.is_followed"
                        class="follow-btn"
                        @click.stop="handleFollowClick($event, short.user)"
                        :disabled="addFollowLoading"
                    >
                        <i class="fa-solid fa-circle-notch fa-spin" v-if="addFollowLoading"></i>
                        <span v-else>Follow</span>
                    </button>
                    <button
                        v-else
                        class="follow-btn unfollow"
                        @click.stop="handleFollowClick($event, short.user)"
                        :disabled="removeFollowLoading"
                    >
                        <i class="fa-solid fa-circle-notch fa-spin" v-if="removeFollowLoading"></i>
                        <span v-else>Unfollow</span>
                    </button>
                </template>
            </div>
            <p class="video-description">{{ short.text_caption }}</p>
        </template>
    </ShortsPlayer>
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
    background-color: var(--primary-color, #e8445a);
    color: var(--text-light, #fff);
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
    color: var(--text-light, #fff);
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