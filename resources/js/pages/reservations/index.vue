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
// Remove Badge import and replace with direct styling
import { Button } from '@/components/ui/button'
import { router } from '@inertiajs/vue3'

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
    router.visit('/stuff/reservations/create')
}

const editReservation = (reservationId) => {
    router.visit(`/stuff/reservations/${reservationId}/edit`)
}

const deleteReservation = (reservationId) => {
    if (confirm('Are you sure you want to delete this reservation? This action cannot be undone.')) {
        router.visit(`/stuff/reservations/${reservationId}/delete`)
    }
}

const toggleApproval = (reservation) => {
    router.patch(`/stuff/reservations/${reservation.id}/approve`, {
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
</script>

<template>
    <div class="container mx-auto py-6 px-4">
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
                                <template v-if="!reservation.is_approved">
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
