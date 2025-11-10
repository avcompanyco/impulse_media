<script setup lang="ts">
import { computed, ref, watch, onMounted } from 'vue';
import UserDashboardLayout from '@/layouts/UserDashboardLayout.vue';
import ErrorLabel from '@/components/form/ErrorLabel.vue';
import { Link, router } from '@inertiajs/vue3';
import type { User, Plan } from '@/types';

import { destroy as LogoutRoute } from '@/actions/App/Http/Controllers/Auth/AuthenticatedSessionController'
import ManageProfileController from '@/actions/App/Http/Controllers/UserProfile/ManageProfileController';
import ShowManageSubscriptionProfileController from '@/actions/App/Http/Controllers/UserProfile/ShowManageSubscriptionProfileController';

const props = defineProps<{
    user: User;
    plan: Plan;
    next_payment_date: string | null;
}>();

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
</script>

<template>
    <UserDashboardLayout 
        :title="`My Account - ${$page.props.name || 'Impulsemedia'}`" 
        :headerTitle="`My Account - ${$page.props.name || 'Impulsemedia'}`">
        <h1 class="page-title">My Account</h1>
        <div class="app-subcontainer" style="margin-bottom: 80px;">
            <section>
                <h2 class="section-title">User</h2>
                <div class="info-card">
                    <img :src="user.image_url" alt="User Avatar" class="user-avatar">
                    <div class="user-details">
                        <p class="info-text">Username: {{ user.username }}</p>
                        <p class="info-text">Email: {{ user.email }}</p>
                        <p class="info-text">Password: *****</p>
                    </div>
                    <Link :href="ManageProfileController.url()" class="manage-button ">Manage</Link>
                </div>
            </section>

            <section>
                <h2 class="section-title">Subcription</h2>
                <div class="info-card">
                    <div>
                        <p v-if="plan" class="info-text">Plan: {{ plan.name }}</p>
                        <p v-if="next_payment_date" class="info-text">Next payment: {{ next_payment_date }}</p>
                        <p v-else class="info-text">No active subscription</p>
                    </div>
                    <Link :href="ShowManageSubscriptionProfileController.url()" class="manage-button">Manage</Link>
                </div>
            </section>

            <section>
                <button class="logout-button" @click="logout">
                    <i class="fa-solid fa-circle-notch fa-spin" v-if="isLoggingOut"></i>
                    Log Out
                </button>
            </section>
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
    cursor: pointer;
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