<script setup lang="ts">
import { User } from '@/types';
import { ref, onMounted, watch, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { datatable as usersDatatable } from '@/routes/admin/users'
import ShowMovieChannelController from '@/actions/App/Http/Controllers/Channel/ShowMovieChannelController';

import DeleteUserModal from './DeleteUserModal.vue';
import EditUserModal from './EditUserModal.vue';

const page = usePage();
const plans = computed(() => page.props.plans || []);
const statusOptions = computed(() => page.props.statusOptions || {});

const query = ref({
    search: '',
    page: 1,
    perPage: 10,
});

const users = ref<User[]>([]);
const pagination = ref({
    total: 0,
    perPage: 10,
    currentPage: 1,
    lastPage: 1,
    from: 1,
    to: 1,
});
const loading = ref(false);
const selectedUser = ref<User | null>(null);

const fetchUsers = async () => {
    try {
        
        loading.value = true;
        const userUrl = usersDatatable({
            query: query.value,
        });
        
        const response = await fetch(userUrl.url, {
            method: userUrl.method,
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
        });
        
        const data = await response.json();

        if (data.data) {
            users.value = data.data;
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
        console.error('Error fetching users:', error);
    } finally {
        loading.value = false;
    }
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

const editUser = (user: User) => {
    selectedUser.value = user;
};

const closeEditModal = () => {
    selectedUser.value = null;
    fetchUsers(); // Refresh data after edit
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

// Watch for query changes and fetch users
watch(query, () => {
    fetchUsers();
}, { deep: true });

onMounted(() => {
    fetchUsers();
});

</script>

<template>
    <div class="table-controls">
        <div class="search-bar">
            <input 
                type="search" 
                id="userSearchInput"
                :value="query.search"
                @input="handleSearch"
                placeholder="Search by Username, Name, Email, Plan..."
            >
        </div>
    </div>
    
    <div class="admin-table-wrapper">
        <div v-if="loading" class="loading-overlay">
            <div class="spinner"></div>
            <span>Loading users...</span>
        </div>
        
        <div class="table-responsive" style="width: 100%; overflow-x: auto;">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Next Payment Date</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="usersTableBody">
                    <tr v-if="users.length === 0 && !loading">
                        <td colspan="6" class="no-data">
                            {{ query.search ? 'No users found matching your search.' : 'No users available.' }}
                        </td>
                    </tr>
                    <tr v-for="user in users" :key="user.id" :class="{ 'loading-row': loading }">
                        <td>
                            <Link :href="ShowMovieChannelController.url({username: user.username})">{{ user.username }}</Link>
                        </td>
                        <td>{{ user.name }}</td>
                        <td>{{ user.email }}</td>
                        <td>
                            <span :class="['status-badge', `status-${user.status?.toLowerCase()}`]">
                                {{ user.status }}
                            </span>
                        </td>
                        <td>{{ user.next_payment_date }}</td>
                        <td>{{ new Date(user.created_at).toLocaleDateString() }}</td>
                        <td>
                            <div class="action-buttons">
                                <button 
                                    class="btn btn-edit" 
                                    :data-id="user.id" 
                                    :disabled="loading"
                                    @click="editUser(user)"
                                >
                                    Edit
                                </button>
                                <DeleteUserModal :user="user" />
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <div v-if="pagination.total > 0" class="d-flex justify-content-between align-items-center mt-4">
        <div class="text-muted">
            Showing {{ pagination.from }} to {{ pagination.to }} of {{ pagination.total }} users
        </div>
        
        <nav aria-label="User pagination">
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

    <!-- Edit User Modal -->
    <EditUserModal 
        :user="selectedUser" 
        :plans="plans"
        :statusOptions="statusOptions"
        @close="closeEditModal"
    />
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

.action-buttons .btn-edit {
    background-color: var(--secondary-color);
}

.action-buttons .btn-edit:hover {
    background-color: var(--secondary-color-hover);
}

.action-buttons .btn-delete {
    background-color: var(--error-color);
}

.action-buttons .btn-delete:hover {
    background-color: var(--error-color-hover);
}
</style>