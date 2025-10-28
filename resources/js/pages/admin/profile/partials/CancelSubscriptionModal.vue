<script setup lang="ts">
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import ActionButton from '@/components/app/ActionButton.vue';
import CancelSubscriptionProfileController from '@/actions/App/Http/Controllers/UserProfile/CancelSubscriptionProfileController';

const props = defineProps<{
    subscription: any;
}>();

const isOpen = ref(false);
const cancelForm = useForm({});

function openModal() {
    isOpen.value = true;
}

function closeModal() {
    isOpen.value = false;
}

function cancelSubscription() {
    cancelForm.post(CancelSubscriptionProfileController.url(), {
        preserveScroll: true,
        onSuccess: () => {
            closeModal();
        },
        onError: (errors) => {
            console.error('Error cancelling subscription:', errors);
        }
    });
}

defineExpose({
    openModal
});
</script>

<template>
    <button 
        type="button" 
        class="action-btn destructive-btn-custom"
        :disabled="!subscription?.active || subscription?.cancelled"
        @click="openModal"
        v-if="subscription && !subscription.cancelled"
    >
        Cancel Subscription
    </button>
    
    <teleport to="body">
        <div id="cancelSubscriptionModal" class="modal" :class="{ 'active': isOpen }">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title" id="cancelSubscriptionModalTitle">Cancel Subscription</h2>
                    <button class="close-modal-btn" id="closeCancelSubscriptionModal" @click="closeModal">&times;</button>
                </div>
                
                <div class="modal-body">
                    <p class="warning-text">
                        Are you sure you want to cancel your subscription?
                    </p>
                    <p class="warning-subtitle">
                        You will continue to have access to your current plan's benefits until the end of your billing period. 
                        Your subscription will not auto-renew.
                    </p>
                    <div v-if="subscription?.ends_at" class="billing-info">
                        <p class="info-text">
                            <strong>Access until:</strong> {{ new Date(subscription.ends_at).toLocaleDateString() }}
                        </p>
                    </div>
                </div>

                <div class="modal-actions">
                    <ActionButton 
                        type="button" 
                        class="action-button" 
                        @click="closeModal" 
                        style="background-color: #6c757d;"
                    >
                        Keep Subscription
                    </ActionButton>

                    <ActionButton 
                        type="submit" 
                        class="action-button" 
                        :processing="cancelForm.processing"
                        @click="cancelSubscription"
                        style="background-color: var(--destructive-action-bg, #dc3545);"
                    >
                        <i class="fa-solid fa-circle-notch fa-spin" v-if="cancelForm.processing"></i>
                        Cancel Subscription
                    </ActionButton>
                </div>
            </div>
        </div>
    </teleport>
</template>

<style scoped>
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

.destructive-btn-custom {
    background: var(--destructive-action-bg, #dc3545);
}

.destructive-btn-custom:hover {
    background: #c82333;
    opacity: 0.9;
    transform: scale(1.01);
}

.destructive-btn-custom:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.destructive-btn-custom:disabled:hover {
    opacity: 0.5;
    transform: scale(1);
}

.action-button {
    background-color: var(--primary-color);
    color: white;
    padding: 0.7rem 1.4rem;
    border-radius: var(--border-radius-sm, 8px);
    text-decoration: none;
    font-weight: 500;
    font-size: 0.95rem;
    transition: background-color 0.2s ease, transform 0.2s ease;
    border: none;
    cursor: pointer;
}

.action-button:hover {
    background-color: var(--primary-color-hover);
    transform: translateY(-1px);
}

.modal {
    display: none;
    position: fixed;
    z-index: 1001;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(10, 10, 35, 0.8);
    align-items: flex-start;
    justify-content: center;
    padding-top: 5vh;
}

.modal.active {
    display: flex;
}

.modal-content {
    background-color: var(--sidebar-bg);
    color: var(--text-light);
    padding: 2rem;
    border: 1px solid var(--border-color);
    border-radius: var(--border-radius-md, 12px);
    width: 90%;
    max-width: 500px;
    box-shadow: var(--shadow-md);
    position: relative;
    animation: slideInModalPlatform 0.3s ease-out;
}

@keyframes slideInModalPlatform {
    from {
        opacity: 0;
        transform: scale(0.95) translateY(-20px);
    }

    to {
        opacity: 1;
        transform: scale(1) translateY(0);
    }
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid var(--border-color);
}

.modal-title {
    font-size: 1.5rem;
    font-weight: 600;
    margin: 0;
    color: var(--text-headings);
}

.close-modal-btn {
    background: none;
    border: none;
    font-size: 1.8rem;
    color: var(--text-muted);
    cursor: pointer;
    line-height: 1;
}

.close-modal-btn:hover {
    color: var(--text-light);
}

.modal-body {
    margin-bottom: 1.5rem;
}

.warning-text {
    font-size: 1rem;
    color: var(--text-light);
    margin-bottom: 0.75rem;
    line-height: 1.5;
    font-weight: 600;
}

.warning-subtitle {
    font-size: 0.9rem;
    color: var(--text-muted);
    margin-bottom: 1rem;
    line-height: 1.4;
}

.billing-info {
    background: rgba(255, 193, 7, 0.1);
    border: 1px solid #ffc107;
    border-radius: 8px;
    padding: 1rem;
    margin-top: 1rem;
}

.info-text {
    font-size: 0.9rem;
    color: var(--text-light);
    margin: 0;
}

.modal-actions {
    margin-top: 2rem;
    display: flex;
    justify-content: flex-end;
    gap: 1rem;
}
</style>
