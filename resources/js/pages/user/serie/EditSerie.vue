<script setup lang="ts">
import { computed, ref, watch, onMounted } from 'vue';
import UserDashboardLayout from '@/layouts/UserDashboardLayout.vue';
import ErrorLabel from '@/components/form/ErrorLabel.vue';
import { Form, router } from '@inertiajs/vue3';
// import UpdateMovieController from '@/actions/App/Http/Controllers/Movie/UpdateMovieController';
import { LoaderCircle } from 'lucide-vue-next';
import StoreSeasonSerieController from '@/actions/App/Http/Controllers/Serie/StoreSeasonSerieController';
import GetSeasonSerieController from '@/actions/App/Http/Controllers/Serie/GetSeasonSerieController';

import { movie as movieRoute, short as shortRoute, serie as serieRoute } from '@/routes/user/upload'

import AddChapterModal from './partials/AddChapterModal.vue';

const props = defineProps<{
    series: any[];
}>();

const serieSelected = ref(0);
const seasonSelected = ref(0);
const seasons = ref<any[]>([]);

function goToShortForm() {
    router.visit(shortRoute.url());
}

function goToSerieForm() {
    router.visit(serieRoute.url());
}

function goToMovieForm() {
    router.visit(movieRoute.url());
}

watch(serieSelected, async () => {
    if (serieSelected.value != 0) {
        getSeasons();
    } else {
        seasons.value = [];
        seasonSelected.value = 0;
    }
});

async function getSeasons() {
    if (serieSelected.value != 0) {
        const response = await fetch(GetSeasonSerieController.url({ serie: serieSelected.value }), {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
            },
        })
            .then((response: any) => response.json());
        seasons.value = response;
        if (seasons.value.length > 0) {
            seasonSelected.value = seasons.value[0].id;
        }
    }
}

function createNewSeason() {
    if (serieSelected.value != 0) {
        router.post(StoreSeasonSerieController.url({ serie: serieSelected.value }), {

        }, {
            preserveScroll: true,
            onSuccess: async (response: any) => {
                await getSeasons();
            },
        });
    }
}

const episodesComputed = computed(() => {
    return seasons.value.find((season: any) => season.id == seasonSelected.value)?.chapters;
});

</script>

<template>

    <UserDashboardLayout 
        :title="`Edit Serie - ${$page.props.name || 'Impulsemedia'}`" 
        :headerTitle="`Edit Serie - ${$page.props.name || 'Impulsemedia'}`">
        <div class="main-content">
            <h1 class="page-title">Upload Content</h1>

            <div class="toggle-container">
                <button class="toggle-btn" data-form="movie" @click="goToMovieForm">Movie</button>
                <button class="toggle-btn active" data-form="serie" @click="goToSerieForm">Serie</button>
                <button class="toggle-btn" data-form="short" @click="goToShortForm">Short</button>
            </div>

            <div class="series-management-container" style="margin-bottom: 80px;">
                <div id="seriesInitialChoice" class="series-choice-buttons">
                    <!-- Back button -->
                    <button type="button" class="back-btn" @click="goToSerieForm">
                        <i class="fa-solid fa-arrow-left"></i>
                    </button>
                </div>
                <div id="manageSerieContent">
                    <div class="form-section">
                        <label for="serieSelect" class="form-label">Select Serie to
                            Manage</label>
                        <select id="serieSelect" v-model="serieSelected" class="form-control">
                            <option value="0">Select Serie</option>
                            <option v-for="serie in series" :value="serie.id" :key="serie.id">
                                {{ serie.title }}
                            </option>
                        </select>
                    </div>
                    <div class="season-management">
                        <div class="seasons-column">
                            <div class="column-header">
                                <span class="column-title">Seasons</span>
                                <button type="button" id="addSeasonBtn" class="add-btn" :disabled="serieSelected == 0"
                                    @click="createNewSeason">
                                    + Add
                                </button>
                            </div>
                            <div id="seasonsList" class="item-list">
                                <div v-for="(season, index) in seasons" :key="`season_${index}_${season.id}`"
                                    :class="{ 'selected': season.id == seasonSelected }"
                                    @click="seasonSelected = season.id" class="list-item">
                                    Season {{ index + 1 }}
                                </div>
                            </div>
                        </div>
                        <div class="episodes-column">
                            <div class="column-header">
                                <span class="column-title">Episodes</span>
                                <template v-if="serieSelected != 0 && seasonSelected != 0">
                                    <Suspense>
                                        <AddChapterModal :serie="serieSelected" :season="seasonSelected" @chapter-added="getSeasons" />
                                    </Suspense>
                                </template>
                            </div>
                            <div id="episodesList" class="item-list">
                                <div v-for="(episode, index) in episodesComputed"
                                    :key="`episode_${index}_${episode.id}`" class="list-item">
                                    {{ episode.chapter_number || index + 1 }}. {{ episode.title }}
                                </div>
                                <div v-if="serieSelected == 0">
                                    Select a series to begin.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <br />
        </div>
    </UserDashboardLayout>
</template>

<style scoped>
/* Unified Styles (Header, Menu, etc.) */
.header {
    padding: 0.75rem 1rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
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

/* Main Content */
.main-content {
    max-width: 800px;
    margin: 0 auto;
    padding: 1rem;
}

.page-title {
    font-size: 2.2rem;
    font-weight: 600;
    margin-bottom: 1.5rem;
}

.toggle-container {
    display: flex;
    gap: 0.5rem;
    margin-bottom: 2rem;
    background-color: var(--card-bg);
    border-radius: 50px;
    padding: 0.5rem;
}

.toggle-btn {
    background: transparent;
    border: none;
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 50px;
    font-size: 1rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
    flex-grow: 1;
}

.toggle-btn.active {
    background: var(--primary-color);
}

.upload-form {
    display: none;
}

.upload-form.active {
    display: block;
}

.form-section {
    margin-bottom: 1.5rem;
}

.form-label {
    font-size: 1.1rem;
    font-weight: 500;
    margin-bottom: 0.75rem;
    display: block;
}

.form-control {
    background: var(--input-bg);
    border: none;
    color: var(--text-dark);
    padding: 0.9rem;
    border-radius: 12px;
    font-size: 1rem;
    width: 100%;
    box-sizing: border-box;
}

.upload-box {
    border: 2px dashed #555;
    background-color: var(--card-bg);
    border-radius: 15px;
    padding: 1.5rem;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
}

.upload-box:hover {
    border-color: var(--primary-color);
}

.upload-box i {
    font-size: 2rem;
    color: #888;
    margin-bottom: 0.75rem;
}

.upload-box p {
    color: var(--text-light);
    font-size: 1rem;
    font-weight: 500;
    margin: 0;
}

.upload-box .file-name {
    color: var(--primary-color);
    font-weight: 600;
    margin-top: 0.5rem;
    font-size: 0.9rem;
}

input[type="file"] {
    display: none;
}

.series-management-container {
    background-color: var(--card-bg);
    border-radius: 15px;
    padding: 1.5rem;
}

.series-choice-buttons {
    display: flex;
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.choice-btn {
    flex: 1;
    padding: 1rem;
    border-radius: 12px;
    background-color: var(--input-bg);
    border: 2px solid transparent;
    cursor: pointer;
    text-align: left;
    transition: all 0.2s ease;
}

.choice-btn h3 {
    color: var(--text-dark);
    font-size: 1.2rem;
    font-weight: 600;
}

.choice-btn p {
    font-size: 0.9rem;
    color: #555;
}

.choice-btn:hover {
    border-color: #ccc;
}

.choice-btn.active {
    border-color: var(--primary-color);
}

.season-management {
    display: flex;
    gap: 1.5rem;
}

.seasons-column,
.episodes-column {
    flex: 1;
}

.column-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #444;
    padding-bottom: 0.75rem;
    margin-bottom: 1rem;
}

.column-title {
    font-size: 1.2rem;
    font-weight: 600;
}

.add-btn {
    background: var(--primary-color);
    color: white;
    border: none;
    padding: 0.4rem 0.8rem;
    border-radius: 8px;
    cursor: pointer;
    font-size: 0.8rem;
}

.item-list>.list-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: rgba(0, 0, 0, 0.2);
    padding: 0.75rem;
    border-radius: 8px;
    margin-bottom: 0.5rem;
    cursor: pointer;
}

.item-list>.list-item.selected {
    background: var(--primary-color);
}

.list-item-actions button {
    background: none;
    border: none;
    color: #ccc;
    cursor: pointer;
    padding: 0.25rem;
}

.save-btn {
    background: var(--primary-color);
    color: white;
    border: none;
    width: 100%;
    padding: 0.9rem;
    border-radius: 15px;
    font-size: 1.1rem;
    font-weight: 600;
    margin-top: 2rem;
    cursor: pointer;
}

.save-btn:disabled {
    background: #ccc;
    cursor: not-allowed;
}

.back-btn {
    background: none;
    border: none;
    color: var(--text-light);
    font-size: 1.5rem;
    cursor: pointer;
}

.back-btn:hover {
    color: var(--primary-color);
}

/* Modal for Episodes */
.modal-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.7);
    z-index: 2000;
    align-items: center;
    justify-content: center;
    overflow-y: auto;
    padding: 1rem;
}

.modal-overlay.active {
    display: flex;
}

.modal-container {
    background: var(--card-bg);
    padding: 2rem;
    border-radius: 15px;
    width: 100%;
    max-width: 500px;
    box-shadow: 0 0 20px rgba(0, 0, 0, 0.5);
    margin: auto;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
}

.modal-title {
    font-size: 1.5rem;
    margin: 0;
}

.modal-close-btn {
    background: none;
    border: none;
    color: white;
    font-size: 2rem;
    cursor: pointer;
}

.modal-container .form-section {
    margin-bottom: 1rem;
}

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

@media (max-width: 768px) {
    .season-management {
        flex-direction: column;
    }
}
</style>