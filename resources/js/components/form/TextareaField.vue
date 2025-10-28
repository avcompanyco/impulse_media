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
    <div class="form-group">
        <label 
            :for="id" 
            class="form-label">{{ label }}</label>
        <textarea 
            :id="id" 
            class="form-control" 
            :placeholder="placeholder"
            @input="emits('update:modelValue', $event.target?.value as string | number)"
            :autocomplete="autocomplete"
            v-bind="$attrs"
        >{{ model }}</textarea>

        <ErrorLabel :message="error" />
    </div>
</template>