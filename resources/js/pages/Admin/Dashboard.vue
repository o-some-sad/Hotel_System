<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'; // Changed to AppLayout
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    stats: Object,
});

// Add breadcrumbs
const breadcrumbs = [{ title: 'Dashboard', href: route('admin.dashboard') }];
</script>

<template>
    <Head title="Admin Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="container px-6 py-6">
            <!-- Stats Grid -->
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                <!-- Total Managers Card -->
                <div class="stats-card bg-indigo-50">
                    <h3 class="stats-card-title">Total Managers</h3>
                    <p class="stats-card-value text-indigo-600">{{ stats.totalManagers }}</p>
                </div>

                <!-- Total Reservations Card -->
                <div class="stats-card bg-teal-50">
                    <h3 class="stats-card-title">Total Reservations</h3>
                    <p class="stats-card-value text-teal-600">{{ stats.totalReservations }}</p>
                </div>

                <!-- Total Bans Card -->
                <div class="stats-card bg-orange-50">
                    <h3 class="stats-card-title">Total Bans</h3>
                    <p class="stats-card-value text-orange-600">{{ stats.totalBans }}</p>
                </div>

                <!-- Active Rooms Card -->
                <div class="stats-card bg-indigo-50">
                    <h3 class="stats-card-title">Active Rooms</h3>
                    <p class="stats-card-value text-indigo-600">{{ stats.activeRooms }}</p>
                </div>

                <!-- Total Receptionists Card -->
                <div class="stats-card bg-teal-50">
                    <h3 class="stats-card-title">Total Receptionists</h3>
                    <p class="stats-card-value text-teal-600">{{ stats.totalReceptionists }}</p>
                </div>

                <!-- Total Clients Card -->
                <div class="stats-card bg-orange-50">
                    <h3 class="stats-card-title">Total Clients</h3>
                    <p class="stats-card-value text-orange-600">{{ stats.totalClients }}</p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.container {
    max-width: 1200px;
}
.stats-card {
    padding: 1.5rem;
    border-radius: 0.5rem;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}
.stats-card-title {
    margin-bottom: 0.5rem;
    font-size: 1.25rem;
    font-weight: 600;
}
.stats-card-value {
    font-size: 2rem;
    font-weight: 700;
}
.section-title {
    margin-bottom: 1rem;
    font-size: 1.5rem;
    font-weight: 600;
}
</style>
