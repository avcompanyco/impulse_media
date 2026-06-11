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
                splitSuccessOutput.value = "Process completed successfully. Check Laravel log files for detailed outputs. / Proceso completado con éxito. Revise los archivos de log de Laravel para ver resultados detallados.";
            }
        },
        onError: (errors) => {
            isProcessingSplit.value = false;
            if (errors.message) {
                splitErrorMsg.value = errors.message;
            } else {
                splitErrorMsg.value = "An error occurred during calculations. / Ocurrió un error durante los cálculos.";
            }
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
        'Request Date / Fecha de Solicitud',
        'Creator Name / Nombre del Creador',
        'Username / Usuario',
        'Amount / Monto',
        'Method / Método',
        'Destination Details / Detalles de Destino',
        'Status / Estado',
        'Processed At / Procesado En',
        'Transaction Reference / ID de Transacción',
        'Rejection Reason / Razón de Rechazo'
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
                <h1 class="page-title">Creator Monetization & Payouts / Monetización y Retiros de Creadores</h1>
                <p class="page-subtitle">Configure splits, discount rates, and manage creator withdrawal requests / Configure comisiones, descuentos y gestione solicitudes de retiro de creadores.</p>
            </div>

            <!-- Platform Stats Cards Grid (Top) -->
            <div class="stats-overview-grid">
                <div class="overview-card earnings-card">
                    <div class="card-icon-wrapper">
                        <i class="fa-solid fa-vault"></i>
                    </div>
                    <div class="card-info">
                        <span class="card-label">Platform Net Earnings / Ingresos Netos de la Plataforma</span>
                        <h2 class="card-value">${{ Number(platformStats.platform_earnings).toFixed(2) }}</h2>
                    </div>
                </div>

                <div class="overview-card paid-card">
                    <div class="card-icon-wrapper">
                        <i class="fa-solid fa-hand-holding-dollar"></i>
                    </div>
                    <div class="card-info">
                        <span class="card-label">Total Paid Out / Total Pagado a Creadores</span>
                        <h2 class="card-value">${{ Number(platformStats.total_paid_out).toFixed(2) }}</h2>
                    </div>
                </div>

                <div class="overview-card pending-card">
                    <div class="card-icon-wrapper">
                        <i class="fa-solid fa-hourglass-half"></i>
                    </div>
                    <div class="card-info">
                        <span class="card-label">Total Pending Payouts / Retiros Pendientes de Pago</span>
                        <h2 class="card-value">${{ Number(platformStats.total_pending).toFixed(2) }}</h2>
                    </div>
                </div>
            </div>

            <!-- Dashboard Layout Grid -->
            <div class="admin-layout-grid">
                <!-- Left Column -->
                <div style="display: flex; flex-direction: column; gap: 2rem;">
                    <!-- Left: Monetization Configuration Settings -->
                    <div class="admin-settings-panel">
                        <h3 class="panel-title">
                            <i class="fa-solid fa-gears text-accent"></i> Platform settings / Ajustes de Plataforma
                        </h3>
                        <form @submit.prevent="saveSettings" class="settings-form">
                            <div class="form-group">
                                <label class="form-label">Creator Share Ratio (%) / Comisión del Creador (%)</label>
                                <input 
                                    type="number" 
                                    v-model="settingsForm.revenue_split_ratio" 
                                    class="form-control" 
                                    min="0" 
                                    max="100" 
                                    required
                                >
                                <span class="form-help">Percentage of PPV sales paid directly to creators. (Rest goes to platform) / Porcentaje de ventas de PPV pagado directamente a los creadores (El resto va a la plataforma).</span>
                                <span v-if="settingsForm.errors.revenue_split_ratio" class="error-msg">{{ settingsForm.errors.revenue_split_ratio }}</span>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Minimum Payout Limit ($) / Límite Mínimo de Retiro ($)</label>
                                <input 
                                    type="number" 
                                    v-model="settingsForm.min_payout_threshold" 
                                    class="form-control" 
                                    min="1" 
                                    step="0.01" 
                                    required
                                >
                                <span class="form-help">Minimum balance required for a creator to request a withdrawal. / Saldo mínimo requerido para que un creador pueda solicitar un retiro.</span>
                                <span v-if="settingsForm.errors.min_payout_threshold" class="error-msg">{{ settingsForm.errors.min_payout_threshold }}</span>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Member PPV Discount Rate (%) / Tasa de Descuento PPV para Miembros (%)</label>
                                <input 
                                    type="number" 
                                    v-model="settingsForm.membership_discount_rate" 
                                    class="form-control" 
                                    min="0" 
                                    max="100" 
                                    required
                                >
                                <span class="form-help">Discount percentage applied to PPV purchases for spectators with active Impulse Membership. / Porcentaje de descuento aplicado a compras PPV para espectadores con Membresía Impulse activa.</span>
                                <span v-if="settingsForm.errors.membership_discount_rate" class="error-msg">{{ settingsForm.errors.membership_discount_rate }}</span>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Minimum PPV Purchase Price ($) / Precio Mínimo de Compra PPV ($)</label>
                                <input 
                                    type="number" 
                                    v-model="settingsForm.min_ppv_price" 
                                    class="form-control" 
                                    min="0.01" 
                                    step="0.01" 
                                    required
                                >
                                <span class="form-help">Minimum price creators are allowed to set for their paid videos. / Precio mínimo que los creadores pueden establecer para sus videos de pago.</span>
                                <span v-if="settingsForm.errors.min_ppv_price" class="error-msg">{{ settingsForm.errors.min_ppv_price }}</span>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Free PPV Video Preview Limit (Seconds) / Límite de Vista Previa Gratuita de Video PPV (Segundos)</label>
                                <input 
                                    type="number" 
                                    v-model="settingsForm.free_preview_seconds" 
                                    class="form-control" 
                                    min="1" 
                                    step="1" 
                                    required
                                >
                                <span class="form-help">Number of seconds a spectator is allowed to watch a PPV video before the paywall blocks it. (e.g. 300 for 5 minutes). / Número de segundos que un espectador puede ver un video PPV antes de que aparezca el muro de pago (ej. 300 para 5 minutos).</span>
                                <span v-if="settingsForm.errors.free_preview_seconds" class="error-msg">{{ settingsForm.errors.free_preview_seconds }}</span>
                            </div>

                            <button type="submit" class="submit-btn" :disabled="isSavingSettings">
                                <i class="fa-solid fa-circle-notch fa-spin" v-if="isSavingSettings"></i>
                                Save Settings / Guardar Ajustes
                            </button>
                        </form>
                    </div>

                    <!-- Calculate Monthly Earnings Split Card -->
                    <div class="admin-settings-panel">
                        <h3 class="panel-title">
                            <i class="fa-solid fa-calculator text-accent"></i> Membership Split / Comisión de Membresía
                        </h3>
                        <form @submit.prevent="runMembershipSplit" class="settings-form">
                            <div class="form-group">
                                <label class="form-label">Billing Month / Mes de Facturación</label>
                                <input 
                                    type="month" 
                                    v-model="splitMonth" 
                                    class="form-control"
                                    :max="currentMonthString"
                                >
                                <span class="form-help">Select the month to process. If left blank, it defaults to the previous calendar month. / Seleccione el mes a procesar. Si se deja en blanco, procesará el mes calendario anterior.</span>
                            </div>

                            <button type="submit" class="submit-btn" :disabled="isProcessingSplit" style="background: linear-gradient(135deg, #805ad5 0%, #553c9a 100%); box-shadow: 0 4px 15px rgba(128, 90, 213, 0.3);">
                                <i class="fa-solid fa-circle-notch fa-spin" v-if="isProcessingSplit"></i>
                                Calculate Split / Calcular Comisión
                            </button>
                        </form>

                        <!-- Console Output for calculated split -->
                        <div v-if="splitSuccessOutput" class="console-output-box" style="margin-top: 1.5rem;">
                            <span class="console-label">Execution Logs / Logs de Ejecución</span>
                            <pre class="console-text">{{ splitSuccessOutput }}</pre>
                        </div>
                        <div v-if="splitErrorMsg" class="console-output-box error" style="margin-top: 1.5rem;">
                            <span class="console-label">Error / Error</span>
                            <pre class="console-text">{{ splitErrorMsg }}</pre>
                        </div>
                    </div>
                </div>

                <!-- Right: Pending Payout Requests -->
                <div class="admin-payouts-panel">
                    <h3 class="panel-title">
                        <i class="fa-solid fa-circle-dollar-to-slot text-accent"></i> Pending Payout Requests / Solicitudes de Retiro Pendientes
                    </h3>
                    <div class="payout-cards-container">
                        <div v-if="pendingPayouts.length === 0" class="empty-requests-msg">
                            <i class="fa-solid fa-check-double checked-icon"></i>
                            <p>All payout requests have been processed. Great job! / Todas las solicitudes de pago han sido procesadas. ¡Buen trabajo!</p>
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
                                <span class="details-label">Method / Método:</span>
                                <span class="details-value text-capitalize">{{ payout.payout_method.replace('_', ' ') }}</span>
                                <div class="details-text-area">
                                    <span class="details-label">Destination Details / Datos de Destino:</span>
                                    <p class="details-text">{{ payout.payout_details }}</p>
                                </div>
                            </div>

                            <div class="card-actions">
                                <button class="action-btn approve-btn" @click="startApproval(payout.id)">
                                    <i class="fa-solid fa-check"></i> Approve / Aprobar
                                </button>
                                <button class="action-btn reject-btn" @click="startRejection(payout.id)">
                                    <i class="fa-solid fa-xmark"></i> Reject / Rechazar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Historical Payouts Log (Full Width) -->
            <div class="admin-panel full-width">
                <div class="panel-header-with-actions">
                    <h3 class="panel-title" style="margin: 0; border: none; padding: 0;">
                        <i class="fa-solid fa-list-check text-accent"></i> Payout History Logs / Historial de Retiros Procesados
                    </h3>
                    <button class="export-csv-btn" @click="exportToCSV">
                        <i class="fa-solid fa-file-csv"></i> Export to CSV / Exportar Reporte
                    </button>
                </div>

                <!-- Filters Control Box -->
                <div class="filters-container">
                    <div class="filter-item">
                        <label class="filter-label">Search Creator / Buscar Creador</label>
                        <input 
                            type="text" 
                            v-model="searchFilter" 
                            class="filter-input" 
                            placeholder="Name or @username..."
                        >
                    </div>
                    <div class="filter-item">
                        <label class="filter-label">Filter by Status / Estado</label>
                        <select v-model="statusFilter" class="filter-select">
                            <option value="">All / Todos</option>
                            <option value="approved">Approved / Aprobado</option>
                            <option value="rejected">Rejected / Rechazado</option>
                        </select>
                    </div>
                    <div class="filter-item">
                        <label class="filter-label">Filter by Method / Método</label>
                        <select v-model="methodFilter" class="filter-select">
                            <option value="">All / Todos</option>
                            <option value="paypal">PayPal</option>
                            <option value="bank_transfer">Bank Transfer</option>
                        </select>
                    </div>
                    <div class="filter-item">
                        <label class="filter-label">Transaction Ref / ID Transacción</label>
                        <input 
                            type="text" 
                            v-model="refFilter" 
                            class="filter-input" 
                            placeholder="Ref ID..."
                        >
                    </div>
                    <div class="filter-actions-wrapper">
                        <button class="clear-filters-btn" @click="clearFilters">
                            <i class="fa-solid fa-filter-circle-xmark"></i> Clear / Limpiar
                        </button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Date / Fecha</th>
                                <th>Creator / Creador</th>
                                <th>Amount / Monto</th>
                                <th>Method / Método</th>
                                <th>Status / Estado</th>
                                <th>Processed At / Procesado En</th>
                                <th>Actions / Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="filteredHistory.length === 0">
                                <td colspan="7" class="empty-table-msg">No historical payout logs match the filters. / No se encontraron retiros históricos que coincidan con los filtros.</td>
                            </tr>
                            <tr v-for="payout in filteredHistory" :key="payout.id">
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
                                    <div v-if="payout.status === 'rejected' && payout.rejection_reason" class="table-rejection text-truncate" style="max-width: 200px;">
                                        Reason: {{ payout.rejection_reason }}
                                    </div>
                                    <div v-if="payout.status === 'approved'" class="table-approved-details">
                                        <div v-if="payout.transaction_reference" class="reference-text">
                                            Ref: {{ payout.transaction_reference }}
                                        </div>
                                    </div>
                                </td>
                                <td>{{ payout.processed_at ? formatDate(payout.processed_at) : 'N/A' }}</td>
                                <td>
                                    <div style="display: flex; gap: 8px; align-items: center;">
                                        <button class="row-action-btn details" @click="viewPayoutDetails(payout)" title="View Details / Ver Detalles">
                                            <i class="fa-solid fa-eye"></i> Details
                                        </button>
                                        <button v-if="payout.receipt_url" class="row-action-btn receipt" @click="openReceiptPreview(payout.receipt_url)" title="View Receipt / Ver Comprobante">
                                            <i class="fa-solid fa-receipt"></i> Receipt
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Creator Monetization Metrics (Full Width) -->
            <div class="admin-panel full-width" style="margin-top: 2rem;">
                <h3 class="panel-title">
                    <i class="fa-solid fa-users-gear text-accent"></i> Creator Balances & Lifetime Metrics / Saldos y Métricas de Creadores
                </h3>
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Creator / Creador</th>
                                <th>Email</th>
                                <th>Lifetime Earnings / Ingresos Históricos</th>
                                <th>Available Balance / Saldo Disponible</th>
                                <th>Total Paid Out / Total Retirado</th>
                                <th>Total Pending / Total Pendiente</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="creatorStats.length === 0">
                                <td colspan="6" class="empty-table-msg">No creators found on the platform. / No se encontraron creadores en la plataforma.</td>
                            </tr>
                            <tr v-for="creator in creatorStats" :key="creator.id">
                                <td>
                                    <div class="table-creator-cell">
                                        <img :src="creator.image_url || '/images/default-avatar.png'" alt="Avatar" class="table-avatar">
                                        <div class="creator-meta">
                                            <span class="creator-name" style="font-weight: 600; color: #fff;">{{ creator.name }}</span>
                                            <span class="creator-handle" style="font-size: 0.8rem; color: #a0aec0;">@{{ creator.username }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ creator.email }}</td>
                                <td style="font-weight: 700; color: #48bb78;">${{ Number(creator.lifetime_earnings).toFixed(2) }}</td>
                                <td style="font-weight: 700; color: #3182ce;">${{ Number(creator.balance).toFixed(2) }}</td>
                                <td style="font-weight: 700; color: #e53e3e;">${{ Number(creator.total_paid).toFixed(2) }}</td>
                                <td style="font-weight: 700; color: #dd6b20;">${{ Number(creator.total_pending).toFixed(2) }}</td>
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
                    <h3>Reject Payout Request / Rechazar Solicitud de Retiro</h3>
                    <button class="close-btn" @click="cancelRejection">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Reason for Rejection / Motivo de Rechazo</label>
                        <textarea 
                            v-model="rejectionReason" 
                            class="form-control" 
                            rows="4" 
                            placeholder="Specify why this payout request is being rejected (e.g., incorrect banking information, account verification needed)... / Especifique el motivo de rechazo..."
                            required
                        ></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="modal-btn secondary" @click="cancelRejection">Cancel / Cancelar</button>
                    <button class="modal-btn danger" @click="submitRejection">Reject Request / Rechazar Solicitud</button>
                </div>
            </div>
        </div>

        <!-- Approve Payout Modal -->
        <div v-if="showApproveModal" class="reject-modal-overlay">
            <div class="reject-modal-content">
                <div class="modal-header">
                    <h3>Approve Payout Request / Aprobar Solicitud de Retiro</h3>
                    <button class="close-btn" @click="cancelApproval">&times;</button>
                </div>
                <form @submit.prevent="submitApproval" style="display: flex; flex-direction: column; gap: 1.25rem; margin-top: 1rem;">
                    <div class="form-group">
                        <label class="form-label">Transaction Reference / ID (Optional) / Referencia de Transacción / ID (Opcional)</label>
                        <input 
                            type="text"
                            v-model="approveForm.transaction_reference" 
                            class="form-control" 
                            placeholder="e.g., PayPal TxID, wire transfer reference..."
                        >
                    </div>
                    <div class="form-group">
                        <label class="form-label">Payment Receipt File (Optional) / Archivo Comprobante de Pago (Opcional)</label>
                        <input 
                            type="file" 
                            @change="approveForm.receipt = $event.target.files[0]"
                            class="form-control" 
                            accept="image/*,application/pdf"
                        >
                        <span class="form-help">Upload a JPG, PNG, or PDF confirmation receipt. / Suba un comprobante en formato JPG, PNG o PDF.</span>
                    </div>
                    <div class="modal-footer" style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid rgba(255,255,255,0.08);">
                        <button type="button" class="modal-btn secondary" @click="cancelApproval">Cancel / Cancelar</button>
                        <button type="submit" class="modal-btn" style="background-color: #48bb78; color: white;">Approve Request / Aprobar Solicitud</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Payout Details Modal -->
        <div v-if="showDetailsModal && activeDetailsPayout" class="reject-modal-overlay">
            <div class="reject-modal-content details-modal-content">
                <div class="modal-header">
                    <h3>Withdrawal Details / Detalles del Retiro</h3>
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
                            <span class="info-label">Amount / Monto:</span>
                            <span class="info-value text-accent" style="font-size: 1.4rem; font-weight: 800;">${{ Number(activeDetailsPayout.amount).toFixed(2) }}</span>
                        </div>
                        <div class="summary-info-item">
                            <span class="info-label">Method / Método:</span>
                            <span class="info-value text-capitalize">{{ activeDetailsPayout.payout_method.replace('_', ' ') }}</span>
                        </div>
                        <div class="summary-info-item">
                            <span class="info-label">Requested Date / Fecha de Solicitud:</span>
                            <span class="info-value">{{ formatDate(activeDetailsPayout.created_at) }}</span>
                        </div>
                        <div class="summary-info-item">
                            <span class="info-label">Status / Estado:</span>
                            <span class="badge" :class="getStatusBadgeClass(activeDetailsPayout.status)" style="align-self: flex-start; margin-top: 4px;">
                                {{ activeDetailsPayout.status }}
                            </span>
                        </div>
                    </div>

                    <!-- Destination Bank Details -->
                    <div class="detail-box-container">
                        <h4 class="box-title">Payout Destination Account Details / Datos de Destino del Pago</h4>
                        <pre class="box-content">{{ activeDetailsPayout.payout_details }}</pre>
                    </div>

                    <!-- Resolution Information -->
                    <div v-if="activeDetailsPayout.status !== 'pending'" class="detail-box-container resolution-box">
                        <h4 class="box-title">Resolution Information / Información de Resolución</h4>
                        <div class="resolution-grid">
                            <div v-if="activeDetailsPayout.processed_at" class="resolution-item">
                                <span class="res-label">Processed At / Procesado En:</span>
                                <span class="res-value">{{ formatDate(activeDetailsPayout.processed_at) }}</span>
                            </div>
                            <div v-if="activeDetailsPayout.status === 'approved' && activeDetailsPayout.transaction_reference" class="resolution-item">
                                <span class="res-label">Ref ID / Referencia:</span>
                                <span class="res-value font-mono">{{ activeDetailsPayout.transaction_reference }}</span>
                            </div>
                            <div v-if="activeDetailsPayout.status === 'rejected' && activeDetailsPayout.rejection_reason" class="resolution-item full-width">
                                <span class="res-label">Rejection Reason / Motivo de Rechazo:</span>
                                <span class="res-value" style="color: #e53e3e;">{{ activeDetailsPayout.rejection_reason }}</span>
                            </div>
                            <div v-if="activeDetailsPayout.status === 'approved' && activeDetailsPayout.receipt_url" class="resolution-item full-width">
                                <span class="res-label">Receipt File / Comprobante Adjunto:</span>
                                <button class="row-action-btn receipt" @click="openReceiptPreview(activeDetailsPayout.receipt_url)" style="margin-top: 6px;">
                                    <i class="fa-solid fa-receipt"></i> Open Receipt Preview / Ver Comprobante
                                </button>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer" style="margin-top: 1rem;">
                    <button class="modal-btn secondary" @click="closePayoutDetails">Close / Cerrar</button>
                </div>
            </div>
        </div>

        <!-- Receipt Preview Lightbox Modal -->
        <div v-if="showReceiptModal && activeReceiptUrl" class="receipt-lightbox-overlay" @click.self="closeReceiptPreview">
            <div class="receipt-lightbox-content">
                <div class="lightbox-header">
                    <h4>Receipt Document Preview / Previsualización de Comprobante</h4>
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
                        <i class="fa-solid fa-download"></i> Download File / Descargar Archivo
                    </a>
                    <button class="modal-btn secondary" @click="closeReceiptPreview">Close / Cerrar</button>
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

/* Console Output Styling */
.console-output-box {
    background: #000;
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 12px;
    padding: 1rem;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.console-output-box.error {
    border-color: rgba(229, 62, 62, 0.4);
    background: rgba(229, 62, 62, 0.05);
}

.console-label {
    font-size: 0.75rem;
    font-weight: 700;
    color: #a0aec0;
    text-transform: uppercase;
}

.console-output-box.error .console-label {
    color: #e53e3e;
}

.console-text {
    margin: 0;
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
    font-size: 0.8rem;
    color: #48bb78;
    white-space: pre-wrap;
    word-break: break-all;
    line-height: 1.5;
}

.console-output-box.error .console-text {
    color: #e53e3e;
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

/* Panel Header with Actions */
.panel-header-with-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid rgba(255, 255, 255, 0.05);
    padding-bottom: 1rem;
    margin-bottom: 1.5rem;
    gap: 1rem;
    flex-wrap: wrap;
}

.export-csv-btn {
    background: rgba(49, 130, 206, 0.15);
    color: #4299e1;
    border: 1px solid rgba(49, 130, 206, 0.3);
    border-radius: 10px;
    padding: 0.6rem 1.2rem;
    font-weight: 700;
    font-size: 0.9rem;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.2s ease;
}

.export-csv-btn:hover {
    background: rgba(49, 130, 206, 0.25);
    transform: translateY(-1px);
}

/* Filters Container */
.filters-container {
    background: rgba(0, 0, 0, 0.2);
    border: 1px solid rgba(255, 255, 255, 0.04);
    border-radius: 16px;
    padding: 1.25rem;
    display: grid;
    grid-template-columns: repeat(4, 1fr) auto;
    gap: 1rem;
    align-items: flex-end;
    margin-bottom: 1.5rem;
}

@media (max-width: 1024px) {
    .filters-container {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 640px) {
    .filters-container {
        grid-template-columns: 1fr;
    }
}

.filter-item {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.filter-label {
    font-size: 0.75rem;
    font-weight: 700;
    color: #a0aec0;
    text-transform: uppercase;
    letter-spacing: 0.03em;
}

.filter-input,
.filter-select {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 10px;
    color: #fff;
    padding: 0.65rem 0.8rem;
    font-size: 0.85rem;
    outline: none;
    transition: all 0.2s ease;
}

.filter-input:focus,
.filter-select:focus {
    border-color: #e8445a;
    background: rgba(255, 255, 255, 0.08);
}

.filter-actions-wrapper {
    display: flex;
    align-items: center;
}

.clear-filters-btn {
    background: rgba(255, 255, 255, 0.05);
    color: #a0aec0;
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 10px;
    padding: 0.65rem 1.2rem;
    font-weight: 600;
    font-size: 0.85rem;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.2s ease;
    width: 100%;
    justify-content: center;
}

.clear-filters-btn:hover {
    background: rgba(255, 255, 255, 0.1);
    color: #fff;
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

/* Row Action Buttons */
.row-action-btn {
    border: none;
    border-radius: 8px;
    padding: 0.4rem 0.8rem;
    font-weight: 700;
    font-size: 0.75rem;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.2s ease;
}

.row-action-btn.details {
    background: rgba(255, 255, 255, 0.08);
    color: #e2e8f0;
}

.row-action-btn.details:hover {
    background: rgba(255, 255, 255, 0.15);
}

.row-action-btn.receipt {
    background: rgba(72, 187, 120, 0.15);
    color: #48bb78;
    border: 1px solid rgba(72, 187, 120, 0.3);
}

.row-action-btn.receipt:hover {
    background: rgba(72, 187, 120, 0.25);
}

/* Payout Details Modal Content */
.details-modal-content {
    max-width: 600px !important;
}

.detail-creator-row {
    display: flex;
    align-items: center;
    gap: 1rem;
    background: rgba(0,0,0,0.15);
    padding: 1rem;
    border-radius: 16px;
    border: 1px solid rgba(255,255,255,0.03);
}

.details-summary-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
}

.summary-info-item {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.info-label {
    font-size: 0.75rem;
    color: #718096;
    font-weight: 700;
    text-transform: uppercase;
}

.info-value {
    color: #fff;
    font-size: 0.95rem;
    font-weight: 600;
}

.detail-box-container {
    background: rgba(0,0,0,0.2);
    border-radius: 12px;
    padding: 1rem;
    border: 1px solid rgba(255,255,255,0.04);
}

.box-title {
    font-size: 0.8rem;
    font-weight: 700;
    color: #a0aec0;
    text-transform: uppercase;
    margin: 0 0 0.5rem 0;
    border-bottom: 1px solid rgba(255,255,255,0.05);
    padding-bottom: 0.35rem;
}

.box-content {
    margin: 0;
    font-family: inherit;
    font-size: 0.9rem;
    color: #e2e8f0;
    white-space: pre-wrap;
    line-height: 1.4;
}

.resolution-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
}

.resolution-item {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.resolution-item.full-width {
    grid-column: span 2;
}

.res-label {
    font-size: 0.75rem;
    color: #718096;
    font-weight: 700;
}

.res-value {
    color: #fff;
    font-size: 0.9rem;
    font-weight: 600;
}

.font-mono {
    font-family: monospace;
}

/* Lightbox Preview Modal */
.receipt-lightbox-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.9);
    backdrop-filter: blur(8px);
    z-index: 1100;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    box-sizing: border-box;
}

.receipt-lightbox-content {
    background: #12121e;
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 20px;
    width: 100%;
    max-width: 800px;
    max-height: 90vh;
    display: flex;
    flex-direction: column;
    box-shadow: 0 10px 40px rgba(0,0,0,0.8);
    overflow: hidden;
}

.lightbox-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.25rem;
    border-bottom: 1px solid rgba(255,255,255,0.06);
    background: rgba(0,0,0,0.2);
}

.lightbox-header h4 {
    margin: 0;
    font-size: 1.1rem;
    font-weight: 700;
    color: #fff;
}

.lightbox-close-btn {
    background: none;
    border: none;
    color: #a0aec0;
    font-size: 2rem;
    cursor: pointer;
    line-height: 1;
}

.lightbox-close-btn:hover {
    color: #fff;
}

.lightbox-body {
    padding: 1rem;
    flex: 1;
    overflow-y: auto;
    display: flex;
    justify-content: center;
    align-items: center;
    background: #09090e;
    min-height: 300px;
}

.pdf-preview-frame {
    width: 100%;
    height: 60vh;
    border-radius: 8px;
    background: #fff;
}

.img-preview-frame {
    max-width: 100%;
    max-height: 60vh;
    object-fit: contain;
    border-radius: 8px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.5);
}

.lightbox-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.25rem;
    border-top: 1px solid rgba(255,255,255,0.06);
    background: rgba(0,0,0,0.2);
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
