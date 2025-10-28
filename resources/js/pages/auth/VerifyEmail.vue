<script setup lang="ts">
import EmailVerificationNotificationController from '@/actions/App/Http/Controllers/Auth/EmailVerificationNotificationController';
import TextLink from '@/components/TextLink.vue';
import ActionButton from '@/components/app/ActionButton.vue';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { logout } from '@/routes';
import { Form, Head } from '@inertiajs/vue3';

defineProps<{
    status?: string;
}>();
</script>

<template>
    <AuthLayout title="Verify email" description="Please verify your email address by clicking on the link we just emailed to you.">
        <Head title="Email verification" />

        <div v-if="status === 'verification-link-sent'" class="mb-4 text-center text-sm font-medium text-green-600">
            A new verification link has been sent to the email address you provided during registration.
        </div>

        <Form v-bind="EmailVerificationNotificationController.store.form()" class="space-y-6 text-center" v-slot="{ processing }">
            <ActionButton type="submit" :disabled="processing" variant="secondary">
                <i class="fa-solid fa-circle-notch fa-spin" v-if="processing"></i>

                Resend verification email
            </ActionButton>

            <TextLink :href="logout()" as="button" class="mx-auto block text-sm"> Log out </TextLink>
        </Form>
    </AuthLayout>
</template>
