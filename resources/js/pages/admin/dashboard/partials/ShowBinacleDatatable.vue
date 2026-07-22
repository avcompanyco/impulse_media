<script setup lang="ts">
import { ref, onMounted, watch, computed } from 'vue';
import ShowMovieChannelController from '@/actions/App/Http/Controllers/Channel/ShowMovieChannelController';
import { Link } from '@inertiajs/vue3';

const query = ref({
    page: 1,
    perPage: 10,
});

const binacles = ref<any[]>([]);
const pagination = ref({
    total: 0,
    perPage: 10,
    currentPage: 1,
    lastPage: 1,
    from: 1,
    to: 1,
});
const loading = ref(false);

const fetchBinacles = async () => {
    try {
        loading.value = true;
        
        const params = new URLSearchParams({
            page: query.value.page.toString(),
            perPage: query.value.perPage.toString(),
        });
        
        const response = await fetch(`/admin/binacles/datatable?${params}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
        });
        
        const data = await response.json();

        if (data.data) {
            binacles.value = data.data;
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
        console.error('Error fetching binacles:', error);
    } finally {
        loading.value = false;
    }
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

// Watch for query changes and fetch binacles
watch(query, () => {
    fetchBinacles();
}, { deep: true });

onMounted(() => {
    fetchBinacles();
});
function timeAgo(date: string) {
    const now = new Date();
    const past = new Date(date);
    const diffInMs = now.getTime() - past.getTime();
    
    const seconds = Math.floor(diffInMs / 1000);
    const minutes = Math.floor(seconds / 60);
    const hours = Math.floor(minutes / 60);
    const days = Math.floor(hours / 24);
    const weeks = Math.floor(days / 7);
    const months = Math.floor(days / 30);
    const years = Math.floor(days / 365);
    
    if (seconds < 60) {
        return seconds <= 1 ? '1 second ago' : `${seconds} seconds ago`;
    } else if (minutes < 60) {
        return minutes === 1 ? '1 minute ago' : `${minutes} minutes ago`;
    } else if (hours < 24) {
        return hours === 1 ? '1 hour ago' : `${hours} hours ago`;
    } else if (days < 7) {
        return days === 1 ? '1 day ago' : `${days} days ago`;
    } else if (weeks < 4) {
        return weeks === 1 ? '1 week ago' : `${weeks} weeks ago`;
    } else if (months < 12) {
        return months === 1 ? '1 month ago' : `${months} months ago`;
    } else {
        return years === 1 ? '1 year ago' : `${years} years ago`;
    }
}

</script>

<template>
    <div class="admin-table-wrapper">
        <div v-if="loading" class="loading-overlay">
            <div class="spinner"></div>
            <span>Loading binacles...</span>
        </div>
        
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Action</th>
                        <th>Details</th>
                        <th>Timestamp</th>
                    </tr>
                </thead>
                <tbody id="binaclesTableBody">
                    <tr v-if="binacles.length === 0 && !loading">
                        <td colspan="4" class="no-data">
                            No binacles available.
                        </td>
                    </tr>
                    <tr v-for="binacle in binacles" :key="binacle.id" :class="{ 'loading-row': loading }">
                        <td>
                            <Link :href="ShowMovieChannelController.url({username: binacle.user.username})" target="_blank">
                                @{{ binacle.user.username }}
                            </Link>
                        </td>
                        <td>{{ binacle.action }}</td>
                        <td>{{ binacle.details }}</td>
                        <td>{{ timeAgo(binacle.created_at) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <div v-if="pagination.total > 0" class="pagination-container">
        <div class="pagination-info">
            Showing {{ pagination.from }} to {{ pagination.to }} of {{ pagination.total }} binacles
        </div>
        
        <nav aria-label="Binacles pagination" class="pagination-nav">
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

.no-data {
    text-align: center;
    color: var(--text-muted);
    font-style: italic;
    padding: 2rem;
}

.loading-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: rgba(0, 0, 0, 0.1);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    z-index: 10;
}

.spinner {
    border: 3px solid var(--border-color);
    border-top: 3px solid var(--primary-color);
    border-radius: 50%;
    width: 30px;
    height: 30px;
    animation: spin 1s linear infinite;
    margin-bottom: 0.5rem;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.loading-row {
    opacity: 0.6;
}

/* Pagination Dark Theme Responsive Styles */
.pagination-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 1.5rem;
    flex-wrap: wrap;
    gap: 1rem;
}

.pagination-info {
    color: #a0aec0;
    font-size: 0.88rem;
    font-weight: 500;
}

.pagination-nav .pagination {
    display: flex;
    gap: 5px;
    list-style: none;
    padding: 0;
    margin: 0;
    flex-wrap: wrap;
}

.page-item {
    display: inline-block;
}

.page-link {
    background-color: var(--section-bg, #1a1a3a);
    color: var(--text-light, #ffffff);
    border: 1px solid var(--border-color, rgba(255, 255, 255, 0.15));
    padding: 0.4rem 0.8rem;
    border-radius: 8px;
    font-size: 0.85rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
}

.page-link:hover:not(:disabled) {
    background-color: var(--primary-color, #e8445a);
    color: #ffffff;
    border-color: var(--primary-color, #e8445a);
}

.page-item.active .page-link {
    background-color: var(--primary-color, #e8445a);
    color: #ffffff;
    border-color: var(--primary-color, #e8445a);
    font-weight: 700;
}

.page-item.disabled .page-link,
.page-link:disabled {
    opacity: 0.4;
    cursor: not-allowed;
    background-color: rgba(255, 255, 255, 0.05);
}

@media (max-width: 768px) {
    .pagination-container {
        flex-direction: column;
        align-items: center;
        text-align: center;
        gap: 0.75rem;
    }

    .pagination-nav .pagination {
        justify-content: center;
    }

    .page-link {
        padding: 0.3rem 0.6rem;
        font-size: 0.78rem;
        border-radius: 6px;
    }
}
</style>