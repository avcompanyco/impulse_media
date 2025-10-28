<script setup lang="ts">
import { computed } from 'vue';

const patternId = computed(() => `pattern-${Math.random().toString(36).substring(2, 9)}`);
</script>

<template>
    <svg class="placeholder-pattern" fill="none">
        <defs>
            <pattern :id="patternId" x="0" y="0" width="8" height="8" patternUnits="userSpaceOnUse">
                <path d="M-1 5L5 -1M3 9L8.5 3.5" stroke-width="0.5"></path>
            </pattern>
        </defs>
        <rect stroke="none" :fill="`url(#${patternId})`" width="100%" height="100%"></rect>
    </svg>
</template>

<style scoped>
.placeholder-pattern {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    stroke: var(--bs-gray-400);
    opacity: 0.2;
}

@media (prefers-color-scheme: dark) {
    .placeholder-pattern {
        stroke: var(--bs-gray-200);
        opacity: 0.1;
    }
}
</style>
