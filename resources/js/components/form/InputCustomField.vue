<script setup lang="ts">
import { useId } from 'vue';

import ErrorLabel from './ErrorLabel.vue';

const props = defineProps<{
    label: string;
    placeholder: string;
    type: string;
    autocomplete: string;
    error?: string;
}>()

const model = defineModel<string | number>()

const emits = defineEmits<{
    (e: 'update:modelValue', payload: string | number): void
}>()

const id = useId()

</script>
<template>
    <div class="form-group-custom">
        <label :for="id" class="form-label-custom">{{ label }}</label>
        <input :type="type" class="form-control-custom" :id="id" :placeholder="placeholder" :value="model"
            @input="emits('update:modelValue', $event.target?.value as string | number)" :autocomplete="autocomplete"
            v-bind="$attrs">
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