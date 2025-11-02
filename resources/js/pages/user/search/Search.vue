<script setup lang="ts">
import UserDashboardLayout from '@/layouts/UserDashboardLayout.vue';
import { ref, onMounted, nextTick, computed, watch } from 'vue';
import { router, Link, usePage } from '@inertiajs/vue3';
import ShowCategoryController from '@/actions/App/Http/Controllers/Category/ShowCategoryController';
import SearchController from '@/actions/App/Http/Controllers/SearchController';
import ShowMovieController from '@/actions/App/Http/Controllers/Movie/ShowMovieController';
import FilterSearchController from '@/actions/App/Http/Controllers/FilterSearchController';
import { useQueryParams } from '@/composables/useQueryParams';
import SearchData from './partials/SearchData.vue';

interface Content {
    id: number;
    type: string;
    contentable: {
        id: number;
        title: string;
        description: string;
        vertical_image_url: string;
        horizontal_image_url: string;
        category_id: number;
        subcategory_id: number;
    };
}

const props = defineProps<{
    categories: any[];
}>();

const showSearchResults = ref(false);
const showCategories = ref(true);
const page = usePage();
const searchInput = ref<HTMLInputElement | null>(null);

const queryParams = ref({
    search: '',
    ...useQueryParams(page.url)
});


const pageTitle = computed(() => {
    if (queryParams.value.category && queryParams.value.category.length > 0) {
        return props.categories.find(category => category.id == queryParams.value.category)?.name;
    }
    return 'Explore';
});


function shuffleArray(array: any[]) {
    const shuffled = [...array];
    for (let i = shuffled.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [shuffled[i], shuffled[j]] = [shuffled[j], shuffled[i]];
    }
    return shuffled;
}

function initializeMovieRowScroll(rowElement: HTMLElement) {
    const isMobile = window.innerWidth < 1200;

    if (isMobile) {
        let isScrolling = false;
        let startX = 0;
        let scrollLeft = 0;

        const startDragging = (e: MouseEvent | TouchEvent) => {
            isScrolling = true;
            rowElement.classList.add('grabbing');
            startX = e.type.includes('mouse') ? (e as MouseEvent).pageX : (e as TouchEvent).touches[0].pageX;
            scrollLeft = rowElement.scrollLeft;
            rowElement.style.scrollBehavior = 'auto';
        };

        const stopDragging = () => {
            isScrolling = false;
            rowElement.classList.remove('grabbing');
            rowElement.style.scrollBehavior = 'smooth';
        };

        const move = (e: MouseEvent | TouchEvent) => {
            if (!isScrolling) return;
            e.preventDefault();
            const x = e.type.includes('mouse') ? (e as MouseEvent).pageX : (e as TouchEvent).touches[0].pageX;
            const distance = x - startX;
            rowElement.scrollLeft = scrollLeft - distance;
        };

        rowElement.addEventListener('touchstart', startDragging, { passive: true });
        rowElement.addEventListener('touchend', stopDragging);
        rowElement.addEventListener('touchmove', move, { passive: false });
        rowElement.addEventListener('mousedown', startDragging);
        rowElement.addEventListener('mouseup', stopDragging);
        rowElement.addEventListener('mouseleave', stopDragging);
        rowElement.addEventListener('mousemove', move);
    } else {
        const prevButton = rowElement.parentElement?.querySelector('.prev') as HTMLButtonElement;
        const nextButton = rowElement.parentElement?.querySelector('.next') as HTMLButtonElement;

        if (prevButton && nextButton) {
            prevButton.addEventListener('click', () => {
                rowElement.scrollBy({ left: -rowElement.offsetWidth + 100, behavior: 'smooth' });
            });

            nextButton.addEventListener('click', () => {
                rowElement.scrollBy({ left: rowElement.offsetWidth - 100, behavior: 'smooth' });
            });

            const updateArrows = () => {
                prevButton.style.opacity = rowElement.scrollLeft <= 0 ? '0' : '1';
                nextButton.style.opacity = Math.ceil(rowElement.scrollLeft + rowElement.clientWidth) >= rowElement.scrollWidth ? '0' : '1';
            };

            rowElement.addEventListener('scroll', updateArrows);
            updateArrows();
        }
    }
}

function handleCategoryClick(categoryId: number) {
    router.visit(SearchController({
        query: { category: categoryId }
    }))
}
let debounceTimeout: ReturnType<typeof setTimeout> | null = null;

function handleSearch() {
    if (debounceTimeout) {
        clearTimeout(debounceTimeout);
    }
    debounceTimeout = setTimeout(() => {
        router.visit(SearchController({
            query: queryParams.value
        }), {
            preserveUrl: false,
            preserveState: true,
            preserveScroll: true,
            onFinish: () => {
                searchInput.value?.focus();
            }
        });
    }, 1000);
}

onMounted(() => {
    // Si hay término de búsqueda al cargar, mostrar resultados
    const urlParams = new URLSearchParams(window.location.search);
    const searchParam = urlParams.get('search');

    if (searchParam) {
        queryParams.value.search = searchParam;
        showCategories.value = false;
        showSearchResults.value = true;
    }

    // Initialize scroll for any existing movie rows
    const movieRows = document.querySelectorAll('.movies-row') as NodeListOf<HTMLElement>;
    movieRows.forEach(row => initializeMovieRowScroll(row));
});

function haveQueryParams() {
    let hasQueryParams = false;
    if (queryParams.value.search && queryParams.value.search.length > 0) {
        hasQueryParams = true;
    }
    if (queryParams.value.category && queryParams.value.category.length > 0) {
        hasQueryParams = true;
    }

    return hasQueryParams;
}

</script>

<template>
    <UserDashboardLayout 
        :title="`Search - ${$page.props.name || 'Impulsemedia'}`" 
        :headerTitle="`Search - ${$page.props.name || 'Impulsemedia'}`">
        <!-- Search Bar -->
        <div class="search-wrapper">
            <div class="search-container">
                <input ref="searchInput" type="text" class="search-bar" placeholder="Search by title, genre..." v-model="queryParams.search"
                    @input:debounce.500ms="handleSearch" />
            </div>
        </div>

        <main class="content" style="margin-bottom: 80px;">
            <h1 class="page-title">{{ pageTitle }}</h1>

            <!-- Categories Grid -->
            <div v-if="!haveQueryParams()" class="categories-grid">
                <div v-for="category in categories"
                    :key="`show_category_${category.id}_search`" 
                    class="category-card"
                    @click="handleCategoryClick(category.id)">
                    <img :src="category.image_url || '/images/action.png'" :alt="category.name" class="category-image">
                    <div class="category-overlay">
                        <h2 class="category-title">{{ category.name }}</h2>
                    </div>
                </div>
            </div>
            <template v-else>
                <Suspense>
                    <SearchData v-model:url="queryParams" />
                </Suspense>
            </template>

            <!-- Search Results -->
            <!-- <div v-if="showSearchResults" class="search-results">
                <div class="movies-row">
                    <div v-for="(content, index) in filteredContents" :key="`content_${content.id}_card_${index + 1}`"
                        class="movie-card">
                        <Link v-if="content.type == 'movies'"
                            :href="ShowMovieController({ movie: content.contentable.id })">
                            <img :src="content.contentable.vertical_image_url" :alt="content.contentable.title">
                        </Link>
                        <Link v-else :href="'#'">
                            <img :src="content.contentable.vertical_image_url" :alt="content.contentable.title">
                        </Link>
                    </div>
                </div>
                <button class="slider-arrow prev" aria-label="Previous"></button>
                <button class="slider-arrow next" aria-label="Next"></button>
            </div> -->
        </main>
        <br />
    </UserDashboardLayout>
</template>

<style scoped>
/* Search Bar */
.search-wrapper {
    padding: 1rem;
}

.search-container {
    position: relative;
    max-width: 800px;
    margin: 0 auto;
    width: 100%;
}

.search-bar {
    width: 100%;
    background: rgba(47, 47, 79, 0.7);
    border: none;
    border-radius: 12px;
    padding: 1rem 1.5rem;
    color: white;
    font-size: 1rem;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.search-bar:focus {
    background: rgba(47, 47, 79, 0.9);
    outline: none;
}

.search-bar::placeholder {
    color: rgba(255, 255, 255, 0.5);
}

/* Main Content */
.content {
    padding: 0 1rem;
}

.page-title {
    font-size: 2rem;
    font-weight: 600;
    margin: 1rem 0 2rem;
}

/* Categories Grid */
.categories-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
    margin-bottom: 2rem;
}

.category-card {
    position: relative;
    aspect-ratio: 1/1;
    border-radius: 12px;
    overflow: hidden;
    cursor: pointer;
    background: rgba(47, 47, 79, 0.3);
    transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.category-card:hover {
    transform: scale(1.02);
}

.category-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    opacity: 0.7;
    transition: opacity 0.3s ease;
}

.category-card:hover .category-image {
    opacity: 1;
}

.category-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(to top, rgba(0, 0, 0, 0.8), rgba(0, 0, 0, 0.1));
    display: flex;
    align-items: flex-end;
    padding: 1.5rem;
}

.category-title {
    font-size: 1.25rem;
    font-weight: 600;
    margin: 0;
}

/* Search Results Section */
.search-results {
    position: relative;
    margin-bottom: 2.5rem;
    padding: 0;
    overflow: visible;
}

.movies-row {
    position: relative;
    display: flex;
    gap: 0.75rem;
    overflow-x: auto;
    padding: 1rem;
    margin: 0;
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
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    scroll-snap-align: start;
    position: relative;
}

.movie-card a {
    display: block;
    width: 100%;
    height: 100%;
    text-decoration: none;
}

.movie-card img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

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
    z-index: 100;
    border: none;
    color: white;
    transition: all 0.2s ease;
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

.slider-arrow.prev {
    left: -30px;
}

.slider-arrow.next {
    right: -30px;
}

/* Desktop Styles */
@media (min-width: 1200px) {
    .search-wrapper {
        padding: 2rem 4rem 1rem;
    }

    .search-container {
        max-width: 100%;
        padding: 0 10%;
    }

    .content {
        padding: 0 4rem;
    }

    .page-title {
        font-size: 2.5rem;
        margin: 2rem 0;
    }

    .categories-grid {
        grid-template-columns: repeat(4, 1fr);
        gap: 2rem;
    }

    .category-title {
        font-size: 1.5rem;
    }

    .search-results {
        padding: 0 20px;
    }

    .movies-row {
        padding: 2rem 0;
    }

    .movie-card {
        width: 180px;
    }

    .movie-card:hover {
        transform: scale(1.2);
        z-index: 20;
    }

    .movie-card:hover~.movie-card {
        transform: translateX(30px);
    }

    .slider-arrow {
        display: flex;
    }
}
</style>