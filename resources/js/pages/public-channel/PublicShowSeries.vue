<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import PublicHeader from '@/components/PublicHeader.vue';
import { user as loginRoute } from '@/routes/login';
import ShowRegisterUserController from '@/actions/App/Http/Controllers/Auth/User/ShowRegisterUserController';

const props = defineProps<{
    series: any[];
    page: number;
    perPage: number;
    user: any;
}>();

const authUser = (window as any).__page?.props?.auth?.user ?? null;

// Login modal
const showLoginModal = ref(false);
const openLoginModal = () => { showLoginModal.value = true; };
const closeLoginModal = () => { showLoginModal.value = false; };

function goToMovieChannel() {
    router.visit(`/public/channel/${props.user.username}/movie`);
}

function goToShortChannel() {
    router.visit(`/public/channel/${props.user.username}/short`);
}

function handleContentClick(event: Event) {
    if (!authUser) {
        event.preventDefault();
        event.stopPropagation();
        openLoginModal();
    }
}
</script>

<template>
    <Head :title="`${user.username} — Series`" />
    <div class="public-channel-wrapper">
        <PublicHeader :user="$page.props.auth?.user" />

        <div class="public-channel-content">
            <!-- Channel info -->
            <section class="channel-header">
                <div class="channel-info-card">
                    <img :src="user.image_url" alt="Avatar" class="channel-avatar">
                    <div class="channel-details">
                        <h1 class="channel-name">@{{ user.username }}</h1>
                        <div class="channel-stats">{{ user.followers_count }} Followers &bull; {{ user.content_count }} Videos</div>
                        <p v-if="user.bio" class="channel-bio">{{ user.bio }}</p>
                        <a v-if="user.external_link" :href="user.external_link" target="_blank" rel="noopener noreferrer" class="channel-link">{{ user.external_link }}</a>
                    </div>
                </div>
            </section>

            <!-- Tabs -->
            <nav class="channel-tabs">
                <button class="tab-btn" @click="goToMovieChannel">Movies</button>
                <button class="tab-btn active">Series</button>
                <button class="tab-btn" @click="goToShortChannel">Shorts</button>
            </nav>

            <!-- Content grid -->
            <main class="channel-content-area">
                <div class="content-grid active">
                    <div v-for="serie in series" :key="`serie_${serie.id}`" class="content-card" @click="handleContentClick($event)">
                        <template v-if="authUser">
                            <Link :href="`/serie/${serie.contentable.id}`">
                                <img :src="serie.contentable.vertical_image_url" alt="Content" />
                            </Link>
                        </template>
                        <template v-else>
                            <a href="#" @click.prevent="openLoginModal">
                                <img :src="serie.contentable.vertical_image_url" alt="Content" />
                            </a>
                        </template>
                    </div>
                </div>
                <div v-if="series.length === 0" class="empty-state">
                    <p>No series yet.</p>
                </div>
            </main>
        </div>
    </div>

    <!-- Login Required modal -->
    <Teleport to="body">
        <div v-if="showLoginModal" class="modal-backdrop" @click.self="closeLoginModal">
            <div class="login-modal">
                <button class="modal-close-btn" @click="closeLoginModal">&times;</button>
                <img src="/images/logo.png" alt="Logo" class="modal-logo">
                <h2 class="modal-title">Join to watch</h2>
                <p class="modal-subtitle">
                    Create an account or log in to watch series, follow creators, and more.
                </p>
                <Link :href="ShowRegisterUserController()" class="btn btn-modal-signup">Create Account</Link>
                <Link :href="loginRoute()" class="btn btn-modal-login">Log In</Link>
            </div>
        </div>
    </Teleport>
</template>

<style scoped>
.public-channel-wrapper {
    width: 100%;
    min-height: 100vh;
    background-color: #0d0d1a;
    color: #fff;
}

.public-channel-content {
    padding-top: 70px;
    max-width: 1200px;
    margin: 0 auto;
}

/* Channel Header */
.channel-header { padding: 2rem 1rem 1rem 1rem; }
.channel-info-card { background-color: rgba(255,255,255,0.05); border-radius: 16px; padding: 1.5rem; display: flex; align-items: center; gap: 1rem; }
.channel-avatar { width: 70px; height: 70px; border-radius: 50%; object-fit: cover; flex-shrink: 0; }
.channel-details { flex-grow: 1; }
.channel-name { font-size: 1.5rem; font-weight: 600; margin: 0 0 0.25rem 0; }
.channel-stats { color: #ccc; font-size: 0.9rem; }
.channel-bio { color: #ddd; font-size: 0.85rem; margin: 0.4rem 0 0.2rem 0; line-height: 1.4; }
.channel-link { color: #a78bfa; font-size: 0.85rem; text-decoration: none; word-break: break-all; }
.channel-link:hover { text-decoration: underline; }

/* Tabs */
.channel-tabs { display: flex; justify-content: flex-start; gap: 1rem; border-bottom: 1px solid #333; margin: 0 1rem 2rem 1rem; overflow-x: auto; }
.channel-tabs::-webkit-scrollbar { display: none; }
.tab-btn { background: none; border: none; color: #aaa; font-size: 1rem; font-weight: 500; padding: 0.75rem 0.5rem; cursor: pointer; border-bottom: 3px solid transparent; transition: all 0.2s; white-space: nowrap; }
.tab-btn.active { color: white; border-bottom-color: #e8445a; }

/* Content */
.channel-content-area { padding: 0 1rem 4rem; }
.content-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 1rem; }
.content-grid.active { display: grid; }
.content-card { position: relative; aspect-ratio: 2/3; border-radius: 12px; overflow: hidden; background-color: #000; }
.content-card img, .content-card video { width: 100%; height: 100%; object-fit: cover; display: block; }
.content-card a { display: block; width: 100%; height: 100%; }

.empty-state { text-align: center; padding: 3rem 1rem; color: #888; }

/* Modal */
.modal-backdrop { position: fixed; inset: 0; background: rgba(0,0,0,0.75); z-index: 1000; display: flex; align-items: center; justify-content: center; padding: 1rem; }
.login-modal { background: #1a1a2e; border-radius: 16px; padding: 2rem 1.75rem; width: 100%; max-width: 380px; text-align: center; position: relative; color: #fff; box-shadow: 0 20px 60px rgba(0,0,0,0.6); }
.modal-close-btn { position: absolute; top: 0.75rem; right: 1rem; background: none; border: none; color: rgba(255,255,255,0.6); font-size: 1.75rem; line-height: 1; cursor: pointer; transition: color 0.2s; }
.modal-close-btn:hover { color: #fff; }
.modal-logo { width: 48px; height: 48px; object-fit: contain; margin-bottom: 1.25rem; }
.modal-title { font-size: 1.4rem; font-weight: 700; margin-bottom: 0.5rem; }
.modal-subtitle { font-size: 0.9rem; color: rgba(255,255,255,0.7); line-height: 1.5; margin-bottom: 1.5rem; }
.btn-modal-signup { display: block; width: 100%; background: #e8445a; color: #fff; border: none; border-radius: 10px; padding: 0.75rem 1rem; font-size: 1rem; font-weight: 600; text-decoration: none; margin-bottom: 0.75rem; transition: background 0.2s; cursor: pointer; }
.btn-modal-signup:hover { background: #d03050; color: #fff; }
.btn-modal-login { display: block; width: 100%; background: transparent; color: #fff; border: 1.5px solid rgba(255,255,255,0.4); border-radius: 10px; padding: 0.75rem 1rem; font-size: 1rem; font-weight: 500; text-decoration: none; transition: border-color 0.2s, background 0.2s; cursor: pointer; }
.btn-modal-login:hover { border-color: rgba(255,255,255,0.8); background: rgba(255,255,255,0.05); color: #fff; }

@media (min-width: 1200px) {
    .channel-tabs { justify-content: center; }
    .content-grid { grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); }
    .channel-content-area { padding: 0 4rem 4rem; }
    .channel-header { padding: 2rem 4rem 1rem 4rem; }
    .channel-avatar { width: 100px; height: 100px; }
    .channel-name { font-size: 2rem; }
}
</style>
