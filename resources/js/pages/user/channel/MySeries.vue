<script setup lang="ts">
import { computed, ref, watch, onMounted } from 'vue';
import UserDashboardLayout from '@/layouts/UserDashboardLayout.vue';
import ErrorLabel from '@/components/form/ErrorLabel.vue';
import { Form, router, Link } from '@inertiajs/vue3';
import ManageProfileController from '@/actions/App/Http/Controllers/UserProfile/ManageProfileController';
import MovieChannelController from '@/actions/App/Http/Controllers/Channel/MovieChannelController';
import ShortChannelController from '@/actions/App/Http/Controllers/Channel/ShortChannelController';

import MySerieCard from './partials/MySerieCard.vue';

const props = defineProps<{
    series: any[];
    page: number;
    perPage: number;
}>();

function goToProfile() {
    router.visit(ManageProfileController());
}

function goToMovieChannel() {
    router.visit(MovieChannelController());
}

function goToShortChannel() {
    router.visit(ShortChannelController());
}
</script>

<template>
    <UserDashboardLayout 
        :title="`Series - ${$page.props.name || 'Impulsemedia'}`" 
        :headerTitle="`Series - ${$page.props.name || 'Impulsemedia'}`">
        <section class="channel-header">
            <div class="channel-info-card">
                <img :src="$page.props.auth.user.image_url" alt="Avatar" class="channel-avatar">
                <div class="channel-details">
                    <h1 class="channel-name">@{{ $page.props.auth.user.username }}</h1>
                    <div class="channel-stats">{{ $page.props.auth.user.followers_count }} Followers &bull; {{ $page.props.auth.user.content_count }} Videos</div>
                    <p v-if="$page.props.auth.user.bio" class="channel-bio">{{ $page.props.auth.user.bio }}</p>
                    <a v-if="$page.props.auth.user.external_link" :href="$page.props.auth.user.external_link" target="_blank" rel="noopener noreferrer" class="channel-link">{{ $page.props.auth.user.external_link }}</a>
                </div>
                <button class="channel-action-btn edit" id="channelActionBtn" @click="goToProfile">Edit Profile</button>
            </div>
        </section>

        <nav class="channel-tabs">
            <button class="tab-btn" data-content="movies" @click="goToMovieChannel">Movies</button>
            <button class="tab-btn active" data-content="series">Series</button>
            <button class="tab-btn" data-content="shorts" @click="goToShortChannel">Shorts</button>
        </nav>

        <main class="channel-content" style="margin-bottom: 80px;">
            <div id="moviesContent" class="content-grid"></div>
            <div id="seriesContent" class="content-grid active">
                <MySerieCard v-for="serie in series" :key="`my_serie_${serie.id}`" :serie="serie.contentable" />
            </div>
            <div id="shortsContent" class="content-grid"></div>
        </main>
        <br />
    </UserDashboardLayout>
</template>

<style scoped>
/* Cabecera del Canal */
.channel-header { padding: 2rem 1rem 1rem 1rem; }
.channel-info-card { background-color: var(--card-bg); border-radius: 16px; padding: 1.5rem; display: flex; align-items: center; gap: 1rem; }
.channel-avatar { width: 70px; height: 70px; border-radius: 50%; object-fit: cover; flex-shrink: 0; }
.channel-details { flex-grow: 1; }
.channel-name { font-size: 1.5rem; font-weight: 600; margin: 0 0 0.25rem 0; }
.channel-stats { color: #ccc; font-size: 0.9rem; }
.channel-bio { color: #ddd; font-size: 0.85rem; margin: 0.4rem 0 0.2rem 0; line-height: 1.4; }
.channel-link { color: #a78bfa; font-size: 0.85rem; text-decoration: none; word-break: break-all; }
.channel-link:hover { text-decoration: underline; }
.channel-action-btn { background-color: var(--primary-color); color: white; border: none; padding: 0.6rem 1.2rem; border-radius: 20px; font-weight: 500; cursor: pointer; flex-shrink: 0; margin-left: auto; transition: background-color 0.2s ease; }
.channel-action-btn.edit { background-color: rgba(255,255,255,0.2); }
.channel-action-btn.following { background-color: #555; }

/* Pestañas de Navegación */
.channel-tabs { display: flex; justify-content: flex-start; gap: 1rem; border-bottom: 1px solid #333; margin: 0 1rem 2rem 1rem; overflow-x: auto; }
.channel-tabs::-webkit-scrollbar { display: none; }
.tab-btn { background: none; border: none; color: #aaa; font-size: 1rem; font-weight: 500; padding: 0.75rem 0.5rem; cursor: pointer; border-bottom: 3px solid transparent; transition: all 0.2s; white-space: nowrap; }
.tab-btn.active { color: white; border-bottom-color: var(--primary-color); }

/* Contenido del Canal */
.channel-content { padding: 0 1rem; }
.content-grid { display: none; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 1rem; }
.content-grid.active { display: grid; }
.content-card { position: relative; aspect-ratio: 2/3; border-radius: 12px; overflow: hidden; background-color: #000; }
.content-card img, .content-card video { width: 100%; height: 100%; object-fit: cover; display: block; }
.content-card a { display: block; width: 100%; height: 100%; }
.options-menu-btn { position: absolute; top: 8px; right: 8px; background: rgba(0,0,0,0.6); color: white; border: none; border-radius: 50%; width: 30px; height: 30px; cursor: pointer; display: flex; align-items: center; justify-content: center; z-index: 10; }
.options-dropdown { display: none; position: absolute; top: 40px; right: 8px; background-color: #333; border-radius: 8px; overflow: hidden; z-index: 11; }
.options-dropdown button { display: block; width: 100%; background: none; border: none; color: white; padding: 0.75rem 1.5rem; text-align: left; white-space: nowrap; cursor: pointer; }
.options-dropdown button:hover { background-color: var(--primary-color); }

/* Navegación Inferior */
.bottom-nav { position: fixed; bottom: 0; left: 0; right: 0; background: var(--main-bg); padding: 1rem; display: flex; justify-content: space-around; border-top: 1px solid rgba(255,255,255,0.1); z-index: 1000; }
.nav-item { display: flex; flex-direction: column; align-items: center; color: white; text-decoration: none; font-size: 0.8rem; gap: 4px; }
.nav-item.active { color: var(--primary-color); }
.nav-icon { width: 24px; height: 24px; }

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