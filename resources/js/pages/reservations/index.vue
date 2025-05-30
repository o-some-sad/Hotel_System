<script setup>

import { 
    Table,
    TableBody,
    TableCaption,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table'
import { Button } from '@/components/ui/button'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { onMounted, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';

// Add breadcrumbs
const breadcrumbs = [
    { title: 'Reservations', href: route('staff.reservation.index') },
];


const columns = [
    { id: "id", label: "ID" },
    { id: "client", label: "Client" },
    { id: "room", label: "Room" },
    { id: "checkin", label: "Check In" },
    { id: "checkout", label: "Check Out" },
    { id: "accompanying", label: "Guests" },
    { id: "price", label: "Price" },
    { id: "status", label: "Status" },
    { id: "actions", label: "Actions" }
]

const createReservation = () => {
    router.visit('/staff/reservations/create')
}

const editReservation = (reservationId) => {
    router.visit(`/staff/reservations/${reservationId}/edit`)
}

const deleteReservation = (reservationId) => {
    if (confirm('Are you sure you want to delete this reservation? This action cannot be undone.')) {
        router.visit(`/staff/reservations/${reservationId}/delete`)
    }
}

const toggleApproval = (reservation) => {
    router.patch(`/staff/reservations/${reservation.id}/approve`, {
        is_approved: !reservation.is_approved
    })
}

const formatPrice = (price) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
        minimumFractionDigits: 0
    }).format(price)
}

// In your Vue component script
const props = defineProps({
    reservations: Object,
    currentUser: Object
});

const canEditReservation = (reservation) => {
    return props.currentUser.isAdmin ||
        (reservation.created_by_id === props.currentUser.id &&
            reservation.created_by_type === props.currentUser.type);
};

const canDeleteReservation = (reservation) => {
    return props.currentUser.isAdmin ||
        (reservation.created_by_id === props.currentUser.id &&
            reservation.created_by_type === props.currentUser.type);
};

const handlePageChange = (url) => {
    router.visit(url)
}

// Add these for flash message handling
const page = usePage();

// Auto-dismiss flash messages after 5 seconds
watch(() => page.props.flash, (flash) => {
    if (flash?.success) {
        setTimeout(() => {
            page.props.flash.success = null;
        }, 5000);
    }

    if (flash?.error) {
        setTimeout(() => {
            page.props.flash.error = null;
        }, 5000);
    }
}, { deep: true, immediate: true });
</script>

<template>
    <AppLayout :title="'Manage Reservations'" :breadcrumbs="breadcrumbs">
    <div class="container mx-auto py-6 px-4">
        <!-- Flash Messages -->
        <div v-if="$page.props.flash?.success"
            class="mb-6 p-4 bg-green-100 border border-green-200 text-green-800 rounded-md flex items-center justify-between">
            <div class="flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                        clip-rule="evenodd" />
                </svg>
                <span>{{ $page.props.flash.success }}</span>
            </div>
            <button @click="$page.props.flash.success = null" class="text-green-700 hover:text-green-900">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd"
                        d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                        clip-rule="evenodd" />
                </svg>
            </button>
        </div>

        <div v-if="$page.props.flash?.error"
            class="mb-6 p-4 bg-red-100 border border-red-200 text-red-800 rounded-md flex items-center justify-between">
            <div class="flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd"
                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                        clip-rule="evenodd" />
                </svg>
                <span>{{ $page.props.flash.error }}</span>
            </div>
            <button @click="$page.props.flash.error = null" class="text-red-700 hover:text-red-900">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd"
                        d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                        clip-rule="evenodd" />
                </svg>
            </button>
        </div>

        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Reservation Management</h1>
            <button @click="createReservation" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md">
                Create Reservation
            </button>
        </div>

        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <Table>
                <TableCaption>A list of all reservations in the system.</TableCaption>
                <TableHeader>
                    <TableRow class="bg-gray-50">
                        <TableHead v-for="column in columns" :key="column.id" class="font-medium">
                            {{ column.label }}
                        </TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-for="reservation in reservations.data" :key="reservation.id" class="hover:bg-gray-50">
                        <TableCell class="font-medium">#{{ reservation.id }}</TableCell>
                        <TableCell>
                            <div class="flex items-center gap-2">
                                <img :src="reservation.client.image ? `/storage/${reservation.client.image}` : '/images/default.jpg'"
                                    alt="Client" class="h-8 w-8 rounded-full object-cover border border-gray-200" />
                                {{ reservation.client.name }}
                            </div>
                        </TableCell>
                        <TableCell>
                            <div class="flex flex-col">
                                <span>{{ reservation.room.name }}</span>
                                <span class="text-xs text-gray-500">Room #{{ reservation.room.number }}</span>
                            </div>
                        </TableCell>
                        <TableCell>{{ new Date(reservation.check_in).toLocaleDateString() }}</TableCell>
                        <TableCell>{{ new Date(reservation.check_out).toLocaleDateString() }}</TableCell>
                        <TableCell>
                            <div class="flex items-center gap-1">
                                <span>{{ reservation.accompanying_number }}</span>
                                <span class="text-xs text-gray-500">people</span>
                            </div>
                        </TableCell>
                        <TableCell class="font-semibold">{{ formatPrice(reservation.price) }}</TableCell>
                        <TableCell>
                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold"
                                :class="reservation.payment_id
                                    ? 'bg-green-100 text-green-800'
                                    : 'bg-red-100 text-red-800'">
                                {{ reservation.payment_id ? 'Paid' : 'Unpaid' }}
                            </span>
                        </TableCell>
                        <TableCell>
                            <!-- Replace Badge with custom styled span -->
                            <span @click="toggleApproval(reservation)"
                                class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold cursor-pointer"
                                :class="reservation.is_approved
                                    ? 'bg-green-100 text-green-800 hover:bg-green-200'
                                    : 'bg-yellow-100 text-yellow-800 hover:bg-yellow-200'">
                                {{ reservation.is_approved ? 'Approved' : 'Pending' }}
                            </span>
                        </TableCell>
                        <TableCell>
                            <div class="flex space-x-2">
                                <!-- Show accept/refuse buttons only for pending reservations -->
                                <template v-if="!reservation.is_approved && reservation.payment_id">
                                    <button @click="toggleApproval(reservation)"
                                        class="p-1 border border-green-300 bg-green-50 text-green-700 rounded-md hover:bg-green-100"
                                        title="Approve reservation">
                                        <span class="sr-only">Accept</span>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M20 6 9 17l-5-5" />
                                        </svg>
                                    </button>
                                </template>

                                <!-- Edit and delete buttons visible for all reservations -->
                                <template v-if="canEditReservation(reservation)">
                                    <button @click="editReservation(reservation.id)"
                                        class="p-1 border border-gray-300 rounded-md hover:bg-gray-100">
                                        <span class="sr-only">Edit</span>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z" />
                                            <path d="m15 5 4 4" />
                                        </svg>
                                    </button>
                                </template>

                                <template v-if="canDeleteReservation(reservation)">
                                    <button @click="deleteReservation(reservation.id)"
                                        class="p-1 border border-red-300 bg-red-50 text-red-700 rounded-md hover:bg-red-100">
                                        <span class="sr-only">Delete</span>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M3 6h18" />
                                            <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6" />
                                            <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2" />
                                            <line x1="10" y1="11" x2="10" y2="17" />
                                            <line x1="14" y1="11" x2="14" y2="17" />
                                        </svg>
                                    </button>
                                </template>
                            </div>
                        </TableCell>
                    </TableRow>
                    <TableRow v-if="!reservations.data || reservations.data.length === 0">
                        <TableCell colspan="9" class="text-center py-8 text-gray-500">
                            <div class="flex flex-col items-center justify-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" class="text-gray-400">
                                    <rect width="18" height="18" x="3" y="4" rx="2" ry="2" />
                                    <line x1="16" x2="16" y1="2" y2="6" />
                                    <line x1="8" x2="8" y1="2" y2="6" />
                                    <line x1="3" x2="21" y1="10" y2="10" />
                                    <path d="m8 14 3 3 5-5" />
                                </svg>
                                <span>No reservations found</span>
                                <button @click="createReservation"
                                    class="mt-2 px-3 py-1 border border-gray-300 text-sm rounded-md hover:bg-gray-50">
                                    Create a reservation
                                </button>
                            </div>
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>

        <!-- Pagination -->
        <div class="mt-6 flex justify-center gap-2" v-if="reservations.links && reservations.links.length > 3">
            <!-- Previous link -->
            <button @click="reservations.links[0].url && handlePageChange(reservations.links[0].url)"
                class="pagination-btn prev-btn" :disabled="!reservations.links[0].url">
                Previous
            </button>

            <!-- Page links -->
            <template v-for="(link, i) in reservations.links" :key="i">
                <button v-if="i > 0 && i < reservations.links.length - 1"
                    @click="link.url && handlePageChange(link.url)" class="pagination-btn page-num"
                    :class="{ 'active': link.active }" v-html="link.label" :disabled="!link.url">
                </button>
            </template>

            <!-- Next link -->
            <button
                @click="reservations.links[reservations.links.length - 1].url && handlePageChange(reservations.links[reservations.links.length - 1].url)"
                class="pagination-btn next-btn" :disabled="!reservations.links[reservations.links.length - 1].url">
                Next
            </button>
        </div>

        <!-- Page information -->
        <div class="text-sm text-gray-500 mt-2 text-center" v-if="reservations.meta">
            Showing {{ reservations.meta.from }} to {{ reservations.meta.to }} of {{ reservations.meta.total }}
            reservations
        </div>
    </div>
    </AppLayout>
</template>

<style scoped>
/* Base styles */
.container {
    max-width: 1200px;
}

/* Pagination styles */
.pagination-btn {
    padding: 0.5rem 1rem;
    background-color: white;
    border: 1px solid #e2e8f0;
    border-radius: 0.375rem;
    font-size: 0.875rem;
    cursor: pointer;
    transition: all 0.2s;
}

.pagination-btn:hover:not(:disabled) {
    background-color: #f7fafc;
}

.pagination-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.pagination-btn.active {
    background-color: #3b82f6;
    color: white;
    border-color: #3b82f6;
}

.prev-btn,
.next-btn {
    font-weight: 500;
}
</style>
