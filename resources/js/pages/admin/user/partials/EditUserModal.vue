<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { User, Plan } from '@/types';

const props = defineProps<{
    user: User | null;
    plans: Plan[];
    statusOptions: Record<string, string>;
}>();

const emit = defineEmits<{
    close: [];
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
    trial_days: null as number | null,
});

// Watch for user prop changes to populate form
watch(() => props.user, (user) => {
    if (user) {
        form.name = user.name;
        form.username = user.username;
        form.email = user.email;
        form.status = user.status;
        form.plan_id = user.plan_id || null;
        form.trial_days = null;
        form.password = '';
        form.password_confirmation = '';
        isOpen.value = true;
    } else {
        isOpen.value = false;
    }
}, { immediate: true });

const submitForm = () => {
    if (!props.user) return;
    
    form.put(`/admin/users/${props.user.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            closeModal();
        },
        onError: () => {
            // Errors will be handled by the form validation
        }
    });
};

const closeModal = () => {
    isOpen.value = false;
    form.clearErrors();
    emit('close');
};

defineExpose({
    open: () => isOpen.value = true,
    close: closeModal
});
</script>

<template>
    <teleport to="body">
        <div id="editUserModal" class="modal" :class="{ 'active': isOpen }">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title">Edit User</h2>
                    <button class="close-modal-btn" @click="closeModal">×</button>
                </div>
                <form @submit.prevent="submitForm" v-if="user">
                    <div class="form-grid">
                        <div class="form-group-custom">
                            <label for="editFullName" class="form-label-custom">Full Name</label>
                            <input 
                                type="text" 
                                id="editFullName" 
                                class="form-control-custom" 
                                v-model="form.name"
                                :class="{ 'error': form.errors.name }"
                                required
                            >
                            <div v-if="form.errors.name" class="error-message">{{ form.errors.name }}</div>
                        </div>
                        <div class="form-group-custom">
                            <label for="editUsername" class="form-label-custom">Username</label>
                            <input 
                                type="text" 
                                id="editUsername" 
                                class="form-control-custom" 
                                v-model="form.username"
                                :class="{ 'error': form.errors.username }"
                                required
                            >
                            <div v-if="form.errors.username" class="error-message">{{ form.errors.username }}</div>
                        </div>
                        <div class="form-group-custom full-width">
                            <label for="editEmail" class="form-label-custom">Email</label>
                            <input 
                                type="email" 
                                id="editEmail" 
                                class="form-control-custom" 
                                v-model="form.email"
                                :class="{ 'error': form.errors.email }"
                                required
                            >
                            <div v-if="form.errors.email" class="error-message">{{ form.errors.email }}</div>
                        </div>
                        <div class="form-group-custom">
                            <label for="editPassword" class="form-label-custom">Password</label>
                            <input 
                                type="password" 
                                id="editPassword" 
                                class="form-control-custom"
                                v-model="form.password"
                                :class="{ 'error': form.errors.password }"
                                placeholder="Leave blank to keep current password"
                            >
                            <div v-if="form.errors.password" class="error-message">{{ form.errors.password }}</div>
                        </div>
                        <div class="form-group-custom">
                            <label for="editPasswordConfirmation" class="form-label-custom">Confirm Password</label>
                            <input 
                                type="password" 
                                id="editPasswordConfirmation" 
                                class="form-control-custom"
                                v-model="form.password_confirmation"
                                placeholder="Confirm new password"
                            >
                        </div>
                        <div class="form-group-custom">
                            <label for="editPlan" class="form-label-custom">Plan</label>
                            <select id="editPlan" class="form-control-custom" v-model="form.plan_id">
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
                            <label for="editStatus" class="form-label-custom">Status</label>
                            <select id="editStatus" class="form-control-custom" v-model="form.status" required>
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
                        <div class="form-group-custom full-width" v-if="form.plan_id">
                            <label for="editFreeTrial" class="form-label-custom">Trial Days Override</label>
                            <input 
                                type="number" 
                                id="editFreeTrial" 
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
                            style="background-color: #6c757d;" 
                            @click="closeModal"
                            :disabled="form.processing"
                        >
                            Cancel
                        </button>
                        <button 
                            type="submit" 
                            class="action-button"
                            :disabled="form.processing"
                        >
                            {{ form.processing ? 'Updating...' : 'Update User' }}
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
    border: none;
    border-radius: var(--border-radius-sm);
    cursor: pointer;
    font-weight: 500;
    font-size: 0.95rem;
    transition: background-color 0.2s ease, transform 0.2s ease;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.action-button:hover {
    transform: translateY(-1px);
}

.action-button:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
}

.secondary-button {
    background-color: #6c757d;
    color: white;
}

.secondary-button:hover {
    background-color: #5a6268;
}

.danger-button {
    background-color: #dc3545;
    color: white;
}

.danger-button:hover {
    background-color: #c82333;
}

.item-actions .btn {
    font-size: 0.8rem;
    padding: 0.2rem 0.5rem;
    margin-left: 0.5rem;
    border: none;
    color: white;
    cursor: pointer;
    border-radius: 4px;
}

.item-actions .btn-delete {
    background-color: var(--error-color);
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