<script setup lang="ts">
import { ref, reactive, nextTick } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import AdminDashboardLayout from '@/layouts/AdminDashboardLayout.vue';

const props = defineProps<{
    campaigns: any[];
}>();

const showCreateForm = ref(false);
const editingCampaignId = ref<number | null>(null);

// ─── Create Form ───
const form = useForm({
    name: '',
    company_name: '',
    media: null as File | null,
    media_type: 'image' as 'image' | 'video',
});

// ─── Image Compression Utility ───
const MAX_IMG_SIZE = 1200; // max width/height in px
const IMG_QUALITY = 0.8;  // JPEG quality (0-1)

function compressImage(file: File): Promise<File> {
    return new Promise((resolve) => {
        // Skip non-image files
        if (!file.type.startsWith('image/')) { resolve(file); return; }
        // Skip small files (< 200KB)
        if (file.size < 200 * 1024) { resolve(file); return; }

        const img = new Image();
        const url = URL.createObjectURL(file);
        img.onload = () => {
            URL.revokeObjectURL(url);
            let { width, height } = img;

            // Only resize if larger than MAX
            if (width > MAX_IMG_SIZE || height > MAX_IMG_SIZE) {
                if (width > height) {
                    height = Math.round((height / width) * MAX_IMG_SIZE);
                    width = MAX_IMG_SIZE;
                } else {
                    width = Math.round((width / height) * MAX_IMG_SIZE);
                    height = MAX_IMG_SIZE;
                }
            }

            const canvas = document.createElement('canvas');
            canvas.width = width;
            canvas.height = height;
            const ctx = canvas.getContext('2d')!;
            ctx.drawImage(img, 0, 0, width, height);

            canvas.toBlob((blob) => {
                if (blob && blob.size < file.size) {
                    const compressed = new File([blob], file.name.replace(/\.\w+$/, '.jpg'), {
                        type: 'image/jpeg',
                        lastModified: Date.now(),
                    });
                    resolve(compressed);
                } else {
                    resolve(file); // keep original if compression didn't help
                }
            }, 'image/jpeg', IMG_QUALITY);
        };
        img.onerror = () => { URL.revokeObjectURL(url); resolve(file); };
        img.src = url;
    });
}

// Multi-file queue for creation
const createMediaQueue = ref<{ file: File; preview: string; type: string }[]>([]);
const isUploadingQueue = ref(false);
const queueUploadProgress = ref(0);

async function handleFileChange(event: Event) {
    const input = event.target as HTMLInputElement;
    if (input.files) {
        for (let i = 0; i < input.files.length; i++) {
            const raw = input.files[i];
            const type = raw.type.startsWith('video/') ? 'video' : 'image';
            // Compress images before adding to queue
            const file = type === 'image' ? await compressImage(raw) : raw;
            createMediaQueue.value.push({
                file,
                preview: URL.createObjectURL(file),
                type,
            });
        }
        // Clear input so same file can be re-selected
        input.value = '';
    }
}

function removeFromQueue(index: number) {
    URL.revokeObjectURL(createMediaQueue.value[index].preview);
    createMediaQueue.value.splice(index, 1);
}

async function submitCampaign() {
    if (createMediaQueue.value.length === 0) return;

    isUploadingQueue.value = true;
    queueUploadProgress.value = 0;

    // Build a single FormData with ALL files
    const fd = new FormData();
    fd.append('name', form.name);
    fd.append('company_name', form.company_name || '');
    
    for (let i = 0; i < createMediaQueue.value.length; i++) {
        fd.append('media_files[]', createMediaQueue.value[i].file);
    }

    // Use Inertia router.post to send everything in one request
    router.post('/admin/ads', fd, {
        forceFormData: true,
        preserveScroll: true,
        onProgress: (progress) => {
            if (progress?.percentage) {
                queueUploadProgress.value = Math.round((progress.percentage / 100) * createMediaQueue.value.length);
            }
        },
        onSuccess: () => {
            // Clean up
            createMediaQueue.value.forEach(item => URL.revokeObjectURL(item.preview));
            createMediaQueue.value = [];
            form.reset();
            showCreateForm.value = false;
            isUploadingQueue.value = false;
        },
        onError: (errors) => {
            isUploadingQueue.value = false;
            console.error('Campaign creation failed:', errors);
        },
    });
}

// ─── Edit Campaign ───
const editForm = useForm({
    name: '',
    company_name: '',
});

function startEditing(campaign: any) {
    editingCampaignId.value = campaign.id;
    editForm.name = campaign.name;
    editForm.company_name = campaign.company_name || '';
}

function cancelEditing() {
    editingCampaignId.value = null;
    editForm.reset();
}

function saveEdit(campaignId: number) {
    editForm.put(`/admin/ads/${campaignId}`, {
        preserveScroll: true,
        onSuccess: () => {
            editingCampaignId.value = null;
        },
    });
}

// ─── Add media to campaign ───
const addMediaForm = useForm({
    media: null as File | null,
});
const addMediaPreview = ref('');
const addMediaType = ref('image');
const addMediaInputRefs = ref<Record<number, HTMLInputElement | null>>({});
const createFileInputRef = ref<HTMLInputElement | null>(null);
const addMediaCampaignId = ref<number | null>(null);

function openAddMedia(campaignId: number) {
    addMediaCampaignId.value = campaignId;
    addMediaPreview.value = '';
    addMediaForm.reset();
}

async function handleAddMediaFile(event: Event) {
    const input = event.target as HTMLInputElement;
    if (input.files && input.files[0]) {
        const raw = input.files[0];
        const file = raw.type.startsWith('image/') ? await compressImage(raw) : raw;
        addMediaForm.media = file;
        addMediaType.value = file.type.startsWith('video/') ? 'video' : 'image';
        addMediaPreview.value = URL.createObjectURL(file);
    }
}

function submitAddMedia(campaignId: number) {
    addMediaForm.post(`/admin/ads/${campaignId}/media`, {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            addMediaCampaignId.value = null;
            addMediaPreview.value = '';
            addMediaForm.reset();
        },
    });
}

function removeMedia(mediaId: number) {
    pendingAction.value = () => {
        router.delete(`/admin/ads/media/${mediaId}`, {
            preserveScroll: true,
        });
        showConfirmModal.value = false;
    };
    confirmMessage.value = 'Are you sure you want to remove this media file?';
    showConfirmModal.value = true;
}

// ─── Toggle / Delete ───
function toggleCampaign(id: number) {
    router.put(`/admin/ads/${id}/toggle`, {}, { preserveScroll: true });
}

const showConfirmModal = ref(false);
const confirmMessage = ref('');
const pendingAction = ref<(() => void) | null>(null);

function deleteCampaign(id: number) {
    pendingAction.value = () => {
        router.delete(`/admin/ads/${id}`, { preserveScroll: true });
        showConfirmModal.value = false;
    };
    confirmMessage.value = 'Are you sure? This will permanently delete the campaign and all its media files.';
    showConfirmModal.value = true;
}

function confirmAction() {
    if (pendingAction.value) pendingAction.value();
}

function cancelConfirm() {
    showConfirmModal.value = false;
    pendingAction.value = null;
}
</script>

<template>
    <AdminDashboardLayout title="Ad Campaigns" headerTitle="Ad Campaigns">
        <template #header-actions>
            <button class="create-btn" @click="showCreateForm = !showCreateForm">
                <i class="fas" :class="showCreateForm ? 'fa-times' : 'fa-plus'"></i>
                {{ showCreateForm ? 'Cancel' : 'New Campaign' }}
            </button>
        </template>

        <!-- Create Campaign Form -->
        <div v-if="showCreateForm" class="create-form-card">
            <h2 class="form-card-title">Create New Campaign</h2>
            <form @submit.prevent="submitCampaign" class="campaign-form">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Campaign Name *</label>
                        <input
                            v-model="form.name"
                            type="text"
                            class="form-input"
                            placeholder="e.g. Summer Promotion 2026"
                            required
                        />
                        <span v-if="form.errors.name" class="form-error">{{ form.errors.name }}</span>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Company Name</label>
                        <input
                            v-model="form.company_name"
                            type="text"
                            class="form-input"
                            placeholder="e.g. Coca-Cola"
                        />
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Media Files (Images or Videos) *</label>
                    
                    <!-- Queued files grid -->
                    <div v-if="createMediaQueue.length > 0" class="queue-grid">
                        <div v-for="(item, idx) in createMediaQueue" :key="idx" class="queue-thumb">
                            <img v-if="item.type === 'image'" :src="item.preview" alt="Preview" />
                            <video v-else :src="item.preview" muted></video>
                            <span class="thumb-badge">{{ item.type === 'image' ? '🖼️' : '🎬' }}</span>
                            <button type="button" class="thumb-remove" @click="removeFromQueue(idx)" title="Remove">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <!-- Add more button -->
                        <div class="queue-thumb add-thumb" @click="createFileInputRef?.click()">
                            <i class="fas fa-plus"></i>
                            <span>Add More</span>
                        </div>
                    </div>
                    
                    <!-- Empty state: click to add first file -->
                    <div v-else class="file-upload-area" @click="createFileInputRef?.click()">
                        <div class="upload-placeholder">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <p>Click to add images or videos</p>
                            <span>You can add multiple files • Max 100MB each • JPG, PNG, MP4, MOV</span>
                        </div>
                    </div>
                    
                    <input
                        ref="createFileInputRef"
                        type="file"
                        accept="image/*,video/*"
                        multiple
                        @change="handleFileChange"
                        style="display: none"
                    />
                    <span v-if="form.errors.media" class="form-error">{{ form.errors.media }}</span>
                </div>

                <!-- Upload progress -->
                <div v-if="isUploadingQueue" class="queue-progress">
                    <div class="queue-progress-bar">
                        <div class="queue-progress-fill" :style="{ width: `${(queueUploadProgress / createMediaQueue.length) * 100}%` }"></div>
                    </div>
                    <span class="queue-progress-text">Uploading {{ queueUploadProgress }} / {{ createMediaQueue.length }} files...</span>
                </div>

                <button type="submit" class="submit-btn" :disabled="form.processing || isUploadingQueue || createMediaQueue.length === 0">
                    <i class="fa-solid fa-circle-notch fa-spin" v-if="form.processing || isUploadingQueue"></i>
                    {{ isUploadingQueue ? `Uploading ${queueUploadProgress}/${createMediaQueue.length}...` : form.processing ? 'Creating...' : `Create Campaign (${createMediaQueue.length} file${createMediaQueue.length !== 1 ? 's' : ''})` }}
                </button>
            </form>
        </div>

        <!-- Campaigns List -->
        <div class="campaigns-section">
            <div v-if="campaigns.length === 0" class="empty-state">
                <i class="fas fa-bullhorn"></i>
                <h3>No Campaigns Yet</h3>
                <p>Create your first ad campaign to start showing ads to spectators.</p>
            </div>

            <div v-else class="campaigns-list">
                <div v-for="campaign in campaigns" :key="campaign.id" class="campaign-card">
                    <!-- Card Header -->
                    <div class="campaign-header">
                        <span class="status-badge" :class="campaign.status">
                            {{ campaign.status }}
                        </span>
                        <div class="campaign-actions-top">
                            <button class="icon-btn edit-btn" @click="startEditing(campaign)" title="Edit">
                                <i class="fas fa-pen"></i>
                            </button>
                            <button
                                class="icon-btn toggle-btn"
                                :class="{ 'active': campaign.status === 'active' }"
                                @click="toggleCampaign(campaign.id)"
                                :title="campaign.status === 'active' ? 'Pause' : 'Activate'"
                            >
                                <i :class="campaign.status === 'active' ? 'fas fa-pause' : 'fas fa-play'"></i>
                            </button>
                            <button class="icon-btn delete-btn" @click="deleteCampaign(campaign.id)" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Edit Mode -->
                    <div v-if="editingCampaignId === campaign.id" class="edit-section">
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Campaign Name</label>
                                <input v-model="editForm.name" type="text" class="form-input" />
                            </div>
                            <div class="form-group">
                                <label class="form-label">Company Name</label>
                                <input v-model="editForm.company_name" type="text" class="form-input" />
                            </div>
                        </div>
                        <div class="edit-actions">
                            <button class="save-btn" @click="saveEdit(campaign.id)" :disabled="editForm.processing">
                                <i class="fas fa-check"></i> Save
                            </button>
                            <button class="cancel-btn" @click="cancelEditing">
                                <i class="fas fa-times"></i> Cancel
                            </button>
                        </div>
                    </div>

                    <!-- Campaign Info (View Mode) -->
                    <div v-else class="campaign-info">
                        <h3 class="campaign-name">{{ campaign.name }}</h3>
                        <p v-if="campaign.company_name" class="campaign-company">
                            <i class="fas fa-building"></i> {{ campaign.company_name }}
                        </p>
                        <p class="campaign-impressions">
                            <i class="fas fa-eye"></i> {{ campaign.impressions_count?.toLocaleString() || 0 }} impressions
                        </p>
                        <p class="campaign-date">
                            <i class="fas fa-calendar"></i> {{ new Date(campaign.created_at).toLocaleDateString() }}
                        </p>
                    </div>

                    <!-- Media Gallery -->
                    <div class="media-gallery">
                        <h4 class="gallery-title">
                            <i class="fas fa-images"></i>
                            Media ({{ (campaign.media_items || []).length + (campaign.media_path && !(campaign.media_items || []).length ? 1 : 0) }})
                        </h4>
                        <div class="media-grid">
                            <!-- Legacy single media -->
                            <div v-if="campaign.media_url && !(campaign.media_items || []).length" class="media-thumb">
                                <img v-if="campaign.media_type === 'image'" :src="campaign.media_url" alt="Ad media" />
                                <video v-else :src="campaign.media_url" muted></video>
                                <span class="thumb-badge">{{ campaign.media_type === 'image' ? '🖼️' : '🎬' }}</span>
                            </div>
                            <!-- Multi-media items -->
                            <div v-for="media in (campaign.media_items || [])" :key="media.id" class="media-thumb">
                                <img v-if="media.media_type === 'image'" :src="media.media_url" alt="Ad media" />
                                <video v-else :src="media.media_url" muted></video>
                                <span class="thumb-badge">{{ media.media_type === 'image' ? '🖼️' : '🎬' }}</span>
                                <button class="thumb-remove" @click="removeMedia(media.id)" title="Remove">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                            <!-- Add Media Button -->
                            <div class="media-thumb add-thumb" @click="openAddMedia(campaign.id)">
                                <i class="fas fa-plus"></i>
                                <span>Add</span>
                            </div>
                        </div>

                        <!-- Inline Add Media Form -->
                        <div v-if="addMediaCampaignId === campaign.id" class="add-media-form">
                            <div class="file-upload-area small" @click="addMediaInputRefs[campaign.id]?.click()">
                                <div v-if="!addMediaPreview" class="upload-placeholder">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                    <p>Click to select file</p>
                                </div>
                                <div v-else class="upload-preview">
                                    <img v-if="addMediaType === 'image'" :src="addMediaPreview" alt="Preview" class="preview-img" />
                                    <video v-else :src="addMediaPreview" class="preview-video" controls></video>
                                </div>
                            </div>
                            <input
                                :ref="(el) => { if (el) addMediaInputRefs[campaign.id] = el as HTMLInputElement; }"
                                type="file"
                                accept="image/*,video/*"
                                @change="handleAddMediaFile"
                                style="display: none"
                            />
                            <div class="add-media-actions">
                                <button class="save-btn" @click="submitAddMedia(campaign.id)" :disabled="addMediaForm.processing || !addMediaForm.media">
                                    <i class="fa-solid fa-circle-notch fa-spin" v-if="addMediaForm.processing"></i>
                                    <template v-else><i class="fas fa-upload"></i> Upload</template>
                                </button>
                                <button class="cancel-btn" @click="addMediaCampaignId = null">Cancel</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminDashboardLayout>

    <!-- Confirm Modal -->
    <Teleport to="body">
        <div v-if="showConfirmModal" class="confirm-overlay" @click.self="cancelConfirm">
            <div class="confirm-card">
                <div class="confirm-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <h3>Confirm Action</h3>
                <p>{{ confirmMessage }}</p>
                <div class="confirm-actions">
                    <button class="confirm-cancel-btn" @click="cancelConfirm">Cancel</button>
                    <button class="confirm-delete-btn" @click="confirmAction">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                </div>
            </div>
        </div>
    </Teleport>
</template>

<style scoped>
/* Create Button */
.create-btn {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    background: var(--primary-color);
    color: white;
    border: none;
    padding: 0.6rem 1.2rem;
    border-radius: 10px;
    font-weight: 600;
    font-size: 0.9rem;
    cursor: pointer;
    transition: all 0.2s;
}
.create-btn:hover {
    transform: scale(1.03);
    opacity: 0.9;
}

/* Create Form Card */
.create-form-card {
    background: var(--section-bg);
    border-radius: 16px;
    padding: 2rem;
    margin-bottom: 2rem;
    border: 1px solid var(--border-color);
    animation: slideDown 0.3s ease;
}
@keyframes slideDown {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

.form-card-title {
    font-size: 1.3rem;
    font-weight: 700;
    color: #fff;
    margin-bottom: 1.5rem;
}

.campaign-form {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 0.4rem;
}

.form-label {
    font-size: 0.9rem;
    font-weight: 600;
    color: #ccc;
}

.form-input {
    background: rgba(255,255,255,0.06);
    border: 1px solid rgba(255,255,255,0.15);
    color: #fff;
    padding: 0.75rem 1rem;
    border-radius: 10px;
    font-size: 0.95rem;
    transition: border-color 0.2s;
}
.form-input::placeholder {
    color: rgba(255,255,255,0.35);
}
.form-input:focus {
    outline: none;
    border-color: var(--primary-color);
}

.form-error {
    color: #f87171;
    font-size: 0.8rem;
}

/* File Upload */
.file-upload-area {
    border: 2px dashed rgba(255,255,255,0.15);
    border-radius: 12px;
    padding: 2rem;
    cursor: pointer;
    transition: all 0.2s;
    text-align: center;
}
.file-upload-area.small {
    padding: 1rem;
}
.file-upload-area:hover {
    border-color: var(--primary-color);
    background: rgba(232, 68, 90, 0.04);
}

.upload-placeholder i {
    font-size: 2.5rem;
    color: rgba(255,255,255,0.4);
    margin-bottom: 0.5rem;
}
.upload-placeholder p {
    color: #ccc;
    font-weight: 500;
    margin-bottom: 0.25rem;
}
.upload-placeholder span {
    color: rgba(255,255,255,0.4);
    font-size: 0.8rem;
}

.upload-preview {
    position: relative;
    display: inline-block;
}
.preview-img {
    max-height: 200px;
    max-width: 100%;
    border-radius: 8px;
    object-fit: contain;
}
.preview-video {
    max-height: 200px;
    max-width: 100%;
    border-radius: 8px;
}
.media-type-badge {
    position: absolute;
    top: 8px;
    right: 8px;
    background: var(--primary-color);
    color: white;
    font-size: 0.7rem;
    font-weight: 700;
    padding: 0.2rem 0.5rem;
    border-radius: 6px;
}

.submit-btn {
    background: var(--primary-color);
    color: white;
    border: none;
    padding: 0.85rem;
    border-radius: 12px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}
.submit-btn:hover { opacity: 0.9; }
.submit-btn:disabled { opacity: 0.5; cursor: not-allowed; }

/* Empty State */
.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    color: rgba(255,255,255,0.4);
}
.empty-state i { font-size: 3rem; margin-bottom: 1rem; opacity: 0.5; }
.empty-state h3 { font-size: 1.3rem; color: #fff; margin-bottom: 0.5rem; }

/* Campaign Cards */
.campaigns-list {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.campaign-card {
    background: var(--section-bg);
    border-radius: 16px;
    overflow: hidden;
    border: 1px solid var(--border-color);
    padding: 1.5rem;
}

.campaign-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 1rem;
}

.status-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.status-badge.active {
    background: rgba(34, 197, 94, 0.15);
    color: #22c55e;
}
.status-badge.inactive {
    background: rgba(245, 158, 11, 0.15);
    color: #f59e0b;
}

.campaign-actions-top {
    display: flex;
    gap: 0.5rem;
}

.icon-btn {
    width: 36px;
    height: 36px;
    border-radius: 8px;
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.8rem;
    transition: all 0.2s;
}
.icon-btn.edit-btn {
    background: rgba(59, 130, 246, 0.12);
    color: #3b82f6;
}
.icon-btn.edit-btn:hover { background: rgba(59, 130, 246, 0.25); }
.icon-btn.toggle-btn {
    background: rgba(255,255,255,0.08);
    color: #ccc;
}
.icon-btn.toggle-btn.active {
    background: rgba(245, 158, 11, 0.12);
    color: #f59e0b;
}
.icon-btn.delete-btn {
    background: rgba(239, 68, 68, 0.1);
    color: #ef4444;
}
.icon-btn.delete-btn:hover { background: rgba(239, 68, 68, 0.2); }

/* Campaign Info */
.campaign-info {
    margin-bottom: 1rem;
}

.campaign-name {
    font-size: 1.15rem;
    font-weight: 700;
    color: #fff;
    margin-bottom: 0.4rem;
}

.campaign-company,
.campaign-impressions,
.campaign-date {
    font-size: 0.85rem;
    color: rgba(255,255,255,0.5);
    margin-bottom: 0.2rem;
}
.campaign-company i,
.campaign-impressions i,
.campaign-date i {
    width: 16px;
    margin-right: 0.4rem;
}

/* Edit Section */
.edit-section {
    margin-bottom: 1rem;
    padding: 1rem;
    background: rgba(255,255,255,0.03);
    border-radius: 12px;
    border: 1px solid rgba(255,255,255,0.08);
}

.edit-actions, .add-media-actions {
    display: flex;
    gap: 0.5rem;
    margin-top: 0.75rem;
}

.save-btn {
    background: #22c55e;
    color: white;
    border: none;
    padding: 0.5rem 1rem;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.85rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 0.4rem;
}
.save-btn:disabled { opacity: 0.5; cursor: not-allowed; }

.cancel-btn {
    background: rgba(255,255,255,0.08);
    color: #ccc;
    border: none;
    padding: 0.5rem 1rem;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.85rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 0.4rem;
}

/* Media Gallery */
.media-gallery {
    border-top: 1px solid rgba(255,255,255,0.06);
    padding-top: 1rem;
}

.gallery-title {
    font-size: 0.9rem;
    font-weight: 600;
    color: rgba(255,255,255,0.6);
    margin-bottom: 0.75rem;
}
.gallery-title i {
    margin-right: 0.4rem;
}

.media-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
}

.media-thumb {
    width: 120px;
    height: 120px;
    border-radius: 10px;
    overflow: hidden;
    position: relative;
    background: rgba(0,0,0,0.3);
    border: 1px solid rgba(255,255,255,0.08);
}
.media-thumb img,
.media-thumb video {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.media-thumb.add-thumb {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    border: 2px dashed rgba(255,255,255,0.15);
    background: transparent;
    color: rgba(255,255,255,0.4);
    font-size: 0.75rem;
    gap: 0.3rem;
    transition: all 0.2s;
}
.media-thumb.add-thumb i {
    font-size: 1.2rem;
}
.media-thumb.add-thumb:hover {
    border-color: var(--primary-color);
    color: var(--primary-color);
}

.thumb-badge {
    position: absolute;
    bottom: 4px;
    left: 4px;
    font-size: 0.7rem;
    background: rgba(0,0,0,0.6);
    padding: 0.15rem 0.4rem;
    border-radius: 4px;
}

.thumb-remove {
    position: absolute;
    top: 4px;
    right: 4px;
    width: 22px;
    height: 22px;
    border-radius: 50%;
    background: rgba(239, 68, 68, 0.85);
    color: white;
    border: none;
    cursor: pointer;
    font-size: 0.65rem;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.2s;
}
.media-thumb:hover .thumb-remove {
    opacity: 1;
}

.add-media-form {
    margin-top: 0.75rem;
    padding: 1rem;
    background: rgba(255,255,255,0.03);
    border-radius: 12px;
    border: 1px solid rgba(255,255,255,0.08);
}

/* Multi-file Queue */
.queue-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
    padding: 1rem;
    background: rgba(255,255,255,0.02);
    border: 2px dashed rgba(255,255,255,0.1);
    border-radius: 12px;
}
.queue-thumb {
    position: relative;
    width: 120px;
    height: 120px;
    border-radius: 10px;
    overflow: hidden;
    border: 2px solid rgba(255,255,255,0.1);
}
.queue-thumb img, .queue-thumb video {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.queue-thumb.add-thumb {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 0.3rem;
    border-style: dashed;
    border-color: rgba(255,255,255,0.2);
    cursor: pointer;
    color: rgba(255,255,255,0.4);
    font-size: 0.75rem;
    transition: all 0.2s;
}
.queue-thumb.add-thumb:hover {
    border-color: var(--primary-color);
    color: var(--primary-color);
}
.queue-thumb.add-thumb i {
    font-size: 1.2rem;
}

.queue-progress {
    margin-top: 0.75rem;
}
.queue-progress-bar {
    width: 100%;
    height: 6px;
    background: rgba(255,255,255,0.1);
    border-radius: 3px;
    overflow: hidden;
}
.queue-progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #e8445a, #f5c518);
    border-radius: 3px;
    transition: width 0.3s ease;
}
.queue-progress-text {
    display: block;
    margin-top: 0.4rem;
    font-size: 0.8rem;
    color: rgba(255,255,255,0.5);
    text-align: center;
}

/* Confirm Modal */
.confirm-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.6);
    backdrop-filter: blur(4px);
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
    animation: fadeIn 0.2s ease;
}
.confirm-card {
    background: linear-gradient(145deg, #1e293b, #0f172a);
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 16px;
    padding: 2rem;
    max-width: 420px;
    width: 90%;
    text-align: center;
    animation: scaleIn 0.2s ease;
}
.confirm-icon {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    background: rgba(239,68,68,0.15);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1rem;
}
.confirm-icon i {
    font-size: 1.5rem;
    color: #ef4444;
}
.confirm-card h3 {
    font-size: 1.2rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: #f8fafc;
}
.confirm-card p {
    color: #94a3b8;
    font-size: 0.9rem;
    margin-bottom: 1.5rem;
    line-height: 1.5;
}
.confirm-actions {
    display: flex;
    gap: 0.75rem;
    justify-content: center;
}
.confirm-cancel-btn {
    padding: 0.6rem 1.4rem;
    border-radius: 10px;
    border: 1px solid rgba(255,255,255,0.15);
    background: transparent;
    color: #cbd5e1;
    cursor: pointer;
    font-weight: 500;
    transition: all 0.2s;
}
.confirm-cancel-btn:hover {
    background: rgba(255,255,255,0.05);
}
.confirm-delete-btn {
    padding: 0.6rem 1.4rem;
    border-radius: 10px;
    border: none;
    background: linear-gradient(135deg, #ef4444, #dc2626);
    color: white;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.2s;
}
.confirm-delete-btn:hover {
    transform: scale(1.03);
    box-shadow: 0 4px 20px rgba(239,68,68,0.3);
}
@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}
@keyframes scaleIn {
    from { opacity: 0; transform: scale(0.9); }
    to { opacity: 1; transform: scale(1); }
}

@media (max-width: 768px) {
    .form-row { grid-template-columns: 1fr; }
    .media-thumb { width: 100px; height: 100px; }
}
</style>
