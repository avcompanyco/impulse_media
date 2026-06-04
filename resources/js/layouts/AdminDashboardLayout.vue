<script setup lang="ts">
import { onMounted, ref, watch, nextTick } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { dashboard } from '@/routes/admin/index'
import users from '@/routes/admin/users'
import plans from '@/routes/admin/plans'
import content from '@/routes/admin/content'
import { destroy as logoutSession } from '@/actions/App/Http/Controllers/Auth/AuthenticatedSessionController'
import Toast from '@/components/Toast.vue';

import ShowAdminProfileController from '@/actions/App/Http/Controllers/AdminProfile/ShowAdminProfileController';

const dropdownOpen = ref(false);
const sidebarOpen = ref(false);

router.on('navigate', () => {
    sidebarOpen.value = false;
});

onMounted(() => {
    // Event listener para cerrar el dropdown cuando se hace click fuera de él
    document.addEventListener('click', (event) => {
        const dropdown = document.getElementById('userProfileDropdown');
        if (dropdown && !dropdown.contains(event.target as Node)) {
            dropdownOpen.value = false;
        }
    });
});

const props = defineProps<{
    title: string;
    headerTitle: string;
}>()

function logout() {
    router.post(logoutSession());
}

function toggleDropdown(event: Event) {
    event.stopPropagation();
    dropdownOpen.value = !dropdownOpen.value;
}

const myToast = ref<any>(null);

watch(() => usePage().props, (newVal: any) => {
    if (newVal.errors.type && newVal.errors.title && newVal.errors.message) {
        myToast.value.addToast(newVal.errors);
    } else if (newVal.flash.type && newVal.flash.title && newVal.flash.message) {
        myToast.value.addToast(newVal.flash);
    }
})

</script>

<template>

    <Head :title="title" />
    <div class="html">
        <div class="body">
            <div class="admin-page-container">
                <aside class="admin-sidebar" :class="{ 'active': sidebarOpen }">
                    <div class="sidebar-header">
                        <img src="/images/logo.png" alt="Platform Logo" class="logo-icon">
                        <h2>Admin Panel</h2>
                    </div>
                    <nav class="admin-nav">
                        <ul>
                            <li>
                                <Link :href="dashboard()" class="nav-link"
                                    :class="{ 'active': $page.url === dashboard.url() }">
                                <svg class="nav-icon-svg" viewBox="0 0 24 24">
                                    <path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z" />
                                </svg>
                                Dashboard
                                </Link>
                            </li>
                            <li>
                                <Link :href="users.index()" class="nav-link"
                                    :class="{ 'active': $page.url === users.index.url() }">
                                <svg class="nav-icon-svg" viewBox="0 0 24 24">
                                    <path
                                        d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z" />
                                </svg>
                                Users
                                </Link>
                            </li>
                            <li>
                                <Link :href="content.index()" class="nav-link"
                                    :class="{ 'active': $page.url === content.index.url() }">
                                <svg class="nav-icon-svg" viewBox="0 0 24 24">
                                    <path
                                        d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z" />
                                </svg>
                                Content
                                </Link>
                            </li>
                            <li>
                                <Link :href="plans.index()" class="nav-link"
                                    :class="{ 'active': $page.url === plans.index.url() }">
                                <svg class="nav-icon-svg" viewBox="0 0 24 24">
                                    <path
                                        d="M20 4H4c-1.11 0-1.99.89-1.99 2L2 18c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V6c0-1.11-.89-2-2-2zm-1 14H5c-.55 0-1-.45-1-1V9h16v8c0 .55-.45 1-1 1zm1-10H4V6h16v2z" />
                                </svg>
                                Plans
                                </Link>
                            </li>
                            <li>
                                <a href="/admin/terms" class="nav-link" :class="{ 'active': $page.url.includes('/terms') }">
                                <svg class="nav-icon-svg" viewBox="0 0 24 24">
                                    <path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zM6 20V4h7v5h5v11H6zm2-6h8v2H8v-2zm0-4h8v2H8v-2z" />
                                </svg>
                                Terms & Conditions
                                </a>
                            </li>
                            <li>
                                <a href="/admin/ads" class="nav-link" :class="{ 'active': $page.url.includes('/ads') }">
                                <svg class="nav-icon-svg" viewBox="0 0 24 24">
                                    <path d="M18 11v2h4v-2h-4zm-2 6.61c.96.71 2.21 1.65 3.2 2.39.4-.53.8-1.07 1.2-1.6-.99-.74-2.24-1.68-3.2-2.4-.4.54-.8 1.08-1.2 1.61zM20.4 5.6c-.4-.53-.8-1.07-1.2-1.6-.99.74-2.24 1.68-3.2 2.4.4.53.8 1.07 1.2 1.6.96-.72 2.21-1.65 3.2-2.4zM4 9c-1.1 0-2 .9-2 2v2c0 1.1.9 2 2 2h1l5 3V6L5 9H4zm5.03 1.71L11 9.53v4.94l-1.97-1.18-.48-.29H4v-2h3.55l.48-.29zM14.5 12c0-1.77-1.02-3.29-2.5-4.03v8.05c1.48-.73 2.5-2.25 2.5-4.02z" />
                                </svg>
                                Ads
                                </a>
                            </li>
                        </ul>
                    </nav>
                </aside>
                <div v-if="sidebarOpen" class="sidebar-backdrop" @click="sidebarOpen = false"></div>

                <div class="admin-main-content-wrapper">
                    <header class="admin-top-bar">
                        <button class="mobile-nav-toggle" @click="sidebarOpen = !sidebarOpen" type="button">
                            <svg viewBox="0 0 24 24" class="hamburger-icon">
                                <path d="M3 18h18v-2H3v2zm0-5h18v-2H3v2zm0-7v2h18V6H3z" />
                            </svg>
                        </button>
                        <div class="user-profile-dropdown" id="userProfileDropdown">
                            <button class="profile-trigger" @click="toggleDropdown">
                                <span class="user-avatar">
                                    <img class="user-avatar-img" :src="$page.props.auth.user.image_url" alt="User Avatar" />
                                </span>
                                <span>Admin</span>
                                <svg style="width:16px; height:16px; margin-left:8px; fill:currentColor;"
                                    viewBox="0 0 16 16">
                                    <path fill-rule="evenodd"
                                        d="M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z" />
                                </svg>
                            </button>
                            <ul class="dropdown-menu" :class="{ 'active': dropdownOpen }">
                                <li>
                                    <Link :href="ShowAdminProfileController()">
                                        Account Settings
                                    </Link>
                                </li>
                                <li class="divider"></li>
                                <li><a href="javascript:void(0)" @click="logout">Logout</a></li>
                            </ul>
                        </div>
                    </header>

                    <main class="admin-main-content">
                        <div class="content-header">
                            <h1 class="page-module-title">{{ headerTitle }}</h1>
                            <slot name="header-actions" />
                        </div>
                        <slot />
                    </main>
                </div>
            </div>
        </div>
    </div>
    <Toast ref="myToast" />
</template>

<style scoped>
.body,
.html {
    margin: 0;
    padding: 0;
    height: 100%;
    font-family: 'Poppins', sans-serif;
    background-color: var(--main-bg);
    color: var(--text-light);
    font-size: 15px;
    line-height: 1.65;
    box-sizing: border-box;
}

*,
*:before,
*:after {
    box-sizing: inherit;
}

.admin-page-container {
    display: flex;
    min-height: 100vh;
}

.admin-sidebar {
    width: 250px;
    background-color: var(--sidebar-bg);
    border-right: 1px solid var(--border-color);
    display: flex;
    flex-direction: column;
    position: fixed;
    left: 0;
    top: 0;
    bottom: 0;
    z-index: 1000;
    box-shadow: 3px 0 10px rgba(0, 0, 0, 0.25);
}

.sidebar-header {
    padding: 1.25rem 1.5rem;
    text-align: center;
    border-bottom: 1px solid var(--border-color);
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.75rem;
}

.sidebar-header .logo-icon {
    width: 36px;
    height: 36px;
    margin-bottom: 0rem !important;
}

.sidebar-header h2 {
    font-size: 1.15rem;
    color: var(--text-headings);
    margin: 0;
    font-weight: 600;
}

.admin-nav ul {
    list-style: none;
    padding: 0;
    margin: 0;
    flex-grow: 1;
}

.admin-nav li a {
    display: flex;
    align-items: center;
    padding: 0.9rem 1.5rem;
    color: var(--text-muted);
    text-decoration: none;
    font-weight: 500;
    font-size: 0.95rem;
    transition: all 0.2s ease;
    border-left: 4px solid transparent;
}

.admin-nav li a .nav-icon-svg {
    width: 18px;
    height: 18px;
    margin-right: 1rem;
    fill: currentColor;
}

.admin-nav li a:hover {
    background-color: var(--table-row-hover-bg);
    color: var(--primary-color);
}

.admin-nav li a.active {
    background-color: var(--table-row-hover-bg);
    color: var(--primary-color);
    border-left-color: var(--primary-color);
    font-weight: 600;
}

.admin-main-content-wrapper {
    flex-grow: 1;
    margin-left: 250px;
    display: flex;
    flex-direction: column;
    background-color: var(--content-area-bg);
}

.admin-top-bar {
    display: flex;
    justify-content: flex-end;
    align-items: center;
    padding: 0 1.5rem;
    background-color: var(--sidebar-bg);
    border-bottom: 1px solid var(--border-color);
    min-height: 60px;
    box-shadow: var(--shadow-sm);
    position: sticky;
    top: 0;
    z-index: 999;
    overflow: visible;
}

.user-profile-dropdown {
    position: relative;
}

.user-profile-dropdown .profile-trigger {
    background: none;
    border: none;
    color: var(--text-light);
    cursor: pointer;
    padding: 0.5rem;
    display: flex;
    align-items: center;
    border-radius: 6px;
}

.user-profile-dropdown .profile-trigger:hover {
    background-color: rgba(255, 255, 255, 0.05);
}

.user-profile-dropdown .profile-trigger .user-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background-color: var(--primary-color);
    margin-right: 0.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    color: white;
}

.user-profile-dropdown .profile-trigger .user-avatar-img {
    width: 100%;
    height: 100%;
    border-radius: 50%;
}

.user-profile-dropdown .dropdown-menu {
    display: none;
    position: absolute;
    top: calc(100% + 8px);
    right: 0;
    background-color: var(--sidebar-bg);
    border: 1px solid var(--border-color);
    border-radius: 6px;
    box-shadow: var(--shadow-md);
    min-width: 180px;
    z-index: 10001;
    list-style: none;
    padding: 0.5rem 0;
    margin: 0;
}

.user-profile-dropdown .dropdown-menu.active {
    display: block;
}

.user-profile-dropdown .dropdown-menu li {
    margin: 0;
    padding: 0;
}

.user-profile-dropdown .dropdown-menu li a {
    display: block;
    padding: 0.6rem 1rem;
    color: var(--text-muted);
    text-decoration: none;
    font-size: 0.9rem;
    border: none;
}

.user-profile-dropdown .dropdown-menu li a:hover {
    background-color: var(--table-row-hover-bg);
    color: var(--primary-color);
}

.user-profile-dropdown .dropdown-menu .divider {
    height: 1px;
    background-color: var(--border-color);
    margin: 0.5rem 0;
    padding: 0;
}

.admin-main-content {
    flex-grow: 1;
    padding: 2rem;
    overflow-y: auto;
}

.content-header {
    margin-bottom: 2rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.page-module-title {
    font-size: 2rem;
    font-weight: 600;
    color: var(--text-headings);
    margin: 0;
}

.dashboard-section {
    background-color: var(--section-bg);
    padding: 1.5rem;
    border-radius: var(--border-radius-md);
    margin-bottom: 2.5rem;
}

.section-title {
    font-size: 1.5rem;
    font-weight: 600;
    color: var(--text-headings);
    margin-bottom: 1.5rem;
}

/* Chart Styles */
.chart-container {
    width: 100%;
    height: 300px;
    background-color: rgba(0, 0, 0, 0.2);
    border-radius: 8px;
    padding: 1rem;
    display: flex;
    align-items: flex-end;
    justify-content: space-around;
    gap: 1.5%;
}

.chart-bar {
    flex-grow: 1;
    background: linear-gradient(to top, var(--primary-color), var(--primary-color-hover));
    border-radius: 4px 4px 0 0;
    position: relative;
    text-align: center;
}

.chart-bar:hover .bar-tooltip {
    opacity: 1;
}

.bar-tooltip {
    position: absolute;
    top: -35px;
    left: 50%;
    transform: translateX(-50%);
    background-color: #333;
    color: white;
    padding: 5px 10px;
    border-radius: 4px;
    font-size: 0.8rem;
    white-space: nowrap;
    opacity: 0;
    transition: opacity 0.2s;
}

/* Recent Activity Table */
.admin-table-wrapper {
    overflow-x: auto;
}

.admin-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    color: var(--text-light);
    font-size: 0.9rem;
}

.admin-table th,
.admin-table td {
    padding: 0.8rem 1rem;
    text-align: left;
    vertical-align: middle;
    border-bottom: 1px solid var(--border-color);
}

.admin-table th {
    font-weight: 600;
    white-space: nowrap;
    color: var(--text-muted);
}

.admin-table tr:last-child td {
    border-bottom: none;
}

/* Mobile responsive toggles and layout modifications */
.mobile-nav-toggle {
    display: none;
    background: none;
    border: none;
    color: var(--text-light);
    cursor: pointer;
    padding: 0.5rem;
    align-items: center;
    justify-content: center;
}

.hamburger-icon {
    width: 24px;
    height: 24px;
    fill: currentColor;
}

.sidebar-backdrop {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.6);
    backdrop-filter: blur(4px);
    z-index: 999;
}

@media (max-width: 1024px) {
    .mobile-nav-toggle {
        display: flex;
    }
    
    .admin-sidebar {
        transform: translateX(-100%);
        transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .admin-sidebar.active {
        transform: translateX(0);
    }
    
    .admin-main-content-wrapper {
        margin-left: 0 !important;
    }
    
    .admin-top-bar {
        justify-content: space-between !important;
    }
    
    .sidebar-backdrop {
        display: block;
    }
}
</style>