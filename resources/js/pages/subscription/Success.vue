<script setup lang="ts">
import UserDashboardLayout from '@/layouts/UserDashboardLayout.vue';
import { Link } from '@inertiajs/vue3';
import { CheckCircle } from 'lucide-vue-next';
import { dashboard } from '@/routes';
import ShowProfileController from '@/actions/App/Http/Controllers/UserProfile/ShowProfileController';

const props = defineProps<{
    plan: any;
}>();
</script>

<template>
    <UserDashboardLayout title="Subscription Success" headerTitle="Success">
        <div class="app-subcontainer">
            <div class="success-container">
                <div class="success-icon-container">
                    <CheckCircle class="success-icon" />
                </div>

                <h1 class="page-title">Subscription Successful!</h1>

                <div class="success-card">
                    <h2 class="success-subtitle">Welcome to {{ plan.name }}</h2>
                    <p class="success-message">
                        {{ plan.description }}.
                    </p>

                    <div v-if="plan.is_unlimited_content" class="benefits-list">
                        <div class="benefit-item">
                            <CheckCircle class="benefit-icon" />
                            <span>Unlimited content</span>
                        </div>
                    </div>
                    <div v-else class="benefits-list">
                        <div class="benefit-item">
                            <CheckCircle class="benefit-icon" />
                            <span>Upload {{ plan.movies_upload_count }} movies</span>
                        </div>
                        <div class="benefit-item">
                            <CheckCircle class="benefit-icon" />
                            <span>Upload {{ plan.series_upload_count }} series</span>
                        </div>
                        <div class="benefit-item">
                            <CheckCircle class="benefit-icon" />
                            <span>Upload {{ plan.shorts_upload_count }} shorts</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="action-buttons">
                <Link :href="dashboard()" class="action-btn primary-btn">
                Start Watching
                </Link>
                <Link :href="ShowProfileController()" class="action-btn secondary-btn">
                View My Account
                </Link>
            </div>
        </div>
    </UserDashboardLayout>
</template>

<style scoped>
.app-subcontainer {
    padding-bottom: 80px;
    max-width: 480px;
    margin: 0 auto;
    padding-left: 1.5rem;
    padding-right: 1.5rem;
}

.success-container {
    text-align: center;
    padding: 2rem 0;
}

.success-icon-container {
    margin-bottom: 2rem;
}

.success-icon {
    width: 80px;
    height: 80px;
    color: #22c55e;
    margin: 0 auto;
}

.page-title {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 2rem;
    color: var(--text-light);
    line-height: 1.2;
}

.success-card {
    background-color: #E6E6E6;
    border-radius: 1.25rem;
    padding: 2rem 1.5rem;
    color: black;
    margin-bottom: 2.5rem;
}

.success-subtitle {
    font-size: 1.75rem;
    font-weight: 600;
    margin-bottom: 1rem;
    color: var(--primary-color);
}

.success-message {
    font-size: 1.1rem;
    font-weight: 500;
    line-height: 1.5;
    margin-bottom: 2rem;
    color: #333;
}

.benefits-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    text-align: left;
}

.benefit-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-size: 1rem;
    font-weight: 500;
}

.benefit-icon {
    width: 20px;
    height: 20px;
    color: #22c55e;
    flex-shrink: 0;
}

.action-buttons {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.action-btn {
    color: white;
    border: none;
    width: 100%;
    padding: 1rem;
    border-radius: 1rem;
    font-size: 1.1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
    display: block;
    text-align: center;
}

.action-btn:hover {
    opacity: 0.9;
    transform: scale(1.02);
}

.primary-btn {
    background: var(--primary-color);
}

.secondary-btn {
    background: var(--secondary-action-bg);
}

@media (min-width: 768px) {
    .app-subcontainer {
        max-width: 600px;
        padding-left: 0;
        padding-right: 0;
    }

    .success-icon {
        width: 100px;
        height: 100px;
    }

    .page-title {
        font-size: 3rem;
        margin-bottom: 2.5rem;
    }

    .success-card {
        padding: 2.5rem 2rem;
    }

    .action-buttons {
        flex-direction: row;
        gap: 1.5rem;
        max-width: 400px;
        margin: 0 auto;
    }
}
</style>
