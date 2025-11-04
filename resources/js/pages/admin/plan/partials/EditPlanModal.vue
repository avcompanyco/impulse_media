<script setup lang="ts">
import { ref } from 'vue';
import { Form } from '@inertiajs/vue3';
import UpdatePlanController from '@/actions/App/Http/Controllers/Plan/UpdatePlanController'

interface Plan {
    id: number;
    name: string;
    price: number;
    billing_period: string;
    free_days_trial: number;
    description?: string;
    is_unlimited_content: boolean;
    movies_upload_count: number;
    series_upload_count: number;
    shorts_upload_count: number;
}

interface Props {
    plan: Plan | null;
}

const props = defineProps<Props>();

const isOpen = ref(false);

const closeModal = () => {
    isOpen.value = false;
};

</script>

<template>
    <button class="btn btn-edit" @click="isOpen = true">Edit</button>
    <Teleport to="body">
        <div id="editPlanModal" class="modal" :class="{ 'active': isOpen }">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title">Edit Plan</h2>
                    <button class="close-modal-btn" @click="closeModal">&times;</button>
                </div>
                <Form v-bind="UpdatePlanController.form(plan)" :url="`/admin/plans/${plan?.id}`" method="put" 
                    v-slot="{ errors, processing }" class="flex flex-col gap-6"
                    @success="closeModal" v-if="plan">
                    <div class="form-grid">
                        <div class="form-group-custom">
                            <label for="editPlanName" class="form-label-custom">Plan Name</label>
                            <input 
                                type="text" 
                                name="name" 
                                id="editPlanName" 
                                :value="plan?.name"
                                class="form-control-custom" 
                                required>
                            <div v-if="errors.name" class="error-text">{{ errors.name }}</div>
                        </div>
                        <div class="form-group-custom">
                            <label for="editPlanPrice" class="form-label-custom">Price ($)</label>
                            <input 
                                type="number" 
                                name="price" 
                                id="editPlanPrice" 
                                :value="plan?.price"
                                class="form-control-custom" 
                                step="0.01" 
                                min="0"
                                required>
                            <div v-if="errors.price" class="error-text">{{ errors.price }}</div>
                        </div>
                        <div class="form-group-custom">
                            <label for="editPlanBillingInterval" class="form-label-custom">Billing Interval</label>
                            <select 
                                name="billing_period" 
                                id="editPlanBillingInterval" 
                                class="form-control-custom">
                                <option value="daily" :selected="plan?.billing_period === 'daily'">Daily</option>
                                <option value="monthly" :selected="plan?.billing_period === 'monthly'">Monthly</option>
                                <option value="yearly" :selected="plan?.billing_period === 'yearly'">Annually</option>
                            </select>
                            <div v-if="errors.billing_period" class="error-text">{{ errors.billing_period }}</div>
                        </div>
                        <div class="form-group-custom">
                            <label for="editPlanTrialDays" class="form-label-custom">Free Trial (Days)</label>
                            <input 
                                type="number" 
                                name="free_days_trial" 
                                id="editPlanTrialDays" 
                                :value="plan?.free_days_trial || 0"
                                class="form-control-custom" 
                                min="0">
                            <div v-if="errors.free_days_trial" class="error-text">{{ errors.free_days_trial }}</div>
                        </div>
                        <div class="form-group-custom full-width">
                            <label for="editPlanDescription" class="form-label-custom">Description</label>
                            <textarea 
                                name="description" 
                                id="editPlanDescription" 
                                class="form-control-custom" 
                                rows="2">{{ plan?.description || '' }}</textarea>
                            <div v-if="errors.description" class="error-text">{{ errors.description }}</div>
                        </div>
                        <div class="form-group-custom full-width">
                            <div class="form-checkbox-group">
                                <input 
                                    type="checkbox" 
                                    name="is_unlimited_content" 
                                    id="editPlanUploadUnlimited" 
                                    :checked="plan?.is_unlimited_content"
                                    value="1">
                                <label for="editPlanUploadUnlimited">Unlimited Content Uploads</label>
                            </div>
                            <div class="upload-limits-group" id="editUploadLimitsGroup">
                                <div class="form-group-custom">
                                    <label for="editPlanMovieLimit" class="form-label-custom">Movie Uploads</label>
                                    <input 
                                        type="number" 
                                        name="movies_upload_count" 
                                        id="editPlanMovieLimit" 
                                        :value="plan?.movies_upload_count || 0"
                                        class="form-control-custom" 
                                        min="0">
                                    <div v-if="errors.movies_upload_count" class="error-text">{{ errors.movies_upload_count }}</div>
                                </div>
                                <div class="form-group-custom">
                                    <label for="editPlanSerieLimit" class="form-label-custom">Series Uploads</label>
                                    <input 
                                        type="number" 
                                        name="series_upload_count" 
                                        id="editPlanSerieLimit" 
                                        :value="plan?.series_upload_count || 0"
                                        class="form-control-custom" 
                                        min="0">
                                    <div v-if="errors.series_upload_count" class="error-text">{{ errors.series_upload_count }}</div>
                                </div>
                                <div class="form-group-custom">
                                    <label for="editPlanShortLimit" class="form-label-custom">Shorts Uploads</label>
                                    <input 
                                        type="number" 
                                        name="shorts_upload_count" 
                                        id="editPlanShortLimit" 
                                        :value="plan?.shorts_upload_count || 0"
                                        class="form-control-custom" 
                                        min="0">
                                    <div v-if="errors.shorts_upload_count" class="error-text">{{ errors.shorts_upload_count }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-actions">
                        <button type="button" class="action-button" 
                            style="background-color:#6c757d;" @click="closeModal">Cancel</button>
                        <button type="submit" class="action-button" :disabled="processing">
                            <i class="fa-solid fa-circle-notch fa-spin" v-if="processing"></i>
                            Update Plan
                        </button>
                    </div>
                </Form>
            </div>
        </div>
    </Teleport>
</template>

<style scoped>
.btn {
    font-size: 0.8rem;
    padding: 0.4rem 0.8rem;
    border-radius: var(--border-radius-sm);
    border: none;
    cursor: pointer;
    color: white;
    transition: background-color 0.2s, transform 0.2s;
}

.btn:hover {
    transform: translateY(-1px);
}

.btn-edit {
    background: var(--secondary-color);
}

.btn-edit:hover {
    background: var(--secondary-color-hover);
}
.action-button {
    background-color: var(--primary-color);
    color: white;
    padding: 0.7rem 1.4rem;
    border-radius: var(--border-radius-sm);
    text-decoration: none;
    font-weight: 500;
    font-size: 0.95rem;
    transition: background-color 0.2s ease, transform 0.2s ease;
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.action-button:hover {
    background-color: var(--primary-color-hover);
    transform: translateY(-1px);
}

.action-button:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
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
    border-radius: var(--border-radius-md);
    width: 90%;
    max-width: 700px;
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

.form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
}

.form-group-custom {
    margin-bottom: 1rem;
}

.form-group-custom.full-width {
    grid-column: 1 / -1;
}

.form-label-custom {
    font-size: 0.95rem;
    font-weight: 500;
    margin-bottom: 0.4rem;
    color: var(--text-muted);
    display: block;
}

.form-control-custom {
    background: var(--input-bg);
    border: 1px solid var(--input-bg);
    color: var(--text-dark-on-light-bg);
    padding: 0.8rem 1rem;
    border-radius: var(--border-radius-sm);
    font-size: 0.95rem;
    width: 100%;
    box-sizing: border-box;
}

.form-checkbox-group {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-top: 0.5rem;
}

.form-checkbox-group input[type="checkbox"] {
    accent-color: var(--primary-color);
}

.form-checkbox-group label {
    font-size: 0.9rem;
    color: var(--text-muted);
}

.upload-limits-group {
    border-left: 3px solid var(--border-color);
    padding-left: 1rem;
    margin-top: 0.5rem;
}

.modal-actions {
    margin-top: 2rem;
    display: flex;
    justify-content: flex-end;
    gap: 1rem;
}

.error-text {
    color: #ef4444;
    font-size: 0.875rem;
    margin-top: 0.25rem;
}
</style>
