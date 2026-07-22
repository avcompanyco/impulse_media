<script setup lang="ts">
import { ref, computed } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import AdminDashboardLayout from '@/layouts/AdminDashboardLayout.vue';

interface Creator {
    id: number;
    name: string;
    username: string;
    email?: string;
    image_url: string;
}

interface Payout {
    id: number;
    amount: number;
    status: string;
    payout_method: string;
    payout_details: string;
    rejection_reason: string | null;
    transaction_reference: string | null;
    receipt_url: string | null;
    processed_at: string | null;
    created_at: string;
    creator: Creator;
}

interface Settings {
    revenue_split_ratio: number;
    min_payout_threshold: number;
    membership_discount_rate: number;
    min_ppv_price: number;
    free_preview_seconds: number;
}

interface PlatformStats {
    platform_earnings: number;
    total_paid_out: number;
    total_pending: number;
}

const props = defineProps<{
    pendingPayouts: Payout[];
    payoutsHistory: Payout[];
    settings: Settings;
    platformStats: PlatformStats;
    creatorStats: any[];
}>();

// Settings Form
const settingsForm = useForm({
    revenue_split_ratio: props.settings.revenue_split_ratio,
    min_payout_threshold: props.settings.min_payout_threshold,
    membership_discount_rate: props.settings.membership_discount_rate,
    min_ppv_price: props.settings.min_ppv_price,
    free_preview_seconds: props.settings.free_preview_seconds,
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

// Membership Split Manual Runner
const splitMonth = ref('');
const isProcessingSplit = ref(false);
const splitSuccessOutput = ref('');
const splitErrorMsg = ref('');

const currentMonthString = computed(() => {
    const now = new Date();
    const year = now.getFullYear();
    const month = String(now.getMonth() + 1).padStart(2, '0');
    return `${year}-${month}`;
});

function runMembershipSplit() {
    isProcessingSplit.value = true;
    splitSuccessOutput.value = '';
    splitErrorMsg.value = '';
    
    router.post('/admin/payouts/process-split', {
        month: splitMonth.value || null
    }, {
        preserveScroll: true,
        onSuccess: (page) => {
            isProcessingSplit.value = false;
            const flash = page.props.flash as any;
            if (flash && flash.message) {
                splitSuccessOutput.value = flash.message;
            } else {
                splitSuccessOutput.value = "Process completed successfully. Check Laravel log files for detailed outputs.";
            }
        },
        onError: (errors) => {
            isProcessingSplit.value = false;
            if (errors.message) {
                splitErrorMsg.value = errors.message;
            } else {
                splitErrorMsg.value = "An error occurred during calculations.";
            }
        }
    });
}

// Filter for Pending Payouts
const pendingSearchQuery = ref('');

const filteredPendingPayouts = computed(() => {
    if (!pendingSearchQuery.value.trim()) {
        return props.pendingPayouts;
    }
    const q = pendingSearchQuery.value.toLowerCase().trim();
    return props.pendingPayouts.filter(p => 
        p.creator.name.toLowerCase().includes(q) ||
        p.creator.username.toLowerCase().includes(q) ||
        p.payout_method.toLowerCase().includes(q) ||
        p.payout_details.toLowerCase().includes(q)
    );
});

// Filter for Creator Balances & Metrics
const searchCreatorMetricQuery = ref('');

const filteredCreatorStats = computed(() => {
    if (!searchCreatorMetricQuery.value.trim()) {
        return props.creatorStats;
    }
    const q = searchCreatorMetricQuery.value.toLowerCase().trim();
    return props.creatorStats.filter(c => 
        (c.name && c.name.toLowerCase().includes(q)) ||
        (c.username && c.username.toLowerCase().includes(q)) ||
        (c.email && c.email.toLowerCase().includes(q))
    );
});

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

// Filters for Payout History
const searchFilter = ref('');
const statusFilter = ref('');
const methodFilter = ref('');
const refFilter = ref('');

// Computed filtered payouts list
const filteredHistory = computed(() => {
    return props.payoutsHistory.filter(payout => {
        const matchesSearch = !searchFilter.value || 
            payout.creator.name.toLowerCase().includes(searchFilter.value.toLowerCase()) ||
            payout.creator.username.toLowerCase().includes(searchFilter.value.toLowerCase());
            
        const matchesStatus = !statusFilter.value || payout.status === statusFilter.value;
        const matchesMethod = !methodFilter.value || payout.payout_method === methodFilter.value;
        
        const matchesRef = !refFilter.value || 
            (payout.transaction_reference && payout.transaction_reference.toLowerCase().includes(refFilter.value.toLowerCase()));
            
        return matchesSearch && matchesStatus && matchesMethod && matchesRef;
    });
});

function clearFilters() {
    searchFilter.value = '';
    statusFilter.value = '';
    methodFilter.value = '';
    refFilter.value = '';
}

// Details Modal Management
const showDetailsModal = ref(false);
const activeDetailsPayout = ref<Payout | null>(null);

function viewPayoutDetails(payout: Payout) {
    activeDetailsPayout.value = payout;
    showDetailsModal.value = true;
}

function closePayoutDetails() {
    activeDetailsPayout.value = null;
    showDetailsModal.value = false;
}

// Receipt Preview Modal Management
const showReceiptModal = ref(false);
const activeReceiptUrl = ref('');
const activeReceiptType = ref<'image' | 'pdf' | 'unknown'>('image');

function openReceiptPreview(url: string) {
    activeReceiptUrl.value = url;
    const extension = url.split('.').pop()?.toLowerCase();
    if (extension === 'pdf') {
        activeReceiptType.value = 'pdf';
    } else {
        activeReceiptType.value = 'image';
    }
    showReceiptModal.value = true;
}

function closeReceiptPreview() {
    showReceiptModal.value = false;
    activeReceiptUrl.value = '';
}

// Export to CSV function
function exportToCSV() {
    const headers = [
        'Request Date',
        'Creator Name',
        'Username',
        'Amount',
        'Method',
        'Destination Details',
        'Status',
        'Processed Date',
        'Transaction Reference',
        'Rejection Reason'
    ];
    
    const rows = filteredHistory.value.map(p => [
        p.created_at,
        p.creator.name,
        `@${p.creator.username}`,
        p.amount,
        p.payout_method,
        p.payout_details.replace(/\r?\n/g, ' ').replace(/"/g, '""'),
        p.status,
        p.processed_at || 'N/A',
        p.transaction_reference || 'N/A',
        (p.rejection_reason || '').replace(/\r?\n/g, ' ').replace(/"/g, '""')
    ]);
    
    let csvContent = "data:text/csv;charset=utf-8,\uFEFF";
    csvContent += [headers.join(','), ...rows.map(r => r.map(val => `"${val}"`).join(','))].join('\n');
    
    const encodedUri = encodeURI(csvContent);
    const link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    
    const date = new Date().toISOString().slice(0, 10);
    link.setAttribute("download", `payouts_report_${date}.csv`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
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
                <p class="page-subtitle">Configure revenue splits, minimum payout limits, and manage creator withdrawal requests.</p>
            </div>

            <!-- Platform Stats Cards Grid (Top) -->
            <div class="stats-overview-grid">
                <div class="overview-card earnings-card">
                    <div class="card-icon-wrapper">
                        <i class="fa-solid fa-vault"></i>
                    </div>
                    <div class="card-info">
                        <span class="card-label">Platform Net Earnings</span>
                        <h2 class="card-value">${{ Number(platformStats.platform_earnings).toFixed(2) }}</h2>
                    </div>
                </div>

                <div class="overview-card paid-card">
                    <div class="card-icon-wrapper">
                        <i class="fa-solid fa-hand-holding-dollar"></i>
                    </div>
                    <div class="card-info">
                        <span class="card-label">Total Paid Out</span>
                        <h2 class="card-value">${{ Number(platformStats.total_paid_out).toFixed(2) }}</h2>
                    </div>
                </div>

                <div class="overview-card pending-card">
                    <div class="card-icon-wrapper">
                        <i class="fa-solid fa-hourglass-half"></i>
                    </div>
                    <div class="card-info">
                        <span class="card-label">Total Pending Payouts</span>
                        <h2 class="card-value">${{ Number(platformStats.total_pending).toFixed(2) }}</h2>
                    </div>
                </div>
            </div>

            <!-- Dashboard Layout Grid (Balanced 2-Column) -->
            <div class="admin-layout-grid">
                <!-- Left Column: Pending Payout Requests + Monthly Membership Split -->
                <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                    <!-- Pending Payout Requests Panel -->
                    <div class="admin-payouts-panel">
                        <div class="panel-header-with-badge">
                            <h3 class="panel-title" style="margin: 0; border: none; padding: 0;">
                                <i class="fa-solid fa-circle-dollar-to-slot text-accent"></i> Pending Payout Requests
                            </h3>
                            <span class="pending-count-badge">
                                {{ pendingPayouts.length }} Pending
                            </span>
                        </div>

                        <!-- Search Filter for Pending Requests -->
                        <div v-if="pendingPayouts.length > 0" class="pending-search-bar">
                            <i class="fa-solid fa-magnifying-glass search-icon"></i>
                            <input 
                                type="text" 
                                v-model="pendingSearchQuery" 
                                class="pending-search-input" 
                                placeholder="Filter requests by creator..."
                            >
                        </div>

                        <!-- Scrollable Feed Container -->
                        <div class="payout-cards-container custom-scrollbar">
                            <div v-if="pendingPayouts.length === 0" class="empty-requests-msg">
                                <i class="fa-solid fa-circle-check checked-icon"></i>
                                <p>All payout requests have been processed. Great job!</p>
                            </div>

                            <div v-else-if="filteredPendingPayouts.length === 0" class="empty-requests-msg">
                                <i class="fa-solid fa-filter checked-icon"></i>
                                <p>No pending payout requests match your search query.</p>
                            </div>

                            <div v-for="payout in filteredPendingPayouts" :key="payout.id" class="payout-request-card">
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
                                    <span class="details-label">Payment Method:</span>
                                    <span class="details-value text-capitalize">{{ payout.payout_method.replace('_', ' ') }}</span>
                                    <div class="details-text-area">
                                        <span class="details-label">Payout Destination:</span>
                                        <p class="details-text">{{ payout.payout_details }}</p>
                                    </div>
                                </div>

                                <div class="card-actions">
                                    <button class="action-btn approve-btn" @click="startApproval(payout.id)">
                                        <i class="fa-solid fa-check"></i> Approve Payout
                                    </button>
                                    <button class="action-btn reject-btn" @click="startRejection(payout.id)">
                                        <i class="fa-solid fa-xmark"></i> Reject Request
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Calculate Monthly Earnings Split Card -->
                    <div class="admin-settings-panel">
                        <h3 class="panel-title">
                            <i class="fa-solid fa-calculator text-accent"></i> Monthly Membership Split
                        </h3>
                        <form @submit.prevent="runMembershipSplit" class="settings-form">
                            <div class="form-group">
                                <label class="form-label">Billing Month</label>
                                <input 
                                    type="month" 
                                    v-model="splitMonth" 
                                    class="form-control"
                                    :max="currentMonthString"
                                >
                                <span class="form-help">Select the month to process. Defaults to the previous calendar month if left blank.</span>
                            </div>

                            <button type="submit" class="submit-btn" :disabled="isProcessingSplit" style="background: linear-gradient(135deg, #805ad5 0%, #553c9a 100%); box-shadow: 0 4px 15px rgba(128, 90, 213, 0.3);">
                                <i class="fa-solid fa-circle-notch fa-spin" v-if="isProcessingSplit"></i>
                                Calculate Revenue Split
                            </button>
                        </form>

                        <!-- Console Output -->
                        <div v-if="splitSuccessOutput" class="console-output-box" style="margin-top: 1.25rem;">
                            <span class="console-label">Execution Logs</span>
                            <pre class="console-text">{{ splitSuccessOutput }}</pre>
                        </div>
                        <div v-if="splitErrorMsg" class="console-output-box error" style="margin-top: 1.25rem;">
                            <span class="console-label">Error</span>
                            <pre class="console-text">{{ splitErrorMsg }}</pre>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Monetization Configuration Settings -->
                <div class="admin-settings-panel">
                    <h3 class="panel-title">
                        <i class="fa-solid fa-gears text-accent"></i> Platform Settings
                    </h3>
                    <form @submit.prevent="saveSettings" class="settings-form">
                        <div class="form-group">
                            <label class="form-label">Creator Revenue Share (%)</label>
                            <input 
                                type="number" 
                                v-model="settingsForm.revenue_split_ratio" 
                                class="form-control" 
                                min="0" 
                                max="100" 
                                required
                            >
                            <span class="form-help">Percentage of PPV sales paid directly to creators. The platform retains the remainder.</span>
                            <span v-if="settingsForm.errors.revenue_split_ratio" class="error-msg">{{ settingsForm.errors.revenue_split_ratio }}</span>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Minimum Withdrawal Threshold ($)</label>
                            <input 
                                type="number" 
                                v-model="settingsForm.min_payout_threshold" 
                                class="form-control" 
                                min="1" 
                                step="0.01" 
                                required
                            >
                            <span class="form-help">Minimum balance required for a creator to request a payout.</span>
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
                            <span class="form-help">Discount percentage applied to PPV purchases for spectators with an active Impulse Membership.</span>
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
                            <span class="form-help">Minimum price creators are allowed to set for paid videos.</span>
                            <span v-if="settingsForm.errors.min_ppv_price" class="error-msg">{{ settingsForm.errors.min_ppv_price }}</span>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Free PPV Video Preview Limit (Seconds)</label>
                            <input 
                                type="number" 
                                v-model="settingsForm.free_preview_seconds" 
                                class="form-control" 
                                min="1" 
                                step="1" 
                                required
                            >
                            <span class="form-help">Number of seconds a spectator is allowed to watch a PPV video before the paywall blocks it (e.g. 300 for 5 minutes).</span>
                            <span v-if="settingsForm.errors.free_preview_seconds" class="error-msg">{{ settingsForm.errors.free_preview_seconds }}</span>
                        </div>

                        <button type="submit" class="submit-btn" :disabled="isSavingSettings">
                            <i class="fa-solid fa-circle-notch fa-spin" v-if="isSavingSettings"></i>
                            Save Platform Settings
                        </button>
                    </form>
                </div>
            </div>

            <!-- Historical Payouts Log (Full Width Datatable with Scroll) -->
            <div class="admin-panel full-width">
                <div class="panel-header-with-actions">
                    <h3 class="panel-title" style="margin: 0; border: none; padding: 0;">
                        <i class="fa-solid fa-list-check text-accent"></i> Payout History Logs
                    </h3>
                    <button class="export-csv-btn" @click="exportToCSV">
                        <i class="fa-solid fa-file-csv"></i> Export to CSV
                    </button>
                </div>

                <!-- Filters Control Box -->
                <div class="filters-container">
                    <div class="filter-item">
                        <label class="filter-label">Search Creator</label>
                        <input 
                            type="text" 
                            v-model="searchFilter" 
                            class="filter-input" 
                            placeholder="Name or @username..."
                        >
                    </div>
                    <div class="filter-item">
                        <label class="filter-label">Filter by Status</label>
                        <select v-model="statusFilter" class="filter-select">
                            <option value="">All Statuses</option>
                            <option value="approved">Approved</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>
                    <div class="filter-item">
                        <label class="filter-label">Filter by Method</label>
                        <select v-model="methodFilter" class="filter-select">
                            <option value="">All Methods</option>
                            <option value="paypal">PayPal</option>
                            <option value="bank_transfer">Bank Transfer</option>
                        </select>
                    </div>
                    <div class="filter-item">
                        <label class="filter-label">Transaction Ref ID</label>
                        <input 
                            type="text" 
                            v-model="refFilter" 
                            class="filter-input" 
                            placeholder="Ref ID..."
                        >
                    </div>
                    <div class="filter-actions-wrapper">
                        <button class="clear-filters-btn" @click="clearFilters">
                            <i class="fa-solid fa-filter-circle-xmark"></i> Clear Filters
                        </button>
                    </div>
                </div>

                <div class="table-scroll-container custom-scrollbar">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Creator</th>
                                <th>Amount</th>
                                <th>Method</th>
                                <th>Status</th>
                                <th>Processed Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="filteredHistory.length === 0">
                                <td colspan="7" class="empty-table-msg">No historical payout logs match the active filters.</td>
                            </tr>
                            <tr v-for="payout in filteredHistory" :key="payout.id">
                                <td style="white-space: nowrap;">{{ formatDate(payout.created_at) }}</td>
                                <td>
                                    <div class="table-creator-cell">
                                        <img :src="payout.creator.image_url || '/images/default-avatar.png'" alt="Avatar" class="table-avatar">
                                        <span>@{{ payout.creator.username }}</span>
                                    </div>
                                </td>
                                <td class="font-bold text-accent-green" style="font-weight: 700; color: #48bb78;">${{ Number(payout.amount).toFixed(2) }}</td>
                                <td class="text-capitalize" style="white-space: nowrap;">{{ payout.payout_method.replace('_', ' ') }}</td>
                                <td>
                                    <span class="badge" :class="getStatusBadgeClass(payout.status)">
                                        {{ payout.status }}
                                    </span>
                                    <div v-if="payout.status === 'rejected' && payout.rejection_reason" class="table-rejection text-truncate" style="max-width: 200px;">
                                        Reason: {{ payout.rejection_reason }}
                                    </div>
                                    <div v-if="payout.status === 'approved'" class="table-approved-details">
                                        <div v-if="payout.transaction_reference" class="reference-text">
                                            Ref: {{ payout.transaction_reference }}
                                        </div>
                                    </div>
                                </td>
                                <td style="white-space: nowrap;">{{ payout.processed_at ? formatDate(payout.processed_at) : 'N/A' }}</td>
                                <td>
                                    <div style="display: flex; gap: 8px; align-items: center;">
                                        <button class="row-action-btn details" @click="viewPayoutDetails(payout)" title="View Details">
                                            <i class="fa-solid fa-eye"></i> Details
                                        </button>
                                        <button v-if="payout.receipt_url" class="row-action-btn receipt" @click="openReceiptPreview(payout.receipt_url)" title="View Receipt">
                                            <i class="fa-solid fa-receipt"></i> Receipt
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Creator Balances & Metrics (Full Width Searchable Datatable with Scroll) -->
            <div class="admin-panel full-width">
                <div class="panel-header-with-badge" style="margin-bottom: 1.25rem;">
                    <h3 class="panel-title" style="margin: 0; border: none; padding: 0;">
                        <i class="fa-solid fa-users-gear text-accent"></i> Creator Balances & Metrics
                    </h3>
                    <span class="pending-count-badge" style="background: rgba(49, 130, 206, 0.15); color: #63b3ed; border-color: rgba(49, 130, 206, 0.3);">
                        {{ creatorStats.length }} Creators Registered
                    </span>
                </div>

                <!-- Search Bar for Creator Metrics -->
                <div class="pending-search-bar" style="margin-bottom: 1.25rem;">
                    <i class="fa-solid fa-magnifying-glass search-icon"></i>
                    <input 
                        type="text" 
                        v-model="searchCreatorMetricQuery" 
                        class="pending-search-input" 
                        placeholder="Search creator by name or email..."
                    >
                </div>

                <div class="table-scroll-container custom-scrollbar">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Creator</th>
                                <th>Email</th>
                                <th>Lifetime Earnings</th>
                                <th>Available Balance</th>
                                <th>Total Paid Out</th>
                                <th>Total Pending</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="creatorStats.length === 0">
                                <td colspan="6" class="empty-table-msg">No creators found on the platform.</td>
                            </tr>
                            <tr v-else-if="filteredCreatorStats.length === 0">
                                <td colspan="6" class="empty-table-msg">No creators match your search query.</td>
                            </tr>
                            <tr v-for="creator in filteredCreatorStats" :key="creator.id">
                                <td>
                                    <div class="table-creator-cell">
                                        <img :src="creator.image_url || '/images/default-avatar.png'" alt="Avatar" class="table-avatar">
                                        <div class="creator-meta">
                                            <span class="creator-name" style="font-weight: 700; color: #fff;">{{ creator.name }}</span>
                                            <span class="creator-handle" style="font-size: 0.8rem; color: #a0aec0;">@{{ creator.username }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td style="color: #e2e8f0; font-size: 0.88rem;">{{ creator.email }}</td>
                                <td>
                                    <span class="metric-pill lifetime">
                                        ${{ Number(creator.lifetime_earnings).toFixed(2) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="metric-pill balance">
                                        ${{ Number(creator.balance).toFixed(2) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="metric-pill paid">
                                        ${{ Number(creator.total_paid).toFixed(2) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="metric-pill pending">
                                        ${{ Number(creator.total_pending).toFixed(2) }}
                                    </span>
                                </td>
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
                        <label class="form-label">Rejection Reason</label>
                        <textarea 
                            v-model="rejectionReason" 
                            class="form-control" 
                            rows="4" 
                            placeholder="Specify why this payout request is being rejected (e.g. incorrect banking information, account verification needed)..."
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
                        <label class="form-label">Transaction Reference / TxID (Optional)</label>
                        <input 
                            type="text"
                            v-model="approveForm.transaction_reference" 
                            class="form-control" 
                            placeholder="e.g. PayPal TxID, wire transfer reference..."
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
                        <button type="submit" class="modal-btn" style="background-color: #48bb78; color: white;">Approve Payout</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Payout Details Modal -->
        <div v-if="showDetailsModal && activeDetailsPayout" class="reject-modal-overlay">
            <div class="reject-modal-content details-modal-content">
                <div class="modal-header">
                    <h3>Withdrawal Request Details</h3>
                    <button class="close-btn" @click="closePayoutDetails">&times;</button>
                </div>
                <div class="modal-body" style="display: flex; flex-direction: column; gap: 1.25rem; margin-top: 1rem;">
                    
                    <!-- Creator Meta -->
                    <div class="detail-creator-row">
                        <img 
                            :src="activeDetailsPayout.creator.image_url || '/images/default-avatar.png'" 
                            alt="Avatar" 
                            class="creator-avatar"
                            style="width: 54px; height: 54px; border-radius: 50%; object-fit: cover; border: 2px solid #e8445a;"
                        >
                        <div style="display: flex; flex-direction: column; gap: 2px;">
                            <span style="font-weight: 700; font-size: 1.1rem; color: #fff;">{{ activeDetailsPayout.creator.name }}</span>
                            <span style="font-size: 0.85rem; color: #a0aec0;">@{{ activeDetailsPayout.creator.username }}</span>
                            <span v-if="activeDetailsPayout.creator.email" style="font-size: 0.85rem; color: #a0aec0;">{{ activeDetailsPayout.creator.email }}</span>
                        </div>
                    </div>

                    <!-- Details Grid -->
                    <div class="details-summary-grid">
                        <div class="summary-info-item">
                            <span class="info-label">Amount:</span>
                            <span class="info-value text-accent" style="font-size: 1.4rem; font-weight: 800;">${{ Number(activeDetailsPayout.amount).toFixed(2) }}</span>
                        </div>
                        <div class="summary-info-item">
                            <span class="info-label">Payment Method:</span>
                            <span class="info-value text-capitalize">{{ activeDetailsPayout.payout_method.replace('_', ' ') }}</span>
                        </div>
                        <div class="summary-info-item">
                            <span class="info-label">Requested Date:</span>
                            <span class="info-value">{{ formatDate(activeDetailsPayout.created_at) }}</span>
                        </div>
                        <div class="summary-info-item">
                            <span class="info-label">Status:</span>
                            <span class="badge" :class="getStatusBadgeClass(activeDetailsPayout.status)" style="align-self: flex-start; margin-top: 4px;">
                                {{ activeDetailsPayout.status }}
                            </span>
                        </div>
                    </div>

                    <!-- Destination Bank Details -->
                    <div class="detail-box-container">
                        <h4 class="box-title">Payout Destination Account Details</h4>
                        <pre class="box-content">{{ activeDetailsPayout.payout_details }}</pre>
                    </div>

                    <!-- Resolution Information -->
                    <div v-if="activeDetailsPayout.status !== 'pending'" class="detail-box-container resolution-box">
                        <h4 class="box-title">Resolution Information</h4>
                        <div class="resolution-grid">
                            <div v-if="activeDetailsPayout.processed_at" class="resolution-item">
                                <span class="res-label">Processed Date:</span>
                                <span class="res-value">{{ formatDate(activeDetailsPayout.processed_at) }}</span>
                            </div>
                            <div v-if="activeDetailsPayout.status === 'approved' && activeDetailsPayout.transaction_reference" class="resolution-item">
                                <span class="res-label">Transaction Ref ID:</span>
                                <span class="res-value font-mono">{{ activeDetailsPayout.transaction_reference }}</span>
                            </div>
                            <div v-if="activeDetailsPayout.status === 'rejected' && activeDetailsPayout.rejection_reason" class="resolution-item full-width">
                                <span class="res-label">Rejection Reason:</span>
                                <span class="res-value" style="color: #e53e3e;">{{ activeDetailsPayout.rejection_reason }}</span>
                            </div>
                            <div v-if="activeDetailsPayout.status === 'approved' && activeDetailsPayout.receipt_url" class="resolution-item full-width">
                                <span class="res-label">Attached Receipt File:</span>
                                <button class="row-action-btn receipt" @click="openReceiptPreview(activeDetailsPayout.receipt_url)" style="margin-top: 6px;">
                                    <i class="fa-solid fa-receipt"></i> Open Receipt Preview
                                </button>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer" style="margin-top: 1rem;">
                    <button class="modal-btn secondary" @click="closePayoutDetails">Close</button>
                </div>
            </div>
        </div>

        <!-- Receipt Preview Lightbox Modal -->
        <div v-if="showReceiptModal && activeReceiptUrl" class="receipt-lightbox-overlay" @click.self="closeReceiptPreview">
            <div class="receipt-lightbox-content">
                <div class="lightbox-header">
                    <h4>Receipt Document Preview</h4>
                    <button class="lightbox-close-btn" @click="closeReceiptPreview">&times;</button>
                </div>
                <div class="lightbox-body">
                    <iframe 
                        v-if="activeReceiptType === 'pdf'" 
                        :src="activeReceiptUrl" 
                        class="pdf-preview-frame"
                        frameborder="0"
                    ></iframe>
                    <img 
                        v-else 
                        :src="activeReceiptUrl" 
                        alt="Receipt Preview" 
                        class="img-preview-frame"
                    >
                </div>
                <div class="lightbox-footer">
                    <a :href="activeReceiptUrl" target="_blank" download class="modal-btn" style="background-color: #3182ce; color: white; text-decoration: none; display: inline-flex; align-items: center; gap: 8px;">
                        <i class="fa-solid fa-download"></i> Download File
                    </a>
                    <button class="modal-btn secondary" @click="closeReceiptPreview">Close</button>
                </div>
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

/* Stats Overview Cards */
.stats-overview-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1.5rem;
}

@media (max-width: 768px) {
    .stats-overview-grid {
        grid-template-columns: 1fr;
    }
}

.overview-card {
    background: rgba(255, 255, 255, 0.02);
    border: 1px solid rgba(255, 255, 255, 0.06);
    border-radius: 20px;
    padding: 1.5rem;
    display: flex;
    align-items: center;
    gap: 1.25rem;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.overview-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 25px rgba(0, 0, 0, 0.35);
}

.card-icon-wrapper {
    width: 56px;
    height: 56px;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: #fff;
    flex-shrink: 0;
}

.earnings-card .card-icon-wrapper {
    background: linear-gradient(135deg, #805ad5 0%, #553c9a 100%);
    box-shadow: 0 0 15px rgba(128, 90, 213, 0.3);
}

.paid-card .card-icon-wrapper {
    background: linear-gradient(135deg, #48bb78 0%, #2f855a 100%);
    box-shadow: 0 0 15px rgba(72, 187, 120, 0.3);
}

.pending-card .card-icon-wrapper {
    background: linear-gradient(135deg, #ed8936 0%, #c05621 100%);
    box-shadow: 0 0 15px rgba(237, 137, 86, 0.3);
}

.card-info {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.card-label {
    font-size: 0.8rem;
    color: #a0aec0;
    font-weight: 600;
}

.card-value {
    font-size: 1.75rem;
    font-weight: 800;
    margin: 0;
    color: #fff;
}

/* Grid Layout (Balanced 2-Column) */
.admin-layout-grid {
    display: grid;
    grid-template-columns: 1.3fr 1fr;
    gap: 1.5rem;
    align-items: start;
}

@media (max-width: 1100px) {
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
    padding: 1.75rem;
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

/* Pending Panel Header with Badge */
.panel-header-with-badge {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid rgba(255, 255, 255, 0.05);
    padding-bottom: 1rem;
    margin-bottom: 1.25rem;
}

.pending-count-badge {
    background: rgba(237, 137, 86, 0.15);
    color: #ed8936;
    border: 1px solid rgba(237, 137, 86, 0.3);
    font-size: 0.8rem;
    font-weight: 700;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
}

.pending-search-bar {
    position: relative;
    margin-bottom: 1.25rem;
}

.pending-search-bar .search-icon {
    position: absolute;
    left: 1rem;
    top: 50%;
    transform: translateY(-50%);
    color: rgba(255, 255, 255, 0.4);
    font-size: 0.85rem;
}

.pending-search-input {
    width: 100%;
    padding: 0.65rem 1rem 0.65rem 2.5rem;
    background: rgba(255, 255, 255, 0.04);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 12px;
    color: #fff;
    font-size: 0.88rem;
    box-sizing: border-box;
    transition: all 0.2s ease;
}

.pending-search-input:focus {
    outline: none;
    border-color: #e8445a;
    background: rgba(255, 255, 255, 0.07);
}

/* Settings Form */
.settings-form {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 0.4rem;
}

.form-label {
    font-size: 0.9rem;
    font-weight: 600;
    color: #e2e8f0;
}

.form-control {
    background: rgba(0, 0, 0, 0.25);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 12px;
    padding: 0.75rem 1rem;
    color: #fff;
    font-size: 0.95rem;
    transition: border-color 0.2s ease;
}

.form-control:focus {
    outline: none;
    border-color: #e8445a;
}

.form-help {
    font-size: 0.8rem;
    color: #718096;
}

.error-msg {
    color: #e53e3e;
    font-size: 0.8rem;
}

.submit-btn {
    background: linear-gradient(135deg, #e8445a 0%, #d83b50 100%);
    color: white;
    border: none;
    border-radius: 12px;
    padding: 0.85rem 1.5rem;
    font-weight: 700;
    font-size: 0.95rem;
    cursor: pointer;
    transition: all 0.2s ease;
    box-shadow: 0 4px 15px rgba(232, 68, 90, 0.3);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    margin-top: 0.5rem;
}

.submit-btn:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(232, 68, 90, 0.4);
}

.submit-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

/* Console Box */
.console-output-box {
    background: rgba(0, 0, 0, 0.5);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 12px;
    padding: 1rem;
}

.console-output-box.error {
    border-color: rgba(229, 62, 62, 0.4);
    background: rgba(229, 62, 62, 0.05);
}

.console-label {
    display: block;
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    color: #a0aec0;
    margin-bottom: 0.5rem;
}

.console-text {
    font-family: monospace;
    font-size: 0.85rem;
    color: #48bb78;
    white-space: pre-wrap;
    word-break: break-all;
    margin: 0;
}

.console-output-box.error .console-text {
    color: #fc8181;
}

/* Scrollable Payout Cards Container */
.payout-cards-container {
    max-height: 460px;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
    padding-right: 4px;
}

.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
    height: 6px;
}

.custom-scrollbar::-webkit-scrollbar-track {
    background: rgba(255, 255, 255, 0.02);
    border-radius: 10px;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.15);
    border-radius: 10px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: rgba(255, 255, 255, 0.25);
}

.empty-requests-msg {
    text-align: center;
    padding: 3rem 1.5rem;
    color: #a0aec0;
}

.checked-icon {
    font-size: 2.5rem;
    color: #48bb78;
    margin-bottom: 1rem;
    display: block;
}

/* Payout Request Card */
.payout-request-card {
    background: rgba(0, 0, 0, 0.25);
    border: 1px solid rgba(255, 255, 255, 0.06);
    border-radius: 16px;
    padding: 1.25rem;
    display: flex;
    flex-direction: column;
    gap: 1rem;
    transition: border-color 0.2s ease;
}

.payout-request-card:hover {
    border-color: rgba(255, 255, 255, 0.12);
}

.card-creator-info {
    display: flex;
    align-items: center;
    gap: 0.85rem;
}

.creator-avatar {
    width: 44px;
    height: 44px;
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
    color: #fff;
}

.creator-handle {
    font-size: 0.8rem;
    color: #a0aec0;
}

.payout-amount {
    font-size: 1.35rem;
    font-weight: 800;
    color: #48bb78;
}

.card-details-box {
    background: rgba(255, 255, 255, 0.02);
    border: 1px solid rgba(255, 255, 255, 0.04);
    border-radius: 12px;
    padding: 0.85rem 1rem;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.details-label {
    font-size: 0.75rem;
    color: #a0aec0;
    font-weight: 600;
    text-transform: uppercase;
}

.details-value {
    font-weight: 700;
    color: #fff;
    font-size: 0.9rem;
}

.details-text-area {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
    margin-top: 0.25rem;
}

.details-text {
    font-family: monospace;
    font-size: 0.85rem;
    color: #cbd5e0;
    margin: 0;
    white-space: pre-wrap;
    word-break: break-all;
    background: rgba(0, 0, 0, 0.3);
    padding: 0.5rem;
    border-radius: 8px;
}

.card-actions {
    display: flex;
    gap: 0.75rem;
}

.action-btn {
    flex: 1;
    border: none;
    border-radius: 10px;
    padding: 0.65rem 1rem;
    font-weight: 700;
    font-size: 0.85rem;
    cursor: pointer;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.4rem;
}

.approve-btn {
    background: rgba(72, 187, 120, 0.15);
    color: #48bb78;
    border: 1px solid rgba(72, 187, 120, 0.3);
}

.approve-btn:hover {
    background: #48bb78;
    color: #fff;
}

.reject-btn {
    background: rgba(229, 62, 62, 0.15);
    color: #f56565;
    border: 1px solid rgba(229, 62, 62, 0.3);
}

.reject-btn:hover {
    background: #e53e3e;
    color: #fff;
}

/* History Logs & Full Width Panels */
.full-width {
    width: 100%;
}

.panel-header-with-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    flex-wrap: wrap;
    gap: 1rem;
}

.export-csv-btn {
    background: rgba(255, 255, 255, 0.06);
    border: 1px solid rgba(255, 255, 255, 0.15);
    color: #fff;
    padding: 0.5rem 1rem;
    border-radius: 10px;
    font-weight: 600;
    font-size: 0.85rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.2s ease;
}

.export-csv-btn:hover {
    background: rgba(255, 255, 255, 0.12);
    border-color: rgba(255, 255, 255, 0.25);
}

/* Filters */
.filters-container {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
    margin-bottom: 1.5rem;
    background: rgba(0, 0, 0, 0.2);
    padding: 1rem;
    border-radius: 16px;
    border: 1px solid rgba(255, 255, 255, 0.04);
}

.filter-item {
    display: flex;
    flex-direction: column;
    gap: 0.3rem;
    flex: 1;
    min-width: 150px;
}

.filter-label {
    font-size: 0.75rem;
    color: #a0aec0;
    font-weight: 600;
}

.filter-input,
.filter-select {
    background: rgba(255, 255, 255, 0.04);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 10px;
    padding: 0.5rem 0.75rem;
    color: #fff;
    font-size: 0.85rem;
}

.filter-input:focus,
.filter-select:focus {
    outline: none;
    border-color: #e8445a;
}

.filter-select option {
    background: #1a202c;
    color: #fff;
}

.filter-actions-wrapper {
    display: flex;
    align-items: flex-end;
}

.clear-filters-btn {
    background: rgba(255, 255, 255, 0.08);
    border: none;
    color: #a0aec0;
    padding: 0.55rem 1rem;
    border-radius: 10px;
    font-size: 0.85rem;
    cursor: pointer;
    transition: all 0.2s ease;
}

.clear-filters-btn:hover {
    color: #fff;
    background: rgba(255, 255, 255, 0.15);
}

/* Scrollable Table Container with Sticky Headers */
.table-scroll-container {
    max-height: 480px;
    overflow-y: auto;
    overflow-x: auto;
    border-radius: 14px;
    border: 1px solid rgba(255, 255, 255, 0.06);
    background: rgba(0, 0, 0, 0.2);
}

.table-scroll-container .admin-table th {
    position: sticky;
    top: 0;
    background: #151a24;
    z-index: 10;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.5);
}

/* Metric Pills */
.metric-pill {
    display: inline-block;
    padding: 0.3rem 0.75rem;
    border-radius: 20px;
    font-weight: 700;
    font-size: 0.85rem;
    white-space: nowrap;
}

.metric-pill.lifetime {
    background: rgba(72, 187, 120, 0.12);
    color: #48bb78;
    border: 1px solid rgba(72, 187, 120, 0.25);
}

.metric-pill.balance {
    background: rgba(49, 130, 206, 0.12);
    color: #63b3ed;
    border: 1px solid rgba(49, 130, 206, 0.25);
}

.metric-pill.paid {
    background: rgba(229, 62, 62, 0.12);
    color: #f56565;
    border: 1px solid rgba(229, 62, 62, 0.25);
}

.metric-pill.pending {
    background: rgba(237, 137, 86, 0.12);
    color: #ed8936;
    border: 1px solid rgba(237, 137, 86, 0.25);
}

/* Admin Table */
.admin-table {
    width: 100%;
    border-collapse: collapse;
    text-align: left;
    font-size: 0.9rem;
}

.admin-table th {
    padding: 0.85rem 1rem;
    color: #a0aec0;
    font-weight: 600;
    border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    white-space: nowrap;
}

.admin-table td {
    padding: 1rem;
    border-bottom: 1px solid rgba(255, 255, 255, 0.04);
    vertical-align: middle;
}

.table-creator-cell {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.table-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    object-fit: cover;
}

.badge {
    padding: 0.25rem 0.6rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    display: inline-block;
}

.badge-pending {
    background: rgba(237, 137, 86, 0.15);
    color: #ed8936;
    border: 1px solid rgba(237, 137, 86, 0.3);
}

.badge-approved {
    background: rgba(72, 187, 120, 0.15);
    color: #48bb78;
    border: 1px solid rgba(72, 187, 120, 0.3);
}

.badge-rejected {
    background: rgba(229, 62, 62, 0.15);
    color: #f56565;
    border: 1px solid rgba(229, 62, 62, 0.3);
}

.table-rejection {
    font-size: 0.75rem;
    color: #e53e3e;
    margin-top: 4px;
}

.reference-text {
    font-size: 0.75rem;
    color: #a0aec0;
    margin-top: 2px;
    font-family: monospace;
}

.row-action-btn {
    border: none;
    border-radius: 8px;
    padding: 0.4rem 0.75rem;
    font-size: 0.8rem;
    font-weight: 600;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    transition: all 0.2s ease;
}

.row-action-btn.details {
    background: rgba(66, 153, 225, 0.15);
    color: #63b3ed;
    border: 1px solid rgba(66, 153, 225, 0.3);
}

.row-action-btn.details:hover {
    background: #3182ce;
    color: #fff;
}

.row-action-btn.receipt {
    background: rgba(128, 90, 213, 0.15);
    color: #b794f4;
    border: 1px solid rgba(128, 90, 213, 0.3);
}

.row-action-btn.receipt:hover {
    background: #805ad5;
    color: #fff;
}

.empty-table-msg {
    text-align: center;
    padding: 2.5rem;
    color: #718096;
}

/* Modals */
.reject-modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background: rgba(0, 0, 0, 0.8);
    backdrop-filter: blur(8px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
}

.reject-modal-content {
    background: #1a202c;
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 20px;
    width: 90%;
    max-width: 500px;
    padding: 1.5rem;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
}

.details-modal-content {
    max-width: 600px;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    padding-bottom: 1rem;
}

.modal-header h3 {
    font-size: 1.2rem;
    font-weight: 700;
    margin: 0;
}

.close-btn {
    background: none;
    border: none;
    font-size: 1.5rem;
    color: #a0aec0;
    cursor: pointer;
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem;
    margin-top: 1.5rem;
}

.modal-btn {
    border: none;
    border-radius: 10px;
    padding: 0.6rem 1.25rem;
    font-weight: 700;
    font-size: 0.85rem;
    cursor: pointer;
}

.modal-btn.secondary {
    background: rgba(255, 255, 255, 0.08);
    color: #a0aec0;
}

.modal-btn.danger {
    background: #e53e3e;
    color: #fff;
}

/* Lightbox Preview */
.receipt-lightbox-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background: rgba(0, 0, 0, 0.9);
    backdrop-filter: blur(10px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10000;
}

.receipt-lightbox-content {
    background: #1a202c;
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 20px;
    width: 90%;
    max-width: 800px;
    max-height: 90vh;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.lightbox-header {
    padding: 1rem 1.5rem;
    border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.lightbox-header h4 {
    margin: 0;
    font-size: 1.1rem;
}

.lightbox-close-btn {
    background: none;
    border: none;
    color: #a0aec0;
    font-size: 1.5rem;
    cursor: pointer;
}

.lightbox-body {
    padding: 1.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow-y: auto;
    max-height: 70vh;
    background: #0d1117;
}

.pdf-preview-frame {
    width: 100%;
    height: 550px;
    border: none;
}

.img-preview-frame {
    max-width: 100%;
    max-height: 550px;
    object-fit: contain;
    border-radius: 12px;
}

.lightbox-footer {
    padding: 1rem 1.5rem;
    border-top: 1px solid rgba(255, 255, 255, 0.08);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.detail-creator-row {
    display: flex;
    align-items: center;
    gap: 1rem;
    background: rgba(255, 255, 255, 0.02);
    padding: 1rem;
    border-radius: 12px;
}

.details-summary-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
}

.summary-info-item {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.info-label {
    font-size: 0.75rem;
    color: #a0aec0;
    font-weight: 600;
}

.info-value {
    font-weight: 700;
    color: #fff;
}

.detail-box-container {
    background: rgba(0, 0, 0, 0.3);
    border: 1px solid rgba(255, 255, 255, 0.06);
    border-radius: 12px;
    padding: 1rem;
}

.box-title {
    font-size: 0.85rem;
    font-weight: 700;
    margin: 0 0 0.5rem 0;
    color: #e8445a;
}

.box-content {
    font-family: monospace;
    font-size: 0.85rem;
    color: #e2e8f0;
    margin: 0;
    white-space: pre-wrap;
    word-break: break-all;
}

.resolution-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 0.75rem;
    margin-top: 0.5rem;
}

.resolution-item {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.resolution-item.full-width {
    grid-column: 1 / -1;
}

.res-label {
    font-size: 0.75rem;
    color: #a0aec0;
}

.res-value {
    font-weight: 600;
    font-size: 0.88rem;
    color: #fff;
}

@media (max-width: 768px) {
    .panel-header-with-badge {
        flex-direction: column !important;
        align-items: flex-start !important;
        gap: 0.5rem !important;
    }

    .panel-title {
        font-size: 1.15rem !important;
    }

    .pending-count-badge {
        align-self: flex-start !important;
    }

    .card-creator-info {
        flex-wrap: wrap !important;
        gap: 0.5rem !important;
    }

    .creator-meta {
        min-width: 120px !important;
    }

    .payout-amount {
        font-size: 1.15rem !important;
        margin-left: auto !important;
    }

    .card-details-box {
        padding: 0.65rem 0.85rem !important;
    }

    .details-text {
        font-size: 0.8rem !important;
        word-break: break-all !important;
    }

    .card-actions {
        flex-direction: row !important;
        gap: 0.5rem !important;
    }

    .card-actions .action-btn {
        padding: 0.5rem 0.65rem !important;
        font-size: 0.78rem !important;
        justify-content: center !important;
        flex: 1 !important;
    }

    .admin-layout-grid {
        grid-template-columns: 1fr !important;
    }

    .stats-summary-grid {
        grid-template-columns: 1fr !important;
    }

    .table-scroll-container,
    .admin-table-container {
        width: 100% !important;
        overflow-x: auto !important;
        -webkit-overflow-scrolling: touch !important;
    }

    .admin-table {
        min-width: 580px !important;
    }
}
</style>
