<script setup lang="ts">
defineProps<{
    message?: string;
}>()
</script>

<template>
    <p v-if="message" class="text-danger small">
        {{ message }}
    </p>
</template>