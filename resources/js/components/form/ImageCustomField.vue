<script setup lang="ts">
import { ref, useId } from 'vue';

import ErrorLabel from './ErrorLabel.vue';

const props = defineProps<{
    label: string;
    placeholder: string;
    type: string;
    autocomplete: string;
    error?: string;
}>()

const inputRef = ref<HTMLInputElement | null>(null);

const id = useId()

const resetInput = () => {
    if (inputRef.value) {
        inputRef.value.value = '';
    }
}

defineExpose({
    resetInput
})

</script>

<template>
    <div class="form-group-custom">
        <label :for="id" class="form-label-custom">{{ label }}</label>
        <input 
            ref="inputRef"
            type="file" 
            name="image" 
            :id="id" 
            class="form-control-custom" 
            accept="image/*" 
            v-bind="$attrs"
            />
        <img id="imagePreview" name="image" src="" alt="Image Preview"
            style="max-width: 100%; display: none; margin-top: 10px; border-radius: 6px;">
        <ErrorLabel :message="error" />
    </div>
</template>

<style scoped>
.form-group-custom {
    margin-bottom: 1.25rem;
}

.form-label-custom {
    font-size: 0.95rem;
    font-weight: 500;
    margin-bottom: 0.4rem;
    color: var(--text-muted);
    display: block;
}

.form-control-custom {
    background: var(--input-bg);
    border: 1px solid var(--input-bg);
    color: var(--text-dark-on-light-bg);
    padding: 0.8rem 1rem;
    border-radius: var(--border-radius-sm);
    font-size: 0.95rem;
    width: 100%;
    box-sizing: border-box;
}
</style>