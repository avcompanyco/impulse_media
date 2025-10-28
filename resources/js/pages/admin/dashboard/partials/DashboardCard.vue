<script setup lang="ts">

const props = defineProps<{
    title: string;
    value: any;
}>()
</script>

<template>
    <div class="stat-card">
        <h3>{{ title }}</h3>
        <p class="stat-value">{{ value }}</p>
    </div>
</template>

<style>

.stat-card {
    background-color: var(--section-bg);
    padding: 1.5rem;
    border-radius: var(--border-radius-md);
    border-left: 4px solid var(--primary-color);
    box-shadow: var(--shadow-sm);
}

.stat-card h3 {
    font-size: 0.9rem;
    color: var(--text-muted);
    margin: 0 0 0.4rem 0;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.stat-card .stat-value {
    font-size: 2rem;
    font-weight: 700;
    color: var(--text-headings);
    margin: 0;
}
</style>