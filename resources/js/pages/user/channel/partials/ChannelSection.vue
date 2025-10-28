<script setup lang="ts">
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

import AddToFollowController from '@/actions/App/Http/Controllers/Follow/AddToFollowController';
import RemoveToFollowController from '@/actions/App/Http/Controllers/Follow/RemoveToFollowController';

const props = defineProps<{
    user: any;
}>();

const followButtonLoading = ref(false);
const unfollowButtonLoading = ref(false);

function addToFollow(userId: number) {
    followButtonLoading.value = true;
    router.post(AddToFollowController(userId), {}, {
        onSuccess: () => {
            followButtonLoading.value = false;
        },
        preserveScroll: true,
    });
}

function removeFromFollow(userId: number) {
    unfollowButtonLoading.value = true;
    router.post(RemoveToFollowController(userId), {}, {
        onSuccess: () => {
            unfollowButtonLoading.value = false;
        },
        preserveScroll: true,
    });
}
</script>

<template>
    <section class="channel-header">
        <div class="channel-info-card">
            <img :src="user.image_url" alt="Avatar" class="channel-avatar">
            <div class="channel-details">
                <h1 class="channel-name">@{{ user.username }}</h1>
                <div class="channel-stats">{{ user.followers_count }} Followers &bull; {{ user.content_count }} Videos
                </div>
            </div>
            <button v-if="!user.is_followed" class="channel-action-btn" id="channelActionBtn"
                @click="addToFollow(user.id)">
                <i class="fa-solid fa-circle-notch fa-spin" v-if="followButtonLoading"></i>
                Follow
            </button>
            <button v-else class="channel-action-btn" id="channelActionBtn" :disabled="unfollowButtonLoading"
                style="background: var(--destructive-action-bg);" @click="removeFromFollow(user.id)">
                <i class="fa-solid fa-circle-notch fa-spin" v-if="unfollowButtonLoading"></i>
                Unfollow
            </button>
        </div>
    </section>
</template>

<style scoped>
/* Cabecera del Canal */
.channel-header { padding: 2rem 1rem 1rem 1rem; }
.channel-info-card { background-color: var(--card-bg); border-radius: 16px; padding: 1.5rem; display: flex; align-items: center; gap: 1rem; }
.channel-avatar { width: 70px; height: 70px; border-radius: 50%; object-fit: cover; flex-shrink: 0; }
.channel-details { flex-grow: 1; }
.channel-name { font-size: 1.5rem; font-weight: 600; margin: 0 0 0.25rem 0; }
.channel-stats { color: #ccc; font-size: 0.9rem; }
.channel-action-btn { background-color: var(--primary-color); color: white; border: none; padding: 0.6rem 1.2rem; border-radius: 20px; font-weight: 500; cursor: pointer; flex-shrink: 0; margin-left: auto; transition: background-color 0.2s ease; }
.channel-action-btn.edit { background-color: rgba(255,255,255,0.2); }
.channel-action-btn.following { background-color: #555; }
@media (min-width: 1200px) {
    .channel-header { padding: 2rem 4rem 1rem 4rem; }
    .channel-avatar { width: 100px; height: 100px; }
    .channel-name { font-size: 2rem; }
    .channel-stats { font-size: 1rem; }
    .channel-tabs { justify-content: center; }
    .content-grid { grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); }
    .channel-content { padding: 0 4rem; }
}
</style>