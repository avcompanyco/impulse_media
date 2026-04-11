<script setup lang="ts">
import { router, Form } from '@inertiajs/vue3';

import { checkout as checkoutAction } from '@/actions/App/Http/Controllers/SubscriptionController';
import AuthenticatedSessionController from '@/actions/App/Http/Controllers/Auth/AuthenticatedSessionController';


interface Props {
    message?: string;
    title?: string;
    plans?: any[];
    url?: string;
}

defineProps<Props>();

function logout() {
    router.post(AuthenticatedSessionController.destroy(), {
        _method: 'delete',
    });
}


</script>

<template>
    <div class="app-container">
        <img src="/images/logo.png" alt="Logo" class="logo-icon">

        <h1 class="plans-title">SELECT YOUR PLAN</h1>

        <div class="plan-cards-container" style="margin-bottom: 2rem;">
            <div v-for="plan in plans" :key="`plan_${plan.id}`" class="plan-card">
                <div v-if="plan.free_days_trial > 0" class="trial-badge">
                    🎁 {{ plan.free_days_trial }} Days Free Trial
                </div>
                <h2 class="plan-name">{{ plan.name }}</h2>
                <p class="plan-description">{{ plan.description }}</p>
                <div class="plan-price" style="text-transform: uppercase;">{{ plan.price_formatted }}</div>
                <div v-if="plan.billing_period" class="plan-billing">/ {{ plan.billing_period }}</div>
                <ul class="plan-features">
                    <li v-if="plan.has_ads === false">✓ Ad-free experience</li>
                    <li v-if="plan.has_ads === true">• Includes advertisements</li>
                    <li v-if="plan.is_unlimited_content">✓ Unlimited content uploads</li>
                    <li v-if="!plan.is_unlimited_content && plan.movies_upload_count">✓ {{ plan.movies_upload_count }} movies</li>
                    <li v-if="!plan.is_unlimited_content && plan.series_upload_count">✓ {{ plan.series_upload_count }} series</li>
                    <li v-if="!plan.is_unlimited_content && plan.shorts_upload_count">✓ {{ plan.shorts_upload_count }} shorts</li>
                </ul>
                <a :href="checkoutAction.url({ plan: plan.id })" class="btn start-button" role="button">
                    {{ plan.free_days_trial > 0 ? 'START FREE TRIAL' : 'START NOW' }}
                </a>
            </div>
        </div>
        <Form 
            :action="AuthenticatedSessionController.destroy()"
            method="delete"
            v-slot="{ errors, processing }"
            >
            <button type="submit" class="btn start-button" :disabled="processing"> 
                <i class="fa-solid fa-circle-notch fa-spin" v-if="processing"></i>
                {{ processing ? 'Logging out...' : 'Logout' }}
            </button>
        </Form>
    </div>
</template>
<style scoped>
.app-container {
    width: 100%;
    max-width: 420px;
    margin: 0 auto;
    padding: 2rem;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.logo-icon {
    width: 48px;
    height: 48px;
    margin-bottom: 2rem;
}

.plans-title {
    font-size: 2rem;
    font-weight: 600;
    text-align: center;
    margin-bottom: 3rem;
    letter-spacing: -0.5px;
}

.plan-card {
    background-color: #F06292;
    border-radius: 24px;
    padding: 2rem;
    text-align: center;
    margin-bottom: 1.5rem;
    width: 100%;
    transition: transform 0.3s ease;
}

.plan-card:hover {
    transform: translateY(-5px);
}

.plan-name {
    font-size: 1.75rem;
    font-weight: 600;
    margin-bottom: 1rem;
    color: white;
}

.plan-description {
    font-size: 1.1rem;
    color: white;
    margin-bottom: 1.5rem;
    line-height: 1.4;
    padding: 0 1rem;
}

.plan-price {
    font-size: 2rem;
    font-weight: 700;
    color: white;
    margin-bottom: 0.25rem;
}

.plan-billing {
    font-size: 0.9rem;
    color: rgba(255, 255, 255, 0.8);
    margin-bottom: 1rem;
    text-transform: capitalize;
}

.trial-badge {
    background: rgba(255, 255, 255, 0.2);
    color: white;
    font-size: 0.85rem;
    font-weight: 700;
    padding: 0.4rem 0.8rem;
    border-radius: 20px;
    margin-bottom: 1rem;
    display: inline-block;
    backdrop-filter: blur(4px);
}

.plan-features {
    list-style: none;
    padding: 0;
    margin: 0 0 1.5rem 0;
    text-align: left;
    width: 100%;
}

.plan-features li {
    color: rgba(255, 255, 255, 0.9);
    font-size: 0.9rem;
    padding: 0.3rem 1rem;
    line-height: 1.5;
}

.start-button {
    background-color: #DC3545;
    color: white;
    border: none;
    border-radius: 30px;
    padding: 0.875rem 2.5rem;
    font-size: 1.1rem;
    font-weight: 500;
    transition: all 0.3s ease;
}

.start-button:hover {
    transform: scale(1.05);
    background-color: #bb2d3b;
    color: white;
}

/* Tablet */
@media (min-width: 768px) {
    .app-container {
        max-width: 680px;
    }

    .logo-icon {
        width: 56px;
        height: 56px;
        margin-bottom: 2.5rem;
    }

    .plans-title {
        font-size: 2.25rem;
        margin-bottom: 4rem;
    }

    .plan-cards-container {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 2rem;
        width: 100%;
    }

    .plan-card {
        margin-bottom: 0;
    }
}

/* Desktop */
@media (min-width: 1200px) {
    .app-container {
        max-width: 900px;
    }

    .logo-icon {
        width: 64px;
        height: 64px;
        margin-bottom: 3rem;
    }

    .plans-title {
        font-size: 2.5rem;
    }

    .plan-card {
        padding: 2.5rem;
    }

    .plan-name {
        font-size: 2rem;
    }

    .plan-description {
        font-size: 1.2rem;
    }

    .plan-price {
        font-size: 2.25rem;
    }
}

/* Mobile small */
@media (max-width: 400px) {
    .app-container {
        padding: 1.5rem;
    }

    .plans-title {
        font-size: 1.75rem;
        margin-bottom: 2.5rem;
    }

    .plan-card {
        padding: 1.5rem;
    }

    .plan-name {
        font-size: 1.5rem;
    }

    .plan-description {
        font-size: 1rem;
    }

    .plan-price {
        font-size: 1.75rem;
    }
}
</style>