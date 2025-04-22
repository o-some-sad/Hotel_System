<script setup>
import { useForm } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    reservation: Object
});

const form = useForm({});

const centsToDollars = (cents) => {
    return cents / 100;
}

const formatPrice = (price) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
        minimumFractionDigits: 0
    }).format(centsToDollars(price))
}

const formatDate = (dateString) => {
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    return new Date(dateString).toLocaleDateString(undefined, options);
}

const confirmDelete = () => {
    form.delete(`/stuff/reservations/${props.reservation.id}`, {
        onSuccess: () => {
            router.visit('/stuff/reservations');
        }
    });
}

const cancel = () => {
    router.visit('/stuff/reservations');
}
</script>

<template>
    <div class="container mx-auto py-8 px-4">
        <div class="max-w-2xl mx-auto bg-white rounded-lg shadow-md p-6">
            <div class="text-center mb-6">
                <h1 class="text-2xl font-bold text-gray-800">Delete Reservation</h1>
                <p class="text-red-600 mt-2">Are you sure you want to delete this reservation?</p>
            </div>

            <div class="bg-gray-50 p-4 rounded-md mb-6 border border-gray-200">
                <h2 class="text-lg font-semibold mb-3 text-gray-700">Reservation Details</h2>

                <div class="grid grid-cols-2 gap-y-2">
                    <div class="text-gray-600">Reservation ID:</div>
                    <div class="font-medium">{{ reservation.id }}</div>

                    <div class="text-gray-600">Client Name:</div>
                    <div class="font-medium">{{ reservation.client.name }}</div>

                    <div class="text-gray-600">Room:</div>
                    <div class="font-medium">{{ reservation.room.name }} (Room #{{ reservation.room.number }})</div>

                    <div class="text-gray-600">Check-in Date:</div>
                    <div class="font-medium">{{ formatDate(reservation.check_in) }}</div>

                    <div class="text-gray-600">Check-out Date:</div>
                    <div class="font-medium">{{ formatDate(reservation.check_out) }}</div>

                    <div class="text-gray-600">Number of Guests:</div>
                    <div class="font-medium">{{ reservation.accompanying_number }}</div>

                    <div class="text-gray-600">Price:</div>
                    <div class="font-medium">{{ formatPrice(reservation.price) }}</div>

                    <div class="text-gray-600">Status:</div>
                    <div class="font-medium">
                        <span :class="{
                            'bg-green-100 text-green-800': reservation.is_approved,
                            'bg-yellow-100 text-yellow-800': !reservation.is_approved
                        }" class="px-2 py-1 rounded-full text-xs">
                            {{ reservation.is_approved ? 'Approved' : 'Pending' }}
                        </span>
                    </div>

                    <div v-if="reservation.notes" class="text-gray-600">Notes:</div>
                    <div v-if="reservation.notes" class="font-medium">{{ reservation.notes }}</div>
                </div>
            </div>

            <div class="text-gray-700 mb-6">
                <p class="text-sm">
                    This will permanently delete the reservation and make the room available for new bookings.
                    This action cannot be undone.
                </p>
            </div>

            <div class="flex justify-end space-x-3">
                <button @click="cancel" type="button"
                    class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500">
                    Cancel
                </button>
                <button @click="confirmDelete" type="button" :disabled="form.processing"
                    class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                    {{ form.processing ? 'Deleting...' : 'Delete Reservation' }}
                </button>
            </div>
        </div>
    </div>
</template>
