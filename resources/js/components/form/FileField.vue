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


const id = useId()

</script>

<template>
    <div class="form-group">
        <label 
            :for="id" 
            class="form-label">{{ label }}</label>
    
        <ErrorLabel :message="error" />
    </div>
</template>