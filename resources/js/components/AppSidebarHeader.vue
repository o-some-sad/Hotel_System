<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { ShieldOff as ShieldOffIcon, BookOpen, Folder, LayoutGrid } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';
import { computed, ref, onMounted } from 'vue';


// Import icons first to avoid undefined errors
import { 
  LayoutDashboard as LayoutDashboardIcon, 
  Building as BuildingIcon,
  Users as UsersIcon,
  BedDouble as BedIcon,
  CalendarDays as CalendarIcon,
  Settings as SettingsIcon,
  FileText as FileTextIcon,
  Menu as MenuIcon,
  ShieldAlert as ShieldAlertIcon
} from 'lucide-vue-next';

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

// Get page instance
const page = usePage();

// Debug output
const debugAuth = computed(() => {
  return {
    auth: page.props.auth,
    guard: page.props.auth?.guard,
    userGuard: page.props.auth?.user?.guard
  };
});

// Determine user roles with proper error checking
const isAdmin = computed(() => {
  return page.props.auth?.guard === 'admin' || 
         page.props.auth?.user?.guard === 'admin' || 
         page.props.isAdmin === true;
});

const isManager = computed(() => {
  return page.props.auth?.guard === 'manager' || 
         page.props.auth?.user?.guard === 'manager';
});

const isReceptionist = computed(() => {
  return page.props.auth?.guard === 'receptionist' || 
         page.props.auth?.user?.guard === 'receptionist';
});

const isClient = computed(() => {
  return page.props.auth?.guard === 'client' || 
         page.props.auth?.user?.guard === 'client';
});

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

// State for sidebar collapse on mobile
const isMobileMenuOpen = ref(false);
const toggleMobileMenu = () => {
  isMobileMenuOpen.value = !isMobileMenuOpen.value;
};

// Debugging - log when component mounts
onMounted(() => {
  console.log('Sidebar component mounted');
  console.log('Auth info:', debugAuth.value);
  console.log('Is Admin:', isAdmin.value);
  console.log('Is Manager:', isManager.value);
  console.log('Is Receptionist:', isReceptionist.value);
});
</script>

<template>
  <Sidebar
    :collapsible="collapsible"
    :defaultCollapsed="defaultCollapsed"
    :variant="variant"
    class="border-r bg-gray-100"
  >
    <!-- Sidebar Header with Logo -->
    <SidebarHeader class="p-4 border-b">
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-2">
          <!-- Gold accent for logo -->
          <div class="bg-yellow-500 p-2 rounded-lg shadow">
            <BuildingIcon class="h-6 w-6 text-blue-900" />
          </div>
          <div>
            <h1 class="font-bold text-blue-900 text-xl">Nexus</h1>
            <p class="text-xs text-yellow-600 font-medium">Hospitality</p>
          </div>
        </div>
        
        <!-- Mobile menu toggle -->
        <button @click="toggleMobileMenu" class="md:hidden text-blue-900">
          <MenuIcon class="h-5 w-5" />
        </button>
      </div>
    </SidebarHeader>

    <!-- Main Navigation Menu -->
    <SidebarContent class="px-2 py-4">
      <SidebarMenu :class="{'hidden md:block': !isMobileMenuOpen && props.collapsible === 'mobile', 'block': isMobileMenuOpen || props.collapsible !== 'mobile'}">
        <!-- Dashboard - always visible -->
        <SidebarMenuItem
          :href="getUserDashboardRoute()"
          :active="route().current('*.dashboard')"
          class="mb-1 hover:bg-gray-200 rounded transition-colors"
          activeClass="bg-yellow-500 hover:bg-yellow-600"
        >
          <template #icon>
            <LayoutDashboardIcon class="h-5 w-5 text-blue-900" />
          </template>
          <span class="font-medium">Dashboard</span>
        </SidebarMenuItem>

        <!-- For debugging - always show this -->
        <div class="px-3 py-2 text-xs text-gray-500">
          Role: {{ isAdmin ? 'Admin' : isManager ? 'Manager' : isReceptionist ? 'Receptionist' : isClient ? 'Client' : 'Guest' }}
        </div>

        <!-- Floors Management -->
        <SidebarMenuItem
          :href="isAdmin ? route('admin.floors.index') : (isManager ? route('manager.floors.index') : '#')"
          :active="route().current('*.floors.index')"
          class="mb-1 hover:bg-gray-200 rounded transition-colors"
          activeClass="bg-yellow-500 hover:bg-yellow-600"
          v-if="isAdmin || isManager"
        >
          <template #icon>
            <BuildingIcon class="h-5 w-5 text-blue-900" />
          </template>
          <span class="font-medium">Manage Floors</span>
        </SidebarMenuItem>
        
        <SidebarMenuItem
          :href="route('managers.index')"
        >
          <template #icon>
            <BuildingIcon class="h-4 w-4" />
          </template>
          Manage Managers
        </SidebarMenuItem>

        <!-- Rooms Management -->
        <SidebarMenuItem
          :href="isAdmin ? route('admin.rooms.index') : (isManager ? route('manager.rooms.index') : '#')"
          :active="route().current('*.rooms.index')"
          class="mb-1 hover:bg-gray-200 rounded transition-colors"
          activeClass="bg-yellow-500 hover:bg-yellow-600"
          v-if="isAdmin || isManager"
        >
          <template #icon>
            <BedIcon class="h-5 w-5 text-blue-900" />
          </template>
          <span class="font-medium">Rooms</span>
        </SidebarMenuItem>

        <!-- <SidebarMenuItem
          v-if="isAdmin"
          :href="route('admin.bans.index')"
          :active="route().current('admin.bans.*')"
          class="mb-1 hover:bg-gray-200 rounded transition-colors"
          activeClass="bg-yellow-500 hover:bg-yellow-600"
        >
          <template #icon>
            <ShieldAlertIcon class="h-5 w-5 text-blue-900" />
          </template>
          <span class="font-medium">User Bans</span>
        </SidebarMenuItem> -->

        <!-- Settings -->

        <SidebarMenuItem
            v-if="isAdmin || isManager"
            :href="route(isAdmin ? 'admin.bans.index' : 'manager.bans.index')"
            :active="route().current('*.bans.index')"
            >
            <template #icon>
                <ShieldOffIcon class="h-4 w-4" />
            </template>
            Manage Bans
        </SidebarMenuItem>

      </SidebarMenu>
    </SidebarContent>

    <!-- Footer with user info -->
    <SidebarFooter class="border-t p-4">
      <div class="flex items-center space-x-3">
        <div class="w-8 h-8 rounded-full bg-yellow-500 flex items-center justify-center font-bold text-blue-900">
          {{ page.props.auth?.user?.name?.charAt(0) || 'N' }}
        </div>
        <div>
          <p class="text-sm font-medium text-blue-900">{{ page.props.auth?.user?.name || 'Guest' }}</p>
          <p class="text-xs text-blue-700">{{ 
            isAdmin ? 'Administrator' : 
            isManager ? 'Manager' : 
            isReceptionist ? 'Receptionist' : 
            isClient ? 'Client' : 'Guest' 
          }}</p>
        </div>
      </div>
    </SidebarFooter>
  </Sidebar>
</template>

<style scoped>
/* Smooth transitions */
:deep(.sidebar-menu-item) {
  transition: all 0.2s ease;
}
</style>