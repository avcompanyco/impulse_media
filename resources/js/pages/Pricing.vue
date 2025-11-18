<script setup lang="ts">
import ShowRegisterUserController from '@/actions/App/Http/Controllers/Auth/User/ShowRegisterUserController';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps<{
    plans: any[];
}>();
</script>

<template>

    <Head :title="`Pricing - ${$page.props.name || 'Impulsemedia'}`">
    </Head>
    <div class="app-container">
        <Link :href="ShowRegisterUserController()" class="btn home-login-button" role="button">Start now</Link>

        <img src="/images/logo.png" alt="Logo" class="logo-icon">

        <h1 class="welcome-title">Choose Your Plan</h1>
        <h2 class="welcome-subtitle">Select the perfect plan for you</h2>

        <div class="plan-cards-container">
            <div v-for="plan in plans" :key="plan.id" class="plan-card" :class="{ 'featured': plan.name === 'Gold' }">
                <div class="plan-header">
                    <h3 class="plan-name">{{ plan.name }}</h3>
                    <p class="plan-description">{{ plan.description }}</p>
                </div>
                
                <div class="plan-price">
                    <span class="price-amount">{{ plan.price_formatted }}</span>
                    <span class="price-period">/ {{ plan.billing_period }}</span>
                </div>

                <div class="plan-features">
                    <div v-if="plan.is_unlimited_content" class="feature-item">
                        <span class="feature-icon">✓</span>
                        <span>Unlimited content</span>
                    </div>
                    <template v-else>
                        <div v-if="plan.movies_upload_count > 0" class="feature-item">
                            <span class="feature-icon">✓</span>
                            <span>{{ plan.movies_upload_count }} Movies</span>
                        </div>
                        <div v-if="plan.series_upload_count > 0" class="feature-item">
                            <span class="feature-icon">✓</span>
                            <span>{{ plan.series_upload_count }} Series</span>
                        </div>
                        <div v-if="plan.shorts_upload_count > 0" class="feature-item">
                            <span class="feature-icon">✓</span>
                            <span>{{ plan.shorts_upload_count }} Shorts</span>
                        </div>
                    </template>
                    <div v-if="plan.free_days_trial > 0" class="feature-item">
                        <span class="feature-icon">✓</span>
                        <span>{{ plan.free_days_trial }} days free trial</span>
                    </div>
                </div>

                <Link :href="ShowRegisterUserController()" class="btn select-plan-button" role="button">
                    Select Plan
                </Link>
            </div>
        </div>

    </div>
</template>

<style scoped>
.app-container {
    position: relative;
    width: 100% !important;
    max-width: 100% !important;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 2rem 1rem;
}

.home-login-button {
    position: absolute;
    top: 20px;
    right: 20px;
    background-color: white;
    color: #0A0A23;
    border: none;
    border-radius: 30px;
    padding: 0.5rem 2rem;
    font-size: 1rem;
    font-weight: 500;
    transition: all 0.3s ease;
}

.home-login-button:hover {
    transform: scale(1.05);
    background-color: white;
    color: #0A0A23;
}

.logo-icon {
    width: 48px;
    height: 48px;
    margin-bottom: 2rem;
}

.welcome-title {
    font-size: 2rem;
    font-weight: 600;
    text-align: center;
    margin-bottom: 0.5rem;
    letter-spacing: -0.5px;
}

.welcome-subtitle {
    font-size: 1.25rem;
    font-weight: 400;
    text-align: center;
    margin-bottom: 3rem;
    letter-spacing: -0.5px;
    opacity: 0.8;
}

.plan-cards-container {
    display: grid;
    grid-template-columns: 1fr;
    gap: 2rem;
    width: 100%;
    max-width: 1200px;
    margin-bottom: 2rem;
}

.plan-card {
    background-color: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 20px;
    padding: 2rem;
    display: flex;
    flex-direction: column;
    transition: all 0.3s ease;
}

.plan-card:hover {
    transform: translateY(-5px);
    border-color: rgba(255, 255, 255, 0.2);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
}

.plan-card.featured {
    border-color: #DC3545;
    background-color: rgba(220, 53, 69, 0.05);
}

.plan-header {
    margin-bottom: 1.5rem;
}

.plan-name {
    font-size: 1.75rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.plan-description {
    font-size: 0.95rem;
    opacity: 0.7;
    margin: 0;
}

.plan-price {
    margin-bottom: 2rem;
    padding-bottom: 2rem;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.price-amount {
    font-size: 2.5rem;
    font-weight: 700;
}

.price-period {
    font-size: 1rem;
    opacity: 0.7;
}

.plan-features {
    flex-grow: 1;
    margin-bottom: 2rem;
}

.feature-item {
    display: flex;
    align-items: center;
    margin-bottom: 1rem;
    font-size: 1rem;
}

.feature-icon {
    color: #DC3545;
    font-weight: bold;
    margin-right: 0.75rem;
    font-size: 1.2rem;
}

.select-plan-button {
    background-color: #DC3545;
    color: white;
    border: none;
    border-radius: 30px;
    padding: 0.875rem 2rem;
    font-size: 1rem;
    font-weight: 500;
    transition: all 0.3s ease;
    text-align: center;
    width: 100%;
}

.select-plan-button:hover {
    transform: scale(1.02);
    background-color: #bb2d3b;
    color: white;
}

.plan-card.featured .select-plan-button {
    background-color: #DC3545;
}

/* Tablet */
@media (min-width: 768px) {
    .logo-icon {
        width: 60px;
        height: 60px;
        margin-bottom: 2.5rem;
    }

    .welcome-title {
        font-size: 2.5rem;
    }

    .welcome-subtitle {
        font-size: 1.5rem;
    }

    .plan-cards-container {
        grid-template-columns: repeat(2, 1fr);
    }
}

/* Desktop */
@media (min-width: 1200px) {
    .logo-icon {
        width: 72px;
        height: 72px;
        margin-bottom: 3rem;
    }

    .welcome-title {
        font-size: 3rem;
    }

    .welcome-subtitle {
        font-size: 1.75rem;
    }

    .home-login-button {
        padding: 0.625rem 2.5rem;
        font-size: 1.1rem;
    }

    .plan-cards-container {
        grid-template-columns: repeat(3, 1fr);
    }

    .plan-card {
        padding: 2.5rem;
    }
}

/* Mobile small */
@media (max-width: 400px) {
    .app-container {
        padding: 1.5rem 1rem;
    }

    .welcome-title {
        font-size: 1.75rem;
    }

    .welcome-subtitle {
        font-size: 1.1rem;
    }

    .home-login-button {
        padding: 0.4rem 1.75rem;
        font-size: 0.9rem;
    }

    .plan-card {
        padding: 1.5rem;
    }

    .plan-name {
        font-size: 1.5rem;
    }

    .price-amount {
        font-size: 2rem;
    }
}
</style>
