<script setup lang="ts">
import { ref } from 'vue';
import AdminDashboardLayout from '@/layouts/AdminDashboardLayout.vue';
import CreatePlanModal from './partials/CreatePlanModal.vue';
import EditPlanModal from './partials/EditPlanModal.vue';
import DeletePlanModal from './partials/DeletePlanModal.vue';

interface Plan {
    id: number;
    name: string;
    price: number;
    price_formatted: string;
    billing_period: string;
    free_days_trial: number;
    description?: string;
    is_unlimited_content: boolean;
    movies_upload_count: number;
    series_upload_count: number;
    shorts_upload_count: number;
}

const props = defineProps<{
    plans: Plan[];
}>();

// Estados para los modales
const isEditModalOpen = ref(false);
const isDeleteModalOpen = ref(false);
const selectedPlan = ref<Plan | null>(null);

// Funciones para manejar los modales
const openEditModal = (plan: Plan) => {
    selectedPlan.value = plan;
    isEditModalOpen.value = true;
};

const openDeleteModal = (plan: Plan) => {
    selectedPlan.value = plan;
    isDeleteModalOpen.value = true;
};

const closeEditModal = () => {
    isEditModalOpen.value = false;
    selectedPlan.value = null;
};

const closeDeleteModal = () => {
    isDeleteModalOpen.value = false;
    selectedPlan.value = null;
};
</script>

<template>
    <AdminDashboardLayout title="List of Subscription Plans" headerTitle="Subscription Plans">
        <template #header-actions>
            <CreatePlanModal />
        </template>
        <div class="plans-grid" id="plansGridContainer">
            <!-- Las tarjetas de planes se insertarán aquí -->
            <div v-for="plan in plans" :key="plan.id" class="plan-card">
                <div class="plan-card-header">
                    <h3 class="plan-card-title">{{ plan.name }}</h3>
                    <div class="plan-card-actions">
                        <EditPlanModal :plan="plan"/>
                        <DeletePlanModal :plan="plan"/>
                    </div>
                </div>
                <ul class="plan-card-details">
                    <li><strong>Price:</strong> {{ plan.price_formatted }} / {{ plan.billing_period }}</li>
                    <li><strong>Uploads:</strong> {{ plan.is_unlimited_content ? 'Unlimited' : 'Limited' }}</li>
                    <li><strong>Description:</strong> {{ plan.description }}</li>
                </ul>
            </div>
        </div>

    </AdminDashboardLayout>
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

.plans-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 1.5rem;
}

.plan-card {
    background-color: var(--section-bg);
    padding: 1.5rem;
    border-radius: var(--border-radius-md);
    border: 1px solid var(--border-color);
    box-shadow: var(--shadow-sm);
    display: flex;
    flex-direction: column;
}

.plan-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
}

.plan-card-title {
    font-size: 1.5rem;
    font-weight: 600;
    color: var(--text-headings);
    margin: 0;
}

.plan-card-actions {
    display: flex;
    gap: 0.5rem;
}

.plan-card-actions .btn {
    font-size: 0.8rem;
    padding: 0.4rem 0.8rem;
    border-radius: var(--border-radius-sm);
    border: none;
    cursor: pointer;
    color: white;
    transition: background-color 0.2s, transform 0.2s;
}

.plan-card-actions .btn:hover {
    transform: translateY(-1px);
}

.plan-card-actions .btn-edit {
    background-color: var(--secondary-color);
}

.plan-card-actions .btn-edit:hover {
    background-color: var(--secondary-color-hover);
}

.plan-card-actions .btn-delete {
    background-color: var(--error-color);
}

.plan-card-actions .btn-delete:hover {
    background-color: var(--error-color-hover);
}

.plan-card-details {
    list-style: none;
    padding: 0;
    margin: 0;
    flex-grow: 1;
}

.plan-card-details li {
    margin-bottom: 0.75rem;
    display: flex;
    align-items: flex-start;
    font-size: 0.95rem;
    color: var(--text-muted);
}

.plan-card-details li strong {
    color: var(--text-light);
    font-weight: 500;
    margin-right: 6px;
}

</style>