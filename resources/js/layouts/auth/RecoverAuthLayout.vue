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

    <div class="app-container">
        <img src="/images/logo.png" alt="Logo" class="logo-icon">

        <h1 class="recover-title">
            {{ title }}
        </h1>

        <p class="instructions" id="instructionsText">{{ description }}</p>

        <div class="form-container">
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

.app-container {
    width: 100%;
    max-width: 450px;
    margin: 0 auto;
    padding: 2rem;
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
}

.logo-icon {
    width: 48px;
    height: 48px;
    margin-bottom: 2rem;
}
.recover-title {
    font-size: 2rem;
    font-weight: 600;
    margin-bottom: 1rem;
    letter-spacing: -0.5px;
}

.instructions {
    color: #ccc;
    margin-bottom: 3.5rem;
    font-size: 1rem;
    max-width: 350px;
    min-height: 40px;
    /* Para evitar que el layout salte al cambiar el texto */
}
</style>