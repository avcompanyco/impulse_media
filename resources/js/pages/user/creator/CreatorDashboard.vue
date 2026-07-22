<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { useForm, router, usePage, Link } from '@inertiajs/vue3';
import UserDashboardLayout from '@/layouts/UserDashboardLayout.vue';
import MovieChannelController from '@/actions/App/Http/Controllers/Channel/MovieChannelController';
import DeleteContentModal from '@/pages/admin/content/partials/DeleteContentModal.vue';

interface Stats {
    lifetime_earnings: number;
    current_balance: number;
    withdrawn: number;
    pending_payouts: number;
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
    created_at: string;
}

interface Earning {
    id: number;
    amount: number;
    source: string;
    description: string;
    created_at: string;
}

interface ContentItem {
    id: number;
    contentable_id: number;
    title: string;
    type: string;
    views_count: number;
    ppv_price: number;
    allow_membership: boolean;
    sales_count: number;
    revenue: number;
}

interface Settings {
    min_payout_threshold: number;
    min_ppv_price: number;
    revenue_split_ratio: number;
}

const props = defineProps<{
    stats: Stats;
    payouts: Payout[];
    earnings: Earning[];
    contents: ContentItem[];
    settings: Settings;
}>();

// Payout Form
const user = usePage().props.auth.user as any;
const payoutForm = useForm({
    amount: props.settings.min_payout_threshold,
    payout_method: user.payout_method || 'paypal',
    payout_details: user.payout_details || '',
});

const isSubmittingPayout = ref(false);

function submitPayoutRequest() {
    isSubmittingPayout.value = true;
    payoutForm.post('/creator/payout-request', {
        preserveScroll: true,
        onSuccess: () => {
            const updatedUser = usePage().props.auth.user as any;
            payoutForm.payout_method = updatedUser.payout_method || 'paypal';
            payoutForm.payout_details = updatedUser.payout_details || '';
            payoutForm.amount = props.settings.min_payout_threshold;
            isSubmittingPayout.value = false;
            router.reload({ only: ['payouts', 'stats'] });
        },
        onError: () => {
            isSubmittingPayout.value = false;
        }
    });
}

// Content Pricing Form Management (per content item)
const activeEditingContent = ref<number | null>(null);
const editPrice = ref(0);
const editAllowMembership = ref(false);

function startEditing(item: ContentItem) {
    activeEditingContent.value = item.id;
    editPrice.value = item.ppv_price;
    editAllowMembership.value = item.allow_membership;
}

function cancelEditing() {
    activeEditingContent.value = null;
}

function saveContentPricing(itemId: number) {
    router.put(`/creator/content/${itemId}/pricing`, {
        ppv_price: editPrice.value,
        allow_membership: editAllowMembership.value ? 1 : 0,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            activeEditingContent.value = null;
            router.reload({ only: ['contents'] });
        }
    });
}

function formatDate(dateStr: string) {
    const d = new Date(dateStr);
    return d.toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
}

// Search, Filter & Pagination
const searchQuery = ref('');
const filterType = ref('all');
const currentPage = ref(1);
const itemsPerPage = 5;

const filteredContents = computed(() => {
    return props.contents.filter((item: any) => {
        const matchesSearch = item.title.toLowerCase().includes(searchQuery.value.toLowerCase());
        const matchesType = filterType.value === 'all' || 
                            (filterType.value === 'movie' && item.type === 'movie') ||
                            (filterType.value === 'series' && item.type === 'series');
        return matchesSearch && matchesType;
    });
});

const paginatedContents = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage;
    const end = start + itemsPerPage;
    return filteredContents.value.slice(start, end);
});

const totalPages = computed(() => {
    return Math.ceil(filteredContents.value.length / itemsPerPage) || 1;
});

watch([searchQuery, filterType], () => {
    currentPage.value = 1;
});

function prevPage() {
    if (currentPage.value > 1) {
        currentPage.value--;
    }
}

function nextPage() {
    if (currentPage.value < totalPages.value) {
        currentPage.value++;
    }
}

function getEditUrl(item: ContentItem) {
    if (item.type === 'movie' || item.type === 'movies') {
        return `/movie/${item.contentable_id}/edit`;
    } else if (item.type === 'series') {
        return `/serie/${item.contentable_id}/edit`;
    } else if (item.type === 'short' || item.type === 'shorts') {
        return `/short/${item.contentable_id}/edit`;
    }
    return '#';
}

function refreshContents() {
    router.reload({ only: ['contents', 'stats'] });
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
    <UserDashboardLayout 
        :title="`Creator Dashboard - ${$page.props.name || 'Impulsemedia'}`" 
        :headerTitle="`Creator Dashboard - ${$page.props.name || 'Impulsemedia'}`"
    >
        <div class="creator-dashboard-container">
            <!-- Header -->
            <div class="dashboard-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
                <div>
                    <h1 class="dashboard-title">Creator Hub</h1>
                    <p class="dashboard-subtitle">Manage your content pricing, watch analytics, and revenue earnings.</p>
                </div>
                <div style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
                    <Link href="/upload/movie" class="upload-content-btn">
                        <i class="fa-solid fa-cloud-arrow-up"></i> Upload Content
                    </Link>
                    <Link :href="MovieChannelController()" class="view-channel-btn">
                        <i class="fa-solid fa-user"></i> View My Channel
                    </Link>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card balance-card">
                    <div class="stat-icon-wrapper">
                        <i class="fa-solid fa-wallet"></i>
                    </div>
                    <div class="stat-details">
                        <span class="stat-label">Available Balance</span>
                        <h2 class="stat-value">${{ Number(stats.current_balance).toFixed(2) }}</h2>
                    </div>
                </div>

                <div class="stat-card lifetime-card">
                    <div class="stat-icon-wrapper">
                        <i class="fa-solid fa-chart-line"></i>
                    </div>
                    <div class="stat-details">
                        <span class="stat-label">Lifetime Earnings</span>
                        <h2 class="stat-value">${{ Number(stats.lifetime_earnings).toFixed(2) }}</h2>
                    </div>
                </div>

                <div class="stat-card withdrawn-card">
                    <div class="stat-icon-wrapper">
                        <i class="fa-solid fa-hand-holding-dollar"></i>
                    </div>
                    <div class="stat-details">
                        <span class="stat-label">Total Withdrawn</span>
                        <h2 class="stat-value">${{ Number(stats.withdrawn).toFixed(2) }}</h2>
                    </div>
                </div>

                <div class="stat-card pending-card">
                    <div class="stat-icon-wrapper">
                        <i class="fa-solid fa-hourglass-half"></i>
                    </div>
                    <div class="stat-details">
                        <span class="stat-label">Pending Payouts</span>
                        <h2 class="stat-value">${{ Number(stats.pending_payouts).toFixed(2) }}</h2>
                    </div>
                </div>
            </div>

            <!-- Two Column Layout -->
            <div class="dashboard-layout">
                <!-- Left Column: Payout Requests & Payout History -->
                <div class="layout-column">
                    <!-- Request Payout -->
                    <div class="dashboard-panel">
                        <h3 class="panel-title"><i class="fa-solid fa-money-bill-transfer text-accent"></i> Request Payout</h3>
                        
                        <div class="revenue-split-info-box" style="margin-bottom: 1.5rem; background: rgba(72, 187, 120, 0.05); border: 1px solid rgba(72, 187, 120, 0.15); border-radius: 12px; padding: 0.9rem; display: flex; align-items: flex-start; gap: 0.75rem;">
                            <i class="fa-solid fa-circle-check text-success" style="font-size: 1.1rem; margin-top: 0.1rem;"></i>
                            <p style="margin: 0; font-size: 0.85rem; color: #cbd5e0; line-height: 1.4;">
                                You receive <strong>{{ settings.revenue_split_ratio }}%</strong> of all PPV sales and proportional membership views. The platform commission is only <strong>{{ 100 - settings.revenue_split_ratio }}%</strong>.
                            </p>
                        </div>
                        
                        <div v-if="stats.current_balance < settings.min_payout_threshold" class="threshold-alert">
                            <i class="fa-solid fa-circle-info alert-icon"></i>
                            <p>You need a minimum balance of <strong>${{ Number(settings.min_payout_threshold).toFixed(2) }}</strong> to request a payout. (Current: ${{ Number(stats.current_balance).toFixed(2) }})</p>
                        </div>

                        <form v-else @submit.prevent="submitPayoutRequest" class="payout-form">
                            <div class="form-group">
                                <label class="form-label">Withdrawal Amount ($)</label>
                                <input 
                                    type="number" 
                                    v-model="payoutForm.amount" 
                                    class="form-control" 
                                    step="0.01" 
                                    :min="settings.min_payout_threshold" 
                                    :max="stats.current_balance"
                                    required
                                >
                                <span class="form-help">Minimum: ${{ Number(settings.min_payout_threshold).toFixed(2) }} | Max: ${{ Number(stats.current_balance).toFixed(2) }}</span>
                                <span v-if="payoutForm.errors.amount" class="error-msg">{{ payoutForm.errors.amount }}</span>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Payout Method</label>
                                <select v-model="payoutForm.payout_method" class="form-control">
                                    <option value="paypal">PayPal</option>
                                    <option value="bank_transfer">Bank Transfer</option>
                                </select>
                                <span v-if="payoutForm.errors.payout_method" class="error-msg">{{ payoutForm.errors.payout_method }}</span>
                            </div>

                            <div class="form-group">
                                <label class="form-label">
                                    {{ payoutForm.payout_method === 'paypal' ? 'PayPal Email Address' : 'Bank Transfer Details' }}
                                </label>
                                <textarea 
                                    v-model="payoutForm.payout_details" 
                                    class="form-control" 
                                    rows="4" 
                                    :placeholder="payoutForm.payout_method === 'paypal' 
                                        ? 'Enter your PayPal email address (e.g., account@paypal.com)...' 
                                        : 'Please enter all details:\n- Bank Name:\n- Account Holder Name:\n- IBAN / Account Number:\n- Swift / BIC Code:'"
                                    required
                                ></textarea>
                                <span v-if="payoutForm.errors.payout_details" class="error-msg">{{ payoutForm.errors.payout_details }}</span>
                            </div>

                            <button type="submit" class="submit-btn" :disabled="isSubmittingPayout">
                                <i class="fa-solid fa-circle-notch fa-spin" v-if="isSubmittingPayout"></i>
                                Request Withdrawal
                            </button>
                        </form>
                    </div>

                    <!-- Payout History -->
                    <div class="dashboard-panel">
                        <h3 class="panel-title"><i class="fa-solid fa-history text-accent"></i> Payout History</h3>
                        <div class="table-responsive">
                            <table class="dashboard-table">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Amount</th>
                                        <th>Method</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-if="payouts.length === 0">
                                        <td colspan="4" class="empty-table-msg">No payout requests yet.</td>
                                    </tr>
                                    <tr v-for="payout in payouts" :key="payout.id">
                                        <td>{{ formatDate(payout.created_at) }}</td>
                                        <td class="font-bold">${{ Number(payout.amount).toFixed(2) }}</td>
                                        <td class="text-capitalize">{{ payout.payout_method.replace('_', ' ') }}</td>
                                        <td>
                                            <span class="badge" :class="getStatusBadgeClass(payout.status)">
                                                {{ payout.status }}
                                            </span>
                                            <div v-if="payout.status === 'rejected' && payout.rejection_reason" class="rejection-reason">
                                                Reason: {{ payout.rejection_reason }}
                                            </div>
                                            <div v-if="payout.status === 'approved'" class="approved-details">
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
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Content Listing & Pricing Toggles -->
                <div class="layout-column">
                    <div class="dashboard-panel">
                        <h3 class="panel-title" style="margin-bottom: 1.25rem;"><i class="fa-solid fa-video text-accent"></i> Content Pricing & Performance</h3>
                        
                        <!-- Search & Filter Controls -->
                        <div class="list-controls-wrapper" style="margin-bottom: 1.5rem; display: flex; flex-direction: column; gap: 1rem;">
                            <div class="search-input-container" style="position: relative; width: 100%;">
                                <i class="fa-solid fa-magnifying-glass search-icon" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #718096; font-size: 0.9rem;"></i>
                                <input 
                                    type="text" 
                                    v-model="searchQuery" 
                                    placeholder="Search by title..." 
                                    class="form-control search-input" 
                                    style="padding-left: 2.5rem; width: 100%; box-sizing: border-box;"
                                >
                            </div>
                            <div class="filter-buttons-group" style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                                <button 
                                    type="button" 
                                    class="filter-pill-btn" 
                                    :class="{ active: filterType === 'all' }"
                                    @click="filterType = 'all'"
                                >
                                    All Content
                                </button>
                                <button 
                                    type="button" 
                                    class="filter-pill-btn" 
                                    :class="{ active: filterType === 'movie' }"
                                    @click="filterType = 'movie'"
                                >
                                    Movies
                                </button>
                                <button 
                                    type="button" 
                                    class="filter-pill-btn" 
                                    :class="{ active: filterType === 'series' }"
                                    @click="filterType = 'series'"
                                >
                                    Series
                                </button>
                            </div>
                        </div>

                        <div class="content-list-container">
                            <div v-if="filteredContents.length === 0" class="empty-list-msg">
                                <i class="fa-solid fa-video-slash text-muted-icon"></i>
                                <p>{{ contents.length === 0 ? "You haven't uploaded any content yet." : "No content matches your search/filters." }}</p>
                            </div>

                            <div v-for="item in paginatedContents" :key="item.id" class="content-item-row">
                                <div class="item-meta-info">
                                    <span class="item-title">{{ item.title }}</span>
                                    <div class="item-badges">
                                        <span class="item-badge-type" :class="item.type">
                                            {{ item.type }}
                                        </span>
                                        <span class="item-badge-stats">
                                            <i class="fa-solid fa-eye"></i> {{ item.views_count }} views
                                        </span>
                                        <span class="item-badge-stats">
                                            <i class="fa-solid fa-shopping-cart"></i> {{ item.sales_count }} sales
                                        </span>
                                        <span class="item-badge-stats font-bold text-success">
                                            ${{ Number(item.revenue).toFixed(2) }} earned
                                        </span>
                                    </div>
                                </div>

                                <!-- Pricing Controls -->
                                <div class="pricing-controls">
                                    <!-- Inline Edit Form -->
                                    <div v-if="activeEditingContent === item.id" class="inline-edit-box">
                                        <div class="inline-inputs">
                                            <div class="price-input-wrapper">
                                                <span class="currency-symbol">$</span>
                                                <input 
                                                    type="number" 
                                                    v-model="editPrice" 
                                                    class="form-control inline-price-input" 
                                                    step="0.01" 
                                                    min="0"
                                                    placeholder="0.00"
                                                >
                                            </div>
                                            <label class="membership-checkbox-label">
                                                <input type="checkbox" v-model="editAllowMembership">
                                                <span>Allow Membership</span>
                                            </label>
                                        </div>
                                        <div class="inline-actions">
                                            <button class="icon-btn save-btn" title="Save" @click="saveContentPricing(item.id)">
                                                <i class="fa-solid fa-check"></i>
                                            </button>
                                            <button class="icon-btn cancel-btn" title="Cancel" @click="cancelEditing">
                                                <i class="fa-solid fa-xmark"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Read Only pricing -->
                                    <div v-else class="read-only-pricing">
                                        <div class="pricing-labels">
                                            <span class="price-display">
                                                PPV Price: <strong>{{ item.ppv_price > 0 ? `$${Number(item.ppv_price).toFixed(2)}` : 'FREE' }}</strong>
                                            </span>
                                            <span class="membership-display" :class="{ allowed: item.allow_membership }">
                                                <i :class="item.allow_membership ? 'fa-solid fa-circle-check text-success' : 'fa-solid fa-circle-xmark text-danger'"></i>
                                                {{ item.allow_membership ? 'Member Access Allowed' : 'PPV Exclusive' }}
                                            </span>
                                        </div>
                                        <div class="content-action-buttons">
                                            <button class="edit-price-btn" @click="startEditing(item)">
                                                <i class="fa-solid fa-pen"></i> Edit Pricing
                                            </button>
                                            <Link :href="getEditUrl(item)" class="edit-content-btn" title="Edit content details">
                                                <i class="fa-solid fa-pen-to-square"></i> Edit Content
                                            </Link>
                                            <DeleteContentModal :content="item" @updated="refreshContents" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pagination Controls -->
                        <div v-if="totalPages > 1" class="pagination-wrapper">
                            <span class="pagination-info">
                                Showing {{ (currentPage - 1) * itemsPerPage + 1 }} - {{ Math.min(currentPage * itemsPerPage, filteredContents.length) }} of {{ filteredContents.length }} contents
                            </span>
                            <div class="pagination-buttons">
                                <button 
                                    type="button" 
                                    class="page-nav-btn" 
                                    :disabled="currentPage === 1"
                                    @click="prevPage"
                                >
                                    <i class="fa-solid fa-chevron-left"></i> Previous
                                </button>
                                <span class="page-current">
                                    {{ currentPage }} / {{ totalPages }}
                                </span>
                                <button 
                                    type="button" 
                                    class="page-nav-btn" 
                                    :disabled="currentPage === totalPages"
                                    @click="nextPage"
                                >
                                    Next <i class="fa-solid fa-chevron-right"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Earnings Log (Bottom) -->
            <div class="dashboard-panel full-width">
                <h3 class="panel-title"><i class="fa-solid fa-receipt text-accent"></i> Recent Earnings Log</h3>
                <div class="table-responsive">
                    <table class="dashboard-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Source</th>
                                <th>Description</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="earnings.length === 0">
                                <td colspan="4" class="empty-table-msg">No earnings recorded yet.</td>
                            </tr>
                            <tr v-for="earning in earnings" :key="earning.id">
                                <td>{{ formatDate(earning.created_at) }}</td>
                                <td class="text-capitalize">{{ earning.source.replace('_', ' ') }}</td>
                                <td>{{ earning.description }}</td>
                                <td class="font-bold text-success">+${{ Number(earning.amount).toFixed(2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </UserDashboardLayout>
</template>

<style scoped>
/* Dashboard Styling */
.creator-dashboard-container {
    padding: 1.5rem;
    color: #fff;
    max-width: 1400px;
    margin: 0 auto;
    display: flex;
    flex-direction: column;
    gap: 2rem;
    padding-bottom: 100px;
}

.dashboard-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    padding-bottom: 1.5rem;
}

.dashboard-title {
    font-size: 2.25rem;
    font-weight: 800;
    margin: 0 0 0.5rem 0;
    background: linear-gradient(135deg, #e8445a 0%, #ff6b81 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.dashboard-subtitle {
    font-size: 0.95rem;
    color: #a0aec0;
    margin: 0;
}

/* Stats Cards */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 1.5rem;
}

.stat-card {
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 20px;
    padding: 1.5rem;
    display: flex;
    align-items: center;
    gap: 1.25rem;
    transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
}

.stat-card:hover {
    transform: translateY(-3px);
    border-color: rgba(232, 68, 90, 0.2);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.3);
}

.stat-icon-wrapper {
    width: 54px;
    height: 54px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    flex-shrink: 0;
}

.balance-card .stat-icon-wrapper {
    background: rgba(72, 187, 120, 0.1);
    color: #48bb78;
    border: 1px solid rgba(72, 187, 120, 0.2);
}

.lifetime-card .stat-icon-wrapper {
    background: rgba(66, 153, 225, 0.1);
    color: #4299e1;
    border: 1px solid rgba(66, 153, 225, 0.2);
}

.withdrawn-card .stat-icon-wrapper {
    background: rgba(237, 137, 54, 0.1);
    color: #ed8936;
    border: 1px solid rgba(237, 137, 54, 0.2);
}

.pending-card .stat-icon-wrapper {
    background: rgba(159, 122, 234, 0.1);
    color: #9f7aea;
    border: 1px solid rgba(159, 122, 234, 0.2);
}

.stat-details {
    display: flex;
    flex-direction: column;
}

.stat-label {
    font-size: 0.8rem;
    color: #a0aec0;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.stat-value {
    font-size: 1.75rem;
    font-weight: 800;
    margin: 0.25rem 0 0 0;
}

/* Layout */
.dashboard-layout {
    display: grid;
    grid-template-columns: 1fr 1.2fr;
    gap: 2rem;
}

@media (max-width: 1024px) {
    .dashboard-layout {
        grid-template-columns: 1fr;
    }
}

.layout-column {
    display: flex;
    flex-direction: column;
    gap: 2rem;
}

.dashboard-panel {
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

/* Payout Request Panel */
.threshold-alert {
    background: rgba(232, 68, 90, 0.08);
    border: 1px solid rgba(232, 68, 90, 0.2);
    border-radius: 12px;
    padding: 1rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 1.5rem;
    color: #ff8a9a;
    font-size: 0.9rem;
}

.alert-icon {
    font-size: 1.2rem;
    flex-shrink: 0;
}

.payout-form {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
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

select.form-control option {
    background-color: #1a1a24;
    color: #fff;
}

.form-help {
    font-size: 0.75rem;
    color: #718096;
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
    transform: none;
    box-shadow: none;
}

/* Dashboard Tables */
.table-responsive {
    width: 100%;
    overflow-x: auto;
    max-height: 380px;
    overflow-y: auto;
}

.dashboard-table {
    width: 100%;
    border-collapse: collapse;
    text-align: left;
}

.dashboard-table th {
    padding: 1rem;
    font-size: 0.85rem;
    color: #a0aec0;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    border-bottom: 2px solid rgba(255, 255, 255, 0.05);
}

.dashboard-table td {
    padding: 1rem;
    font-size: 0.9rem;
    border-bottom: 1px solid rgba(255, 255, 255, 0.05);
}

.empty-table-msg {
    text-align: center;
    color: #718096;
    padding: 2rem !important;
}

.font-bold {
    font-weight: 700;
}

.text-success {
    color: #48bb78;
}

.text-danger {
    color: #e53e3e;
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

.rejection-reason {
    font-size: 0.75rem;
    color: #e53e3e;
    margin-top: 4px;
    font-style: italic;
}

/* Content Listing */
.content-list-container {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
    max-height: 480px;
    overflow-y: auto;
    padding-right: 6px;
}

.empty-list-msg {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 3rem;
    color: #718096;
    text-align: center;
    gap: 1rem;
}

.text-muted-icon {
    font-size: 2.5rem;
    color: rgba(255, 255, 255, 0.08);
}

.content-item-row {
    background: rgba(255, 255, 255, 0.01);
    border: 1px solid rgba(255, 255, 255, 0.04);
    border-radius: 16px;
    padding: 1.25rem;
    display: flex;
    flex-direction: column;
    gap: 1rem;
    transition: all 0.2s ease;
}

.content-item-row:hover {
    background: rgba(255, 255, 255, 0.03);
    border-color: rgba(255, 255, 255, 0.1);
}

.item-meta-info {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.item-title {
    font-size: 1.1rem;
    font-weight: 700;
    color: #fff;
}

.item-badges {
    display: flex;
    gap: 0.75rem;
    flex-wrap: wrap;
    align-items: center;
}

.item-badge-type {
    font-size: 0.7rem;
    font-weight: 700;
    padding: 0.15rem 0.5rem;
    border-radius: 6px;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.item-badge-type.movie {
    background: rgba(66, 153, 225, 0.15);
    color: #4299e1;
}

.item-badge-type.series {
    background: rgba(159, 122, 234, 0.15);
    color: #9f7aea;
}

.item-badge-stats {
    font-size: 0.75rem;
    color: #a0aec0;
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

/* Pricing Controls */
.pricing-controls {
    display: flex;
    justify-content: flex-end;
    align-items: center;
    border-top: 1px solid rgba(255, 255, 255, 0.04);
    padding-top: 0.75rem;
}

.read-only-pricing {
    display: flex;
    width: 100%;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
}

.pricing-labels {
    display: flex;
    gap: 1.25rem;
    align-items: center;
    flex-wrap: wrap;
}

.price-display {
    font-size: 0.9rem;
    color: #cbd5e0;
}

.membership-display {
    font-size: 0.85rem;
    display: flex;
    align-items: center;
    gap: 0.35rem;
    color: #a0aec0;
}

.membership-display.allowed {
    color: #48bb78;
}

.edit-price-btn {
    background: rgba(255, 255, 255, 0.08);
    border: 1px solid rgba(255, 255, 255, 0.15);
    color: #fff;
    border-radius: 8px;
    padding: 0.4rem 0.8rem;
    font-size: 0.8rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    gap: 0.35rem;
}

.edit-price-btn:hover {
    background: #e8445a;
    border-color: #e8445a;
    transform: translateY(-1px);
}

.content-action-buttons {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.edit-content-btn {
    background: rgba(66, 153, 225, 0.12);
    border: 1px solid rgba(66, 153, 225, 0.25);
    color: #60a5fa;
    border-radius: 8px;
    padding: 0.4rem 0.8rem;
    font-size: 0.8rem;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
}

.edit-content-btn:hover {
    background: #3b82f6;
    color: #fff;
    border-color: #3b82f6;
    transform: translateY(-1px);
}

/* Inline Edit Box */
.inline-edit-box {
    display: flex;
    width: 100%;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
    background: rgba(255, 255, 255, 0.02);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 12px;
    padding: 0.5rem 1rem;
}

.inline-inputs {
    display: flex;
    align-items: center;
    gap: 1.5rem;
    flex-wrap: wrap;
}

.price-input-wrapper {
    position: relative;
    display: flex;
    align-items: center;
}

.currency-symbol {
    position: absolute;
    left: 10px;
    color: #a0aec0;
    font-size: 0.9rem;
}

.inline-price-input {
    padding: 0.4rem 0.5rem 0.4rem 1.5rem !important;
    width: 100px !important;
    border-radius: 8px !important;
    font-size: 0.9rem !important;
}

.membership-checkbox-label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.85rem;
    font-weight: 600;
    color: #cbd5e0;
    cursor: pointer;
}

.inline-actions {
    display: flex;
    gap: 0.5rem;
}

.icon-btn {
    border: none;
    border-radius: 8px;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 0.85rem;
    color: #fff;
    transition: all 0.2s ease;
}

.icon-btn.save-btn {
    background: #48bb78;
}

.icon-btn.save-btn:hover {
    background: #38a169;
    transform: scale(1.05);
}

.icon-btn.cancel-btn {
    background: #718096;
}

.icon-btn.cancel-btn:hover {
    background: #4a5568;
    transform: scale(1.05);
}

.approved-details {
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

.view-channel-btn {
    background-color: var(--primary-color);
    color: white;
    padding: 0.6rem 1.2rem;
    border-radius: 20px;
    font-weight: 600;
    font-size: 0.9rem;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.2s ease;
    border: none;
    cursor: pointer;
}

.view-channel-btn:hover {
    background-color: #d81b60;
    transform: translateY(-1px);
}

.upload-content-btn {
    background-color: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    color: var(--text-light);
    padding: 0.6rem 1.2rem;
    border-radius: 20px;
    font-weight: 600;
    font-size: 0.9rem;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.2s ease;
    cursor: pointer;
}

.upload-content-btn:hover {
    background-color: rgba(255, 255, 255, 0.1);
    border-color: var(--primary-color);
    transform: translateY(-1px);
}

/* Filter Buttons Styling */
.filter-buttons-group {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.filter-pill-btn {
    background: rgba(255, 255, 255, 0.04);
    border: 1px solid rgba(255, 255, 255, 0.08);
    color: #cbd5e0;
    padding: 0.5rem 1.25rem;
    border-radius: 9999px;
    font-size: 0.85rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    outline: none;
}

.filter-pill-btn:hover {
    background: rgba(255, 255, 255, 0.1);
    color: #fff;
    border-color: rgba(255, 255, 255, 0.2);
    transform: translateY(-1px);
}

.filter-pill-btn.active {
    background: linear-gradient(135deg, #e8445a 0%, #ff6b81 100%);
    color: #fff;
    border-color: transparent;
    box-shadow: 0 4px 12px rgba(232, 68, 90, 0.3);
}

/* Pagination Controls Styling */
.pagination-wrapper {
    margin-top: 2rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
    padding-top: 1.5rem;
    border-top: 1px solid rgba(255, 255, 255, 0.06);
}

.pagination-info {
    font-size: 0.85rem;
    color: #a0aec0;
}

.pagination-buttons {
    display: flex;
    gap: 0.75rem;
    align-items: center;
}

.page-nav-btn {
    background: rgba(255, 255, 255, 0.04);
    border: 1px solid rgba(255, 255, 255, 0.08);
    color: #cbd5e0;
    padding: 0.5rem 1rem;
    border-radius: 10px;
    font-size: 0.85rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    display: flex;
    align-items: center;
    gap: 0.35rem;
}

.page-nav-btn:hover:not(:disabled) {
    background: rgba(255, 255, 255, 0.1);
    color: #fff;
    border-color: rgba(255, 255, 255, 0.2);
    transform: translateY(-1px);
}

.page-nav-btn:disabled {
    opacity: 0.35;
    cursor: not-allowed;
    transform: none;
}

.page-current {
    font-size: 0.85rem;
    font-weight: 700;
    color: #fff;
    padding: 0.5rem 0.85rem;
    background: rgba(255, 255, 255, 0.06);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 8px;
    min-width: 3.5rem;
    text-align: center;
}
</style>
