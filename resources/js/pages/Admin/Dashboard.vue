<script setup lang="ts">
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>

    <Head title="Admin Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Admin Dashboard
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <!-- Quick Stats -->
                            <div class="bg-white p-6 rounded-lg shadow-md">
                                <h3 class="text-lg font-semibold mb-2">Total Users</h3>
                                <p class="text-3xl font-bold text-blue-600">0</p>
                            </div>

                            <div class="bg-white p-6 rounded-lg shadow-md">
                                <h3 class="text-lg font-semibold mb-2">Active Rooms</h3>
                                <p class="text-3xl font-bold text-green-600">0</p>
                            </div>

                            <div class="bg-white p-6 rounded-lg shadow-md">
                                <h3 class="text-lg font-semibold mb-2">Total Bookings</h3>
                                <p class="text-3xl font-bold text-purple-600">0</p>
                            </div>
                        </div>

                        <!-- Recent Activity -->
                        <div class="mt-8">
                            <h3 class="text-lg font-semibold mb-4">Recent Activity</h3>
                            <div class="bg-white p-6 rounded-lg shadow-md">
                                <p class="text-gray-600">No recent activity to display</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>