<script setup lang="ts">
import { onMounted, watch, ref, onUnmounted } from 'vue';
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import { movie as movieUpload } from '@/routes/user/upload'

import { dashboard as dashboardRoute } from '@/routes'
import { show as profileRoute } from '@/routes/user/profile/index'

import ShowWatchlistController from '@/actions/App/Http/Controllers/Watchlist/ShowWatchlistController';
import ShowCategoryController from '@/actions/App/Http/Controllers/Category/ShowCategoryController';
import ShowSubcategoryController from '@/actions/App/Http/Controllers/Subcategory/ShowSubcategoryController';
import SearchController from '@/actions/App/Http/Controllers/SearchController';
import MovieChannelController from '@/actions/App/Http/Controllers/Channel/MovieChannelController';
import ShowMovieChannelController from '@/actions/App/Http/Controllers/Channel/ShowMovieChannelController';
import IndexShortController from '@/actions/App/Http/Controllers/Short/IndexShortController';

import Toast from '@/components/Toast.vue';

const props = defineProps<{
    title: string;
    headerTitle: string;
}>()

const myToast = ref<any>(null);
const isSideMenuOpen = ref(false);

const openSideMenu = () => {
    isSideMenuOpen.value = true;
    document.body.style.overflow = 'hidden';
};

const closeSideMenu = () => {
    isSideMenuOpen.value = false;
    document.body.style.overflow = 'auto';
};

onMounted(() => {
    // Close menu when clicking outside
    const menuOverlay = document.getElementById('menuOverlay');
    if (menuOverlay) {
        menuOverlay.addEventListener('click', closeSideMenu);
    }
});

onUnmounted(() => {
    document.body.style.overflow = 'auto';
});

watch(() => usePage().props, (newVal: any) => {
    if (newVal.errors.type && newVal.errors.title && newVal.errors.message) {
        myToast.value.addToast(newVal.errors);
    } else if (newVal.flash.type && newVal.flash.title && newVal.flash.message) {
        myToast.value.addToast(newVal.flash);
    }
})

</script>

<template>

    <Head>
        <title>{{ title }}</title>
    </Head>

    <div class="menu-overlay" id="menuOverlay" :class="{ active: isSideMenuOpen }"></div>
    <aside class="side-menu" id="sideMenu" :class="{ active: isSideMenuOpen }">
        <div class="side-menu-header">
            <button class="close-btn" id="closeMenuBtn" @click="closeSideMenu">&times;</button>
            <img src="/images/logo.png" alt="Logo" class="logo-icon">
        </div>
        <div class="side-menu-content">
            <Link v-if="$page.props.auth.user.is_creator" :href="MovieChannelController()" class="menu-item">
            <img :src="$page.props.auth.user.image_url" alt="My Channel" class="menu-profile-icon">
            <span>My Channel</span>
            </Link>
            <Link :href="ShowWatchlistController()" class="menu-item">
            <span>Watchlist</span>
            </Link>
            <div class="menu-section-title">Categories</div>
            <template v-for="(category, index) in $page.props.web_categories" :key="`web_category_${index}`">
                <details v-if="category.subcategories.length > 0" class="category-item">
                    <summary>{{ category.name }}</summary>
                    <ul class="subcategory-list">
                        <li v-for="(subcategory, i) in category.subcategories" :key="`web_subcategory_${i}`">
                            <Link :href="ShowSubcategoryController(subcategory)" class="menu-item">{{ subcategory.name
                            }}</Link>
                        </li>
                    </ul>
                </details>
            </template>
            <div class="menu-section-title">Subscriptions</div>
            <Link v-for="(subscription, index) in $page.props.subscriptions" :key="`web_subscription_${index}`"
                :href="ShowMovieChannelController({ user: subscription.username })" class="menu-item subscription-item">
            <img :src="subscription.image_url" alt="User Avatar">
            <span>@{{ subscription.username }}</span>
            </Link>
        </div>
    </aside>
    <div class="app-wrapper">
        <header class="header">
            <button class="hamburger-menu-btn" id="openMenuBtn" @click="openSideMenu">&#9776;</button>
            <img src="/images/logo.png" alt="Logo" class="logo-icon" style="margin: 0px;cursor: pointer;"
                @click="router.visit(dashboardRoute())">
            <div class="header-placeholder"></div>
        </header>
    </div>
    <slot name="shorts-content" />
    <main class="main-content">
        <slot name="main-content" />
    </main>
    <div class="app-wrapper" style="overflow-y: auto;">
        <slot />
    </div>
    <nav class="bottom-nav">
        <Link :href="dashboardRoute()" class="nav-item" :class="{ active: $page.url === dashboardRoute.url() }">
            <img src="/images/home.svg" alt="Home" class="nav-icon">
            <span>Home</span>
        </Link>
        <Link :href="SearchController()" class="nav-item" :class="{ active: $page.url.includes('search') }">
            <img src="/images/search.svg" alt="Explore" class="nav-icon">
            <span>Explore</span>
        </Link>
        <Link :href="IndexShortController()" class="nav-item" :class="{ active: $page.url === IndexShortController.url() }">
            <img src="/images/clip.svg" alt="Shorts" class="nav-icon">
            <span>Shorts</span>
        </Link>
        <Link v-if="$page.props.auth.user.is_creator" :href="movieUpload()" class="nav-item" :class="{ active: $page.url === movieUpload.url() }">
            <img src="/images/upload.svg" alt="Upload" class="nav-icon">
            <span>Upload</span>
        </Link>
        <Link :href="profileRoute()" class="nav-item" :class="{ active: $page.url === profileRoute.url() }">
            <img :src="$page.props.auth.user.image_url || '/images/Jhon.webp'" alt="Profile" class="nav-icon" style="border-radius: 50%;">
            <span>Profile</span>
        </Link>
    </nav>

    <Toast ref="myToast" />

</template>

<style scoped>
/* Header */
.header {
    padding: 0.75rem 1rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-shrink: 0;
    position: sticky;
    top: 0;
    z-index: 101;
    background-color: var(--main-bg);
}

.hamburger-menu-btn {
    background: none;
    border: none;
    color: var(--text-light);
    font-size: 1.5rem;
    cursor: pointer;
}

.logo-icon {
    width: 48px;
    height: 48px;
    position: absolute;
    left: 50%;
    transform: translateX(-50%);
}

.header-placeholder {
    width: 40px;
}

/* Side Menu Styles */
.side-menu {
    position: fixed;
    top: 0;
    left: 0;
    width: 280px;
    height: 100%;
    background-color: var(--sidebar-bg);
    z-index: 1001;
    transform: translateX(-100%);
    transition: transform 0.3s ease-in-out;
    display: flex;
    flex-direction: column;
}

.side-menu.active {
    transform: translateX(0);
}

.side-menu-header {
    display: flex;
    align-items: center;
    padding: 0.75rem 1rem;
}

.side-menu-header .close-btn {
    background: none;
    border: none;
    color: var(--text-light);
    font-size: 1.5rem;
    cursor: pointer;
}

.side-menu-header .logo-icon {
    position: static;
    transform: none;
    margin-left: 1rem;
}

.side-menu-content {
    padding: 1rem;
    overflow-y: auto;
}

.menu-section-title {
    color: #aaa;
    font-weight: 500;
    font-size: 0.9rem;
    text-transform: uppercase;
    padding: 0.5rem 0;
    margin-top: 1rem;
    border-top: 1px solid #444;
}

.menu-section-title:first-child {
    margin-top: 0;
    border-top: none;
}

.menu-item,
.category-item summary {
    display: flex;
    align-items: center;
    gap: 1rem;
    color: var(--text-light);
    text-decoration: none;
    padding: 0.8rem;
    border-radius: 8px;
    font-weight: 500;
    transition: background-color 0.2s;
}

.menu-item:hover,
.category-item summary:hover {
    background-color: rgba(255, 255, 255, 0.1);
}

.menu-item .menu-profile-icon {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    object-fit: cover;
}

.category-item summary {
    cursor: pointer;
    list-style: none;
    justify-content: space-between;
}

.category-item summary::-webkit-details-marker {
    display: none;
}

.category-item summary::after {
    content: '›';
    transform: rotate(90deg);
    transition: transform 0.2s;
}

.category-item[open]>summary::after {
    transform: rotate(-90deg);
}

.subcategory-list {
    padding-left: 1.5rem;
    list-style: none;
}

.subcategory-list .menu-item {
    padding-left: 0.5rem;
}

.subscription-item img {
    width: 32px;
    height: 32px;
    border-radius: 50%;
}

.menu-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.6);
    z-index: 1000;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.3s ease-in-out;
}

.menu-overlay.active {
    opacity: 1;
    pointer-events: auto;
}

/* Main Carousel */
.carousel-container {
    padding: 0 1rem;
    margin-bottom: 1.5rem;
}

.carousel-wrapper {
    position: relative;
    border-radius: 24px;
    padding: 2px;
    background: linear-gradient(45deg, var(--gradient-start), var(--gradient-end));
    overflow: hidden;
}

.carousel-content {
    position: relative;
    border-radius: 22px;
    overflow: hidden;
    background: var(--main-bg);
    aspect-ratio: 1/1;
}

.carousel-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s var(--transition-bezier);
}

.mobile-image {
    display: block;
}

.desktop-image {
    display: none;
}

.slide-indicators {
    position: absolute;
    bottom: 20px;
    left: 50%;
    transform: translateX(-50%);
    background: rgba(0, 0, 0, 0.6);
    border-radius: 30px;
    padding: 4px;
    display: flex;
    gap: 4px;
    z-index: 10;
}

.indicator-pill {
    display: flex;
    gap: 4px;
}

.indicator-number {
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: rgba(255, 255, 255, 0.7);
    font-weight: 500;
    border-radius: 50%;
    cursor: pointer;
    transition: all 0.3s var(--transition-bezier);
}

.indicator-number.active {
    background: var(--gradient-start);
    color: white;
    font-size: 1.2rem;
}

/* Now Playing Section */
.now-playing {
    padding: 0 1rem;
    margin-bottom: 2rem;
}

.user-info {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 0.75rem;
}

.user-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    object-fit: cover;
}

.username {
    font-size: 1rem;
    font-weight: 500;
}

.movie-title {
    font-size: 2rem;
    font-weight: 600;
    margin-bottom: 1rem;
    line-height: 1.2;
}

.content-meta {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 1.5rem;
}

.content-type-tag {
    background: rgba(255, 255, 255, 0.15);
    color: var(--text-light);
    padding: 0.25rem 0.75rem;
    border-radius: 8px;
    font-size: 0.8rem;
    font-weight: 500;
}

.button-group {
    display: flex;
    gap: 1rem;
}

.action-button {
    border: none;
    border-radius: 20px;
    padding: 0.75rem 1.5rem;
    font-size: 0.9rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s var(--transition-bezier);
}

.action-button.primary {
    background-color: var(--primary-color);
    color: white;
}

.action-button.primary:hover {
    background-color: #d84373;
}

.action-button.secondary {
    background: rgba(255, 255, 255, 0.2);
    color: white;
}

.action-button.secondary:hover {
    background: rgba(255, 255, 255, 0.3);
}


/* Movie Sections */
.movies-section {
    position: relative;
    padding: 0 1rem;
    margin-bottom: 2rem;
    overflow: visible;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.section-title {
    font-size: 1.75rem;
    font-weight: 600;
    margin-bottom: 0;
    color: inherit;
    text-decoration: none;
}

.view-all-btn {
    background-color: rgba(255, 255, 255, 0.1);
    color: var(--text-light);
    text-decoration: none;
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    font-size: 0.8rem;
    font-weight: 500;
    transition: background-color 0.2s;
}

.view-all-btn:hover {
    background-color: rgba(255, 255, 255, 0.2);
    color: var(--text-light);
}

.movies-row {
    position: relative;
    display: flex;
    gap: 0.75rem;
    overflow-x: auto;
    padding: 1rem;
    margin: -1rem;
    scroll-snap-type: x mandatory;
    -ms-overflow-style: none;
    scrollbar-width: none;
    cursor: grab;
    user-select: none;
    scroll-behavior: smooth;
    -webkit-overflow-scrolling: touch;
}

.movies-row::-webkit-scrollbar {
    display: none;
}

.movies-row.grabbing {
    cursor: grabbing;
}

.movie-card {
    flex: 0 0 auto;
    width: 110px;
    aspect-ratio: 2/3;
    border-radius: 12px;
    overflow: hidden;
    transition: all 0.3s var(--transition-bezier);
    scroll-snap-align: start;
    position: relative;
    display: block;
    text-decoration: none;
}

.movie-card a {
    display: block;
    width: 100%;
    height: 100%;
}

.movie-card img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

/* Navigation Arrows Style */
.slider-arrow {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    width: 40px;
    height: 40px;
    background: rgba(128, 128, 128, 0.3);
    border-radius: 50%;
    display: none;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    z-index: 99;
    border: none;
    color: white;
    transition: all 0.2s ease;
    opacity: 0;
}

.slider-arrow::after {
    content: '›';
    font-size: 28px;
    font-weight: 400;
    line-height: 1;
}

.slider-arrow.prev::after {
    content: '‹';
}

.slider-arrow:hover {
    background: rgba(128, 128, 128, 0.5);
}

/* Bottom Navigation */
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
    transition: all 0.3s var(--transition-bezier);
}

.nav-item.active {
    color: var(--gradient-start);
}

.nav-icon {
    width: 24px;
    height: 24px;
}

/* Desktop Styles */
@media (min-width: 1200px) {
    .app-wrapper {
        max-width: 1600px;
        margin: 0 auto;
        padding: 0 4rem;
    }

    .header {
        justify-content: space-between;
    }

    .logo-icon {
        position: absolute;
        left: 50%;
        transform: translateX(-50%);
    }

    .carousel-container {
        padding: 0;
    }

    .carousel-content {
        aspect-ratio: 21/9;
    }

    .mobile-image {
        display: none;
    }

    .desktop-image {
        display: block;
    }

    .movies-section {
        padding: 0;
        margin: 0 60px 3rem;
    }

    .movies-row {
        padding: 2rem;
        margin: -2rem;
    }

    .movie-card {
        width: 180px;
        transition: all 0.3s ease;
        transform-origin: center center;
    }

    .movies-row:hover .movie-card {
        transform-origin: center left;
    }

    .movie-card:hover {
        transform: scale(1.2);
        z-index: 20;
    }

    .movie-card:hover~.movie-card {
        transform: translateX(30px);
    }

    .movies-row .movie-card:first-child {
        transform-origin: left center;
    }

    .movies-row .movie-card:last-child {
        transform-origin: right center;
    }

    .movie-title {
        font-size: 2.5rem;
        max-width: 800px;
    }

    .slider-arrow {
        display: flex;
    }

    .slider-arrow.prev {
        left: -80px;
    }

    .slider-arrow.next {
        right: -80px;
    }
}
</style>