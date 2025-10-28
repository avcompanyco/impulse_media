<script setup lang="ts">
import { useId, ref } from 'vue';

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

const modelInput = ref(null);

// expose reset method
defineExpose({
    modelInput
})

</script>

<template>
    <div class="form-group">
        <label 
            :for="id" 
            class="form-label">{{ label }}</label>
        <input 
            ref="modelInput"
            :type="type" 
            class="form-control" 
            :id="id" 
            :placeholder="placeholder"
            :value="model"
            @input="emits('update:modelValue', $event.target?.value as string | number)"
            :autocomplete="autocomplete"
            v-bind="$attrs"
        >
        <ErrorLabel :message="error" />
    </div>
</template>