<script setup lang="ts">
import UserDashboardLayout from '@/layouts/UserDashboardLayout.vue';
import GetShorts from './partials/GetShorts.vue';

const props = defineProps<{
    initialShort?: any;
}>();
</script>

<template>
    <UserDashboardLayout :title="`Shorts - ${$page.props.name || 'Impulsemedia'}`"
        :headerTitle="`Shorts - ${$page.props.name || 'Impulsemedia'}`">
        <template #shorts-content>
            <Suspense>
                <GetShorts :initialShort="initialShort" />
            </Suspense>
        </template>
    </UserDashboardLayout>
</template>
