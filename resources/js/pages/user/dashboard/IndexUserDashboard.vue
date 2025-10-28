<script setup lang="ts">
import UserDashboardLayout from '@/layouts/UserDashboardLayout.vue';
import { onMounted } from 'vue';
import FrontPageCarousel from './partial/FrontPageCarousel.vue';
import CategoriesSections from './partial/CategoriesSections.vue';

const props = defineProps<{
    frontpage: any[];
    categories: any[];
}>();

</script>

<template>

    <UserDashboardLayout title="Dashboard" headerTitle="Dashboard">
        <FrontPageCarousel :frontpage="frontpage" />

        <div style="padding-top: 30px;"> </div>
        <CategoriesSections :categories="categories" />
        <br />
        <br />
        <br />
    </UserDashboardLayout>
</template>