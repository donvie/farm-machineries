<script setup lang="ts">
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem, type User, type SharedData,   } from '@/types';
// import { Link } from '@inertiajs/vue3';
import { BookOpen, Construction, Folder, LayoutGrid, ShoppingBag, Tractor, Users, Wallet, List } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';

import { onMounted, ref, computed} from 'vue';
import { Head, usePage, Link } from '@inertiajs/vue3';

const page = usePage<SharedData>();
const user = page.props.auth.user as User;
const role = computed(() => page.props.auth.user.role);

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
        icon: LayoutGrid,
    },
    {
        title: 'Machinery',
        href: '/machinery',
        icon: Tractor,
    },
    // {
    //     title: 'Maintainance',
    //     href: '/maintainance',
    //     icon: Construction,
    // },
    {
        title: 'Rental',
        href: '/rental',
        icon: ShoppingBag,
    },
    {
        title: 'Loan',
        href: '/loan',
        icon: Wallet,
    },
    // {
    //     title: 'Technician',
    //     href: '/technician',
    //     icon: List,
    // },
    {
        title: 'Inventory Supply',
        href: '/supply',
        icon: Users,
    },
    {
        title: 'Member',
        href: '/user',
        icon: Users,
    },
];
const mainNavItems1: NavItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
        icon: LayoutGrid,
    },
    {
        title: 'Machinery',
        href: '/machinery',
        icon: Tractor,
    },
    {
        title: 'Maintenance ',
        href: '/maintainance',
        icon: Construction,
    },
    {
        title: 'Rental',
        href: '/rental',
        icon: ShoppingBag,
    },
    {
        title: 'Loan',
        href: '/loan',
        icon: Wallet,
    },
    // {
    //     title: 'Technician',
    //     href: '/technician',
    //     icon: List,
    // },
    {
        title: 'Inventory Supply',
        href: '/supply',
        icon: Users,
    },
    {
        title: 'Member',
        href: '/user',
        icon: Users,
    },
];
const mainNavItems2: NavItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
        icon: LayoutGrid,
    },
    {
        title: 'Machinery',
        href: '/machinery',
        icon: Tractor,
    },
    {
        title: 'Maintenance ',
        href: '/maintainance',
        icon: Construction,
    },
    {
        title: 'Routine checking',
        href: '/technician',
        icon: List,
    },
    // {
    //     title: 'Rental',
    //     href: '/rental',
    //     icon: ShoppingBag,
    // },
    // {
    //     title: 'Loan',
    //     href: '/loan',
    //     icon: Wallet,
    // },
    // {
    //     title: 'Inventory Supply',
    //     href: '/supply',
    //     icon: Users,
    // },
    // {
    //     title: 'Member',
    //     href: '/user',
    //     icon: Users,
    // },
];

const footerNavItems: NavItem[] = [
    {
        title: 'Github Repo',
        href: 'https://github.com/laravel/vue-starter-kit',
        icon: Folder,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits',
        icon: BookOpen,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="route('dashboard')">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain v-if="role === 'admin'" :items="mainNavItems" />
            <NavMain v-if="role === 'management'" :items="mainNavItems1" />
            <NavMain v-if="role === 'technician'" :items="mainNavItems2" />
        </SidebarContent>

        <SidebarFooter>
            <!-- <NavFooter :items="footerNavItems" /> -->
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
