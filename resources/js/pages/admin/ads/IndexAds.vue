<script setup lang="ts">
import { ref } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import AdminDashboardLayout from '@/layouts/AdminDashboardLayout.vue';

const props = defineProps<{
    campaigns: any[];
}>();

const showCreateForm = ref(false);

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

        // Auto-detect media type
        if (file.type.startsWith('video/')) {
            form.media_type = 'video';
        } else {
            form.media_type = 'image';
        }

        // Preview
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

function toggleCampaign(id: number) {
    router.put(`/admin/ads/${id}/toggle`, {}, {
        preserveScroll: true,
    });
}

function deleteCampaign(id: number) {
    if (confirm('Are you sure? This will permanently delete the campaign and its media files.')) {
        router.delete(`/admin/ads/${id}`, {
            preserveScroll: true,
        });
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
                    <div class="file-upload-area" @click="($refs.fileInput as HTMLInputElement).click()">
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
                        ref="fileInput"
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

            <div v-else class="campaigns-grid">
                <div v-for="campaign in campaigns" :key="campaign.id" class="campaign-card">
                    <div class="campaign-media">
                        <img v-if="campaign.media_type === 'image'" :src="campaign.media_url" alt="Campaign media" />
                        <video v-else :src="campaign.media_url" class="campaign-video" muted></video>
                        <span class="media-badge" :class="campaign.media_type">
                            {{ campaign.media_type === 'image' ? '🖼️ Image' : '🎬 Video' }}
                        </span>
                        <span class="status-badge" :class="campaign.status">
                            {{ campaign.status }}
                        </span>
                    </div>
                    <div class="campaign-info">
                        <h3 class="campaign-name">{{ campaign.name }}</h3>
                        <p v-if="campaign.company_name" class="campaign-company">
                            <i class="fas fa-building"></i> {{ campaign.company_name }}
                        </p>
                        <p class="campaign-impressions">
                            <i class="fas fa-eye"></i> {{ campaign.impressions_count.toLocaleString() }} impressions
                        </p>
                        <p class="campaign-date">
                            <i class="fas fa-calendar"></i> {{ new Date(campaign.created_at).toLocaleDateString() }}
                        </p>
                    </div>
                    <div class="campaign-actions">
                        <button
                            class="action-btn toggle-btn"
                            :class="{ 'active': campaign.status === 'active' }"
                            @click="toggleCampaign(campaign.id)"
                            :title="campaign.status === 'active' ? 'Deactivate' : 'Activate'"
                        >
                            <i :class="campaign.status === 'active' ? 'fas fa-pause' : 'fas fa-play'"></i>
                        </button>
                        <button class="action-btn delete-btn" @click="deleteCampaign(campaign.id)" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
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
    color: var(--text-headings);
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
    color: var(--text-light);
}

.form-input {
    background: var(--input-bg, rgba(255,255,255,0.08));
    border: 1px solid var(--border-color);
    color: var(--text-light);
    padding: 0.75rem 1rem;
    border-radius: 10px;
    font-size: 0.95rem;
    transition: border-color 0.2s;
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
    border: 2px dashed var(--border-color);
    border-radius: 12px;
    padding: 2rem;
    cursor: pointer;
    transition: all 0.2s;
    text-align: center;
}
.file-upload-area:hover {
    border-color: var(--primary-color);
    background: rgba(232, 68, 90, 0.04);
}

.upload-placeholder i {
    font-size: 2.5rem;
    color: var(--text-muted);
    margin-bottom: 0.5rem;
}
.upload-placeholder p {
    color: var(--text-light);
    font-weight: 500;
    margin-bottom: 0.25rem;
}
.upload-placeholder span {
    color: var(--text-muted);
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
.submit-btn:hover {
    opacity: 0.9;
    transform: scale(1.01);
}
.submit-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    color: var(--text-muted);
}
.empty-state i {
    font-size: 3rem;
    margin-bottom: 1rem;
    opacity: 0.5;
}
.empty-state h3 {
    font-size: 1.3rem;
    color: var(--text-light);
    margin-bottom: 0.5rem;
}

/* Campaigns Grid */
.campaigns-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
    gap: 1.5rem;
}

.campaign-card {
    background: var(--section-bg);
    border-radius: 16px;
    overflow: hidden;
    border: 1px solid var(--border-color);
    transition: transform 0.2s, box-shadow 0.2s;
}
.campaign-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
}

.campaign-media {
    position: relative;
    height: 180px;
    background: rgba(0,0,0,0.3);
    overflow: hidden;
}
.campaign-media img,
.campaign-media video {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.media-badge {
    position: absolute;
    bottom: 8px;
    left: 8px;
    padding: 0.25rem 0.6rem;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 600;
    background: rgba(0,0,0,0.7);
    color: white;
}

.status-badge {
    position: absolute;
    top: 8px;
    right: 8px;
    padding: 0.25rem 0.6rem;
    border-radius: 6px;
    font-size: 0.7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.status-badge.active {
    background: #22c55e;
    color: white;
}
.status-badge.inactive {
    background: #f59e0b;
    color: #000;
}

.campaign-info {
    padding: 1rem 1.25rem;
}

.campaign-name {
    font-size: 1.1rem;
    font-weight: 700;
    color: var(--text-headings);
    margin-bottom: 0.4rem;
}

.campaign-company,
.campaign-impressions,
.campaign-date {
    font-size: 0.85rem;
    color: var(--text-muted);
    margin-bottom: 0.2rem;
}
.campaign-company i,
.campaign-impressions i,
.campaign-date i {
    width: 16px;
    margin-right: 0.4rem;
}

.campaign-actions {
    display: flex;
    gap: 0.5rem;
    padding: 0 1.25rem 1.25rem;
}

.action-btn {
    flex: 1;
    padding: 0.6rem;
    border: none;
    border-radius: 10px;
    cursor: pointer;
    font-size: 0.85rem;
    font-weight: 600;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.4rem;
}

.toggle-btn {
    background: rgba(255,255,255,0.08);
    color: var(--text-light);
}
.toggle-btn:hover {
    background: rgba(255,255,255,0.15);
}
.toggle-btn.active {
    background: rgba(245, 158, 11, 0.15);
    color: #f59e0b;
}

.delete-btn {
    background: rgba(239, 68, 68, 0.1);
    color: #ef4444;
}
.delete-btn:hover {
    background: rgba(239, 68, 68, 0.2);
}

@media (max-width: 768px) {
    .form-row {
        grid-template-columns: 1fr;
    }
    .campaigns-grid {
        grid-template-columns: 1fr;
    }
}
</style>
