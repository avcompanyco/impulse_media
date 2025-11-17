<script setup lang="ts">
import { ref, onMounted, watch, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import DatatableContentController from '@/actions/App/Http/Controllers/Content/DatatableContentController';
import ShowMovieChannelController from '@/actions/App/Http/Controllers/Channel/ShowMovieChannelController';
import ViewContentController from '@/actions/App/Http/Controllers/Content/ViewContentController';

import PauseContentController from '@/actions/App/Http/Controllers/Content/PauseContentController';
import PublishContentController from '@/actions/App/Http/Controllers/Content/PublishContentController';

import TogglePauseBtn from './TogglePauseBtn.vue';
import DeleteContentModal from './DeleteContentModal.vue';

interface Content {
    id: number;
    username: string;
    title: string;
    url: string;
    type: string;
    status: string;
    created_at: string;
    updated_at: string;
}

const query = ref({
    search: '',
    page: 1,
    perPage: 10,
});

const contents = ref<Content[]>([]);
const pagination = ref({
    total: 0,
    perPage: 10,
    currentPage: 1,
    lastPage: 1,
    from: 1,
    to: 1,
});
const loading = ref(false);

const fetchContents = async () => {
    try {
        loading.value = true;
        
        const response = await fetch(DatatableContentController.url({
            query: query.value
        }), {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
        });
        
        const data = await response.json();

        if (data.data) {
            contents.value = data.data;
            pagination.value = {
                total: data.meta.total,
                perPage: data.meta.per_page,
                currentPage: data.meta.current_page,
                lastPage: data.meta.last_page,
                from: data.meta.from || 0,
                to: data.meta.to || 0,
            };
        }
    } catch (error) {
        console.error('Error fetching contents:', error);
    } finally {
        loading.value = false;
    }
    console.log(contents.value);
};

const handleSearch = (event: Event) => {
    const target = event.target as HTMLInputElement;
    query.value.search = target.value;
    query.value.page = 1; // Reset to first page when searching
};

const changePage = (page: number) => {
    if (page >= 1 && page <= pagination.value.lastPage) {
        query.value.page = page;
    }
};

const changePerPage = (perPage: number) => {
    query.value.perPage = perPage;
    query.value.page = 1; // Reset to first page when changing per page
};

const getContentTypeLabel = (type: string): string => {
    switch (type) {
        case 'movies':
            return 'Movie';
        case 'series':
            return 'Serie';
        case 'shorts':
            return 'Short';
        default:
            return type;
    }
};

const getStatusBadgeClass = (status: string): string => {
    switch (status.toLowerCase()) {
        case 'published':
            return 'status-published';
        case 'draft':
            return 'status-draft';
        case 'paused':
            return 'status-paused';
        default:
            return 'status-default';
    }
};

// Computed property for visible pages in pagination
const getVisiblePages = computed(() => {
    const pages: (number | string)[] = [];
    const current = pagination.value.currentPage;
    const last = pagination.value.lastPage;
    
    if (last <= 7) {
        // Show all pages if 7 or fewer
        for (let i = 1; i <= last; i++) {
            pages.push(i);
        }
    } else {
        // Always show first page
        pages.push(1);
        
        if (current <= 4) {
            // Near the beginning
            for (let i = 2; i <= 5; i++) {
                pages.push(i);
            }
            pages.push('...');
            pages.push(last);
        } else if (current >= last - 3) {
            // Near the end
            pages.push('...');
            for (let i = last - 4; i <= last; i++) {
                pages.push(i);
            }
        } else {
            // In the middle
            pages.push('...');
            for (let i = current - 1; i <= current + 1; i++) {
                pages.push(i);
            }
            pages.push('...');
            pages.push(last);
        }
    }
    
    return pages;
});

function pauseContent(id: number) {
    router.put(PauseContentController.url({ id }), {}, {
        preserveScroll: true,
    });
}
function publishContent(id: number) {
    router.put(PublishContentController.url({ id }), {}, {
        preserveScroll: true,
    });
}

// Watch for query changes and fetch contents
watch(() => query, () => {
    fetchContents();
}, { deep: true });

onMounted(() => {
    fetchContents();
});

</script>

<template>
    <div class="table-controls">
        <div class="search-bar">
            <input 
                type="search" 
                id="contentSearchInput"
                :value="query.search"
                @input="handleSearch"
                placeholder="Search by Title, Username, Type..."
            >
        </div>
        <!-- <div class="per-page-selector form-group">
            <label class="form-label" for="perPageSelect">Show:</label>
            <select 
                id="perPageSelect" 
                class="form-control"
                :value="query.perPage" 
                @change="changePerPage(parseInt(($event.target as HTMLSelectElement).value))"
                :disabled="loading"
                style="background-color: var(--input-bg); border: 1px solid var(--input-bg); color: var(--text-dark-on-light-bg); border-radius: var(--border-radius-sm);"
            >
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
                <option value="100">100</option>
            </select>
        </div> -->
    </div>
    
    <div class="admin-table-wrapper">
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Uploader</th>
                        <th>Type</th>
                        <th>Upload Date</th>
                        <th>Content Url</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="contentsTableBody">
                    <tr v-if="contents.length === 0 && !loading">
                        <td colspan="6" class="no-data">
                            {{ query.search ? 'No content found matching your search.' : 'No content available.' }}
                        </td>
                    </tr>
                    <tr v-for="content in contents" :key="content.id" :class="{ 'loading-row': loading }">
                        <td>
                            <a :href="ViewContentController.url({content: content.id})" target="_blank" class="content-title-link">
                                {{ content.title || 'Untitled' }}
                            </a>
                        </td>
                        <td>
                            @{{ content.username }}
                        </td>
                        <td>
                            <span class="type-badge">
                                {{ getContentTypeLabel(content.type) }}
                            </span>
                        </td>
                        <td>{{ new Date(content.created_at).toLocaleDateString() }}</td>
                        <td>
                            <a :href="ViewContentController.url({content: content.id})" target="_blank" class="content-title-link">
                                View Content
                            </a>
                        </td>
                        <td>
                            <span :class="['status-badge', getStatusBadgeClass(content.status)]">
                                {{ content.status.charAt(0).toUpperCase() + content.status.slice(1) }}
                            </span>
                        </td>
                        <td class="action-buttons">
                            <TogglePauseBtn :content="content" @updated="fetchContents" />
                            <DeleteContentModal :content="content" @updated="fetchContents" />
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div v-if="loading" class="loading-overlay">
            <div class="spinner"></div>
            <span>Loading content...</span>
        </div>
    </div>

    <!-- Pagination -->
    <div v-if="pagination.total > 0" class="d-flex justify-content-between align-items-center mt-4">
        <div class="text-muted">
            Showing {{ pagination.from }} to {{ pagination.to }} of {{ pagination.total }} contents
        </div>
        
        <nav aria-label="Content pagination">
            <ul class="pagination mb-0">
                <li class="page-item" :class="{ disabled: pagination.currentPage <= 1 || loading }">
                    <button 
                        class="page-link" 
                        :disabled="pagination.currentPage <= 1 || loading"
                        @click="changePage(pagination.currentPage - 1)"
                    >
                        Previous
                    </button>
                </li>
                
                <template v-for="page in getVisiblePages" :key="page">
                    <li 
                        v-if="page !== '...'"
                        class="page-item"
                        :class="{ active: page === pagination.currentPage, disabled: loading }"
                    >
                        <button 
                            class="page-link"
                            :disabled="loading"
                            @click="changePage(page as number)"
                        >
                            {{ page }}
                        </button>
                    </li>
                    <li v-else class="page-item disabled">
                        <span class="page-link">...</span>
                    </li>
                </template>
                
                <li class="page-item" :class="{ disabled: pagination.currentPage >= pagination.lastPage || loading }">
                    <button 
                        class="page-link" 
                        :disabled="pagination.currentPage >= pagination.lastPage || loading"
                        @click="changePage(pagination.currentPage + 1)"
                    >
                        Next
                    </button>
                </li>
            </ul>
        </nav>
    </div>
</template>

<style scoped>
.table-controls {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    flex-wrap: wrap;
    gap: 1rem;
}

.search-bar input[type="search"] {
    padding: 0.75rem 1rem;
    border-radius: var(--border-radius-sm);
    background-color: var(--input-bg);
    border: 1px solid var(--input-bg);
    color: var(--text-dark-on-light-bg);
    min-width: 300px;
    font-size: 0.95rem;
}

.search-bar input[type="search"]::placeholder {
    color: #777;
}

.search-bar input[type="search"]:focus {
    border-color: var(--primary-color);
    outline: none;
    box-shadow: 0 0 0 3px rgba(240, 98, 146, 0.3);
}

.admin-table-wrapper {
    overflow-x: auto;
    background-color: var(--section-bg);
    border-radius: var(--border-radius-md);
    padding: 0.5rem;
    box-shadow: var(--shadow-sm);
}

.admin-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    color: var(--text-light);
    font-size: 0.95rem;
}

.admin-table th,
.admin-table td {
    padding: 1rem 1.1rem;
    text-align: left;
    vertical-align: middle;
    border-bottom: 1px solid var(--border-color);
}

.admin-table th {
    background-color: var(--table-header-bg);
    font-weight: 600;
    white-space: nowrap;
    color: var(--text-headings);
    font-size: 1rem;
}

.admin-table tr:last-child td {
    border-bottom: none;
}

.admin-table tbody tr:hover {
    background-color: var(--table-row-hover-bg);
}

.admin-table a {
    color: var(--text-light);
    text-decoration: none;
}

.admin-table a:hover {
    color: var(--primary-color);
    text-decoration: underline;
}

.content-title-link {
    font-weight: 500;
    color: var(--primary-color) !important;
}

.content-title-link:hover {
    color: var(--primary-color-hover) !important;
}

.type-badge {
    display: inline-block;
    padding: 0.25rem 0.5rem;
    border-radius: var(--border-radius-sm);
    background-color: var(--secondary-color);
    color: white;
    font-size: 0.75rem;
    font-weight: 500;
}

.status-badge {
    display: inline-block;
    padding: 0.25rem 0.5rem;
    border-radius: var(--border-radius-sm);
    font-size: 0.75rem;
    font-weight: 500;
}

.status-published {
    background-color: #28a745;
    color: white;
}

.status-draft {
    background-color: #6c757d;
    color: white;
}

.status-paused {
    background-color: #ffc107;
    color: #212529;
}

.status-default {
    background-color: var(--border-color);
    color: var(--text-light);
}

.action-buttons {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    white-space: nowrap;
}

.action-buttons .btn {
    font-size: 0.825rem;
    padding: 0.45rem 0.85rem;
    border-radius: var(--border-radius-sm);
    border: none;
    font-weight: 500;
    text-align: center;
    min-width: 65px;
    cursor: pointer;
    text-decoration: none;
    color: white;
    transition: background-color 0.2s ease, transform 0.2s ease;
}

.action-buttons .btn:hover {
    transform: translateY(-1px);
}

.action-buttons .btn-publish {
    background-color: var(--secondary-color);
}

.action-buttons .btn-publish:hover {
    background-color: var(--secondary-color-hover);
}

.action-buttons .btn-pause {
    background-color: var(--primary-color);
}

.action-buttons .btn-pause:hover {
    background-color: var(--primary-color-hover);
}

.action-buttons .btn-delete {
    background-color: var(--error-color);
}

.action-buttons .btn-delete:hover {
    background-color: var(--error-color-hover);
}
</style>