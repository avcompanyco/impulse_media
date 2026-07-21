<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { user as loginRoute } from '@/routes/login';
import ShowRegisterUserController from '@/actions/App/Http/Controllers/Auth/User/ShowRegisterUserController';

const props = defineProps<{
    user?: any;
}>();
</script>

<template>
    <header class="public-header">
        <Link :href="user ? '/dashboard' : '/'">
            <img src="/images/logo.png" alt="Logo" class="public-logo">
        </Link>
        <div class="public-header-actions">
            <template v-if="user">
                <Link href="/profile" class="btn btn-profile">
                    <img :src="user.image_url || '/images/Jhon.webp'" alt="Profile" class="header-profile-img">
                    <span>My Profile</span>
                </Link>
            </template>
            <template v-else>
                <Link :href="loginRoute()" class="btn btn-login">Log In</Link>
                <Link :href="ShowRegisterUserController()" class="btn btn-signup">Sign Up</Link>
            </template>
        </div>
    </header>
</template>

<style scoped>
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
    background: linear-gradient(to bottom, rgba(0, 0, 0, 0.85), rgba(0, 0, 0, 0.4));
    pointer-events: none;
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

.btn-profile {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    background: rgba(255, 255, 255, 0.1);
    border: 1.5px solid rgba(255, 255, 255, 0.3);
    color: #fff;
    border-radius: 20px;
    padding: 0.3rem 1rem 0.3rem 0.3rem;
    font-size: 0.9rem;
    font-weight: 500;
    text-decoration: none;
    transition: background 0.2s;
}
.btn-profile:hover {
    background: rgba(255, 255, 255, 0.2);
    color: #fff;
}

.header-profile-img {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    object-fit: cover;
}

@media (max-width: 400px) {
    .public-logo { width: 32px; height: 32px; }
    .btn-login, .btn-signup { padding: 0.35rem 0.9rem; font-size: 0.8rem; }
    .btn-profile { padding: 0.25rem 0.75rem 0.25rem 0.25rem; font-size: 0.8rem; }
    .btn-profile span { display: none; }
}
</style>
