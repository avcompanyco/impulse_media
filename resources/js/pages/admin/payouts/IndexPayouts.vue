<script setup lang="ts">
import { ref } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import AdminDashboardLayout from '@/layouts/AdminDashboardLayout.vue';

interface Creator {
    id: number;
    name: string;
    username: string;
    image_url: string;
}

interface Payout {
    id: number;
    amount: number;
    status: string;
    payout_method: string;
    payout_details: string;
    rejection_reason: string | null;
    created_at: string;
    creator: Creator;
}

interface Settings {
    revenue_split_ratio: number;
    min_payout_threshold: number;
    membership_discount_rate: number;
    min_ppv_price: number;
}

const props = defineProps<{
    pendingPayouts: Payout[];
    payoutsHistory: Payout[];
    settings: Settings;
}>();

// Settings Form
const settingsForm = useForm({
    revenue_split_ratio: props.settings.revenue_split_ratio,
    min_payout_threshold: props.settings.min_payout_threshold,
    membership_discount_rate: props.settings.membership_discount_rate,
    min_ppv_price: props.settings.min_ppv_price,
});

const isSavingSettings = ref(false);
function saveSettings() {
    isSavingSettings.value = true;
    settingsForm.put('/admin/settings', {
        preserveScroll: true,
        onSuccess: () => {
            isSavingSettings.value = false;
        },
        onError: () => {
            isSavingSettings.value = false;
        }
    });
}

// Approve Payout Modal Management
const showApproveModal = ref(false);
const activeApprovePayoutId = ref<number | null>(null);

const approveForm = useForm({
    _method: 'PUT',
    status: 'approved',
    transaction_reference: '',
    receipt: null as File | null,
});

function startApproval(id: number) {
    activeApprovePayoutId.value = id;
    approveForm.reset();
    approveForm.transaction_reference = '';
    approveForm.receipt = null;
    showApproveModal.value = true;
}

function cancelApproval() {
    activeApprovePayoutId.value = null;
    showApproveModal.value = false;
}

function submitApproval() {
    approveForm.post(`/admin/payouts/${activeApprovePayoutId.value}`, {
        preserveScroll: true,
        onSuccess: () => {
            cancelApproval();
        }
    });
}

// Reject Payout Modal Management
const activeRejectPayoutId = ref<number | null>(null);
const rejectionReason = ref('');
const showRejectModal = ref(false);

function startRejection(id: number) {
    activeRejectPayoutId.value = id;
    rejectionReason.value = '';
    showRejectModal.value = true;
}

function cancelRejection() {
    activeRejectPayoutId.value = null;
    rejectionReason.value = '';
    showRejectModal.value = false;
}

function submitRejection() {
    if (!rejectionReason.value.trim()) {
        alert('Please specify a rejection reason.');
        return;
    }

    router.put(`/admin/payouts/${activeRejectPayoutId.value}`, {
        status: 'rejected',
        rejection_reason: rejectionReason.value
    }, {
        preserveScroll: true,
        onSuccess: () => {
            cancelRejection();
        }
    });
}

function formatDate(dateStr: string) {
    const d = new Date(dateStr);
    return d.toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
}

function getStatusBadgeClass(status: string) {
    switch (status) {
        case 'pending': return 'badge-pending';
        case 'approved': return 'badge-approved';
        case 'rejected': return 'badge-rejected';
        default: return '';
    }
}
</script>

<template>
    <AdminDashboardLayout 
        title="Creator Payouts & Monetization Settings" 
        headerTitle="Payouts & Settings"
    >
        <div class="admin-payouts-container">
            <!-- Header -->
            <div class="admin-payouts-header">
                <h1 class="page-title">Creator Monetization & Payouts</h1>
                <p class="page-subtitle">Configure splits, discount rates, and manage creator withdrawal requests.</p>
            </div>

            <!-- Dashboard Layout Grid -->
            <div class="admin-layout-grid">
                <!-- Left: Monetization Configuration Settings -->
                <div class="admin-settings-panel">
                    <h3 class="panel-title">
                        <i class="fa-solid fa-gears text-accent"></i> Platform settings
                    </h3>
                    <form @submit.prevent="saveSettings" class="settings-form">
                        <div class="form-group">
                            <label class="form-label">Creator Share Ratio (%)</label>
                            <input 
                                type="number" 
                                v-model="settingsForm.revenue_split_ratio" 
                                class="form-control" 
                                min="0" 
                                max="100" 
                                required
                            >
                            <span class="form-help">Percentage of PPV sales paid directly to creators. (Rest goes to platform)</span>
                            <span v-if="settingsForm.errors.revenue_split_ratio" class="error-msg">{{ settingsForm.errors.revenue_split_ratio }}</span>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Minimum Payout Limit ($)</label>
                            <input 
                                type="number" 
                                v-model="settingsForm.min_payout_threshold" 
                                class="form-control" 
                                min="1" 
                                step="0.01" 
                                required
                            >
                            <span class="form-help">Minimum balance required for a creator to request a withdrawal.</span>
                            <span v-if="settingsForm.errors.min_payout_threshold" class="error-msg">{{ settingsForm.errors.min_payout_threshold }}</span>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Member PPV Discount Rate (%)</label>
                            <input 
                                type="number" 
                                v-model="settingsForm.membership_discount_rate" 
                                class="form-control" 
                                min="0" 
                                max="100" 
                                required
                            >
                            <span class="form-help">Discount percentage applied to PPV purchases for spectators with active Impulse Membership.</span>
                            <span v-if="settingsForm.errors.membership_discount_rate" class="error-msg">{{ settingsForm.errors.membership_discount_rate }}</span>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Minimum PPV Purchase Price ($)</label>
                            <input 
                                type="number" 
                                v-model="settingsForm.min_ppv_price" 
                                class="form-control" 
                                min="0.01" 
                                step="0.01" 
                                required
                            >
                            <span class="form-help">Minimum price creators are allowed to set for their paid videos.</span>
                            <span v-if="settingsForm.errors.min_ppv_price" class="error-msg">{{ settingsForm.errors.min_ppv_price }}</span>
                        </div>

                        <button type="submit" class="submit-btn" :disabled="isSavingSettings">
                            <i class="fa-solid fa-circle-notch fa-spin" v-if="isSavingSettings"></i>
                            Save Settings
                        </button>
                    </form>
                </div>

                <!-- Right: Pending Payout Requests -->
                <div class="admin-payouts-panel">
                    <h3 class="panel-title">
                        <i class="fa-solid fa-circle-dollar-to-slot text-accent"></i> Pending Payout Requests
                    </h3>
                    <div class="payout-cards-container">
                        <div v-if="pendingPayouts.length === 0" class="empty-requests-msg">
                            <i class="fa-solid fa-check-double checked-icon"></i>
                            <p>All payout requests have been processed. Great job!</p>
                        </div>

                        <div v-for="payout in pendingPayouts" :key="payout.id" class="payout-request-card">
                            <div class="card-creator-info">
                                <img 
                                    :src="payout.creator.image_url || '/images/default-avatar.png'" 
                                    alt="Creator Avatar" 
                                    class="creator-avatar"
                                >
                                <div class="creator-meta">
                                    <span class="creator-name">{{ payout.creator.name }}</span>
                                    <span class="creator-handle">@{{ payout.creator.username }}</span>
                                </div>
                                <span class="payout-amount">${{ Number(payout.amount).toFixed(2) }}</span>
                            </div>

                            <div class="card-details-box">
                                <span class="details-label">Method:</span>
                                <span class="details-value text-capitalize">{{ payout.payout_method.replace('_', ' ') }}</span>
                                <div class="details-text-area">
                                    <span class="details-label">Destination Details:</span>
                                    <p class="details-text">{{ payout.payout_details }}</p>
                                </div>
                            </div>

                            <div class="card-actions">
                                <button class="action-btn approve-btn" @click="startApproval(payout.id)">
                                    <i class="fa-solid fa-check"></i> Approve
                                </button>
                                <button class="action-btn reject-btn" @click="startRejection(payout.id)">
                                    <i class="fa-solid fa-xmark"></i> Reject
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Historical Payouts Log (Full Width) -->
            <div class="admin-panel full-width">
                <h3 class="panel-title">
                    <i class="fa-solid fa-list-check text-accent"></i> Payout History Logs
                </h3>
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Creator</th>
                                <th>Amount</th>
                                <th>Method</th>
                                <th>Status</th>
                                <th>Processed At</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="payoutsHistory.length === 0">
                                <td colspan="6" class="empty-table-msg">No historical payout logs found.</td>
                            </tr>
                            <tr v-for="payout in payoutsHistory" :key="payout.id">
                                <td>{{ formatDate(payout.created_at) }}</td>
                                <td>
                                    <div class="table-creator-cell">
                                        <img :src="payout.creator.image_url || '/images/default-avatar.png'" alt="Avatar" class="table-avatar">
                                        <span>@{{ payout.creator.username }}</span>
                                    </div>
                                </td>
                                <td class="font-bold">${{ Number(payout.amount).toFixed(2) }}</td>
                                <td class="text-capitalize">{{ payout.payout_method.replace('_', ' ') }}</td>
                                <td>
                                    <span class="badge" :class="getStatusBadgeClass(payout.status)">
                                        {{ payout.status }}
                                    </span>
                                    <div v-if="payout.status === 'rejected' && payout.rejection_reason" class="table-rejection">
                                        Reason: {{ payout.rejection_reason }}
                                    </div>
                                    <div v-if="payout.status === 'approved'" class="table-approved-details">
                                        <div v-if="payout.transaction_reference" class="reference-text">
                                            Ref: {{ payout.transaction_reference }}
                                        </div>
                                        <div v-if="payout.receipt_url" class="receipt-link-container">
                                            <a :href="payout.receipt_url" target="_blank" class="receipt-download-link">
                                                <i class="fa-solid fa-file-invoice-dollar"></i> View Receipt
                                            </a>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ payout.processed_at ? formatDate(payout.processed_at) : 'N/A' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Reject Payout Modal -->
        <div v-if="showRejectModal" class="reject-modal-overlay">
            <div class="reject-modal-content">
                <div class="modal-header">
                    <h3>Reject Payout Request</h3>
                    <button class="close-btn" @click="cancelRejection">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Reason for Rejection</label>
                        <textarea 
                            v-model="rejectionReason" 
                            class="form-control" 
                            rows="4" 
                            placeholder="Specify why this payout request is being rejected (e.g., incorrect banking information, account verification needed)..."
                            required
                        ></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="modal-btn secondary" @click="cancelRejection">Cancel</button>
                    <button class="modal-btn danger" @click="submitRejection">Reject Request</button>
                </div>
            </div>
        </div>

        <!-- Approve Payout Modal -->
        <div v-if="showApproveModal" class="reject-modal-overlay">
            <div class="reject-modal-content">
                <div class="modal-header">
                    <h3>Approve Payout Request</h3>
                    <button class="close-btn" @click="cancelApproval">&times;</button>
                </div>
                <form @submit.prevent="submitApproval" style="display: flex; flex-direction: column; gap: 1.25rem; margin-top: 1rem;">
                    <div class="form-group">
                        <label class="form-label">Transaction Reference / ID (Optional)</label>
                        <input 
                            type="text"
                            v-model="approveForm.transaction_reference" 
                            class="form-control" 
                            placeholder="e.g., PayPal TxID, wire transfer reference..."
                        >
                    </div>
                    <div class="form-group">
                        <label class="form-label">Payment Receipt File (Optional)</label>
                        <input 
                            type="file" 
                            @change="approveForm.receipt = $event.target.files[0]"
                            class="form-control" 
                            accept="image/*,application/pdf"
                        >
                        <span class="form-help">Upload a JPG, PNG, or PDF confirmation receipt.</span>
                    </div>
                    <div class="modal-footer" style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid rgba(255,255,255,0.08);">
                        <button type="button" class="modal-btn secondary" @click="cancelApproval">Cancel</button>
                        <button type="submit" class="modal-btn" style="background-color: #48bb78; color: white;">Approve Request</button>
                    </div>
                </form>
            </div>
        </div>
    </AdminDashboardLayout>
</template>

<style scoped>
/* Admin Payout Styling */
.admin-payouts-container {
    padding: 1.5rem;
    color: #fff;
    max-width: 1400px;
    margin: 0 auto;
    display: flex;
    flex-direction: column;
    gap: 2rem;
    padding-bottom: 80px;
}

.admin-payouts-header {
    border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    padding-bottom: 1.5rem;
}

.page-title {
    font-size: 2rem;
    font-weight: 800;
    margin: 0 0 0.5rem 0;
    background: linear-gradient(135deg, #e8445a 0%, #ff6b81 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.page-subtitle {
    font-size: 0.95rem;
    color: #a0aec0;
    margin: 0;
}

/* Grid Layout */
.admin-layout-grid {
    display: grid;
    grid-template-columns: 1fr 1.5fr;
    gap: 2rem;
}

@media (max-width: 1024px) {
    .admin-layout-grid {
        grid-template-columns: 1fr;
    }
}

.admin-settings-panel,
.admin-payouts-panel,
.admin-panel {
    background: rgba(255, 255, 255, 0.02);
    border: 1px solid rgba(255, 255, 255, 0.06);
    border-radius: 24px;
    padding: 2rem;
    box-sizing: border-box;
    box-shadow: 0 4px 30px rgba(0, 0, 0, 0.2);
}

.panel-title {
    font-size: 1.25rem;
    font-weight: 700;
    margin: 0 0 1.5rem 0;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    border-bottom: 1px solid rgba(255, 255, 255, 0.05);
    padding-bottom: 1rem;
}

.text-accent {
    color: #e8445a;
}

/* Settings Form */
.settings-form {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.form-label {
    font-size: 0.9rem;
    font-weight: 600;
    color: #a0aec0;
}

.form-control {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 12px;
    color: #fff;
    padding: 0.8rem 1rem;
    font-size: 0.95rem;
    outline: none;
    transition: all 0.2s ease;
}

.form-control:focus {
    border-color: #e8445a;
    background: rgba(255, 255, 255, 0.08);
}

.form-help {
    font-size: 0.75rem;
    color: #718096;
    line-height: 1.4;
}

.error-msg {
    font-size: 0.8rem;
    color: #e8445a;
    font-weight: 600;
}

.submit-btn {
    background: linear-gradient(135deg, #e8445a 0%, #b82337 100%);
    color: #fff;
    border: none;
    border-radius: 12px;
    padding: 0.9rem;
    font-weight: 700;
    font-size: 1rem;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    box-shadow: 0 4px 15px rgba(232, 68, 90, 0.3);
}

.submit-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(232, 68, 90, 0.5);
    background: linear-gradient(135deg, #f8546a 0%, #c83347 100%);
}

.submit-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

/* Payout Cards */
.payout-cards-container {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.empty-requests-msg {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 4rem 2rem;
    color: #718096;
    text-align: center;
    gap: 1rem;
}

.checked-icon {
    font-size: 3rem;
    color: #48bb78;
}

.payout-request-card {
    background: rgba(255, 255, 255, 0.01);
    border: 1px solid rgba(255, 255, 255, 0.05);
    border-radius: 20px;
    padding: 1.5rem;
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
    transition: all 0.2s ease;
}

.payout-request-card:hover {
    background: rgba(255, 255, 255, 0.02);
    border-color: rgba(255, 255, 255, 0.08);
}

.card-creator-info {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.creator-avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #e8445a;
}

.creator-meta {
    display: flex;
    flex-direction: column;
    flex-grow: 1;
}

.creator-name {
    font-weight: 700;
    font-size: 1rem;
}

.creator-handle {
    font-size: 0.8rem;
    color: #a0aec0;
}

.payout-amount {
    font-size: 1.75rem;
    font-weight: 800;
    color: #e8445a;
}

.card-details-box {
    background: rgba(0, 0, 0, 0.2);
    border-radius: 12px;
    padding: 1rem;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    font-size: 0.85rem;
}

.details-label {
    font-weight: 700;
    color: #a0aec0;
    margin-right: 0.25rem;
}

.details-value {
    color: #fff;
}

.details-text-area {
    margin-top: 0.25rem;
    border-top: 1px solid rgba(255, 255, 255, 0.05);
    padding-top: 0.5rem;
}

.details-text {
    margin: 0.25rem 0 0 0;
    color: #e2e8f0;
    white-space: pre-wrap;
    line-height: 1.4;
}

.card-actions {
    display: flex;
    gap: 1rem;
}

.action-btn {
    flex: 1;
    border: none;
    border-radius: 10px;
    padding: 0.7rem;
    font-weight: 700;
    font-size: 0.9rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    transition: all 0.2s ease;
}

.approve-btn {
    background: #48bb78;
    color: #fff;
}

.approve-btn:hover {
    background: #38a169;
    transform: translateY(-1px);
}

.reject-btn {
    background: rgba(229, 62, 62, 0.15);
    color: #e53e3e;
    border: 1px solid rgba(229, 62, 62, 0.3);
}

.reject-btn:hover {
    background: rgba(229, 62, 62, 0.25);
    transform: translateY(-1px);
}

/* History table Cell */
.table-creator-cell {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.table-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    object-fit: cover;
}

.table-rejection {
    font-size: 0.75rem;
    color: #e53e3e;
    font-style: italic;
    margin-top: 4px;
}

/* Admin Table */
.table-responsive {
    width: 100%;
    overflow-x: auto;
}

.admin-table {
    width: 100%;
    border-collapse: collapse;
    text-align: left;
}

.admin-table th {
    padding: 1rem;
    font-size: 0.85rem;
    color: #a0aec0;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    border-bottom: 2px solid rgba(255, 255, 255, 0.05);
}

.admin-table td {
    padding: 1rem;
    font-size: 0.9rem;
    border-bottom: 1px solid rgba(255, 255, 255, 0.05);
    vertical-align: middle;
}

.empty-table-msg {
    text-align: center;
    color: #718096;
    padding: 2.5rem !important;
}

.font-bold {
    font-weight: 700;
}

.text-success {
    color: #48bb78;
}

.text-capitalize {
    text-transform: capitalize;
}

.badge {
    padding: 0.25rem 0.6rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.badge-pending {
    background: rgba(237, 137, 54, 0.15);
    color: #ed8936;
}

.badge-approved {
    background: rgba(72, 187, 120, 0.15);
    color: #48bb78;
}

.badge-rejected {
    background: rgba(229, 62, 62, 0.15);
    color: #e53e3e;
}

/* Reject Modal */
.reject-modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.8);
    backdrop-filter: blur(8px);
    z-index: 1000;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    box-sizing: border-box;
}

.reject-modal-content {
    background: #1e1e2d;
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 24px;
    padding: 2rem;
    max-width: 500px;
    width: 100%;
    box-shadow: 0 10px 30px rgba(0,0,0,0.5);
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    padding-bottom: 0.75rem;
}

.modal-header h3 {
    margin: 0;
    font-size: 1.25rem;
    font-weight: 700;
}

.close-btn {
    background: none;
    border: none;
    color: #a0aec0;
    font-size: 1.5rem;
    cursor: pointer;
}

.close-btn:hover {
    color: #fff;
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 1rem;
    border-top: 1px solid rgba(255, 255, 255, 0.08);
    padding-top: 1rem;
}

.modal-btn {
    border: none;
    border-radius: 10px;
    padding: 0.6rem 1.2rem;
    font-weight: 600;
    cursor: pointer;
    font-size: 0.9rem;
    transition: all 0.2s ease;
}

.modal-btn.secondary {
    background: rgba(255,255,255,0.08);
    color: #fff;
}

.modal-btn.secondary:hover {
    background: rgba(255,255,255,0.15);
}

.modal-btn.danger {
    background: #e53e3e;
    color: #fff;
}

.modal-btn.danger:hover {
    background: #c53030;
}

.table-approved-details {
    margin-top: 6px;
    font-size: 0.8rem;
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.reference-text {
    color: #a0aec0;
}

.receipt-download-link {
    color: #3b82f6;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-weight: 600;
}

.receipt-download-link:hover {
    color: #60a5fa;
    text-decoration: underline;
}
</style>
