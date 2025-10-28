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
                <h2 class="plan-name">{{ plan.name }}</h2>
                <p class="plan-description">{{ plan.description }}</p>
                <div class="plan-price" style="text-transform: uppercase;">{{ plan.price_formatted }}</div>
                <a :href="checkoutAction.url({ plan: plan.id })" class="btn start-button" role="button">
                START NOW
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
    margin-bottom: 1.5rem;
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