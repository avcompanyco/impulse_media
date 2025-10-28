<script setup lang="ts">
import { ref } from 'vue';
import AuthenticatedSessionController from '@/actions/App/Http/Controllers/Auth/User/LoginUserController';
import { create as createPasswordResetLinkController } from '@/actions/App/Http/Controllers/Auth/PasswordResetLinkController'
import AuthUserLayout from '@/layouts/auth/AuthUserLayout.vue';
import { Form, Head, Link } from '@inertiajs/vue3';
import InputField from '@/components/form/InputField.vue';
import LoginButton from '@/components/app/LoginButton.vue';
import TextLink from '@/components/TextLink.vue';
import RegisterUserController from '@/actions/App/Http/Controllers/Auth/User/RegisterUserController';

defineProps<{
    status?: string;
    canResetPassword: boolean;
}>();

const password = ref('');
const email = ref('');

function errorHandler(error: any) {
    password.value = '';
}

</script>

<template>

    <Head title="Log in" />
    <AuthUserLayout title="Log in to your account" description="Enter your email and password below to log in">


        <div v-if="status" class="mb-4 text-center text-sm font-medium text-green-600">
            {{ status }}
        </div>

        <Form v-bind="AuthenticatedSessionController.form()" 
            :reset-on-success="['password']"
            @error="errorHandler"
            v-slot="{ errors, processing }" class="flex flex-col gap-6">

            <InputField name="email" label="Email or Username" placeholder="Your email or username" type="text"
                autocomplete="email" :error="errors.email" v-model="email" />
            <InputField id="password" name="password" label="Password" placeholder="******************" type="password"
                autocomplete="current-password" :error="errors.password" v-model="password" />

            <Link :href="createPasswordResetLinkController()" class="forgot-password-link">Forgot your password?</Link>

            <LoginButton type="submit" :disabled="processing">
                <i class="fa-solid fa-circle-notch fa-spin" v-if="processing"></i>
                Log in
            </LoginButton>

        </Form>
        <div class="text-center text-sm text-muted-foreground my-3">
            Don't have an account?
            <TextLink :href="RegisterUserController.url()" as="a" class="underline underline-offset-4" :tabindex="6">Sign up</TextLink>
        </div>
    </AuthUserLayout>
</template>

<style scoped></style>
