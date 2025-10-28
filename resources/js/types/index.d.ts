import { InertiaLinkProps } from '@inertiajs/vue3';
import type { LucideIcon } from 'lucide-vue-next';

export interface Auth {
    user: User;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavItem {
    title: string;
    href: NonNullable<InertiaLinkProps['href']>;
    icon?: LucideIcon;
    isActive?: boolean;
}

export type AppPageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    sidebarOpen: boolean;
};

export interface User {
    id: number;
    name: string;
    username: string;
    image: string;
    image_url?: string;
    email: string;
    status: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
    followers_count: number;
    followings_count: number;
    content_count: number;
    is_following: boolean;
    is_followed: boolean;
    plan?: Plan | null;
    plan_id?: number | null;
}

export interface Plan {
    id: number;
    name: string;
    description?: string;
    price: number;
    price_formatted: string;
    billing_period: string;
    free_days_trial: number;
    is_unlimited_content: boolean;
    movies_upload_count: number;
    series_upload_count: number;
    shorts_upload_count: number;
    stripe_product_id?: string;
    stripe_price_id?: string;
    status: string;
    created_at?: string;
    updated_at?: string;
}


export interface Category {
    id: number;
    name: string;
    image: string;
    image_url?: string;
    created_at: string;
    updated_at: string;
    subcategories: Subcategory[];
}

export interface Subcategory {
    id: number;
    name: string;
    category_id: number;
    created_at: string;
    updated_at: string;
    category: Category;
}


export type BreadcrumbItemType = BreadcrumbItem;

// Export the Plan interface for use in components
export type { Plan };
