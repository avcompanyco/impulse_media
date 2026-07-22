<script setup lang="ts">
import { onMounted, ref, nextTick } from 'vue';
import DashboardCard from './partials/DashboardCard.vue';
import AdminDashboardLayout from '@/layouts/AdminDashboardLayout.vue';
import { router } from '@inertiajs/vue3';

import RevenueController from '@/actions/App/Http/Controllers/Payment/RevenueController';
import ShowBinacleDatatable from './partials/ShowBinacleDatatable.vue';

interface RevenueData {
    month: string;
    revenue: number;
}

const revenueData = ref<RevenueData[]>([]);

const props = defineProps<{
    cards: any[];
}>()

function createChart() {
    nextTick(() => {
        const revenueChart = document.getElementById('revenueChart');
        if (revenueChart && revenueData.value.length > 0) {
            const maxRevenue = Math.max(...revenueData.value.map(d => d.revenue));

            revenueData.value.forEach(data => {
                const barHeight = (data.revenue / maxRevenue) * 100;
                const bar = document.createElement('div');
                bar.className = 'chart-bar';
                bar.style.height = `${barHeight}%`;
                bar.innerHTML = `<div class="bar-tooltip">$${data.revenue.toLocaleString()}<br>${data.month}</div>`;
                revenueChart.appendChild(bar);
            });
        }
    });
}

function getRevenueData() {
    // Comentado hasta que se resuelvan los tipos de RevenueController
    fetch(RevenueController.url())
        .then(response => response.json())
        .then(data => {
            revenueData.value = data;
            createChart();
        })
        .catch(error => {
            createChart();
        });
}

onMounted(() => {
    getRevenueData();
});
</script>

<template>
    <AdminDashboardLayout 
        :title="`Dashboard - ${$page.props.name || 'Impulsemedia'}`" 
        :headerTitle="`Dashboard Summary - ${$page.props.name || 'Impulsemedia'}`">

        <div class="stats-grid">
            <DashboardCard v-for="card in cards" :key="card.title" :title="card.title" :value="card.value" />
        </div>

        <div class="dashboard-section">
            <h2 class="section-title">Monthly Revenue</h2>
            <div class="chart-container" id="revenueChart">
            </div>
        </div>

        <ShowBinacleDatatable />
       
    </AdminDashboardLayout>
</template>

<style>

.admin-top-bar {
    display: flex;
    justify-content: flex-end;
    align-items: center;
    padding: 0 1.5rem;
    background-color: var(--sidebar-bg);
    border-bottom: 1px solid var(--border-color);
    min-height: 60px;
    box-shadow: var(--shadow-sm);
    position: sticky;
    top: 0;
    z-index: 999;
}

.user-profile-dropdown {
    position: relative;
}

.user-profile-dropdown .profile-trigger {
    background: none;
    border: none;
    color: var(--text-light);
    cursor: pointer;
    padding: 0.5rem;
    display: flex;
    align-items: center;
    border-radius: 6px;
}

.user-profile-dropdown .profile-trigger:hover {
    background-color: rgba(255, 255, 255, 0.05);
}

.user-profile-dropdown .profile-trigger .user-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background-color: var(--primary-color);
    margin-right: 0.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    color: white;
}

.user-profile-dropdown .dropdown-menu {
    display: none;
    position: absolute;
    top: calc(100% + 8px);
    right: 0;
    background-color: var(--sidebar-bg);
    border: 1px solid var(--border-color);
    border-radius: 6px;
    box-shadow: var(--shadow-md);
    min-width: 180px;
    z-index: 1001;
    list-style: none;
    padding: 0.5rem 0;
}

.user-profile-dropdown .dropdown-menu.active {
    display: block;
}

.user-profile-dropdown .dropdown-menu li a {
    display: block;
    padding: 0.6rem 1rem;
    color: var(--text-muted);
    text-decoration: none;
    font-size: 0.9rem;
}

.user-profile-dropdown .dropdown-menu li a:hover {
    background-color: var(--table-row-hover-bg);
    color: var(--primary-color);
}

.user-profile-dropdown .dropdown-menu .divider {
    height: 1px;
    background-color: var(--border-color);
    margin: 0.5rem 0;
}

.admin-main-content {
    flex-grow: 1;
    padding: 2rem;
    overflow-y: auto;
}

.content-header {
    margin-bottom: 2rem;
}

.page-module-title {
    font-size: 2rem;
    font-weight: 600;
    color: var(--text-headings);
    margin: 0;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2.5rem;
}

@media (max-width: 768px) {
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 0.75rem;
        margin-bottom: 1.5rem;
    }

    .stat-card {
        padding: 1rem;
        border-radius: 10px;
    }

    .stat-card h3 {
        font-size: 0.72rem;
        letter-spacing: 0;
    }

    .stat-card .stat-value {
        font-size: 1.4rem;
    }

    .dashboard-section {
        padding: 1rem;
        margin-bottom: 1.5rem;
    }
}

.stat-card {
    background-color: var(--section-bg);
    padding: 1.5rem;
    border-radius: var(--border-radius-md);
    border-left: 4px solid var(--primary-color);
    box-shadow: var(--shadow-sm);
}

.stat-card h3 {
    font-size: 0.9rem;
    color: var(--text-muted);
    margin: 0 0 0.4rem 0;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.stat-card .stat-value {
    font-size: 2rem;
    font-weight: 700;
    color: var(--text-headings);
    margin: 0;
}

.dashboard-section {
    background-color: var(--section-bg);
    padding: 1.5rem;
    border-radius: var(--border-radius-md);
    margin-bottom: 2.5rem;
}

.section-title {
    font-size: 1.5rem;
    font-weight: 600;
    color: var(--text-headings);
    margin-bottom: 1.5rem;
}

/* Chart Styles */
.chart-container {
    width: 100%;
    height: 300px;
    background-color: rgba(0, 0, 0, 0.2);
    border-radius: 8px;
    padding: 1rem;
    display: flex;
    align-items: flex-end;
    justify-content: space-around;
    gap: 1.5%;
}

.chart-bar {
    flex-grow: 1;
    background: linear-gradient(to top, var(--primary-color), var(--primary-color-hover));
    border-radius: 4px 4px 0 0;
    position: relative;
    text-align: center;
}

.chart-bar:hover .bar-tooltip {
    opacity: 1;
}

.bar-tooltip {
    position: absolute;
    top: -35px;
    left: 50%;
    transform: translateX(-50%);
    background-color: #333;
    color: white;
    padding: 5px 10px;
    border-radius: 4px;
    font-size: 0.8rem;
    white-space: nowrap;
    opacity: 0;
    transition: opacity 0.2s;
}

/* Recent Activity Table */
.admin-table-wrapper {
    overflow-x: auto;
}

.admin-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    color: var(--text-light);
    font-size: 0.9rem;
}

.admin-table th,
.admin-table td {
    padding: 0.8rem 1rem;
    text-align: left;
    vertical-align: middle;
    border-bottom: 1px solid var(--border-color);
}

.admin-table th {
    font-weight: 600;
    white-space: nowrap;
    color: var(--text-muted);
}

.admin-table tr:last-child td {
    border-bottom: none;
}
</style>