<script setup lang="ts">
import ConfirmablePasswordController from '@/actions/App/Http/Controllers/Auth/ConfirmablePasswordController';
import ActionButton from '@/components/app/ActionButton.vue';
import InputField from '@/components/form/InputField.vue';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { Form, Head } from '@inertiajs/vue3';
</script>

<template>
    <AuthLayout title="Confirm your password"
        description="This is a secure area of the application. Please confirm your password before continuing.">

        <Head title="Confirm password" />

        <Form v-bind="ConfirmablePasswordController.store.form()" reset-on-success v-slot="{ errors, processing }">
            <div class="space-y-6">
                <InputField name="password" label="Password" placeholder="Enter your password" type="password"
                    autocomplete="current-password" autofocus :error="errors.password" />

                <div class="d-flex justify-content-center">
                    <ActionButton type="submit" class="w-full" :disabled="processing">
                        <i class="fa-solid fa-circle-notch fa-spin" v-if="processing"></i>
                        Confirm Password
                    </ActionButton>
                </div>
            </div>
        </Form>
    </AuthLayout>
</template>
