<script setup lang="ts">
import UserDashboardLayout from '@/layouts/UserDashboardLayout.vue';
import GetShorts from './partials/GetShorts.vue';
</script>

<template>
    <UserDashboardLayout :title="`Shorts - ${$page.props.name || 'Impulsemedia'}`"
        :headerTitle="`Shorts - ${$page.props.name || 'Impulsemedia'}`">
        <template #shorts-content>
            <Suspense>
                <GetShorts />
            </Suspense>
        </template>
    </UserDashboardLayout>
</template>
