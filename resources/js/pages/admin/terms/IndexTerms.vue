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
    <AdminDashboardLayout title="Terms & Conditions" headerTitle="Terms & Conditions">
        <!-- Tab Switcher -->
        <div class="terms-tabs">
            <button
                class="tab-btn"
                :class="{ active: activeTab === 'spectator' }"
                @click="activeTab = 'spectator'; cancelEdit()"
            >
                <i class="fas fa-tv" style="margin-right:6px;"></i> Spectator Terms
            </button>
            <button
                class="tab-btn"
                :class="{ active: activeTab === 'creator' }"
                @click="activeTab = 'creator'; cancelEdit()"
            >
                <i class="fas fa-video" style="margin-right:6px;"></i> Creator Terms
            </button>
        </div>

        <!-- Terms List -->
        <div v-for="term in filteredTerms" :key="term.id" class="terms-card">
            <!-- View Mode -->
            <div v-if="editingId !== term.id">
                <div class="terms-card-header">
                    <div>
                        <h3 class="terms-card-title">{{ term.title }}</h3>
                        <span class="terms-badge">v{{ term.version }}</span>
                        <span class="terms-badge" :class="term.is_active ? 'badge-active' : 'badge-inactive'">
                            {{ term.is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                    <button class="edit-btn" @click="startEdit(term)">
                        <i class="fas fa-pencil-alt" style="margin-right:4px;"></i> Edit
                    </button>
                </div>
                <div class="terms-preview" v-html="term.content"></div>
                <div class="terms-meta">
                    Last updated: {{ new Date(term.updated_at).toLocaleDateString() }}
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
                    <div class="form-group-tc">
                        <label class="form-label-tc">Title</label>
                        <input
                            type="text"
                            name="title"
                            v-model="editTitle"
                            class="form-input-tc"
                        />
                    </div>
                    <div class="form-group-tc">
                        <label class="form-label-tc">Version</label>
                        <input
                            type="text"
                            name="version"
                            v-model="editVersion"
                            class="form-input-tc"
                            style="max-width: 150px;"
                        />
                    </div>
                    <div class="form-group-tc">
                        <label class="form-label-tc">Content (HTML)</label>
                        <textarea
                            name="content"
                            v-model="editContent"
                            class="form-textarea-tc"
                            rows="20"
                        ></textarea>
                    </div>

                    <!-- Live Preview -->
                    <div class="form-group-tc">
                        <label class="form-label-tc">Preview</label>
                        <div class="terms-preview live-preview" v-html="editContent"></div>
                    </div>

                    <div class="edit-actions">
                        <button type="button" class="cancel-btn" @click="cancelEdit()">Cancel</button>
                        <button type="submit" class="save-btn" :disabled="processing">
                            <i class="fa-solid fa-circle-notch fa-spin" v-if="processing"></i>
                            Save Changes
                        </button>
                    </div>
                </div>
            </Form>
        </div>

        <div v-if="filteredTerms.length === 0" class="empty-state">
            <p>No terms & conditions found for {{ activeTab }} users.</p>
        </div>
    </AdminDashboardLayout>
</template>

<style scoped>
.terms-tabs {
    display: flex;
    gap: 0.5rem;
    margin-bottom: 2rem;
}

.tab-btn {
    padding: 0.75rem 1.5rem;
    border: 1.5px solid var(--border-color);
    background: transparent;
    color: var(--text-muted);
    border-radius: 10px;
    font-size: 0.95rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
}

.tab-btn.active {
    background: var(--primary-color);
    border-color: var(--primary-color);
    color: white;
}

.tab-btn:hover:not(.active) {
    border-color: var(--primary-color);
    color: var(--primary-color);
}

.terms-card {
    background: var(--section-bg);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
}

.terms-card-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1rem;
}

.terms-card-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--text-headings);
    margin: 0 0 0.5rem;
}

.terms-badge {
    display: inline-block;
    padding: 0.2rem 0.6rem;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 600;
    background: rgba(255, 255, 255, 0.1);
    color: var(--text-muted);
    margin-right: 0.5rem;
}

.badge-active {
    background: rgba(34, 197, 94, 0.15);
    color: #22c55e;
}

.badge-inactive {
    background: rgba(239, 68, 68, 0.15);
    color: #ef4444;
}

.edit-btn {
    background: transparent;
    border: 1px solid var(--border-color);
    color: var(--text-muted);
    padding: 0.5rem 1rem;
    border-radius: 8px;
    cursor: pointer;
    font-size: 0.85rem;
    transition: all 0.2s;
}

.edit-btn:hover {
    border-color: var(--primary-color);
    color: var(--primary-color);
}

.terms-preview {
    max-height: 300px;
    overflow-y: auto;
    padding: 1rem;
    background: rgba(0, 0, 0, 0.2);
    border-radius: 8px;
    color: var(--text-light);
    font-size: 0.85rem;
    line-height: 1.7;
}

.terms-preview :deep(h2) { font-size: 1.1rem; margin-bottom: 0.4rem; color: var(--text-headings); }
.terms-preview :deep(h3) { font-size: 0.95rem; margin: 0.8rem 0 0.3rem; color: var(--primary-color); }
.terms-preview :deep(ul) { padding-left: 1.25rem; }
.terms-preview :deep(strong) { color: var(--text-headings); }

.live-preview {
    max-height: 250px;
    border: 1px solid var(--border-color);
}

.terms-meta {
    margin-top: 0.75rem;
    font-size: 0.8rem;
    color: var(--text-muted);
}

/* Edit Form */
.edit-form {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.form-group-tc { display: flex; flex-direction: column; gap: 0.3rem; }
.form-label-tc { font-size: 0.9rem; font-weight: 500; color: var(--text-muted); }

.form-input-tc {
    background: var(--input-bg);
    border: 1px solid var(--border-color);
    color: var(--text-dark-on-light-bg);
    padding: 0.7rem 1rem;
    border-radius: 8px;
    font-size: 0.9rem;
    width: 100%;
}

.form-textarea-tc {
    background: var(--input-bg);
    border: 1px solid var(--border-color);
    color: var(--text-dark-on-light-bg);
    padding: 0.7rem 1rem;
    border-radius: 8px;
    font-size: 0.85rem;
    font-family: monospace;
    width: 100%;
    resize: vertical;
}

.edit-actions {
    display: flex;
    gap: 0.75rem;
    justify-content: flex-end;
}

.cancel-btn {
    background: transparent;
    border: 1px solid var(--border-color);
    color: var(--text-muted);
    padding: 0.65rem 1.25rem;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s;
}

.cancel-btn:hover {
    border-color: var(--text-light);
    color: var(--text-light);
}

.save-btn {
    background: var(--primary-color);
    border: none;
    color: white;
    padding: 0.65rem 1.5rem;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}

.save-btn:hover { opacity: 0.9; }
.save-btn:disabled { opacity: 0.5; cursor: not-allowed; }

.empty-state {
    text-align: center;
    padding: 3rem;
    color: var(--text-muted);
    font-size: 0.95rem;
}
</style>
