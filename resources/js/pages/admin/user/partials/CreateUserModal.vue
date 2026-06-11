<script setup lang="ts">
import { ref, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import type { Plan } from '@/types';

const props = defineProps<{
    plans: Plan[];
    statusOptions: Record<string, string>;
}>();

const isOpen = ref(false);

const form = useForm({
    name: '',
    username: '',
    email: '',
    password: '',
    password_confirmation: '',
    status: 'active',
    plan_id: null as number | null,
    trial_days: 0,
    user_type: 'spectator',
});

const submitForm = () => {
    form.post('/admin/users', {
        preserveScroll: true,
        onSuccess: () => {
            closeModal();
            form.reset();
        },
        onError: () => {
            // Errors will be handled by the form validation
        }
    });
};

const closeModal = () => {
    isOpen.value = false;
    form.clearErrors();
    form.reset();
};
</script>
<template>
    <button class="action-button" id="addUserBtn" @click="isOpen = true">+ Add User</button>
    <teleport to="body">
        <div id="userModal" class="modal" :class="{ 'active': isOpen }">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title" id="userModalTitle">Add New User</h2>
                    <button class="close-modal-btn" id="closeUserModal" @click="closeModal">×</button>
                </div>
                <form @submit.prevent="submitForm">
                    <div class="form-grid">
                        <div class="form-group-custom">
                            <label for="fullName" class="form-label-custom">Full Name</label>
                            <input 
                                type="text" 
                                id="fullName" 
                                class="form-control-custom" 
                                v-model="form.name"
                                :class="{ 'error': form.errors.name }"
                                required
                            >
                            <div v-if="form.errors.name" class="error-message">{{ form.errors.name }}</div>
                        </div>
                        <div class="form-group-custom">
                            <label for="username" class="form-label-custom">Username</label>
                            <input 
                                type="text" 
                                id="username" 
                                class="form-control-custom" 
                                v-model="form.username"
                                :class="{ 'error': form.errors.username }"
                                required
                            >
                            <div v-if="form.errors.username" class="error-message">{{ form.errors.username }}</div>
                        </div>
                        <div class="form-group-custom full-width">
                            <label for="email" class="form-label-custom">Email</label>
                            <input 
                                type="email" 
                                id="email" 
                                class="form-control-custom" 
                                v-model="form.email"
                                :class="{ 'error': form.errors.email }"
                                required
                            >
                            <div v-if="form.errors.email" class="error-message">{{ form.errors.email }}</div>
                        </div>
                        <div class="form-group-custom">
                            <label for="password" class="form-label-custom">Password</label>
                            <input 
                                type="password" 
                                id="password" 
                                class="form-control-custom"
                                v-model="form.password"
                                :class="{ 'error': form.errors.password }"
                                placeholder="Enter password" 
                                required
                            >
                            <div v-if="form.errors.password" class="error-message">{{ form.errors.password }}</div>
                        </div>
                        <div class="form-group-custom">
                            <label for="passwordConfirmation" class="form-label-custom">Confirm Password</label>
                            <input 
                                type="password" 
                                id="passwordConfirmation" 
                                class="form-control-custom"
                                v-model="form.password_confirmation"
                                placeholder="Confirm password" 
                                required
                            >
                        </div>
                        <div class="form-group-custom">
                            <label for="plan" class="form-label-custom">Plan</label>
                            <select id="plan" class="form-control-custom" v-model="form.plan_id">
                                <option :value="null">No Plan</option>
                                <option 
                                    v-for="plan in plans" 
                                    :key="plan.id" 
                                    :value="plan.id"
                                >
                                    {{ plan.name }} ({{ plan.price_formatted }} / {{ plan.billing_period }})
                                </option>
                            </select>
                            <div v-if="form.errors.plan_id" class="error-message">{{ form.errors.plan_id }}</div>
                        </div>
                        <div class="form-group-custom">
                            <label for="status" class="form-label-custom">Status</label>
                            <select id="status" class="form-control-custom" v-model="form.status" required>
                                <option 
                                    v-for="(label, value) in statusOptions" 
                                    :key="value" 
                                    :value="value"
                                >
                                    {{ label }}
                                </option>
                            </select>
                            <div v-if="form.errors.status" class="error-message">{{ form.errors.status }}</div>
                        </div>
                        <div class="form-group-custom">
                            <label for="userType" class="form-label-custom">User Type</label>
                            <select id="userType" class="form-control-custom" v-model="form.user_type" required>
                                <option value="spectator">Spectator</option>
                                <option value="creator">Creator</option>
                                <option value="admin">Admin</option>
                            </select>
                            <div v-if="form.errors.user_type" class="error-message">{{ form.errors.user_type }}</div>
                        </div>
                        <div class="form-group-custom full-width" v-if="form.plan_id">
                            <label for="freeTrial" class="form-label-custom">Free Trial Days</label>
                            <input 
                                type="number" 
                                id="freeTrial" 
                                class="form-control-custom" 
                                v-model.number="form.trial_days" 
                                min="0"
                                placeholder="Override default trial days"
                            >
                            <div v-if="form.errors.trial_days" class="error-message">{{ form.errors.trial_days }}</div>
                        </div>
                    </div>
                    <div class="modal-actions">
                        <button 
                            type="button" 
                            class="action-button" 
                            id="cancelUserForm"
                            style="background-color: #6c757d;" 
                            @click="closeModal"
                            :disabled="form.processing"
                        >
                            Cancel
                        </button>
                        <button 
                            type="submit" 
                            class="action-button" 
                            id="saveUserBtn"
                            :disabled="form.processing"
                        >
                            {{ form.processing ? 'Creating...' : 'Create User' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </teleport>
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
    max-width: 600px;
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

.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.form-group-custom {
    margin-bottom: 1.25rem;
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
    transition: border-color 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
}

.form-control-custom:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(240, 98, 146, 0.3);
}

.form-control-custom.error {
    border-color: var(--error-color);
}

.error-message {
    color: var(--error-color);
    font-size: 0.875rem;
    margin-top: 0.25rem;
}

.modal-actions {
    margin-top: 2rem;
    display: flex;
    justify-content: flex-end;
    gap: 1rem;
}
</style>