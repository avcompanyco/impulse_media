<script setup lang="ts">
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import PauseContentController from '@/actions/App/Http/Controllers/Content/PauseContentController';
import PublishContentController from '@/actions/App/Http/Controllers/Content/PublishContentController';

const props = defineProps<{
    content: any;
}>();

const loading = ref(false);

const emits = defineEmits(['updated']);

function pauseContent(id: number) {
    loading.value = true;
    router.put(PauseContentController.url({ id }), {}, {
        preserveScroll: true,
        onFinish: () => {
            loading.value = false;
            emits('updated');
        },
    });
}
function publishContent(id: number) {
    loading.value = true;
    router.put(PublishContentController.url({ id }), {}, {
        preserveScroll: true,
        onFinish: () => {
            loading.value = false;
            emits('updated');
        },
    });
}
</script>
<template>
    <button v-if="content.status === 'published'" @click="pauseContent(content.id)" class="btn btn-pause"
        :data-id="content.id" :disabled="loading">
        <i class="fa-solid fa-circle-notch fa-spin" v-if="loading"></i>
        Pause
    </button>
    <button v-if="content.status === 'paused'" @click="publishContent(content.id)" class="btn btn-publish"
        :data-id="content.id" :disabled="loading">
        <i class="fa-solid fa-circle-notch fa-spin" v-if="loading"></i>
        Publish
    </button>
</template>

<style scoped>
 .btn {
    font-size: 0.825rem;
    padding: 0.45rem 0.85rem;
    border-radius: var(--border-radius-sm);
    border: none;
    font-weight: 500;
    text-align: center;
    min-width: 65px;
    cursor: pointer;
    text-decoration: none;
    color: white;
    transition: background-color 0.2s ease, transform 0.2s ease;
}

.btn:hover {
    transform: translateY(-1px);
}

.btn-publish {
    background-color: var(--secondary-color);
}

.btn-publish:hover {
    background-color: var(--secondary-color-hover);
}

.btn-pause {
    background-color: var(--primary-color);
}

.btn-pause:hover {
    background-color: var(--primary-color-hover);
}

.btn-delete {
    background-color: var(--error-color);
}

.btn-delete:hover {
    background-color: var(--error-color-hover);
}
</style>