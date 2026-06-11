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

        <div class="plan-cards-container" :class="`plans-${plans?.length || 0}`" style="margin-bottom: 2rem;">
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
    background: linear-gradient(145deg, rgba(35,35,50,0.95), rgba(25,25,35,0.98));
    border: 2px solid rgba(255,255,255,0.08);
    border-radius: 24px;
    padding: 2.25rem 1.75rem;
    text-align: center;
    margin-bottom: 1.5rem;
    width: 100%;
    transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
}

.plan-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #e8445a, #f5c518, #e8445a);
    opacity: 0.7;
}

.plan-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 16px 48px rgba(0,0,0,0.4);
    border-color: rgba(232, 68, 90, 0.3);
}

.plan-name {
    font-size: 1.6rem;
    font-weight: 800;
    margin-bottom: 0.75rem;
    color: white;
    letter-spacing: -0.02em;
}

.plan-description {
    font-size: 0.95rem;
    color: rgba(255,255,255,0.6);
    margin-bottom: 1.25rem;
    line-height: 1.5;
    padding: 0 0.5rem;
}

.plan-price {
    font-size: 2.2rem;
    font-weight: 900;
    background: linear-gradient(135deg, #e8445a, #ff6b81);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin-bottom: 0.15rem;
    line-height: 1.2;
}

.plan-billing {
    font-size: 0.85rem;
    color: rgba(255, 255, 255, 0.45);
    margin-bottom: 1rem;
    text-transform: capitalize;
    font-weight: 500;
}

.trial-badge {
    background: rgba(245, 197, 24, 0.12);
    color: #f5c518;
    font-size: 0.85rem;
    font-weight: 700;
    padding: 0.45rem 1rem;
    border-radius: 20px;
    margin-bottom: 1.25rem;
    display: inline-block;
    border: 1px solid rgba(245, 197, 24, 0.2);
}

.plan-features {
    list-style: none;
    padding: 0;
    margin: 0 0 1.75rem 0;
    text-align: left;
    width: 100%;
    border-top: 1px solid rgba(255,255,255,0.06);
    padding-top: 1rem;
}

.plan-features li {
    color: rgba(255, 255, 255, 0.75);
    font-size: 0.9rem;
    padding: 0.35rem 0.5rem;
    line-height: 1.5;
}

.start-button {
    background: linear-gradient(135deg, #e8445a, #d63851);
    color: white;
    border: none;
    border-radius: 14px;
    padding: 0.95rem 2.5rem;
    font-size: 1.05rem;
    font-weight: 700;
    transition: all 0.3s ease;
    letter-spacing: 0.3px;
    width: 100%;
    box-shadow: 0 4px 15px rgba(232, 68, 90, 0.3);
}

.start-button:hover {
    transform: scale(1.02);
    background: linear-gradient(135deg, #d63851, #c22d45);
    color: white;
    box-shadow: 0 6px 20px rgba(232, 68, 90, 0.4);
}

/* Tablet */
@media (min-width: 768px) {
    .app-container {
        max-width: 100% !important;
        width: 100% !important;
        padding-left: 2rem;
        padding-right: 2rem;
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
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 2rem;
        width: 100%;
        max-width: 1200px;
        justify-content: center;
        margin: 0 auto;
    }

    .plan-cards-container.plans-3 {
        grid-template-columns: repeat(3, 1fr);
        max-width: 1100px;
    }

    .plan-cards-container.plans-2 {
        grid-template-columns: repeat(2, 1fr);
        max-width: 750px;
    }

    .plan-cards-container.plans-1 {
        grid-template-columns: 1fr;
        max-width: 380px;
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