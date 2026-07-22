<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import UserDashboardLayout from '@/layouts/UserDashboardLayout.vue';
import { dashboard as dashboardRoute } from '@/routes';

const props = defineProps<{
    purchases: Array<{
        id: number;
        amount: string;
        created_at: string;
        content: {
            id: number;
            type: string;
            contentable: {
                id: number;
                title?: string;
                text_caption?: string;
                description?: string;
                vertical_image_url?: string;
                horizontal_image_url?: string;
                user?: {
                    name: string;
                    username: string;
                    image_url?: string;
                }
            }
        }
    }>;
}>();

// Helper to get direct player URL
const getPlayerUrl = (type: string, contentableId: number) => {
    if (type === 'movies') {
        return `/movie/${contentableId}/player`;
    } else if (type === 'series') {
        return `/serie/${contentableId}/player`;
    }
    return `/dashboard`;
};

// Helper to format date
const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleDateString(undefined, {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};
</script>

<template>
    <UserDashboardLayout 
        :title="`My Purchases - ${$page.props.name || 'Impulsemedia'}`" 
        :headerTitle="`My Purchases - ${$page.props.name || 'Impulsemedia'}`">
        
        <div class="purchases-container">
            <div class="purchases-header">
                <h1 class="page-title">My Purchases</h1>
                <p class="purchases-count" v-if="purchases.length > 0">
                    You have unlocked {{ purchases.length }} premium content {{ purchases.length === 1 ? 'item' : 'items' }}. You can rewatch your purchases as many times as you like.
                </p>
            </div>

            <!-- Empty State -->
            <div v-if="purchases.length === 0" class="empty-state">
                <div class="empty-icon-wrapper">
                    <i class="fas fa-ticket-alt empty-icon"></i>
                </div>
                <h2>No purchases yet</h2>
                <p>Explore our catalog, buy individual content as Pay-Per-View, and access it here anytime.</p>
                <Link :href="dashboardRoute()" class="explore-btn">
                    Explore Catalog
                </Link>
            </div>

            <!-- Purchases Grid -->
            <div v-else class="purchases-grid">
                <div v-for="purchase in purchases" :key="purchase.id" class="purchase-card">
                    <div class="card-image-wrapper">
                        <img 
                            :src="purchase.content.contentable.vertical_image_url || purchase.content.contentable.horizontal_image_url || '/images/default_poster.webp'" 
                            :alt="purchase.content.contentable.title || 'Poster'" 
                            class="card-image"
                        />
                        <div class="card-overlay">
                            <Link 
                                :href="getPlayerUrl(purchase.content.type, purchase.content.contentable.id)"
                                class="play-btn-overlay"
                                title="Watch Now"
                            >
                                <i class="fas fa-play"></i>
                            </Link>
                        </div>
                    </div>
                    <div class="card-details">
                        <h3 class="content-title">
                            <Link :href="getPlayerUrl(purchase.content.type, purchase.content.contentable.id)" class="title-link">
                                {{ purchase.content.contentable.title || purchase.content.contentable.text_caption || 'Untitled' }}
                            </Link>
                        </h3>
                        <p class="creator-name" v-if="purchase.content.contentable.user">
                            by @{{ purchase.content.contentable.user.username }}
                        </p>
                        <div class="purchase-meta">
                            <span class="price-paid">${{ purchase.amount }}</span>
                            <span class="dot">•</span>
                            <span class="purchase-date">{{ formatDate(purchase.created_at) }}</span>
                        </div>
                        <Link 
                            :href="getPlayerUrl(purchase.content.type, purchase.content.contentable.id)"
                            class="watch-now-btn"
                        >
                            <i class="fas fa-play" style="font-size: 0.75rem;"></i> Watch Now
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </UserDashboardLayout>
</template>

<style scoped>
.purchases-container {
    padding: 1rem 1rem 80px 1rem;
}

.page-title {
    font-size: 2rem;
    font-weight: 600;
    margin: 1rem 0 0.25rem 0;
}

.purchases-count {
    color: #aaa;
    font-size: 0.95rem;
    margin-bottom: 2rem;
}

/* Empty State */
.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    padding: 4rem 2rem;
    background: rgba(255, 255, 255, 0.03);
    border-radius: 16px;
    border: 1px dashed rgba(255, 255, 255, 0.1);
    margin-top: 1rem;
}

.empty-icon-wrapper {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, rgba(108, 58, 237, 0.1) 0%, rgba(236, 72, 153, 0.1) 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 1.5rem;
    border: 1px solid rgba(108, 58, 237, 0.2);
}

.empty-icon {
    font-size: 2rem;
    background: linear-gradient(135deg, #6c3aed 0%, #ec4899 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.empty-state h2 {
    font-size: 1.5rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.empty-state p {
    color: #aaa;
    max-width: 400px;
    margin-bottom: 1.5rem;
    font-size: 0.95rem;
}

.explore-btn {
    display: inline-block;
    padding: 0.75rem 1.5rem;
    background: linear-gradient(135deg, #6c3aed 0%, #ec4899 100%);
    color: white;
    text-decoration: none;
    font-weight: 600;
    border-radius: 30px;
    transition: transform 0.2s, box-shadow 0.2s;
    box-shadow: 0 4px 15px rgba(108, 58, 237, 0.3);
}

.explore-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(108, 58, 237, 0.4);
}

/* Purchases Grid */
.purchases-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
    gap: 1.5rem;
}

.purchase-card {
    background: rgba(255, 255, 255, 0.02);
    border-radius: 14px;
    border: 1px solid rgba(255, 255, 255, 0.06);
    overflow: hidden;
    display: flex;
    flex-direction: column;
    transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.3s;
}

.purchase-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.35);
    border-color: rgba(236, 72, 153, 0.3);
}

.card-image-wrapper {
    position: relative;
    aspect-ratio: 2/3;
    overflow: hidden;
}

.card-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s;
}

.purchase-card:hover .card-image {
    transform: scale(1.05);
}

.card-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.2s;
}

.purchase-card:hover .card-overlay {
    opacity: 1;
}

.play-btn-overlay {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: linear-gradient(135deg, #e8445a 0%, #d83b50 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    box-shadow: 0 4px 15px rgba(232, 68, 90, 0.4);
    transition: transform 0.2s;
    text-decoration: none;
}

.play-btn-overlay:hover {
    transform: scale(1.1);
}

.card-details {
    padding: 0.85rem;
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
    flex-grow: 1;
}

.content-title {
    font-size: 0.95rem;
    font-weight: 600;
    margin: 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.title-link {
    color: #fff;
    text-decoration: none;
    transition: color 0.2s;
}

.title-link:hover {
    color: #e8445a;
}

.creator-name {
    font-size: 0.8rem;
    color: rgba(236, 72, 153, 0.85);
    margin: 0;
    font-weight: 500;
}

.purchase-meta {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 0.75rem;
    color: #aaa;
}

.price-paid {
    color: #4ade80;
    font-weight: 600;
}

.dot {
    color: #555;
}

.watch-now-btn {
    margin-top: 0.5rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.4rem;
    background: linear-gradient(135deg, #e8445a 0%, #d83b50 100%);
    color: #fff;
    text-decoration: none;
    font-weight: 700;
    font-size: 0.8rem;
    padding: 0.45rem 0.75rem;
    border-radius: 8px;
    transition: all 0.2s ease;
    box-shadow: 0 2px 8px rgba(232, 68, 90, 0.3);
}

.watch-now-btn:hover {
    background: linear-gradient(135deg, #f43f5e 0%, #e8445a 100%);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(232, 68, 90, 0.5);
}

/* Desktop Media Queries */
@media (min-width: 768px) {
    .purchases-grid {
        grid-template-columns: repeat(auto-fill, minmax(190px, 1fr));
    }
}

@media (min-width: 1200px) {
    .purchases-container {
        max-width: 1600px;
        margin: 0 auto;
        padding: 0 4rem 80px 4rem;
    }

    .page-title {
        font-size: 2.5rem;
        padding: 1rem 0 0.25rem 0;
    }
}
</style>
