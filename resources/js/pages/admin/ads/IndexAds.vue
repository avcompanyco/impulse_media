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

const previewUrl = ref('');

function handleFileChange(event: Event) {
    const input = event.target as HTMLInputElement;
    if (input.files && input.files[0]) {
        const file = input.files[0];
        form.media = file;
        if (file.type.startsWith('video/')) {
            form.media_type = 'video';
        } else {
            form.media_type = 'image';
        }
        previewUrl.value = URL.createObjectURL(file);
    }
}

function submitCampaign() {
    form.post('/admin/ads', {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            previewUrl.value = '';
            showCreateForm.value = false;
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

function handleAddMediaFile(event: Event) {
    const input = event.target as HTMLInputElement;
    if (input.files && input.files[0]) {
        const file = input.files[0];
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
    if (confirm('Remove this media file?')) {
        router.delete(`/admin/ads/media/${mediaId}`, {
            preserveScroll: true,
        });
    }
}

// ─── Toggle / Delete ───
function toggleCampaign(id: number) {
    router.put(`/admin/ads/${id}/toggle`, {}, { preserveScroll: true });
}

function deleteCampaign(id: number) {
    if (confirm('Are you sure? This will permanently delete the campaign and all its media files.')) {
        router.delete(`/admin/ads/${id}`, { preserveScroll: true });
    }
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
                    <label class="form-label">Media File (Image 1:1 or Video) *</label>
                    <div class="file-upload-area" @click="createFileInputRef?.click()">
                        <div v-if="!previewUrl" class="upload-placeholder">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <p>Click to upload image or video</p>
                            <span>Max 100MB • Supported: JPG, PNG, MP4, MOV</span>
                        </div>
                        <div v-else class="upload-preview">
                            <img v-if="form.media_type === 'image'" :src="previewUrl" alt="Preview" class="preview-img" />
                            <video v-else :src="previewUrl" class="preview-video" controls></video>
                            <span class="media-type-badge">{{ form.media_type.toUpperCase() }}</span>
                        </div>
                    </div>
                    <input
                        ref="createFileInputRef"
                        type="file"
                        accept="image/*,video/*"
                        @change="handleFileChange"
                        style="display: none"
                    />
                    <span v-if="form.errors.media" class="form-error">{{ form.errors.media }}</span>
                </div>

                <button type="submit" class="submit-btn" :disabled="form.processing">
                    <i class="fa-solid fa-circle-notch fa-spin" v-if="form.processing"></i>
                    {{ form.processing ? 'Uploading...' : 'Create Campaign' }}
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

@media (max-width: 768px) {
    .form-row { grid-template-columns: 1fr; }
    .media-thumb { width: 100px; height: 100px; }
}
</style>
