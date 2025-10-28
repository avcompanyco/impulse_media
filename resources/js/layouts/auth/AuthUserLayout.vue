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
        <img src="/images/logo.png" alt="Logo" class="logo-icon" @click="router.visit(home())">

        <h1 class="login-title" style="text-transform: uppercase;">{{ title }}</h1>

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
    margin: 0 auto;
    display: flex;
    flex-direction: column;
    align-items: center;
}
</style>