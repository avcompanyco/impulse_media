<script setup lang="ts">
import AdminDashboardLayout from '@/layouts/AdminDashboardLayout.vue';
import ShowUserDatatable from './partials/ShowUserDatatable.vue';
import CreateUserModal from './partials/CreateUserModal.vue';
import type { Plan } from '@/types';

const props = defineProps<{
    plans: Plan[];
    statusOptions: Record<string, string>;
}>()
</script>

<template>
    <AdminDashboardLayout 
        :title="`Manage Users - ${$page.props.name || 'Impulsemedia'}`" 
        :headerTitle="`Manage Users - ${$page.props.name || 'Impulsemedia'}`">
        <template #header-actions>
            <CreateUserModal :plans="plans" :statusOptions="statusOptions" />
        </template>
        <Suspense>
            <ShowUserDatatable />
        </Suspense>
    </AdminDashboardLayout>
</template>

<style scoped>
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

.modal-actions {
    margin-top: 2rem;
    display: flex;
    justify-content: flex-end;
    gap: 1rem;
}

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

.btn-edit {
    background-color: var(--secondary-color);
}

.btn-edit:hover {
    background-color: var(--secondary-color-hover);
}

.btn-delete {
    background-color: var(--error-color);
}

.btn-delete:hover {
    background-color: var(--error-color-hover);
}


</style>