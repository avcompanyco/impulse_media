<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { user as loginRoute } from '@/routes/login';
import ShowRegisterUserController from '@/actions/App/Http/Controllers/Auth/User/ShowRegisterUserController';
import PublicGetShorts from './public-shorts/partials/PublicGetShorts.vue';

// Controls the "login required" intercept modal
const showLoginModal = ref(false);

const openLoginModal = () => {
    showLoginModal.value = true;
};
const closeLoginModal = () => {
    showLoginModal.value = false;
};
</script>

<template>
    <Head :title="`${$page.props.name || 'Impulsemedia'} — Watch Reels`" />

    <!-- Full-screen reels layout -->
    <div class="public-shorts-wrapper">

        <!-- Sticky header -->
        <header class="public-header">
            <img src="/images/logo.png" alt="Logo" class="public-logo">
            <div class="public-header-actions">
                <Link :href="loginRoute()" class="btn btn-login">Log In</Link>
                <Link :href="ShowRegisterUserController()" class="btn btn-signup">Sign Up</Link>
            </div>
        </header>

        <!-- Reels player -->
        <div class="public-shorts-content">
            <PublicGetShorts @require-login="openLoginModal" />
        </div>
    </div>

    <!-- Login Required modal -->
    <Teleport to="body">
        <div v-if="showLoginModal" class="modal-backdrop" @click.self="closeLoginModal">
            <div class="login-modal">
                <button class="modal-close-btn" @click="closeLoginModal">&times;</button>

                <img src="/images/logo.png" alt="Logo" class="modal-logo">
                <h2 class="modal-title">Join to interact</h2>
                <p class="modal-subtitle">
                    Create an account or log in to follow creators, build your watchlist, and access movies &amp; series.
                </p>

                <Link :href="ShowRegisterUserController()" class="btn btn-modal-signup">
                    Create Account
                </Link>
                <Link :href="loginRoute()" class="btn btn-modal-login">
                    Log In
                </Link>
            </div>
        </div>
    </Teleport>
</template>

<style scoped>
/* ──────────────────────────────────────
   Wrapper
────────────────────────────────────── */
.public-shorts-wrapper {
    position: relative;
    width: 100%;
    min-height: 100vh;
    background-color: #000;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

/* ──────────────────────────────────────
   Header
────────────────────────────────────── */
.public-header {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    z-index: 500;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.75rem 1.25rem;
    background: linear-gradient(to bottom, rgba(0, 0, 0, 0.75), transparent);
    pointer-events: none; /* let clicks pass through gradient area */
}

.public-header > * {
    pointer-events: auto;
}

.public-logo {
    width: 40px;
    height: 40px;
    object-fit: contain;
}

.public-header-actions {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.btn-login {
    background: transparent;
    border: 1.5px solid rgba(255, 255, 255, 0.8);
    color: #fff;
    border-radius: 20px;
    padding: 0.4rem 1.2rem;
    font-size: 0.9rem;
    font-weight: 500;
    text-decoration: none;
    transition: background 0.2s, color 0.2s;
}
.btn-login:hover {
    background: rgba(255, 255, 255, 0.15);
    color: #fff;
}

.btn-signup {
    background: #e8445a;
    border: none;
    color: #fff;
    border-radius: 20px;
    padding: 0.4rem 1.2rem;
    font-size: 0.9rem;
    font-weight: 500;
    text-decoration: none;
    transition: background 0.2s;
}
.btn-signup:hover {
    background: #d03050;
}

/* ──────────────────────────────────────
   Shorts content area
────────────────────────────────────── */
.public-shorts-content {
    flex: 1;
    display: flex;
    flex-direction: column;
    width: 100%;
    height: 100vh;
    padding-top: 0; /* header overlaps via fixed positioning + gradient */
}

/* ──────────────────────────────────────
   Login required modal
────────────────────────────────────── */
.modal-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.75);
    z-index: 1000;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
}

.login-modal {
    background: #1a1a2e;
    border-radius: 16px;
    padding: 2rem 1.75rem;
    width: 100%;
    max-width: 380px;
    text-align: center;
    position: relative;
    color: #fff;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.6);
}

.modal-close-btn {
    position: absolute;
    top: 0.75rem;
    right: 1rem;
    background: none;
    border: none;
    color: rgba(255, 255, 255, 0.6);
    font-size: 1.75rem;
    line-height: 1;
    cursor: pointer;
    transition: color 0.2s;
}
.modal-close-btn:hover { color: #fff; }

.modal-logo {
    width: 48px;
    height: 48px;
    object-fit: contain;
    margin-bottom: 1.25rem;
}

.modal-title {
    font-size: 1.4rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
}

.modal-subtitle {
    font-size: 0.9rem;
    color: rgba(255, 255, 255, 0.7);
    line-height: 1.5;
    margin-bottom: 1.5rem;
}

.btn-modal-signup {
    display: block;
    width: 100%;
    background: #e8445a;
    color: #fff;
    border: none;
    border-radius: 10px;
    padding: 0.75rem 1rem;
    font-size: 1rem;
    font-weight: 600;
    text-decoration: none;
    margin-bottom: 0.75rem;
    transition: background 0.2s;
    cursor: pointer;
}
.btn-modal-signup:hover { background: #d03050; color: #fff; }

.btn-modal-login {
    display: block;
    width: 100%;
    background: transparent;
    color: #fff;
    border: 1.5px solid rgba(255, 255, 255, 0.4);
    border-radius: 10px;
    padding: 0.75rem 1rem;
    font-size: 1rem;
    font-weight: 500;
    text-decoration: none;
    transition: border-color 0.2s, background 0.2s;
    cursor: pointer;
}
.btn-modal-login:hover { border-color: rgba(255,255,255,0.8); background: rgba(255,255,255,0.05); color: #fff; }

/* Responsive */
@media (max-width: 400px) {
    .public-logo { width: 32px; height: 32px; }
    .btn-login, .btn-signup { padding: 0.35rem 0.9rem; font-size: 0.8rem; }
}
</style>
