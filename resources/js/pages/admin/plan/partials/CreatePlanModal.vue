<script setup lang="ts">
import { ref } from 'vue';
import { Form } from '@inertiajs/vue3';
import StorePlanController from '@/actions/App/Http/Controllers/Plan/StorePlanController'

const isOpen = ref(false);
const isUnlimitedChecked = ref(false);

</script>

<template>
    <button class="action-button" id="addNewPlanBtn" @click="isOpen = true">+ Add New Plan</button>

    <Teleport to="body">
        <div id="planModal" class="modal" :class="{ 'active': isOpen }">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title" id="planModalTitle">Add New Plan</h2>
                    <button class="close-modal-btn" id="closePlanModal" @click="isOpen = false">&times;</button>
                </div>
                <Form v-bind="StorePlanController.form()" :reset-on-success="['name','price','billing_period','free_days_trial','is_unlimited_content','movies_upload_count','series_upload_count','shorts_upload_count']"
                    v-slot="{ errors, processing }">
                    <input type="hidden" id="planEditId">
                    <div class="form-grid">
                        <div class="form-group-custom">
                            <label for="planName" class="form-label-custom">Plan Name</label>
                            <input type="text" name="name" id="planName" class="form-control-custom" required>
                        </div>
                        <div class="form-group-custom">
                            <label for="planPrice" class="form-label-custom">Price ($)</label>
                            <input type="number" name="price" id="planPrice" class="form-control-custom" step="0.01" min="0"
                                required>
                        </div>
                        <div class="form-group-custom">
                            <label for="planBillingInterval" class="form-label-custom">Billing Interval</label>
                            <select name="billing_period" id="planBillingInterval" class="form-control-custom">
                                <option value="daily">Daily</option>
                                <option value="monthly" selected>Monthly</option>
                                <option value="yearly">Annually</option>
                            </select>
                        </div>
                        <div class="form-group-custom">
                            <label for="planTrialDays" class="form-label-custom">Free Trial (Days)</label>
                            <input type="number" name="free_days_trial" id="planTrialDays" class="form-control-custom" min="0" value="0">
                        </div>
                        <div class="form-group-custom full-width">
                            <label for="planDescription" class="form-label-custom">Description</label>
                            <textarea name="description" id="planDescription" class="form-control-custom" rows="2"></textarea>
                        </div>
                        <div class="form-group-custom full-width">
                            <div class="form-checkbox-group">
                                <input type="checkbox" name="is_unlimited_content" id="planUploadUnlimited" value="1" v-model="isUnlimitedChecked">
                                <label for="planUploadUnlimited">Unlimited Content Uploads</label>
                            </div>
                            <div class="upload-limits-group" id="uploadLimitsGroup" v-if="!isUnlimitedChecked">
                                <div class="form-group-custom">
                                    <label for="planMovieLimit" class="form-label-custom">Movie Uploads</label>
                                    <input type="number" name="movies_upload_count" id="planMovieLimit" class="form-control-custom" min="0"
                                        value="0">
                                </div>
                                <div class="form-group-custom">
                                    <label for="planSerieLimit" class="form-label-custom">Series Uploads</label>
                                    <input type="number" name="series_upload_count" id="planSerieLimit" class="form-control-custom" min="0"
                                        value="0">
                                </div>
                                <div class="form-group-custom">
                                    <label for="planShortLimit" class="form-label-custom">Shorts Uploads</label>
                                    <input type="number" name="shorts_upload_count" id="planShortLimit" class="form-control-custom" min="0"
                                        value="0">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-actions">
                        <button type="button" class="action-button" id="cancelPlanForm"
                            style="background-color:#6c757d;" @click="isOpen = false">Cancel</button>
                        <button type="submit" class="action-button" id="savePlanBtn">
                            <i class="fa-solid fa-circle-notch fa-spin" v-if="processing"></i>
                            Save Plan
                        </button>
                    </div>
                </Form>
            </div>
        </div>
    </Teleport>
</template>

<style scoped>
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
</style>