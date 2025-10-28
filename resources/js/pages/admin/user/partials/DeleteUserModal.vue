<script setup lang="ts">
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { AlertTriangle } from 'lucide-vue-next';
import { User } from '@/types';

import DestroyUserController from '@/actions/App/Http/Controllers/User/DestroyUserController';

const props = defineProps<{
    user: User | null;
}>();

const processing = ref(false);

const isOpen = ref(false);

const closeModal = () => {
    isOpen.value = false;
};

const deleteUser = () => {
    processing.value = true;
    if (!props.user) {
        processing.value = false;
        return;
    }
    router.delete(DestroyUserController.url({user: props.user?.id}), {
        onSuccess: () => {
            processing.value = false;
            isOpen.value = false;
        },
        onError: () => {
            processing.value = false;
        }
    });
};

</script>

<template>
    <button class="btn btn-delete" @click="isOpen = true">Delete</button>
    <Teleport to="body">
        <div id="deleteUserModal" class="modal" :class="{ 'active': isOpen }">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title">Delete User</h2>
                    <button class="close-modal-btn" @click="closeModal">&times;</button>
                </div>

                <div class="delete-confirmation">
                    <div class="warning-icon">
                        <AlertTriangle class="h-12 w-12 text-red-500" />
                    </div>

                    <div class="confirmation-content">
                        <h3 class="confirmation-title">Are you sure you want to delete this user?</h3>
                        <p class="confirmation-message">
                            This action will permanently delete the user
                            <strong>"{{ user?.name }}"</strong>
                            with email <strong>{{ user?.email }}</strong>
                            and username <strong>@{{ user?.username }}</strong>.
                        </p>
                        <p class="warning-text">
                            This action cannot be undone. All user data, subscriptions, and content will be permanently removed.
                        </p>
                    </div>
                </div>

                <div class="modal-actions">
                    <button type="button" class="action-button secondary-button" @click="closeModal">
                        Cancel
                    </button>
                    <button type="button" class="action-button danger-button" @click="deleteUser"
                        :disabled="processing">
                        <i class="fa-solid fa-circle-notch fa-spin" v-if="processing"></i>
                        Delete User
                    </button>
                </div>
            </div>
        </div>
    </Teleport>
</template>

<style scoped>
 .btn {
    font-size: 0.825rem;
    padding: 0.45rem 0.85rem;
    border-radius: var(--border-radius-sm);
    border: none;
    font-weight: 500;
    text-align: center;
    min-width: 65px;
    cursor: pointer;
    text-decoration: none;
    color: white;
    transition: background-color 0.2s ease, transform 0.2s ease;
}

 .btn:hover {
    transform: translateY(-1px);
}
.btn-delete {
    background-color: var(--error-color);
}

.btn-delete:hover {
    background-color: var(--error-color-hover);
}
.action-button {
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
    padding-top: 10vh;
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

.delete-confirmation {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    margin-bottom: 2rem;
}

.warning-icon {
    margin-bottom: 1rem;
}

.confirmation-content {
    max-width: 400px;
}

.confirmation-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--text-headings);
    margin-bottom: 1rem;
}

.confirmation-message {
    font-size: 1rem;
    color: var(--text-light);
    margin-bottom: 1rem;
    line-height: 1.5;
}

.confirmation-message strong {
    color: var(--text-headings);
    font-weight: 600;
}

.warning-text {
    font-size: 0.9rem;
    color: #fbbf24;
    background-color: rgba(251, 191, 36, 0.1);
    padding: 0.75rem;
    border-radius: var(--border-radius-sm);
    border-left: 3px solid #fbbf24;
    margin-top: 1rem;
}

.modal-actions {
    display: flex;
    justify-content: flex-end;
    gap: 1rem;
}

/* Responsive design */
@media (max-width: 768px) {
    .modal-content {
        width: 95%;
        margin: 1rem;
        padding: 1.5rem;
    }

    .modal-actions {
        flex-direction: column-reverse;
    }

    .action-button {
        width: 100%;
        justify-content: center;
    }
}
</style>
