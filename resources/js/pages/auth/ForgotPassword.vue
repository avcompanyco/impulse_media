<script setup lang="ts">
import ShowLoginUserController from '@/actions/App/Http/Controllers/Auth/User/ShowLoginUserController';
import PasswordResetLinkController from '@/actions/App/Http/Controllers/Auth/PasswordResetLinkController';
import SendVerificationCodeController from '@/actions/App/Http/Controllers/Auth/SendVerificationCodeController';

import InputField from '@/components/form/InputField.vue';

import TextLink from '@/components/TextLink.vue';
import RecoverAuthLayout from '@/layouts/auth/RecoverAuthLayout.vue';
import { Form } from '@inertiajs/vue3';
import ActionButton from '@/components/app/ActionButton.vue';

defineProps<{
    status?: string;
}>();
</script>

<template>
    <RecoverAuthLayout 
        title="RECOVER PASSWORD" 
        description="Enter your email address and we will send you a code to reset your password.">

        <Form v-bind="SendVerificationCodeController.form()" v-slot="{ errors, processing }">
            <InputField name="email" label="Email" placeholder="Enter your registered email" type="email"
                autocomplete="off" autofocus :error="errors.email" />
            <ActionButton type="submit" :disabled="processing">
                <i class="fa-solid fa-circle-notch fa-spin" v-if="processing"></i>
                Continue
            </ActionButton>
        </Form>
        <div class="mt-3 text-center text-sm text-muted-foreground">
            <span>Or, return to </span>
            <TextLink :href="ShowLoginUserController()">log in</TextLink>
        </div>
    </RecoverAuthLayout>
</template>

<style scoped>

.form-container {
    width: 100%;
}

/* Estilos para los pasos del formulario */
.password-step {
    display: none;
}

.password-step.active {
    display: block;
}

.form-group {
    margin-bottom: 2rem;
    text-align: left;
}

.form-label {
    color: white;
    font-size: 1.25rem;
    font-weight: 500;
    margin-bottom: 0.875rem;
    display: block;
}

.form-control {
    background-color: #E8E8E8;
    border: none;
    border-radius: 30px;
    padding: 1rem 1.75rem;
    font-size: 1.1rem;
    height: auto;
    color: #333;
    width: 100%;
}

.form-control::placeholder {
    color: #666;
}

.form-control:focus {
    box-shadow: 0 0 0 2px rgba(255, 255, 255, 0.25);
    background-color: #fff;
}

.action-button {
    background-color: #F06292;
    color: white;
    border: none;
    border-radius: 30px;
    padding: 1rem 2rem;
    font-size: 1.125rem;
    font-weight: 500;
    width: 100%;
    max-width: 300px;
    display: block;
    margin: 3rem auto 0;
    transition: all 0.3s ease;
    text-decoration: none;
}

.action-button:hover {
    transform: scale(1.02);
    background-color: #e91e63;
}

@media (max-width: 400px) {
    .app-container {
        padding: 1.5rem;
    }

    .recover-title {
        font-size: 1.75rem;
    }

    .instructions {
        font-size: 0.9rem;
        margin-bottom: 3rem;
    }

    .form-control {
        padding: 0.875rem 1.5rem;
        font-size: 1rem;
    }

    .form-label {
        font-size: 1.125rem;
    }
}
</style>