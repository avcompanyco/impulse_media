<script setup lang="ts">
import RegisterUserController from '@/actions/App/Http/Controllers/Auth/User/RegisterUserController';
import AuthUserLayout from '@/layouts/auth/AuthUserLayout.vue';
import TextLink from '@/components/TextLink.vue';
import ActionButton from '@/components/app/ActionButton.vue';

import InputField from '@/components/form/InputField.vue';

import AuthBase from '@/layouts/AuthLayout.vue';
import { user as loginUser } from '@/routes/login';
import { Form, Head } from '@inertiajs/vue3';
</script>

<template>
    <AuthUserLayout title="Create an account" description="Enter your details below to create your account">

        <Head title="Register" />

        <Form v-bind="RegisterUserController.form()" :reset-on-success="['password', 'password_confirmation']"
            v-slot="{ errors, processing }" class="">
            <div class="grid gap-6">
                <InputField 
                    name="name" 
                    label="Full Name" 
                    placeholder="Full name" 
                    type="text" 
                    autocomplete="name" 
                    autofocus
                    :error="errors.name" />
                <InputField 
                    name="username" 
                    label="Username" 
                    placeholder="Username" 
                    type="text" 
                    autocomplete="username" 
                    autofocus
                    :error="errors.username" />
                <InputField 
                    name="email" 
                    label="Email address" 
                    placeholder="Email" 
                    type="email"
                    autocomplete="email" autofocus :error="errors.email" />
                <InputField 
                    name="password" 
                    label="Password" 
                    placeholder="******************" 
                    type="password"
                    autocomplete="new-password" 
                    autofocus 
                    :error="errors.password" />
                <InputField 
                    name="password_confirmation" 
                    label="Confirm password" 
                    placeholder="Confirm password"
                    type="password" 
                    autocomplete="new-password" 
                    autofocus 
                    :error="errors.password_confirmation" />

                <button type="submit" class="btn signup-button" :disabled="processing">
                    <i class="fa-solid fa-circle-notch fa-spin" v-if="processing"></i>
                    Sign up 
                </button>

            </div>

            <div class="text-center text-sm text-muted-foreground my-3">
                Already have an account?
                <TextLink :href="loginUser()" class="underline underline-offset-4" :tabindex="6">Log in</TextLink>
            </div>
        </Form>
    </AuthUserLayout>
</template>

<style scoped>
.logo-icon {
    width: 48px;
    height: 48px;
    margin-bottom: 2rem;
}

.create-title {
    font-size: 1.75rem;
    font-weight: 600;
    text-align: center;
    margin-bottom: 2rem;
    letter-spacing: -0.5px;
}

.form-container {
    width: 100%;
}

.profile-picture-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    margin-bottom: 2.5rem;
}

.profile-avatar {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #F06292;
    margin-bottom: 1rem;
    background-color: #12122A;
}

.change-picture-btn {
    background: none;
    border: 1px solid #FFFFFF;
    color: #FFFFFF;
    padding: 0.5rem 1rem;
    border-radius: 10px;
    cursor: pointer;
    font-weight: 500;
    transition: background-color 0.2s, color 0.2s;
}

.change-picture-btn:hover {
    background-color: #FFFFFF;
    color: #0A0A23;
}

.form-group {
    margin-bottom: 1.75rem;
}

.form-label {
    color: white;
    font-size: 1.125rem;
    font-weight: 500;
    margin-bottom: 0.75rem;
    display: block;
}

.form-control {
    background-color: #E8E8E8;
    border: none;
    border-radius: 30px;
    padding: 0.875rem 1.5rem;
    font-size: 1rem;
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

.signup-button {
    background-color: #DC3545;
    color: white;
    border: none;
    border-radius: 30px;
    padding: 0.875rem 2rem;
    font-size: 1rem;
    font-weight: 500;
    width: 100%;
    max-width: 200px;
    display: block;
    margin: 2.5rem auto 0;
    transition: all 0.3s ease;
}

.signup-button:hover {
    transform: scale(1.02);
    background-color: #bb2d3b;
}

@media (min-width: 768px) {
    .app-container {
        max-width: 480px;
    }

    .logo-icon {
        width: 56px;
        height: 56px;
        margin-bottom: 2.5rem;
    }

    .create-title {
        font-size: 2rem;
        margin-bottom: 3.5rem;
    }

    .form-control {
        padding: 1rem 1.75rem;
        font-size: 1.1rem;
    }
}

@media (min-width: 1200px) {
    .app-container {
        max-width: 520px;
        padding: 0;
    }

    .logo-icon {
        width: 64px;
        height: 64px;
        margin-bottom: 2.5rem;
    }

    .create-title {
        font-size: 2.25rem;
        margin-bottom: 4rem;
    }

    .form-label {
        font-size: 1.25rem;
        margin-bottom: 0.875rem;
    }

    .form-control {
        padding: 1.125rem 2rem;
        font-size: 1.125rem;
    }

    .form-group {
        margin-bottom: 2rem;
    }

    .signup-button {
        padding: 1rem 2rem;
        font-size: 1.125rem;
        margin-top: 3rem;
    }
}

@media (max-width: 400px) {
    .app-container {
        padding: 1.5rem;
    }

    .create-title {
        font-size: 1.5rem;
        margin-bottom: 2.5rem;
    }

    .form-control {
        padding: 0.75rem 1.25rem;
    }
}
</style>