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

    <div class="auth-page">

        <!-- Background GIF covering full screen -->
        <div class="auth-background">
            <img src="/background.gif" alt="Background" class="auth-background-img">
            <div class="auth-background-overlay"></div>
        </div>

        <!-- Form card centered -->
        <div class="app-container-recover">
            <img src="/images/logo.png" alt="Logo" class="logo-icon-recover" @click="router.visit(home())">

            <h1 class="recover-title">{{ title }}</h1>

            <p class="instructions" id="instructionsText">{{ description }}</p>

            <div class="form-container-recover">
                <slot />
            </div>
        </div>
    </div>
</template>

<style scoped>
/* ---- Page wrapper ---- */
.auth-page {
    position: relative;
    min-height: 100vh;
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}

/* ---- Background ---- */
.auth-background {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 0;
}

.auth-background-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.auth-background-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    background-color: rgba(0, 0, 0, 0.4);
}

/* ---- Form card ---- */
.app-container-recover {
    position: relative;
    z-index: 10;
    width: 100%;
    max-width: 450px;
    margin: 0 auto;
    padding: 2.5rem 2rem;
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    background: rgba(10, 10, 35, 0.72);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border: 1px solid rgba(124, 58, 237, 0.18);
    border-radius: 20px;
    box-shadow:
        0 0 60px rgba(124, 58, 237, 0.08),
        0 24px 64px rgba(0, 0, 0, 0.6);
}

.logo-icon-recover {
    width: 48px;
    height: 48px;
    margin-bottom: 2rem;
    cursor: pointer;
    transition: transform 0.2s;
}
.logo-icon-recover:hover {
    transform: scale(1.08);
}

.recover-title {
    font-size: 2rem;
    font-weight: 600;
    margin-bottom: 1rem;
    letter-spacing: -0.5px;
    color: #fff;
}

.instructions {
    color: #ccc;
    margin-bottom: 3.5rem;
    font-size: 1rem;
    max-width: 350px;
    min-height: 40px;
}

.form-container-recover {
    width: 100%;
}

/* Tablet */
@media (min-width: 768px) {
    .app-container-recover {
        max-width: 480px;
    }
    .logo-icon-recover {
        width: 56px;
        height: 56px;
        margin-bottom: 2.5rem;
    }
    .recover-title {
        font-size: 2.25rem;
    }
}

/* Desktop */
@media (min-width: 1200px) {
    .app-container-recover {
        max-width: 520px;
    }
    .logo-icon-recover {
        width: 64px;
        height: 64px;
        margin-bottom: 3rem;
    }
    .recover-title {
        font-size: 2.5rem;
    }
}

/* Mobile small */
@media (max-width: 400px) {
    .app-container-recover {
        padding: 1.5rem;
    }
    .recover-title {
        font-size: 1.75rem;
    }
}
</style>