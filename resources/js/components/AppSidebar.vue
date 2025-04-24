<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { BookOpen, Folder, LayoutGrid } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';
import { computed } from 'vue';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
        icon: LayoutGrid,
    },
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

// Determine if user is admin
const page = usePage();
const isAdmin = computed(() => {
    // Check user type based on your auth structure
    return page.props.auth?.guard === 'admin';
});

import {
    LayoutDashboard as LayoutDashboardIcon,
    Building as BuildingIcon,
    // Add other icons as needed
} from 'lucide-vue-next';

// Define props
const props = defineProps({
    collapsible: {
        type: String,
        default: 'icon',
    },
    defaultCollapsed: {
        type: Boolean,
        default: false,
    },
    variant: {
        type: String,
        default: 'inset',
    },
});

// Determine user type
const isManager = computed(() => page.props.auth?.guard === 'manager');
const isReceptionist = computed(() => page.props.auth?.guard === 'receptionist');
const isClient = computed(() => page.props.auth?.guard === 'client');

// Function to get the correct dashboard route based on user type
const getUserDashboardRoute = () => {
    if (isAdmin.value) return route('admin.dashboard');
    if (isManager.value) return route('manager.dashboard');
    if (isReceptionist.value) return route('receptionist.dashboard');
    if (isClient.value) return route('client.dashboard');

    // Fallback - you might want to customize this based on your application logic
    return route('home');
};
</script>

<template>
    <Sidebar :collapsible="collapsible" :defaultCollapsed="defaultCollapsed" :variant="variant" class="border-r">
        <SidebarHeader>
            <SidebarMenu>
                <!-- Dashboard - using correct guard-specific dashboard routes -->
                <SidebarMenuItem :href="getUserDashboardRoute()" :active="route().current('*.dashboard')">
                    <template #icon>
                        <LayoutDashboardIcon class="h-4 w-4" />
                    </template>
                    Dashboard
                </SidebarMenuItem>
                <!-- Floors Management - only for admin and manager users -->
                <SidebarMenuItem v-if="isAdmin || isManager"
                    :href="route(isAdmin ? 'admin.floors.index' : 'manager.floors.index')"
                    :active="route().current('*.floors.index')">
                    <template #icon>
                        <BuildingIcon class="h-4 w-4" />
                    </template>
                    Manage Floors
                </SidebarMenuItem>

                <!-- Add other menu items as needed -->

                <!-- User Management - only for admin users -->
                <SidebarMenuItem v-if="isAdmin" :href="route('admin.users.index')"
                    :active="route().current('*.users.index')">
                    <template #icon>
                        <BuildingIcon class="h-4 w-4" />
                    </template>
                    Manage Users
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>
    </Sidebar>
    <Sidebar :collapsible="collapsible" :defaultCollapsed="defaultCollapsed" :variant="variant" class="border-r">
        <SidebarHeader>
            <SidebarMenu>
                <!-- Dashboard - using correct guard-specific dashboard routes -->
                <SidebarMenuItem :href="getUserDashboardRoute()" :active="route().current('*.dashboard')">
                    <template #icon>
                        <LayoutDashboardIcon class="h-4 w-4" />
                    </template>
                    Dashboardaaaa
                </SidebarMenuItem>

                <SidebarMenuItem :href="route('managers.index')">
                    <template #icon>
                        <BuildingIcon class="h-4 w-4" />
                    </template>
                    Manage Managers
                </SidebarMenuItem>

                <!-- Floors Management - only for admin and manager users -->
                <SidebarMenuItem v-if="isAdmin || isManager"
                    :href="route(isAdmin ? 'admin.floors.index' : 'manager.floors.index')"
                    :active="route().current('*.floors.index')">
                    <template #icon>
                        <BuildingIcon class="h-4 w-4" />
                    </template>
                    Manage Floors
                </SidebarMenuItem>



                <!-- Add other menu items as needed -->

                <!-- User Management - only for admin users -->
                <!-- <SidebarMenuItem
          v-if="isAdmin"
          :href="route('admin.users.index')"
          :active="route().current('*.users.index')"
        >
            <template #icon>
                <BuildingIcon class="h-4 w-4" />
            </template>
            Manage Users
        </SidebarMenuItem> -->
            </SidebarMenu>
        </SidebarHeader>
    </Sidebar>
</template>
