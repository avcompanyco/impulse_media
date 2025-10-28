<script setup lang="ts">
import { computed, ref, watch, onMounted } from 'vue';
import UserDashboardLayout from '@/layouts/UserDashboardLayout.vue';
import { Form, router, Link } from '@inertiajs/vue3';
import ShowMovieChannelController from '@/actions/App/Http/Controllers/Channel/ShowMovieChannelController';
import ShowSerieChannelController from '@/actions/App/Http/Controllers/Channel/ShowSerieChannelController';

import ChannelSection from './partials/ChannelSection.vue';
import ShortCard from './partials/ShortCard.vue';

const props = defineProps<{
    shorts: any[];
    page: number;
    perPage: number;
    user: any;
}>();

function goToMovieChannel() {
    router.visit(ShowMovieChannelController({ user: props.user.username }));
}

function goToSerieChannel() {
    router.visit(ShowSerieChannelController({ user: props.user.username }));
}

</script>

<template>
    <UserDashboardLayout title="Dashboard" headerTitle="Dashboard">
        <ChannelSection :user="user" />

        <nav class="channel-tabs">
            <button class="tab-btn" data-content="movies" @click="goToMovieChannel">Movies</button>
            <button class="tab-btn" data-content="series" @click="goToSerieChannel">Series</button>
            <button class="tab-btn active" data-content="shorts">Shorts</button>
        </nav>

        <main class="channel-content" style="margin-bottom: 80px;">
            <div id="moviesContent" class="content-grid"></div>
            <div id="seriesContent" class="content-grid"></div>
            <div id="shortsContent" class="content-grid active">
                <ShortCard v-for="short in shorts" :key="`my_short_${short.id}`" :short="short.contentable" />
            </div>
        </main>
    </UserDashboardLayout>
</template>

<style scoped>
/* Pestañas de Navegación */
.channel-tabs {
    display: flex;
    justify-content: flex-start;
    gap: 1rem;
    border-bottom: 1px solid #333;
    margin: 0 1rem 2rem 1rem;
    overflow-x: auto;
}

.channel-tabs::-webkit-scrollbar {
    display: none;
}

.tab-btn {
    background: none;
    border: none;
    color: #aaa;
    font-size: 1rem;
    font-weight: 500;
    padding: 0.75rem 0.5rem;
    cursor: pointer;
    border-bottom: 3px solid transparent;
    transition: all 0.2s;
    white-space: nowrap;
}

.tab-btn.active {
    color: white;
    border-bottom-color: var(--primary-color);
}

/* Contenido del Canal */
.channel-content {
    padding: 0 1rem;
}

.content-grid {
    display: none;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 1rem;
}

.content-grid.active {
    display: grid;
}

.content-card {
    position: relative;
    aspect-ratio: 2/3;
    border-radius: 12px;
    overflow: hidden;
    background-color: #000;
}

.content-card img,
.content-card video {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

.content-card a {
    display: block;
    width: 100%;
    height: 100%;
}

.options-menu-btn {
    position: absolute;
    top: 8px;
    right: 8px;
    background: rgba(0, 0, 0, 0.6);
    color: white;
    border: none;
    border-radius: 50%;
    width: 30px;
    height: 30px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10;
}

.options-dropdown {
    display: none;
    position: absolute;
    top: 40px;
    right: 8px;
    background-color: #333;
    border-radius: 8px;
    overflow: hidden;
    z-index: 11;
}

.options-dropdown button {
    display: block;
    width: 100%;
    background: none;
    border: none;
    color: white;
    padding: 0.75rem 1.5rem;
    text-align: left;
    white-space: nowrap;
    cursor: pointer;
}

.options-dropdown button:hover {
    background-color: var(--primary-color);
}

/* Navegación Inferior */
.bottom-nav {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    background: var(--main-bg);
    padding: 1rem;
    display: flex;
    justify-content: space-around;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    z-index: 1000;
}

.nav-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    color: white;
    text-decoration: none;
    font-size: 0.8rem;
    gap: 4px;
}

.nav-item.active {
    color: var(--primary-color);
}

.nav-icon {
    width: 24px;
    height: 24px;
}

@media (min-width: 1200px) {
    .channel-header {
        padding: 2rem 4rem 1rem 4rem;
    }

    .channel-avatar {
        width: 100px;
        height: 100px;
    }

    .channel-name {
        font-size: 2rem;
    }

    .channel-stats {
        font-size: 1rem;
    }

    .channel-tabs {
        justify-content: center;
    }

    .content-grid {
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    }

    .channel-content {
        padding: 0 4rem;
    }
}
</style>