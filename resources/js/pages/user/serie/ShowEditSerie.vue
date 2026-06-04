<script setup lang="ts">
import { computed, ref, watch, onMounted } from 'vue';
import UserDashboardLayout from '@/layouts/UserDashboardLayout.vue';
import ErrorLabel from '@/components/form/ErrorLabel.vue';
import { Form, router } from '@inertiajs/vue3';
import UploadTrailerSerieForm from './partials/UploadTrailerSerieForm.vue';
import UploadVerticalImageSerieForm from './partials/UploadVerticalImageSerieForm.vue';
import UploadHorizontalImageSerieForm from './partials/UploadHorizontalImageSerieForm.vue';
import UpdateSerieController from '@/actions/App/Http/Controllers/Serie/UpdateSerieController';
import DestroySerieController from '@/actions/App/Http/Controllers/Serie/DestroySerieController';
import StoreSeasonSerieController from '@/actions/App/Http/Controllers/Serie/StoreSeasonSerieController';
import GetSeasonSerieController from '@/actions/App/Http/Controllers/Serie/GetSeasonSerieController';
import DeleteSeasonItem from './partials/DeleteSeasonItem.vue';
import AddChapterModal from './partials/AddChapterModal.vue';
import EditChapterItemModal from './partials/EditChapterItemModal.vue';
import DeleteChapterItem from './partials/DeleteChapterItem.vue';
import DeleteSerieModal from './partials/DeleteSerieModal.vue';


const props = defineProps<{
    serie: any;
    categories: any[];
}>();

const myRefForm = ref({
    title: props.serie.title,
    description: props.serie.description,
    category_id: props.serie.category_id,
    subcategory_id: props.serie.subcategory_id,
});

const categorySelected = ref(0);
const subcategorySelected = ref(0);
const seasonSelected = ref(0);
const seasons = ref<any[]>(props.serie.seasons || []);
const isLoading = ref(false);
const publishForm = ref(null);

const subcategories = computed(() => {
    if (categorySelected.value) {
        return props.categories.find((category: any) => category.id === categorySelected.value)?.subcategories;
    }
    return [];
});

const episodesComputed = computed(() => {
    return seasons.value.find((season: any) => season.id == seasonSelected.value)?.chapters;
});

watch(categorySelected, (newVal) => {
    if (newVal === props.serie.category_id) {
        subcategorySelected.value = props.serie.subcategory_id || 0;
    } else {
        subcategorySelected.value = 0;
    }
});

watch(() => seasonSelected, async () => {
    if (seasonSelected.value != 0) {
        getSeasons();
    } else {
        seasons.value = props.serie.seasons || [];
        seasonSelected.value = 0;
    }
});

onMounted(() => {
    if (props.serie.category_id) {
        categorySelected.value = props.serie.category_id;
    }
    if (seasons.value.length > 0) {
        seasonSelected.value = seasons.value[0].id;
    }
});

async function getSeasons() {
    if (seasonSelected.value != 0) {
        const response = await fetch(GetSeasonSerieController.url({ serie: props.serie.id }), {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
            },
        })
            .then((response: any) => response.json());
        seasons.value = response;
        if (seasons.value.length > 0 && seasonSelected.value == 0) {
            seasonSelected.value = seasons.value[0].id;
        }
    }
}

function createNewSeason() {
    router.post(StoreSeasonSerieController.url({ serie: props.serie.id }), {

    }, {
        preserveScroll: true,
        onSuccess: async (response: any) => {
            await getSeasons();
        },
    });
}

function submitPublishForm() {
    if (!publishForm.value) {
        return;
    }
    // @ts-ignore
    publishForm.value.submit();
}

function deleteSerie() {
    if (confirm('Are you sure you want to delete this series? This action cannot be undone.')) {
        router.delete(DestroySerieController.url({ serie: props.serie.id }));
    }
}


function selectSeason(seasonId: number) {
    seasonSelected.value = seasonId;
}

const isUploadingSomething = ref(false);

function getSeasonsWithDelay(delay = 1000) {
    setTimeout(() => {
        getSeasons();
    }, delay);
}

</script>

<template>

    <UserDashboardLayout 
        :title="`Edit Serie - ${$page.props.name || 'Impulsemedia'}`" 
        :headerTitle="`Edit Serie - ${$page.props.name || 'Impulsemedia'}`">
        <div class="main-content">
            <h1 class="page-title">Edit Series</h1>
            <p class="page-subtitle">Editing: {{ serie.title }}</p>

            <Form ref="publishForm" v-bind="UpdateSerieController.form(serie)"
                v-on:start="isLoading = true" v-on:finish="isLoading = false"
                v-slot="{ errors, processing, form }" class="upload-form active">
                <div class="form-section">
                    <label for="serieTitle" class="form-label">Title</label>
                    <input name="title" v-model="myRefForm.title" type="text" id="serieTitle"
                        class="form-control" placeholder="e.g., The Great Adventure">
                    <ErrorLabel :message="errors.title" />
                </div>
                <div class="form-section">
                    <label for="serieDescription" class="form-label">Description</label>
                    <textarea name="description" id="serieDescription" class="form-control" rows="4"
                        v-model="myRefForm.description"
                        placeholder="Tell something about your series..."></textarea>
                    <ErrorLabel :message="errors.description" />
                </div>
                <div class="form-section">
                    <label for="serieCategory" class="form-label">Category</label>
                    <select v-model="categorySelected" id="serieCategory" name="category_id"
                        class="form-control">
                        <option value="0">Select Category</option>
                        <option v-for="category in categories" :key="`category_${category.id}`"
                            :value="category.id">{{
                                category.name }}</option>
                    </select>
                    <ErrorLabel :message="errors.category_id" />
                </div>
                <div class="form-section">
                    <label for="serieSubcategory" class="form-label">Subcategory</label>
                    <select name="subcategory_id" id="serieSubcategory" class="form-control"
                        v-model="subcategorySelected">
                        <option :value="0">Select Subcategory</option>
                        <option v-for="subcategory in subcategories" :key="`subcategory_${subcategory.id}`"
                            :value="subcategory.id">{{ subcategory.name }}</option>
                    </select>
                    <ErrorLabel :message="errors.subcategory_id" />
                </div>
            </Form>
            <div>
                <UploadTrailerSerieForm :serie="serie" v-model:disable="isUploadingSomething" />
                <UploadVerticalImageSerieForm :serie="serie" v-model:disable="isUploadingSomething" />
                <UploadHorizontalImageSerieForm :serie="serie" v-model:disable="isUploadingSomething" />
            </div>

            <div class="series-management-container">
                <h3 style="font-size: 1.4rem; font-weight: 600; margin-bottom: 1.5rem;">Manage Seasons & Episodes</h3>
                <div class="season-management">
                    <div class="seasons-column">
                        <div class="column-header">
                            <span class="column-title">Seasons</span>
                            <button type="button" @click="createNewSeason" class="add-btn">+ Add</button>
                        </div>
                        <div id="seasonsList" class="item-list">
                            <template v-for="(season, index) in seasons" :key="`seasons_${season.id}`">
                                <div class="list-item" 
                                     :class="{ selected: seasonSelected === season.id }"
                                     @click="selectSeason(season.id)">
                                    <span>Season {{ index + 1 }}</span>
                                    <div class="list-item-actions">
                                        <DeleteSeasonItem :season="season" :serie="serie.id" @season-deleted="getSeasonsWithDelay" />
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                    <div class="episodes-column">
                        <div class="column-header">
                            <span class="column-title">Episodes</span>
                            <template v-if="seasonSelected != 0">
                                <AddChapterModal 
                                    :serie="serie.id" 
                                    :season="seasonSelected" 
                                    @chapter-added="getSeasons" />
                            </template>
                        </div>
                        <div id="episodesList" class="item-list">
                            <template v-for="(episode, index) in episodesComputed" :key="`episode_${index}_${episode.id}_${episode.chapter_number}_${episode.season_id}`">
                                <div class="list-item">
                                    <span>Episode {{ episode.chapter_number }}: {{ episode.title }}</span>
                                    <div class="list-item-actions">
                                        <EditChapterItemModal 
                                            :chapter="episode" 
                                            :serie="serie.id" 
                                            :season="episode.season_id" 
                                            @chapter-updated="getSeasons"
                                        />
                                        <DeleteChapterItem 
                                            :chapter="episode" 
                                            :serie="serie.id" 
                                            :season="episode.season_id" 
                                            @chapter-deleted="getSeasons"
                                        />
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
            <div class="button-group">
                <button type="submit" class="action-btn save-btn" @click="submitPublishForm" :disabled="isLoading">
                    <i class="fa-solid fa-circle-notch fa-spin" v-if="isLoading"></i>
                    Save Changes
                </button>
                <DeleteSerieModal :serie="serie" />
            </div>
            
        </div>

        <br />
        <br />
        <br />
        <br />
    </UserDashboardLayout>
</template>

<style scoped>
 /* Main Content */
 .main-content { max-width: 800px; margin: 0 auto; padding: 1rem; }
.page-title { font-size: 2.2rem; font-weight: 600; margin-bottom: 1.5rem; }
.page-subtitle { font-size: 1.1rem; color: #ccc; margin-top: -1rem; margin-bottom: 2rem; }
.edit-form { display: none; }
.edit-form.active { display: block; }
.form-section { margin-bottom: 1.5rem; }
.form-label { font-size: 1.1rem; font-weight: 500; margin-bottom: 0.75rem; display: block;}
.form-control { background: var(--input-bg); border: none; color: var(--text-dark); padding: 0.9rem; border-radius: 12px; font-size: 1rem; width: 100%; box-sizing: border-box; }
.upload-box { border: 2px dashed #555; background-color: var(--card-bg); border-radius: 15px; padding: 1.5rem; text-align: center; cursor: pointer; transition: all 0.3s ease; }
.upload-box:hover { border-color: var(--primary-color); }
.upload-box i { font-size: 2rem; color: #888; margin-bottom: 0.75rem; }
.upload-box p { color: var(--text-light); font-size: 1rem; font-weight: 500; margin: 0; }
.upload-box .file-name { color: #aaa; font-style: italic; margin-top: 0.5rem; font-size: 0.9rem; }
.upload-box .file-name strong { color: var(--primary-color); font-style: normal; }
input[type="file"] { display: none; }
.series-management-container { background-color: var(--card-bg); border-radius: 15px; padding: 1.5rem; margin-top: 2.5rem; }
.season-management { display: flex; gap: 1.5rem; }
.seasons-column, .episodes-column { flex: 1; }
.column-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #444; padding-bottom: 0.75rem; margin-bottom: 1rem; }
.column-title { font-size: 1.2rem; font-weight: 600; }
.add-btn { background: var(--primary-color); color: white; border: none; padding: 0.4rem 0.8rem; border-radius: 8px; cursor: pointer; font-size: 0.8rem; }
.item-list > .list-item { display: flex; justify-content: space-between; align-items: center; background: rgba(0,0,0,0.2); padding: 0.75rem; border-radius: 8px; margin-bottom: 0.5rem; }
.list-item > span { flex-grow: 1; cursor: pointer; }
.item-list > .list-item:hover { background: rgba(255,255,255,0.1); }
.item-list > .list-item.selected { background: var(--primary-color); }
.list-item-actions button { background: none; border: none; color: #ccc; cursor: pointer; padding: 0.25rem; font-size: 1rem; transition: color 0.2s; }
.list-item-actions button.delete-btn:hover { color: var(--danger-color); }
.list-item-actions button.edit-btn:hover { color: white; }
.button-group { display: flex; gap: 1rem; margin-top: 2rem; }
.action-btn { border: none; width: 100%; padding: 0.9rem; border-radius: 15px; font-size: 1.1rem; font-weight: 600; cursor: pointer; }
.save-btn { background: var(--primary-color); color: white; }
.delete-btn { background: transparent; border: 2px solid var(--danger-color); color: var(--danger-color); }

/* Modal for Episodes */
.modal-overlay { 
    display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; 
    background: rgba(0,0,0,0.7); z-index: 2000; align-items: center; justify-content: center; 
    overflow-y: auto; padding: 1rem;
}
.modal-overlay.active { display: flex; }
.modal-container { 
    background: var(--card-bg); padding: 2rem; border-radius: 15px; 
    width: 100%; max-width: 500px; box-shadow: 0 0 20px rgba(0,0,0,0.5); margin: auto;
}
.modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
.modal-title { font-size: 1.5rem; margin: 0; }
.modal-close-btn { background: none; border: none; color: white; font-size: 2rem; cursor: pointer; }
.modal-container .form-section { margin-bottom: 1rem; }

.bottom-nav { position: fixed; bottom: 0; left: 0; right: 0; background: var(--main-bg); padding: 1rem; display: flex; justify-content: space-around; border-top: 1px solid rgba(255,255,255,0.1); z-index: 1000; }
.nav-item { display: flex; flex-direction: column; align-items: center; color: white; text-decoration: none; font-size: 0.8rem; gap: 4px; }
.nav-item.active { color: var(--primary-color); }
.nav-icon { width: 24px; height: 24px; }

@media (max-width: 768px) {
    .season-management { flex-direction: column; }
}
</style>