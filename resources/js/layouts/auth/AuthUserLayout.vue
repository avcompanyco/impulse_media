<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { home } from '@/routes'

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>

    <Head :title="title" :description="description" />

    <div class="app-container-auth-user">
        <img src="/images/logo.png" alt="Logo" class="logo-icon-auth-user" @click="router.visit(home())">

        <h1 class="login-title-auth-user" style="text-transform: uppercase;">{{ title }}</h1>

        <div class="form-container-auth-user">
            <slot />
        </div>
    </div>
</template>

<style scoped>
body {
    font-family: 'Poppins', sans-serif;
    background-color: #0A0A23;
    color: white;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
}

.app-container-auth-user {
    width: 100%;
    max-width: 420px;
    margin: 0 auto;
    padding: 2rem;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.logo-icon-auth-user {
    width: 48px;
    height: 48px;
    margin-bottom: 2rem;
}

.login-title-auth-user {
    font-size: 2rem;
    font-weight: 600;
    text-align: center;
    margin-bottom: 4rem;
    letter-spacing: -0.5px;
}

.form-container-auth-user {
    width: 100%;
}

/* Tablet */
@media (min-width: 768px) {
    .app-container-auth-user {
        max-width: 480px;
    }

    .logo-icon-auth-user {
        width: 56px;
        height: 56px;
        margin-bottom: 2.5rem;
    }

    .login-title-auth-user {
        font-size: 2.25rem;
    }
}

/* Desktop */
@media (min-width: 1200px) {
    .app-container-auth-user {
        max-width: 520px;
    }

    .logo-icon-auth-user {
        width: 64px;
        height: 64px;
        margin-bottom: 3rem;
    }

    .login-title-auth-user {
        font-size: 2.5rem;
        margin-bottom: 5rem;
    }
}

/* Mobile small */
@media (max-width: 400px) {
    .app-container-auth-user {
        padding: 1.5rem;
    }

    .login-title-auth-user {
        font-size: 1.75rem;
        margin-bottom: 3rem;
    }
}
</style>