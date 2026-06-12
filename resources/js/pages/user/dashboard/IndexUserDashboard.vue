<script setup lang="ts">
import UserDashboardLayout from '@/layouts/UserDashboardLayout.vue';
import { Link } from '@inertiajs/vue3';
import FrontPageCarousel from './partial/FrontPageCarousel.vue';
import CategoriesSections from './partial/CategoriesSections.vue';

const props = defineProps<{
    frontpage: any[];
    categories: any[];
}>();

</script>

<template>

    <UserDashboardLayout 
        :title="`Dashboard - ${$page.props.name || 'Impulsemedia'}`" 
        :headerTitle="`Dashboard - ${$page.props.name || 'Impulsemedia'}`">
        <FrontPageCarousel :frontpage="frontpage" />

        <!-- Creator Shortcut Card (Premium & Modern) -->
        <div v-if="$page.props.auth.user && $page.props.auth.user.is_creator" class="creator-welcome-card">
            <div class="welcome-card-content">
                <div class="welcome-card-text">
                    <span class="welcome-tag"><i class="fa-solid fa-star"></i> Creator Privileges</span>
                    <h2>Welcome back, {{ $page.props.auth.user.name }}!</h2>
                    <p>Track your real-time analytics, check your current earnings, and upload new media to your subscribers.</p>
                </div>
                <div class="welcome-card-actions">
                    <Link href="/creator/dashboard" class="action-btn-primary">
                        <i class="fa-solid fa-chart-line"></i> Creator Dashboard
                    </Link>
                    <Link href="/upload/movie" class="action-btn-secondary">
                        <i class="fa-solid fa-cloud-arrow-up"></i> Upload Media
                    </Link>
                </div>
            </div>
        </div>

        <div style="padding-top: 30px;"> </div>
        <CategoriesSections :categories="categories" />
        <br />
        <br />
        <br />
    </UserDashboardLayout>
</template>

<style scoped>
.creator-welcome-card {
    margin: 1.5rem 1rem 0 1rem;
    background: linear-gradient(135deg, rgba(232, 68, 90, 0.12) 0%, rgba(159, 122, 234, 0.04) 100%);
    border: 1px solid rgba(232, 68, 90, 0.2);
    border-radius: 24px;
    padding: 1.75rem;
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
    transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
}

.creator-welcome-card:hover {
    transform: translateY(-2px);
    border-color: rgba(232, 68, 90, 0.3);
    box-shadow: 0 12px 35px rgba(232, 68, 90, 0.15);
}

.welcome-card-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1.5rem;
}

.welcome-card-text {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    max-width: 600px;
}

.welcome-tag {
    font-size: 0.75rem;
    font-weight: 800;
    color: var(--primary-color);
    letter-spacing: 0.05em;
    text-transform: uppercase;
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
}

.creator-welcome-card h2 {
    margin: 0;
    font-size: 1.5rem;
    font-weight: 800;
    color: #fff;
}

.creator-welcome-card p {
    margin: 0;
    font-size: 0.9rem;
    color: #a0aec0;
    line-height: 1.5;
}

.welcome-card-actions {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}

.action-btn-primary {
    background: var(--primary-color);
    color: #fff;
    text-decoration: none;
    padding: 0.75rem 1.5rem;
    border-radius: 14px;
    font-weight: 700;
    font-size: 0.95rem;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    box-shadow: 0 4px 12px rgba(232, 68, 90, 0.25);
}

.action-btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 18px rgba(232, 68, 90, 0.4);
    background: #f8546a;
}

.action-btn-secondary {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    color: var(--text-light);
    text-decoration: none;
    padding: 0.75rem 1.5rem;
    border-radius: 14px;
    font-weight: 700;
    font-size: 0.95rem;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.action-btn-secondary:hover {
    background: rgba(255, 255, 255, 0.1);
    border-color: var(--primary-color);
    transform: translateY(-2px);
}

@media (min-width: 1200px) {
    .creator-welcome-card {
        margin: 2rem 4rem 0 4rem;
    }
}
</style>