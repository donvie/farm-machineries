import type { PageProps } from '@inertiajs/core';
import type { LucideIcon } from 'lucide-vue-next';

export interface Auth {
    user: User;
}

// export interface Machinery {
//     machine_name: number;
//     type: string;
//     status: string;
//     year_acquired?: string;
//     email_verified_at: string | null;
//     last_maintenance_date: string;
//     next_scheduled_maintenance: string;
//     image: string | null;
// }

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavItem {
    title: string;
    href: string;
    icon?: LucideIcon;
    isActive?: boolean;
}

export interface SharedData extends PageProps {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    // machinery: Machinery;
}

export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
}


export type BreadcrumbItemType = BreadcrumbItem;
