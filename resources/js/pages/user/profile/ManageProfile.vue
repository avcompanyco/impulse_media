<script setup lang="ts">
import { computed, ref, watch, onMounted } from 'vue';
import UserDashboardLayout from '@/layouts/UserDashboardLayout.vue';
import ErrorLabel from '@/components/form/ErrorLabel.vue';
import { Form, router, Link } from '@inertiajs/vue3';
import type { User } from '@/types';


import { destroy as LogoutRoute } from '@/actions/App/Http/Controllers/Auth/AuthenticatedSessionController'
import UpdateImageProfileController from '@/actions/App/Http/Controllers/UserProfile/UpdateImageProfileController';
import ShowProfileController from '@/actions/App/Http/Controllers/UserProfile/ShowProfileController';
import UpdateProfileController from '@/actions/App/Http/Controllers/UserProfile/UpdateProfileController';

const props = defineProps<{
    user: User;
}>();

const name = ref(props.user.name);
const username = ref(props.user.username);
const email = ref(props.user.email);
const bio = ref(props.user.bio || '');
const external_link = ref(props.user.external_link || '');
const password = ref('');
const password_confirmation = ref('');

const isLoggingOut = ref(false);

function logout() {
    isLoggingOut.value = true;
    router.post(LogoutRoute(), {}, {
        onSuccess: () => {
            isLoggingOut.value = false;
        },
        onError: () => {
            isLoggingOut.value = false;
        },
    });
}

const chagenPictureRef = ref<HTMLInputElement | null>(null);
const changePictureBtnRef = ref<HTMLButtonElement | null>(null);
const isChangingPicture = ref(false);

function changePicture() {
    chagenPictureRef.value?.click();
}

function handleChangePicture(event: Event) {
    const file = (event.target as HTMLInputElement).files?.[0];
    if (file) {
        isChangingPicture.value = true;
        router.post(UpdateImageProfileController(), { image: file }, {
            onSuccess: () => {
                isChangingPicture.value = false;
            },
            onError: () => {
                isChangingPicture.value = false;
            },
        });
    }
}
</script>

<template>
    <UserDashboardLayout 
        :title="`Manage User - ${$page.props.name || 'Impulsemedia'}`" 
        :headerTitle="`Manage User - ${$page.props.name || 'Impulsemedia'}`">
        <h1 class="page-title">Manage User</h1>
        <div class="app-subcontainer" style="margin-bottom: 80px;">

            <Form v-bind="UpdateProfileController.form()" :reset-on-success="['password', 'password_confirmation']"
                v-slot="{ errors, processing }" :options="{ preserveScroll: true, preserveState: true }" class="upload-form active">
                <div class="profile-picture-container">
                    <img :src="user.image_url" alt="User Avatar" id="avatarPreview" class="profile-avatar">
                    <input type="file" id="avatarUpload" accept="image/*" style="display: none;" ref="chagenPictureRef"
                        @change="handleChangePicture">
                    <button type="button" id="changePictureBtn" class="change-picture-btn" ref="changePictureBtnRef"
                        @click="changePicture">
                        <i class="fa-solid fa-circle-notch fa-spin" v-if="isChangingPicture"></i>
                        Change Picture
                    </button>
                </div>

                <div class="form-group-custom">
                    <label for="userFullName" class="form-label-custom">Full Name</label>
                    <input type="text" class="form-control-custom" id="userFullName" name="name"
                        placeholder="Enter your full name" v-model="name" required>
                    <ErrorLabel :message="errors.name" />
                </div>

                <div class="form-group-custom">
                    <label for="userName" class="form-label-custom">Username</label>
                    <input type="text" class="form-control-custom" id="userName" name="username"
                        placeholder="Enter your username" v-model="username" required>
                    <ErrorLabel :message="errors.username" />
                </div>

                <div class="form-group-custom">
                    <label for="userEmail" class="form-label-custom">Email</label>
                    <input type="email" class="form-control-custom" id="userEmail" name="email"
                        placeholder="Enter your email" v-model="email" required>
                    <ErrorLabel :message="errors.email" />
                </div>

                <div class="form-group-custom">
                    <label for="userBio" class="form-label-custom">Bio</label>
                    <textarea class="form-control-custom" id="userBio" name="bio"
                        placeholder="Tell us about yourself..." v-model="bio" rows="4" maxlength="500"></textarea>
                    <small class="form-text-sm">{{ bio.length }}/500 characters</small>
                    <ErrorLabel :message="errors.bio" />
                </div>

                <div class="form-group-custom">
                    <label for="userExternalLink" class="form-label-custom">External Link</label>
                    <input type="url" class="form-control-custom" id="userExternalLink" name="external_link"
                        placeholder="https://yourwebsite.com" v-model="external_link">
                    <small class="form-text-sm">Link to your website or social media profile.</small>
                    <ErrorLabel :message="errors.external_link" />
                </div>

                <div class="form-group-custom">
                    <label for="userPassword" class="form-label-custom">New Password</label>
                    <input type="password" class="form-control-custom" id="userPassword" name="password" v-model="password"
                        placeholder="Enter new password (optional)">
                    <small class="form-text-sm">Leave blank to keep your current password.</small>
                    <ErrorLabel :message="errors.password" />
                </div>

                <div class="form-group-custom">
                    <label for="confirmPassword" class="form-label-custom">Confirm New Password</label>
                    <input type="password" class="form-control-custom" id="confirmPassword" name="password_confirmation" v-model="password_confirmation"
                        placeholder="Confirm new password">
                    <ErrorLabel :message="errors.password_confirmation" />
                </div>

                <button type="submit" class="action-btn save-btn-custom">
                    <i class="fa-solid fa-circle-notch fa-spin" v-if="processing"></i>
                    Save Changes
                </button>
                <Link :href="ShowProfileController()" class="action-btn cancel-btn-custom">Cancel</Link>
            </Form>

        </div>
        <br />
    </UserDashboardLayout>
</template>

<style scoped>
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

.app-subcontainer {
    padding-bottom: 80px;
    max-width: 480px;
    margin: 0 auto;
    padding-left: 1.5rem;
    padding-right: 1.5rem;
}

.page-title {
    font-size: 2rem;
    font-weight: 700;
    margin-top: 1.5rem;
    margin-bottom: 2.5rem;
}

.section-title {
    font-size: 1.75rem;
    font-weight: 600;
    margin-bottom: 1rem;
}

.info-card {
    background-color: #E6E6E6;
    border-radius: 1.25rem;
    padding: 1.5rem;
    color: black;
    position: relative;
    margin-bottom: 3rem;
    display: flex;
    align-items: center;
    gap: 1.5rem;
}

.user-avatar {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    object-fit: cover;
    flex-shrink: 0;
}

.info-text {
    font-size: 1.1rem;
    /* Ajustado para mejor fit */
    font-weight: 500;
    margin-bottom: 0.5rem;
    line-height: 1.4;
}

.info-text:last-child {
    margin-bottom: 0;
}

.manage-button {
    position: absolute;
    bottom: -1.25rem;
    right: 1.25rem;
    background-color: white;
    border: none;
    border-radius: 1.5rem;
    padding: 0.625rem 1.5rem;
    font-size: 1rem;
    font-weight: 600;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    transition: all 0.3s var(--transition-bezier);
    text-decoration: none;
    color: inherit;
    display: inline-block;
    text-align: center;
}

.manage-button:hover {
    transform: scale(1.05);
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
}

.logout-button {
    display: block;
    width: 100%;
    padding: 1rem;
    background-color: var(--primary-color);
    color: var(--text-light);
    border: none;
    border-radius: 1rem;
    font-size: 1.1rem;
    font-weight: 600;
    text-align: center;
    cursor: pointer;
    transition: background-color 0.3s;
    text-decoration: none;
}

.logout-button:hover {
    background-color: #d81b60;
    /* Un tono más oscuro de rosado */
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

.page-title {
    font-size: 2.5rem;
    font-weight: 700;
    margin-top: 1rem;
    margin-bottom: 2.5rem;
    color: var(--text-light);
    text-align: center;
}

/* Profile Picture Section */
.profile-picture-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    margin-bottom: 2.5rem;
}

.profile-avatar {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid var(--primary-color);
    margin-bottom: 1rem;
}

.change-picture-btn {
    background: none;
    border: 1px solid var(--text-light);
    color: var(--text-light);
    padding: 0.5rem 1rem;
    border-radius: 10px;
    cursor: pointer;
    font-weight: 500;
    transition: background-color 0.2s, color 0.2s;
}

.change-picture-btn:hover {
    background-color: var(--text-light);
    color: var(--main-bg);
}

.form-label-custom {
    font-size: 1.5rem;
    font-weight: 600;
    margin-bottom: 0.75rem;
    color: var(--text-light);
    display: block;
}

.form-control-custom {
    background: var(--input-bg);
    border: none;
    color: var(--text-dark);
    padding: 1rem;
    border-radius: 15px;
    font-size: 1.1rem;
    width: 100%;
    box-sizing: border-box;
}

.form-control-custom::placeholder {
    color: rgba(0, 0, 0, 0.4);
    font-weight: 400;
}

.form-control-custom:focus {
    outline: 2px solid var(--primary-color);
    outline-offset: 2px;
}

.form-group-custom {
    margin-bottom: 1.75rem;
}

.form-text-sm {
    font-size: 0.85em;
    color: #ccc;
    display: block;
    margin-top: 0.3rem;
    font-weight: 400;
}

/* Buttons */
.action-btn {
    color: white;
    border: none;
    width: 100%;
    padding: 0.85rem;
    border-radius: 15px;
    font-size: 1.1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
    display: block;
    text-align: center;
}

.action-btn:hover {
    opacity: 0.9;
    transform: scale(1.01);
}

.save-btn-custom {
    background: var(--primary-color);
    margin-top: 2.5rem;
    margin-bottom: 1rem;
}

.cancel-btn-custom {
    background: var(--secondary-action-bg);
    margin-bottom: 7rem;
}


@media (min-width: 768px) {
    .app-subcontainer {
        max-width: 600px;
        padding-left: 0;
        padding-right: 0;
    }

    .page-title {
        text-align: center;
        margin-bottom: 3rem;
    }

    .section-title {
        text-align: center;
        margin-bottom: 1.5rem;
    }
}
</style>