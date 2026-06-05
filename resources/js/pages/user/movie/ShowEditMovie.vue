<script setup lang="ts">
import { computed, ref, watch, onMounted } from 'vue';
import UserDashboardLayout from '@/layouts/UserDashboardLayout.vue';
import ErrorLabel from '@/components/form/ErrorLabel.vue';
import { Form, router } from '@inertiajs/vue3';
// import UpdateMovieController from '@/actions/App/Http/Controllers/Movie/UpdateMovieController';
import UpdateMovieController from '@/actions/App/Http/Controllers/Movie/UpdateMovieController';

import UploadVerticalImageForm from './partials/UploadVerticalImageForm.vue';
import UploadHorizontalImageForm from './partials/UploadHorizontalImageForm.vue';
import UploadTrailerForm from './partials/UploadTrailerForm.vue';
import UploadMovieForm from './partials/UploadMovieForm.vue';

import { movie as movieRoute, short as shortRoute, serie as serieRoute } from '@/routes/user/upload'

const props = defineProps<{
    movie: any;
    categories: any;
}>();

const categorySelected = ref(0);
const subcategorySelected = ref(0);
const movieRef = ref(props.movie);

const movieTitle = ref(props.movie.title);
const movieDescription = ref(props.movie.description);
const ppvPrice = ref(props.movie.content?.ppv_price || 0.00);
const allowMembership = ref(props.movie.content?.allow_membership !== undefined ? !!props.movie.content.allow_membership : true);

const subcategories = computed(() => {
    if (categorySelected.value) {
        return props.categories.find((category: any) => category.id === categorySelected.value)?.subcategories;
    }
    return [];
});

watch(categorySelected, (newVal) => {
    if (newVal === props.movie.category_id) {
        subcategorySelected.value = props.movie.subcategory_id || 0;
    } else {
        subcategorySelected.value = 0;
    }
});

watch(() => props.movie, (newVal) => {
    movieRef.value = newVal;
});

const canPublish = computed(() => {
    if (!props.movie.movie_video || !props.movie.trailer_video || !props.movie.horizontal_image || !props.movie.vertical_image) {
        return false;
    }
    return true;
});

onMounted(() => {
    if (props.movie.category_id) {
        categorySelected.value = props.movie.category_id;
    }
});


function goToShortForm() {
    router.visit(shortRoute.url());
}

function goToSerieForm() {
    router.visit(serieRoute.url());
}

function goToMovieForm() {
    router.visit(movieRoute.url());
}


</script>

<template>

    <UserDashboardLayout 
        :title="`Edit Movie - ${$page.props.name || 'Impulsemedia'}`" 
        :headerTitle="`Edit Movie - ${$page.props.name || 'Impulsemedia'}`">
        <div class="main-content" style="margin-bottom: 80px;">
            <h1 class="page-title">Edit Content</h1>
            <p class="page-subtitle" id="editingSubtitle">
                Editing movie: {{ movie.title }}
            </p>

            <div>
                <UploadMovieForm v-model="movieRef" />
                <UploadTrailerForm v-model="movieRef" />
                <UploadVerticalImageForm v-model="movieRef" />
                <UploadHorizontalImageForm v-model="movieRef" />
            </div>
            <Form v-bind="UpdateMovieController.form(movie)"
                v-slot="{ errors, processing }" :options="{ preserveScroll: true }"
                class="upload-form active">
                <div class="form-section">
                    <label for="movieTitle" class="form-label">Title</label>
                    <input name="title" type="text" id="movieTitle" class="form-control" v-model="movieTitle"
                        placeholder="e.g., The Great Adventure">
                    <ErrorLabel :message="errors.title" />
                </div>
                <div class="form-section">
                    <label for="movieDescription" class="form-label">Description</label>
                    <textarea name="description" id="movieDescription" class="form-control" rows="4"
                        v-model="movieDescription" placeholder="Tell something about your movie..."></textarea>
                    <ErrorLabel :message="errors.description" />
                </div>
                <div class="form-section">
                    <label for="movieCategory" class="form-label">Category</label>
                    <select v-model="categorySelected" id="movieCategory" name="category_id" class="form-control">
                        <option value="0">Select Category</option>
                        <option v-for="category in categories" :key="`category_${category.id}`" :value="category.id">{{
                            category.name }}</option>
                    </select>
                    <ErrorLabel :message="errors.category_id" />
                </div>
                <div class="form-section">
                    <label for="movieSubcategory" class="form-label">Subcategory</label>
                    <select name="subcategory_id" id="movieSubcategory" class="form-control"
                        v-model="subcategorySelected">
                        <option :value="0">Select Subcategory</option>
                        <option v-for="subcategory in subcategories" :key="`subcategory_${subcategory.id}`"
                            :value="subcategory.id">{{ subcategory.name }}</option>
                    </select>
                    <ErrorLabel :message="errors.subcategory_id" />
                </div>

                <!-- Monetization Options -->
                <div class="form-section">
                    <label class="form-label">Monetization Option / Opción de Monetización</label>
                    <div class="monetization-options-group">
                        <label class="monetization-option-card" :class="{ active: allowMembership }">
                            <input type="radio" :value="true" v-model="allowMembership" class="sr-only">
                            <div class="option-card-content">
                                <div class="option-icon"><i class="fa-solid fa-crown text-accent"></i></div>
                                <div class="option-details">
                                    <span class="option-title">Subscription / Suscripción (Option 1)</span>
                                    <p class="option-desc">Available to members. Non-members can purchase via PPV. / Disponible para miembros. No miembros pueden comprarlo via PPV.</p>
                                </div>
                            </div>
                        </label>
                        <label class="monetization-option-card" :class="{ active: !allowMembership }">
                            <input type="radio" :value="false" v-model="allowMembership" class="sr-only">
                            <div class="option-card-content">
                                <div class="option-icon"><i class="fa-solid fa-ticket text-accent"></i></div>
                                <div class="option-details">
                                    <span class="option-title">PPV Only / Solo PPV (Option 2)</span>
                                    <p class="option-desc">All users must purchase this video individually to watch. / Todos los usuarios deben comprar este video individualmente para verlo.</p>
                                </div>
                            </div>
                        </label>
                    </div>
                    <input type="hidden" name="allow_membership" :value="allowMembership ? '1' : '0'">
                </div>

                <div class="form-section">
                    <label for="ppvPriceInput" class="form-label">PPV Price ($) / Precio PPV ($)</label>
                    <input name="ppv_price" type="number" step="0.01" min="0" id="ppvPriceInput" class="form-control" v-model="ppvPrice" placeholder="0.00 (Free / Gratis)">
                    <span class="form-help">Enter 0 to make it free, or set a price (Minimum is ${{ Number($page.props.min_ppv_price || 0.99).toFixed(2) }}). / Ingrese 0 para hacerlo gratis, o fije un precio (Mínimo es ${{ Number($page.props.min_ppv_price || 0.99).toFixed(2) }}).</span>
                    <ErrorLabel :message="errors.ppv_price" />
                </div>

                <button type="submit" class="save-btn" :disabled="!canPublish">
                    <i class="fa-solid fa-circle-notch fa-spin" v-if="processing"></i>
                    Upload Movie
                </button>
            </Form>
        </div>
        <br />
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

.page-subtitle { font-size: 1.1rem; color: #ccc; margin-top: -1rem; margin-bottom: 2rem; }

.monetization-options-group {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    margin-top: 0.5rem;
}

.monetization-option-card {
    background: var(--input-bg);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 12px;
    padding: 1rem;
    cursor: pointer;
    transition: all 0.25s ease;
    display: block;
}

.monetization-option-card:hover {
    background: rgba(255, 255, 255, 0.08);
    border-color: var(--primary-color);
}

.monetization-option-card.active {
    background: rgba(232, 68, 90, 0.08);
    border-color: var(--primary-color);
}

.option-card-content {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
}

.option-icon {
    font-size: 1.5rem;
    margin-top: 0.2rem;
}

.option-details {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.option-title {
    font-weight: 700;
    color: #fff;
    font-size: 0.95rem;
}

.option-desc {
    font-size: 0.8rem;
    color: #aaa;
    margin: 0;
    line-height: 1.4;
}

.sr-only {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    border: 0;
}
</style>