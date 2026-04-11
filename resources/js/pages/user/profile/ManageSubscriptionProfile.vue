<script setup lang="ts">
import { ref } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import UserDashboardLayout from '@/layouts/UserDashboardLayout.vue';

import CancelSubscriptionModal from './partials/CancelSubscriptionModal.vue';
import UpdateSubscriptionProfileController from '@/actions/App/Http/Controllers/UserProfile/UpdateSubscriptionProfileController';

const props = defineProps<{
    user: any;
    plans: any[];
    my_plan: any;
    subscription: any;
    subscription_status: string;
}>();

const planSelected = ref(props.my_plan);

function selectPlan(plan: any) {
    planSelected.value = plan;
}

// Form for updating subscription plan
const updateForm = useForm({
    plan_id: planSelected.value?.id || null,
});

// Form for cancelling subscription
const cancelForm = useForm({});

function updatePlan() {
    if (!planSelected.value || planSelected.value.id === props.my_plan?.id) {
        return;
    }

    updateForm.plan_id = planSelected.value.id;
    
    updateForm.post(UpdateSubscriptionProfileController.url(), {
        preserveScroll: true,
        onSuccess: () => {
            // Success handled by flash messages
        },
        onError: (errors) => {
            console.error('Error updating plan:', errors);
        }
    });
}

</script>

<template>
    <UserDashboardLayout 
        :title="`Manage Subscription - ${$page.props.name || 'Impulsemedia'}`" 
        :headerTitle="`Manage Subscription - ${$page.props.name || 'Impulsemedia'}`">
        <div class="manage-wrapper" style="margin-bottom: 80px;">
            <h1 class="page-title">Manage Subscription</h1>

            <section class="subscription-page-section">
                <h2 class="section-title-custom">Your Plan</h2>
                <p class="current-info-display">Current Plan: <strong id="currentPlanNameDisplay">{{ my_plan?.name || 'No Plan' }}</strong></p>
                
                <!-- Subscription Status Information -->
                <div v-if="subscription" class="subscription-status">
                    <p class="info-text-sm">
                        <strong>Status:</strong> 
                        <span :class="{ 
                            'text-success': subscription.active, 
                            'text-warning': subscription.cancelled,
                            'text-info': subscription.on_trial 
                        }">
                            {{ subscription.on_trial ? 'On Trial' : subscription.cancelled ? 'Cancelled' : ` ${subscription.stripe_status}` }}
                        </span>
                    </p>
                    <p v-if="subscription.trial_ends_at" class="info-text-sm">
                        <strong>Trial ends:</strong> {{ new Date(subscription.trial_ends_at).toLocaleDateString() }}
                    </p>
                    <p v-if="subscription.ends_at" class="info-text-sm">
                        <strong>Subscription ends:</strong> {{ new Date(subscription.ends_at).toLocaleDateString() }}
                    </p>
                </div>
                <div v-else class="subscription-status">
                    <p class="info-text-sm text-warning">
                        <strong>Status:</strong> No active subscription
                    </p>
                </div>

                <!-- Plan Cards -->
                <div class="plan-cards-grid">
                    <div 
                        v-for="plan in plans" :key="`plans_to_select_${plan.id}`" 
                        class="plan-detail-card"
                        :class="{ 'selected': planSelected?.id === plan.id, 'current': my_plan?.id === plan.id }"
                        @click="selectPlan(plan)"
                    >
                        <div v-if="my_plan?.id === plan.id" class="current-badge">Current</div>
                        <h3 class="plan-card-name">{{ plan.name }}</h3>
                        <div class="plan-card-price">{{ plan.price_formatted }}</div>
                        <div v-if="plan.billing_period" class="plan-card-period">/ {{ plan.billing_period }}</div>
                        <p v-if="plan.description" class="plan-card-desc">{{ plan.description }}</p>
                        <div v-if="plan.free_days_trial > 0" class="plan-trial-info">
                            🎁 {{ plan.free_days_trial }} days free trial
                        </div>
                        <ul class="plan-card-features">
                            <li v-if="plan.has_ads === false">✓ No ads</li>
                            <li v-if="plan.has_ads === true">• With advertisements</li>
                            <li v-if="plan.is_unlimited_content">✓ Unlimited uploads</li>
                            <li v-if="!plan.is_unlimited_content && plan.movies_upload_count">✓ {{ plan.movies_upload_count }} movies</li>
                            <li v-if="!plan.is_unlimited_content && plan.series_upload_count">✓ {{ plan.series_upload_count }} series</li>
                            <li v-if="!plan.is_unlimited_content && plan.shorts_upload_count">✓ {{ plan.shorts_upload_count }} shorts</li>
                        </ul>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div v-if="planSelected && planSelected.id !== my_plan?.id">
                    <!-- If user has active subscription: swap plan -->
                    <button 
                        v-if="subscription && subscription.active"
                        type="button" 
                        id="updatePlanButton" 
                        class="action-btn save-btn-custom"
                        :disabled="updateForm.processing"
                        @click="updatePlan"
                    >
                        <span v-if="updateForm.processing">Updating...</span>
                        <span v-else>Switch to {{ planSelected.name }}</span>
                    </button>

                    <!-- If user has NO subscription: redirect to Stripe checkout -->
                    <a 
                        v-else
                        :href="`/subscription/checkout/${planSelected.id}`"
                        class="action-btn save-btn-custom"
                        style="text-decoration: none;"
                    >
                        Subscribe to {{ planSelected.name }}
                    </a>
                </div>
            </section>

            <!-- <section class="subscription-page-section">
                <h2 class="section-title-custom">Payment Method</h2>
                <div id="currentPaymentMethodDisplay" class="current-info-display">
                    Card: <strong>Visa ending in 1234</strong><br>
                    Expires: <strong>12/2026</strong>
                </div>

                <form id="paymentMethodForm">
                    <div class="form-group-custom">
                        <label for="cardName" class="form-label-custom">Name on Card</label>
                        <input type="text" class="form-control-custom" id="cardName"
                            placeholder="Full name as it appears on card">
                    </div>
                    <div class="form-group-custom">
                        <label for="cardNumber" class="form-label-custom">Card Number</label>
                        <input type="text" class="form-control-custom" id="cardNumber"
                            placeholder="•••• •••• •••• ••••">
                    </div>
                    <div class="form-row-flex">
                        <div class="form-group-custom">
                            <label for="cardExpiry" class="form-label-custom">Expiry Date</label>
                            <input type="text" class="form-control-custom" id="cardExpiry" placeholder="MM/YY">
                        </div>
                        <div class="form-group-custom">
                            <label for="cardCVV" class="form-label-custom">CVV</label>
                            <input type="text" class="form-control-custom" id="cardCVV" placeholder="•••">
                        </div>
                    </div>
                    <button type="submit" class="action-btn save-btn-custom">Update Payment Method</button>
                </form>
            </section> -->

            <section class="subscription-page-section" v-if="subscription || my_plan">
                <h2 class="section-title-custom">Cancel Subscription</h2>
                <p class="info-text-sm">
                    If you cancel, you'll retain access to your current plan's benefits until the end of your billing
                    period.
                    Your subscription will not auto-renew.
                </p>
                <CancelSubscriptionModal :subscription="subscription" />
                
                <div v-if="subscription?.cancelled" class="cancelled-info">
                    <p class="info-text-sm text-warning">
                        <strong>Subscription Cancelled:</strong> Your subscription will end on {{ new Date(subscription.ends_at).toLocaleDateString() }}
                    </p>
                </div>
            </section>

        </div>
        <br />
    </UserDashboardLayout>
</template>

<style scoped>
/* Main Content */
.manage-wrapper {
    max-width: 600px;
    margin: 0 auto;
    padding: 0 1.5rem;
}

.page-title {
    font-size: 2.5rem;
    font-weight: 700;
    margin-top: 1rem;
    margin-bottom: 1.5rem;
    color: var(--text-light);
    text-align: center;
}

.subscription-page-section {
    margin-bottom: 3rem;
    padding-top: 1.5rem;
}

.subscription-page-section:first-of-type {
    padding-top: 0;
}

.section-title-custom {
    font-size: 1.75rem;
    font-weight: 700;
    color: var(--text-light);
    margin-bottom: 1.25rem;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid var(--border-light);
}

.current-info-display {
    font-size: 1rem;
    color: #e0e0e0;
    margin-bottom: 1.5rem;
    line-height: 1.6;
}

.current-info-display strong {
    font-weight: 600;
    color: var(--text-light);
}

.plan-cards-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.plan-detail-card {
    background: var(--input-bg, rgba(255,255,255,0.08));
    border: 2px solid transparent;
    border-radius: 16px;
    padding: 1.25rem;
    cursor: pointer;
    transition: all 0.3s ease;
    position: relative;
    text-align: center;
}

.plan-detail-card:hover {
    border-color: rgba(240, 98, 146, 0.3);
    transform: translateY(-2px);
}

.plan-detail-card.selected {
    border-color: var(--primary-color);
    background: rgba(240, 98, 146, 0.08);
    box-shadow: 0 0 15px rgba(240, 98, 146, 0.2);
}

.plan-detail-card.current {
    border-color: rgba(40, 167, 69, 0.3);
}

.current-badge {
    position: absolute;
    top: -10px;
    right: 12px;
    background: #28a745;
    color: white;
    font-size: 0.7rem;
    font-weight: 700;
    padding: 0.2rem 0.6rem;
    border-radius: 10px;
    text-transform: uppercase;
}

.plan-card-name {
    font-size: 1.2rem;
    font-weight: 700;
    color: var(--text-light);
    margin-bottom: 0.5rem;
}

.plan-card-price {
    font-size: 1.5rem;
    font-weight: 800;
    color: var(--primary-color);
    margin-bottom: 0.1rem;
}

.plan-card-period {
    font-size: 0.8rem;
    color: var(--text-muted);
    margin-bottom: 0.75rem;
    text-transform: capitalize;
}

.plan-card-desc {
    font-size: 0.85rem;
    color: #b0b0b0;
    margin-bottom: 0.75rem;
    line-height: 1.4;
}

.plan-trial-info {
    font-size: 0.8rem;
    font-weight: 600;
    color: #f5c518;
    margin-bottom: 0.75rem;
}

.plan-card-features {
    list-style: none;
    padding: 0;
    margin: 0;
    text-align: left;
}

.plan-card-features li {
    font-size: 0.8rem;
    color: #ccc;
    padding: 0.15rem 0;
}

#planDescriptionDisplay {
    text-align: center;
    margin-top: 1.25rem;
    min-height: 40px;
    color: #ccc;
    font-style: italic;
}

.form-label-custom {
    font-size: 1.1rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: var(--text-light);
    display: block;
}

.form-control-custom {
    background: var(--input-bg);
    border: none;
    color: var(--text-dark);
    padding: 0.9rem 1rem;
    border-radius: 15px;
    font-size: 1rem;
    width: 100%;
    box-sizing: border-box;
}

.form-control-custom::placeholder {
    color: rgba(0, 0, 0, 0.4);
    font-weight: 400;
}

.form-control-custom:focus {
    outline: 2px solid var(--primary-color);
    outline-offset: 2px;
}

.form-group-custom {
    margin-bottom: 1.5rem;
}

.form-row-flex {
    display: flex;
    gap: 1rem;
}

.form-row-flex>div {
    flex-grow: 1;
}

.info-text-sm {
    font-size: 0.9rem;
    color: #b0b0b0;
    display: block;
    margin-bottom: 1.5rem;
    font-weight: 400;
    line-height: 1.5;
}

.action-btn {
    color: white;
    border: none;
    width: 100%;
    padding: 0.85rem;
    border-radius: 15px;
    font-size: 1.1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
    display: block;
    text-align: center;
    margin-bottom: 1rem;
}

.action-btn.disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.action-btn:hover {
    opacity: 0.9;
    transform: scale(1.01);
}

.action-btn.disabled:hover {
    opacity: 0.5;
    transform: scale(1);
}

.save-btn-custom {
    background: var(--primary-color);
    margin-top: 1rem;
}

.destructive-btn-custom {
    background: var(--destructive-action-bg);
}

.destructive-btn-custom:hover {
    background: #c82333;
}

.manage-wrapper>section:last-of-type .action-btn:last-of-type {
    margin-bottom: 7rem;
}

/* Bottom Navigation */
.bottom-nav {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    background: var(--main-bg);
    padding: 1rem;
    display: flex;
    justify-content: space-around;
    border-top: 1px solid var(--border-light);
    z-index: 1000;
}

.nav-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    color: var(--text-light);
    text-decoration: none;
    font-size: 0.8rem;
    gap: 4px;
}

.nav-item.active {
    color: var(--primary-color);
}

.nav-icon {
    width: 24px;
    height: 24px;
}

.nav-item .profile-icon {
    border-radius: 50%;
}

/* Status text colors */
.text-success {
    color: #28a745;
}

.text-warning {
    color: #ffc107;
}

.text-info {
    color: #17a2b8;
}

.text-error {
    color: #dc3545;
}

.subscription-status {
    margin-bottom: 1rem;
    padding: 1rem;
    background: rgba(255, 255, 255, 0.05);
    border-radius: 10px;
}

.cancelled-info {
    margin-top: 1rem;
    padding: 1rem;
    background: rgba(255, 193, 7, 0.1);
    border: 1px solid #ffc107;
    border-radius: 10px;
}
</style>