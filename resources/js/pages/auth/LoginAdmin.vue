<script setup lang="ts">
import LoginAdminController from '@/actions/App/Http/Controllers/Auth/Admin/LoginAdminController';
import { create as createPasswordResetLinkController } from '@/actions/App/Http/Controllers/Auth/PasswordResetLinkController'

import InputField from '@/components/form/InputField.vue';
import LoginButton from '@/components/app/LoginButton.vue';
import AuthUserLayout from '@/layouts/auth/AuthUserLayout.vue';
import { Form, Head, Link } from '@inertiajs/vue3';

defineProps<{
    status?: string;
    canResetPassword: boolean;
    errors: any;
}>();
</script>

<template>
    <AuthUserLayout title="Log in to your account" description="Enter your email and password below to log in">

        <Head title="Log in" />

        <div v-if="errors && errors.type === 'error'" class="mb-3 text-danger text-center">
            {{ errors.message }}
        </div>

        <Form v-bind="LoginAdminController.form()" :reset-on-success="['password']" v-slot="{ errors, processing }"
            class="flex flex-col gap-6">

            <InputField name="email" label="Email or Username" placeholder="Your email or username" type="text"
                autocomplete="email" :error="errors.email" />
            <InputField id="password" name="password" label="Password" placeholder="******************" type="password"
                autocomplete="current-password" :error="errors.password" />

            <Link :href="createPasswordResetLinkController()" class="forgot-password-link">Forgot your password?</Link>

            <LoginButton type="submit" :disabled="processing">
                <i class="fa-solid fa-circle-notch fa-spin" v-if="processing"></i>
                Log in
            </LoginButton>

        </Form>
    </AuthUserLayout>
</template>

<style scoped>
.form-container {
    width: 100%;
}

.form-group {
    margin-bottom: 2rem;
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

.forgot-password-link {
    display: block;
    text-align: right;
    margin-top: -1rem;
    margin-bottom: 2rem;
    font-size: 0.9rem;
    color: #ccc;
    text-decoration: none;
    transition: color 0.2s;
}

.forgot-password-link:hover {
    color: white;
    text-decoration: underline;
}

.login-button {
    background-color: #DC3545;
    color: white;
    border: none;
    border-radius: 30px;
    padding: 1rem 2rem;
    font-size: 1.125rem;
    font-weight: 500;
    width: 100%;
    max-width: 200px;
    display: block;
    margin: 3rem auto 0;
    transition: all 0.3s ease;
}

.login-button:hover {
    transform: scale(1.02);
    background-color: #bb2d3b;
}

/* Tablet */
@media (min-width: 768px) {
    .app-container {
        max-width: 480px;
    }

    .logo-icon {
        width: 56px;
        height: 56px;
        margin-bottom: 2.5rem;
    }

    .login-title {
        font-size: 2.25rem;
    }
}

/* Desktop */
@media (min-width: 1200px) {
    .app-container {
        max-width: 520px;
        padding: 0;
    }

    .logo-icon {
        width: 64px;
        height: 64px;
        margin-bottom: 3rem;
    }

    .login-title {
        font-size: 2.5rem;
        margin-bottom: 5rem;
    }

    .form-group {
        margin-bottom: 2.5rem;
    }

    .login-button {
        margin-top: 4rem;
    }
}

/* Mobile small */
@media (max-width: 400px) {
    .app-container {
        padding: 1.5rem;
    }

    .login-title {
        font-size: 1.75rem;
        margin-bottom: 3rem;
    }

    .form-control {
        padding: 0.875rem 1.5rem;
        font-size: 1rem;
    }

    .form-label {
        font-size: 1.125rem;
    }

    .login-button {
        margin-top: 2.5rem;
        padding: 0.875rem 1.75rem;
    }
}
</style>