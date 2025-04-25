<script setup lang="ts">
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { router, usePage } from '@inertiajs/vue3';
import {
    BedDouble as BedIcon,
    BookOpen,
    Building as BuildingIcon,
    Calendar as CalendarIcon,
    Folder,
    LayoutDashboard as LayoutDashboardIcon,
    LayoutGrid,
    LogOut,
    Menu as MenuIcon,
    ShieldOff as ShieldOffIcon,
    Users as UsersIcon,
} from 'lucide-vue-next';
import { computed, onMounted, ref } from 'vue';

// Main navigation items
const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
        icon: LayoutGrid,
    },
];

// Get page instance
const page = usePage();

// Determine user roles with proper error checking
const isAdmin = computed(() => {
    return page.props.auth?.guard === 'admin' || page.props.auth?.user?.guard === 'admin' || page.props.isAdmin === true;
});

const isManager = computed(() => {
    return page.props.auth?.guard === 'manager' || page.props.auth?.user?.guard === 'manager';
});

const isReceptionist = computed(() => {
    return page.props.auth?.guard === 'receptionist' || page.props.auth?.user?.guard === 'receptionist';
});

const isClient = computed(() => {
    return page.props.auth?.guard === 'client' || page.props.auth?.user?.guard === 'client';
});

// Computed route properties based on user role
const receptionistRoute = computed(() => {
    return 'receptionists.index';
});

const clientRoute = computed(() => {
    return 'clients.index';
});

const reservationRoute = computed(() => {
    return 'staff.reservation.index';
});

// Access control for route visibility
const canManageReceptionists = computed(() => {
    return isAdmin.value || isManager.value;
});

const canManageReservations = computed(() => {
    return isAdmin.value || isManager.value || isReceptionist.value;
});

const canManageClients = computed(() => {
    return isAdmin.value || isManager.value || isReceptionist.value;
});

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

// Function to get the correct dashboard route based on user type
const getUserDashboardRoute = () => {
    if (isAdmin.value) return route('admin.dashboard');
    if (isManager.value) return route('manager.dashboard');
    if (isReceptionist.value) return route('receptionist.dashboard');
    if (isClient.value) return route('client.dashboard');

    // Fallback
    return route('home');
};

// Helper function to check if current route matches any pattern
const routeMatches = (patterns) => {
    if (typeof patterns === 'string') {
        return route().current(patterns);
    }

    if (Array.isArray(patterns)) {
        return patterns.some((pattern) => route().current(pattern));
    }

    return false;
};

// State for sidebar collapse on mobile
const isMobileMenuOpen = ref(false);
const toggleMobileMenu = () => {
    isMobileMenuOpen.value = !isMobileMenuOpen.value;
};

// Debugging - log when component mounts
onMounted(() => {
    console.log('Sidebar component mounted');
    console.log('Is Admin:', isAdmin.value);
    console.log('Is Manager:', isManager.value);
    console.log('Is Receptionist:', isReceptionist.value);
    console.log('Is Client:', props);
});
</script>

<template>
    <Sidebar
        :collapsible="collapsible"
        :defaultCollapsed="defaultCollapsed"
        :variant="variant"
        class="border-r bg-gradient-to-b from-blue-50 to-gray-100"
    >
        <!-- Sidebar Header with Logo -->
        <SidebarHeader class="border-b border-blue-200 p-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <!-- Gold accent for logo -->
                    <div class="rounded-lg bg-gradient-to-br from-yellow-400 to-yellow-600 p-2 shadow-md">
                        <BuildingIcon class="h-6 w-6 text-blue-900" />
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-blue-900">Laralive</h1>
                        <p class="text-xs font-medium text-yellow-600">Hotel</p>
                    </div>
                </div>

                <!-- Mobile menu toggle -->
                <button @click="toggleMobileMenu" class="text-blue-900 md:hidden">
                    <MenuIcon class="h-5 w-5" />
                </button>
            </div>
        </SidebarHeader>

        <!-- Main Navigation Menu -->
        <SidebarContent class="px-3 py-4">
            <SidebarMenu
                :class="{
                    'hidden md:block': !isMobileMenuOpen && props.collapsible === 'mobile',
                    block: isMobileMenuOpen || props.collapsible !== 'mobile',
                }"
            >
                <!-- Dashboard - always visible -->
                <SidebarMenuItem
                    :href="getUserDashboardRoute()"
                    :active="route().current('*.dashboard')"
                    class="mb-2 rounded-md transition-colors hover:bg-blue-100"
                    activeClass="bg-yellow-500 hover:bg-yellow-600 text-blue-900"
                >
                    <template #icon>
                        <LayoutDashboardIcon class="h-5 w-5 text-blue-800" />
                    </template>
                    <span class="font-medium">Dashboard</span>
                </SidebarMenuItem>

                <!-- Role indicator -->
                <div class="mt-1 mb-3 px-3 py-2 text-xs font-medium text-gray-500">
                    Role: {{ isAdmin ? 'Admin' : isManager ? 'Manager' : isReceptionist ? 'Receptionist' : isClient ? 'Client' : 'Guest' }}
                </div>

                <!-- Floors Management -->
                <SidebarMenuItem
                    v-if="isAdmin || isManager"
                    :href="isAdmin ? route('admin.floors.index') : route('manager.floors.index')"
                    :active="route().current('*.floors.index')"
                    class="mb-2 rounded-md transition-colors hover:bg-blue-100"
                    activeClass="bg-yellow-500 hover:bg-yellow-600 text-blue-900"
                >
                    <template #icon>
                        <BuildingIcon class="h-5 w-5 text-blue-800" />
                    </template>
                    <span class="font-medium">Manage Floors</span>
                </SidebarMenuItem>

                <!-- Managers Management - Admin only -->
                <SidebarMenuItem
                    v-if="isAdmin"
                    :href="route('managers.index')"
                    :active="route().current('managers.index')"
                    class="mb-2 rounded-md transition-colors hover:bg-blue-100"
                    activeClass="bg-yellow-500 hover:bg-yellow-600 text-blue-900"
                >
                    <template #icon>
                        <UsersIcon class="h-5 w-5 text-blue-800" />
                    </template>
                    <span class="font-medium">Manage Managers</span>
                </SidebarMenuItem>

                <!-- Rooms Management -->
                <SidebarMenuItem
                    v-if="isAdmin || isManager"
                    :href="isAdmin ? route('admin.rooms.index') : route('manager.rooms.index')"
                    :active="route().current('*.rooms.index')"
                    class="mb-2 rounded-md transition-colors hover:bg-blue-100"
                    activeClass="bg-yellow-500 hover:bg-yellow-600 text-blue-900"
                >
                    <template #icon>
                        <BedIcon class="h-5 w-5 text-blue-800" />
                    </template>
                    <span class="font-medium">Rooms</span>
                </SidebarMenuItem>

                <!-- Ban Management -->
                <SidebarMenuItem
                    v-if="isAdmin || isManager"
                    :href="route(isAdmin ? 'admin.bans.index' : 'manager.bans.index')"
                    :active="route().current('*.bans.index')"
                    class="mb-2 rounded-md transition-colors hover:bg-blue-100"
                    activeClass="bg-yellow-500 hover:bg-yellow-600 text-blue-900"
                >
                    <template #icon>
                        <ShieldOffIcon class="h-5 w-5 text-blue-800" />
                    </template>
                    <span class="font-medium">Manage Bans</span>
                </SidebarMenuItem>

                <!-- Manage Receptionists -->
                <SidebarMenuItem
                    v-if="canManageReceptionists"
                    :href="route(receptionistRoute)"
                    :active="routeMatches('*.receptionists.index')"
                    class="mb-2 rounded-md transition-colors hover:bg-blue-100"
                    activeClass="bg-yellow-500 hover:bg-yellow-600 text-blue-900"
                >
                    <template #icon>
                        <UsersIcon class="h-5 w-5 text-blue-800" />
                    </template>
                    <span class="font-medium">Manage Receptionists</span>
                </SidebarMenuItem>

                <!-- Manage Reservations -->
                <SidebarMenuItem
                    v-if="canManageReservations"
                    :href="route(reservationRoute)"
                    :active="routeMatches('staff.reservation.*')"
                    class="mb-2 rounded-md transition-colors hover:bg-blue-100"
                    activeClass="bg-yellow-500 hover:bg-yellow-600 text-blue-900"
                >
                    <template #icon>
                        <CalendarIcon class="h-5 w-5 text-blue-800" />
                    </template>
                    <span class="font-medium">Manage Reservations</span>
                </SidebarMenuItem>

                <!-- Manage Clients -->
                <SidebarMenuItem
                    v-if="canManageClients"
                    :href="route(clientRoute)"
                    :active="routeMatches(['admin.clients.index', 'manager.clients.index', 'clients.index'])"
                    class="mb-2 rounded-md transition-colors hover:bg-blue-100"
                    activeClass="bg-yellow-500 hover:bg-yellow-600 text-blue-900"
                >
                    <template #icon>
                        <UsersIcon class="h-5 w-5 text-blue-800" />
                    </template>
                    <span class="font-medium">Manage Clients</span>
                </SidebarMenuItem>

                <!-- Logout Button -->
                <div class="mt-6 border-t border-blue-200 pt-4">
                    <form @submit.prevent="router.post(route('auth.logout'))">
                        <button
                            type="submit"
                            class="flex w-full items-center space-x-2 rounded-md px-3 py-2 text-left text-red-600 transition-colors hover:bg-red-50"
                        >
                            <LogOut class="h-5 w-5" />
                            <span class="font-medium">Log Out</span>
                        </button>
                    </form>
                </div>
            </SidebarMenu>
        </SidebarContent>

        <!-- Footer with user info -->
        <SidebarFooter class="border-t border-blue-200 p-4">
            <div class="flex items-center space-x-3">
                <div
                    class="flex h-9 w-9 items-center justify-center rounded-full bg-gradient-to-br from-yellow-400 to-yellow-600 font-bold text-blue-900 shadow-sm"
                >
                    {{ page.props.auth?.user?.name?.charAt(0) || 'G' }}
                </div>
                <div>
                    <p class="text-sm font-medium text-blue-900">{{ page.props.auth?.user?.name || 'Guest' }}</p>
                    <p class="text-xs text-blue-700">
                        {{ isAdmin ? 'Administrator' : isManager ? 'Manager' : isReceptionist ? 'Receptionist' : isClient ? 'Client' : 'Guest' }}
                    </p>
                </div>
            </div>
        </SidebarFooter>
    </Sidebar>
</template>

<style scoped>
/* Enhanced styling for sidebar */
:deep(.sidebar-menu-item) {
    transition: all 0.2s ease;
}

:deep(.sidebar-menu-item:hover) {
    transform: translateX(2px);
}

:deep(.sidebar-menu-item.active) {
    font-weight: 600;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}
</style>
