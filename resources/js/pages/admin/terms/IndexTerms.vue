<script setup lang="ts">
import { ref, computed } from 'vue';
import { Form, usePage } from '@inertiajs/vue3';
import AdminDashboardLayout from '@/layouts/AdminDashboardLayout.vue';

const page = usePage();
const terms = computed(() => (page.props as any).terms || []);

const activeTab = ref<'spectator' | 'creator'>('spectator');
const editingId = ref<number | null>(null);
const editTitle = ref('');
const editContent = ref('');
const editVersion = ref('');

const filteredTerms = computed(() => {
    return terms.value.filter((t: any) => t.type === activeTab.value);
});

function startEdit(term: any) {
    editingId.value = term.id;
    editTitle.value = term.title;
    editContent.value = term.content;
    editVersion.value = term.version;
}

function cancelEdit() {
    editingId.value = null;
    editTitle.value = '';
    editContent.value = '';
    editVersion.value = '';
}
</script>

<template>
    <AdminDashboardLayout title="Terms & Conditions" headerTitle="Terms & Conditions Management">
        <!-- Tab Switcher -->
        <div class="terms-tabs">
            <button
                class="tab-btn"
                :class="{ active: activeTab === 'spectator' }"
                @click="activeTab = 'spectator'; cancelEdit()"
            >
                <i class="fas fa-tv" style="margin-right:8px;"></i> Spectator Terms
            </button>
            <button
                class="tab-btn"
                :class="{ active: activeTab === 'creator' }"
                @click="activeTab = 'creator'; cancelEdit()"
            >
                <i class="fas fa-video" style="margin-right:8px;"></i> Creator Terms
            </button>
        </div>

        <!-- Terms List -->
        <div v-for="term in filteredTerms" :key="term.id" class="terms-card">
            <!-- View Mode -->
            <div v-if="editingId !== term.id">
                <div class="terms-card-header">
                    <div>
                        <h3 class="terms-card-title">{{ term.title }}</h3>
                        <span class="terms-badge">Version {{ term.version }}</span>
                        <span class="terms-badge" :class="term.is_active ? 'badge-active' : 'badge-inactive'">
                            {{ term.is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                    <button class="edit-btn-primary" @click="startEdit(term)">
                        <i class="fas fa-edit" style="margin-right:6px;"></i> Edit Terms & Conditions
                    </button>
                </div>

                <div class="section-label-tc">
                    <i class="fas fa-eye" style="margin-right:6px;"></i> Current Published Version Content:
                </div>
                <div class="terms-preview" v-html="term.content"></div>
                <div class="terms-meta">
                    <i class="fas fa-clock" style="margin-right:4px;"></i> Last updated: {{ new Date(term.updated_at).toLocaleString() }}
                </div>
            </div>

            <!-- Edit Mode -->
            <Form
                v-else
                :action="`/admin/terms/${term.id}`"
                method="put"
                v-slot="{ processing }"
                @success="cancelEdit()"
            >
                <div class="edit-form">
                    <div class="edit-header">
                        <h2><i class="fas fa-pen-to-square" style="color: var(--primary-color); margin-right:8px;"></i> Editing {{ term.title }}</h2>
                        <span class="edit-subtitle">Make changes below and see real-time preview instantly.</span>
                    </div>

                    <div class="form-row-2col">
                        <div class="form-group-tc">
                            <label class="form-label-tc">Document Title</label>
                            <input
                                type="text"
                                name="title"
                                v-model="editTitle"
                                class="form-input-tc"
                                placeholder="Terms & Conditions Title"
                            />
                        </div>
                        <div class="form-group-tc">
                            <label class="form-label-tc">Version Number</label>
                            <input
                                type="text"
                                name="version"
                                v-model="editVersion"
                                class="form-input-tc"
                                placeholder="1.0"
                            />
                        </div>
                    </div>

                    <!-- Split Editor and Live Preview -->
                    <div class="editor-split-grid">
                        <!-- Left Column: HTML Editor -->
                        <div class="form-group-tc">
                            <label class="form-label-tc">
                                <i class="fas fa-code" style="margin-right:6px; color: #a855f7;"></i> Content HTML Code Editor
                            </label>
                            <textarea
                                name="content"
                                v-model="editContent"
                                class="form-textarea-tc"
                                rows="22"
                                placeholder="Enter HTML content here..."
                            ></textarea>
                        </div>

                        <!-- Right Column: Real-time Live Preview -->
                        <div class="form-group-tc">
                            <label class="form-label-tc">
                                <i class="fas fa-desktop" style="margin-right:6px; color: #22c55e;"></i> Live Real-Time Preview (Vista Previa en Vivo)
                            </label>
                            <div class="terms-preview live-preview" v-html="editContent || '<p style=\'color: gray;\'>Preview will appear here as you type...</p>'"></div>
                        </div>
                    </div>

                    <div class="edit-actions">
                        <button type="button" class="cancel-btn" @click="cancelEdit()">
                            <i class="fas fa-times" style="margin-right:6px;"></i> Cancel
                        </button>
                        <button type="submit" class="save-btn" :disabled="processing">
                            <i class="fa-solid fa-circle-notch fa-spin" v-if="processing"></i>
                            <i class="fas fa-save" v-else style="margin-right:6px;"></i>
                            Save & Publish Changes
                        </button>
                    </div>
                </div>
            </Form>
        </div>

        <div v-if="filteredTerms.length === 0" class="empty-state">
            <i class="fas fa-file-contract" style="font-size: 2.5rem; margin-bottom: 1rem; color: var(--text-muted);"></i>
            <p>No terms & conditions document found for {{ activeTab }} users.</p>
        </div>
    </AdminDashboardLayout>
</template>

<style scoped>
.terms-tabs {
    display: flex;
    gap: 0.75rem;
    margin-bottom: 2rem;
}

.tab-btn {
    padding: 0.85rem 1.75rem;
    border: 1.5px solid rgba(255, 255, 255, 0.12);
    background: rgba(255, 255, 255, 0.03);
    color: var(--text-muted);
    border-radius: 12px;
    font-size: 0.95rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.25s ease;
    display: flex;
    align-items: center;
}

.tab-btn.active {
    background: var(--primary-color, #e8445a);
    border-color: var(--primary-color, #e8445a);
    color: white;
    box-shadow: 0 4px 20px rgba(232, 68, 90, 0.3);
}

.tab-btn:hover:not(.active) {
    border-color: rgba(255, 255, 255, 0.3);
    color: white;
}

.terms-card {
    background: rgba(15, 15, 35, 0.75);
    backdrop-filter: blur(16px);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 16px;
    padding: 1.75rem;
    margin-bottom: 1.5rem;
}

.terms-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.25rem;
}

.terms-card-title {
    font-size: 1.35rem;
    font-weight: 700;
    color: #fff;
    margin: 0 0 0.5rem;
}

.terms-badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    background: rgba(255, 255, 255, 0.1);
    color: rgba(255, 255, 255, 0.8);
    margin-right: 0.5rem;
}

.badge-active {
    background: rgba(34, 197, 94, 0.18);
    color: #4ade80;
    border: 1px solid rgba(34, 197, 94, 0.3);
}

.badge-inactive {
    background: rgba(239, 68, 68, 0.18);
    color: #f87171;
    border: 1px solid rgba(239, 68, 68, 0.3);
}

.edit-btn-primary {
    background: linear-gradient(135deg, #e8445a, #b91c1c);
    border: none;
    color: white;
    padding: 0.75rem 1.5rem;
    border-radius: 10px;
    cursor: pointer;
    font-size: 0.9rem;
    font-weight: 600;
    transition: all 0.2s;
    box-shadow: 0 4px 15px rgba(232, 68, 90, 0.3);
    display: flex;
    align-items: center;
}

.edit-btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(232, 68, 90, 0.4);
}

.section-label-tc {
    font-size: 0.9rem;
    font-weight: 600;
    color: rgba(255, 255, 255, 0.7);
    margin-bottom: 0.5rem;
}

.terms-preview {
    max-height: 350px;
    overflow-y: auto;
    padding: 1.25rem;
    background: rgba(0, 0, 0, 0.4);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 12px;
    color: rgba(255, 255, 255, 0.88);
    font-size: 0.88rem;
    line-height: 1.7;
}

.terms-preview :deep(h2) { font-size: 1.15rem; margin-bottom: 0.5rem; color: #fff; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 0.3rem; }
.terms-preview :deep(h3) { font-size: 1rem; margin: 1rem 0 0.4rem; color: #e8445a; }
.terms-preview :deep(p) { margin-bottom: 0.6rem; }
.terms-preview :deep(ul) { padding-left: 1.25rem; margin-bottom: 0.6rem; }
.terms-preview :deep(li) { margin-bottom: 0.3rem; }
.terms-preview :deep(strong) { color: #fff; }

.live-preview {
    max-height: 480px;
    height: 480px;
    border: 1.5px solid rgba(34, 197, 94, 0.4);
    background: rgba(10, 25, 15, 0.4);
}

.terms-meta {
    margin-top: 0.85rem;
    font-size: 0.82rem;
    color: rgba(255, 255, 255, 0.5);
}

/* Edit Form */
.edit-form {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}

.edit-header {
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    padding-bottom: 0.75rem;
    margin-bottom: 0.5rem;
}

.edit-header h2 {
    font-size: 1.3rem;
    font-weight: 700;
    color: white;
    margin: 0 0 0.25rem;
}

.edit-subtitle {
    font-size: 0.85rem;
    color: rgba(255, 255, 255, 0.6);
}

.form-row-2col {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 1rem;
}

.editor-split-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.25rem;
}

.form-group-tc { display: flex; flex-direction: column; gap: 0.4rem; }
.form-label-tc { font-size: 0.9rem; font-weight: 600; color: rgba(255, 255, 255, 0.85); display: flex; align-items: center; }

.form-input-tc {
    background: rgba(0, 0, 0, 0.4);
    border: 1.5px solid rgba(255, 255, 255, 0.15);
    color: #fff;
    padding: 0.75rem 1rem;
    border-radius: 10px;
    font-size: 0.9rem;
    width: 100%;
}
.form-input-tc:focus {
    border-color: var(--primary-color, #e8445a);
    outline: none;
}

.form-textarea-tc {
    background: rgba(0, 0, 0, 0.5);
    border: 1.5px solid rgba(168, 85, 247, 0.4);
    color: #4ade80;
    padding: 1rem;
    border-radius: 12px;
    font-size: 0.85rem;
    font-family: 'Fira Code', monospace;
    width: 100%;
    resize: vertical;
    line-height: 1.6;
    height: 480px;
}
.form-textarea-tc:focus {
    border-color: #a855f7;
    outline: none;
}

.edit-actions {
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
    margin-top: 0.5rem;
}

.cancel-btn {
    background: transparent;
    border: 1.5px solid rgba(255, 255, 255, 0.2);
    color: rgba(255, 255, 255, 0.8);
    padding: 0.75rem 1.5rem;
    border-radius: 10px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}

.cancel-btn:hover {
    border-color: white;
    color: white;
}

.save-btn {
    background: linear-gradient(135deg, #22c55e, #15803d);
    border: none;
    color: white;
    padding: 0.75rem 2rem;
    border-radius: 10px;
    font-weight: 700;
    font-size: 0.95rem;
    cursor: pointer;
    transition: all 0.2s;
    box-shadow: 0 4px 15px rgba(34, 197, 94, 0.3);
}

.save-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(34, 197, 94, 0.4);
}
.save-btn:disabled { opacity: 0.5; cursor: not-allowed; }

.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    color: var(--text-muted);
    font-size: 1rem;
    background: rgba(255, 255, 255, 0.02);
    border-radius: 16px;
    border: 1px dashed rgba(255, 255, 255, 0.1);
}

@media (max-width: 992px) {
    .editor-split-grid {
        grid-template-columns: 1fr;
    }
    .form-row-2col {
        grid-template-columns: 1fr;
    }
}
</style>
