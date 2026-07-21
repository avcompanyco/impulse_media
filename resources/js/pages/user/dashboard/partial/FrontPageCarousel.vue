<script setup lang="ts">
import { ref, onMounted, computed, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';
import ShowMovieController from '@/actions/App/Http/Controllers/Movie/ShowMovieController';
import ShowSerieController from '@/actions/App/Http/Controllers/Serie/ShowSerieController';
import AddToFollowController from '@/actions/App/Http/Controllers/Follow/AddToFollowController';
import RemoveToFollowController from '@/actions/App/Http/Controllers/Follow/RemoveToFollowController';
import ShowMovieChannelController from '@/actions/App/Http/Controllers/Channel/ShowMovieChannelController';
import ShowCreatorProfileController from '@/actions/App/Http/Controllers/CreatorProfile/ShowCreatorProfileController';

interface Content {
    id: number;
    type: string;
    status: string;
    contentable: {
        id: number;
        title: string;
        description: string;
        horizontal_image_url: string;
        vertical_image_url: string;
        trailer_video_url?: string;
    };
    user: {
        id: number;
        name: string;
        image_url: string;
        username: string;
        is_followed: boolean;
    };
}

const props = defineProps<{
    frontpage: Content[];
}>();

const currentSlide = ref(0);

const validFrontpage = computed(() => {
    return props.frontpage.filter(c => c.contentable);
});

const currentContent = computed(() => {
    if (validFrontpage.value.length === 0) return null;
    return validFrontpage.value[currentSlide.value];
});

const nextSlide = () => {
    if (validFrontpage.value.length === 0) return;
    currentSlide.value = (currentSlide.value + 1) % validFrontpage.value.length;
};

const prevSlide = () => {
    if (validFrontpage.value.length === 0) return;
    currentSlide.value = currentSlide.value === 0 ? validFrontpage.value.length - 1 : currentSlide.value - 1;
};

const goToSlide = (index: number) => {
    currentSlide.value = index;
};

const interval = ref<any>(null);
const followButtonLoading = ref(false);
const unfollowButtonLoading = ref(false);

onMounted(() => {
    // Auto-play carousel
    interval.value = setInterval(() => {
        nextSlide();
    }, 5000);
});

onUnmounted(() => {
    if (interval.value) {
        clearInterval(interval.value);
    }
});

function followUser(userId: number) {
    followButtonLoading.value = true;
    router.post(AddToFollowController({user: userId}), {}, {
        preserveScroll: true,
        onSuccess: () => {
            followButtonLoading.value = false;
        },
    });
}

function unfollowUser(userId: number) {
    unfollowButtonLoading.value = true;
    router.post(RemoveToFollowController({user: userId}), {}, {
        preserveScroll: true,
        onSuccess: () => {
            unfollowButtonLoading.value = false;
        },
    });
}
</script>

<template>
    <div class="carousel-container" v-if="validFrontpage.length > 0">
        <div class="carousel-wrapper">
            <div class="main-carousel">
                <div class="carousel-slides">
                    <div 
                        v-for="(content, index) in validFrontpage" 
                        :key="content.id"
                        class="carousel-slide"
                        :class="{ 'active': index === currentSlide }"
                    >
                        <div class="carousel-content">
                            <img 
                                :src="content.contentable.horizontal_image_url" 
                                :alt="content.contentable.title" 
                                class="carousel-image mobile-image"
                            >
                            <img 
                                :src="content.contentable.horizontal_image_url" 
                                :alt="content.contentable.title" 
                                class="carousel-image desktop-image"
                            >
                        </div>
                    </div>
                </div>
                
                <div class="slide-indicators">
                    <div class="indicator-pill">
                        <div 
                            v-for="(content, index) in validFrontpage" 
                            :key="content.id"
                            class="indicator-number"
                            :class="{ 'active': index === currentSlide }"
                            @click="goToSlide(index)"
                            tabindex="0"
                        >
                            {{ index + 1 }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="now-playing" v-if="currentContent">
        <div class="user-info">
            <img 
                :src="currentContent.user.image_url" 
                :alt="currentContent.user.name" 
                class="user-avatar"
                style="cursor: pointer;"
                @click="router.visit(ShowCreatorProfileController({ user: currentContent.user.username }))"
            >
            <span class="username" style="cursor: pointer;" @click="router.visit(ShowCreatorProfileController({ user: currentContent.user.username }))">@{{ currentContent.user.username }}</span>
        </div>
        <h1 class="movie-title">{{ currentContent.contentable.title }}</h1>
        <div class="content-meta">
            <span class="content-type-tag">{{ currentContent.type }}</span>
            <span v-if="currentContent" class="ppv-carousel-badge" :class="{ 'ppv-paid': !currentContent.allow_membership || Number(currentContent.ppv_price) > 0 }">
                {{ (!currentContent.allow_membership || Number(currentContent.ppv_price) > 0) ? `PPV $${Number(currentContent.ppv_price).toFixed(2)}` : 'Included with Membership' }}
            </span>
        </div>
        <div class="button-group">
            <button 
                v-if="currentContent.type == 'movies'" 
                class="action-button primary"
                @click="router.visit(ShowMovieController({movie: currentContent.contentable.id}))"
                >
                Watch Now
            </button>
            <button 
                v-if="currentContent.type == 'series'" 
                class="action-button primary"
                @click="router.visit(ShowSerieController({serie: currentContent.contentable.id}))"
                >
                Watch Now
            </button>
            <template v-if="$page.props.auth.user.id !== currentContent.user.id">
                <button 
                    v-if="!currentContent.user.is_followed"
                    class="action-button secondary"
                    @click="followUser(currentContent.user.id)"
                    :disabled="followButtonLoading"
                    >
                    <i class="fa-solid fa-circle-notch fa-spin"
                        v-if="followButtonLoading"
                    ></i>
                    Follow
                </button>
                <button 
                    v-else
                    class="action-button secondary"
                    @click="router.visit(ShowCreatorProfileController({user: currentContent.user.username}))"
                    >
                    View Profile
                </button>
            </template>
        </div>
    </div>
</template>

<style scoped>

.carousel-wrapper {
    position: relative;
    width: 100%;
    height: 100%;
}

.main-carousel {
    position: relative;
    width: 100%;
    height: 100%;
}

.carousel-slides {
    position: relative;
    width: 100%;
    height: 100%;
    min-height: 354px;
}

.carousel-slide {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    opacity: 0;
    transition: opacity 0.8s ease-in-out;
}

.carousel-slide.active {
    opacity: 1;
}

.carousel-content {
    position: relative;
    width: 100%;
    height: 100%;
}

.carousel-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 12px;
}

.mobile-image {
    display: block;
}

.desktop-image {
    display: none;
}

@media (min-width: 768px) {
    .mobile-image {
        display: none;
    }
    
    .desktop-image {
        display: block;
    }
    
    .carousel-container {
        height: 400px;
    }
}

@media (min-width: 1200px) {
    .carousel-container {
        height: 594px;
    }
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
    color: rgba(255,255,255,0.7);
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
    background: rgba(255,255,255,0.2);
    color: white;
}
.action-button.secondary:hover {
    background: rgba(255,255,255,0.3);
}

.ppv-carousel-badge {
    display: inline-block;
    margin-left: 0.5rem;
    background: rgba(72, 187, 120, 0.2);
    color: #48bb78;
    border: 1px solid rgba(72, 187, 120, 0.4);
    font-size: 0.75rem;
    font-weight: 700;
    padding: 0.2rem 0.6rem;
    border-radius: 12px;
    vertical-align: middle;
}

.ppv-carousel-badge.ppv-paid {
    background: rgba(232, 68, 90, 0.2);
    color: #e8445a;
    border-color: rgba(232, 68, 90, 0.4);
}
</style>