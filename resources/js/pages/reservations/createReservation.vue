<script setup>
import { ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';
import axios from 'axios';

const form = useForm({
    client_id: '',
    client_name: '',
    room_id: '',
    check_in: '',
    check_out: '',
    accompanying_number: 1,
    price: '',
    notes: ''
});

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

//calc the price
const calculatePrice = () => {
    if (!selectedRoom.value || !form.check_in || !form.check_out) return;

    const checkIn = new Date(form.check_in);
    const checkOut = new Date(form.check_out);

    const diffTime = checkOut - checkIn;

    const diffDays = Math.max(1, Math.ceil(diffTime / (1000 * 60 * 60 * 24)));

    form.price = selectedRoom.value.price * diffDays;
}

//Add watch to show the changes if date change
watch([() => form.check_in, () => form.check_out], () => {
    calculatePrice();
})



const props = defineProps({
    rooms: Array
});

const searchResults = ref([]);
const isSearching = ref(false);
const selectedRoom = ref(null);
const availableRooms = ref(props.rooms || []);

// Function to get the client to search box
const searchClient = async (query) => {
    if (!query) {
        searchResults.value = [];
        return;
    }
    isSearching.value = true;
    try {
        const response = await axios.get('/clients/search', {
            params: { query: query }
        });
        searchResults.value = response.data;
    } catch (err) {
        console.error(err);
        searchResults.value = [];
    }
    setTimeout(() => {
        isSearching.value = false;
    }, 500);
};

const selectClient = (client) => {
    form.client_id = client.id;
    form.client_name = client.name;
    searchResults.value = [];
};

const handleRoomChange = (roomId) => {
    selectedRoom.value = availableRooms.value.find(room => room.id === parseInt(roomId));

    // Update price based on selected room if needed
    if (selectedRoom.value) {
        calculatePrice();
    }
};

const onSubmit = () => {
    form.post('/staff/reservations', {
        onSuccess: () => {
            form.reset();
        }
    })
}

const cancel = () => {
    router.visit('/staff/reservations');
}
</script>

<template>
    <div class="container mx-auto py-8 px-4">
        <div class="max-w-2xl mx-auto bg-white rounded-lg shadow-md p-6">
            <h1 class="text-2xl font-bold mb-6 text-center">Create New Reservation</h1>

            <form @submit.prevent="onSubmit">
                <!-- Client Search Field -->
                <div class="mb-4">
                    <label for="client_search" class="block text-sm font-medium text-gray-700 mb-1">Client</label>
                    <div class="relative">
                        <input type="text" id="client_search" v-model="form.client_name"
                            @input="searchClient($event.target.value)"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            :class="{ 'border-red-500': form.errors.client_id }"
                            placeholder="Search client by name or email..." />
                        <p v-if="form.errors.client_id" class="mt-1 text-sm text-red-600">{{ form.errors.client_id }}
                        </p>
                        <div v-if="isSearching" class="absolute right-3 top-2">
                            <svg class="animate-spin h-5 w-5 text-blue-500" xmlns="http://www.w3.org/2000/svg"
                                fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                        </div>

                        <!-- Search Results -->
                        <div v-if="searchResults.length > 0"
                            class="absolute z-10 w-full bg-white border border-gray-300 rounded-md mt-1 shadow-lg max-h-60 overflow-y-auto">
                            <div v-for="client in searchResults" :key="client.id" @click="selectClient(client)"
                                class="px-4 py-2 hover:bg-gray-100 cursor-pointer flex items-center space-x-2">
                                <img :src="client.image ? `/storage/${client.image}` : '/images/default.jpg'"
                                    class="h-8 w-8 rounded-full object-cover" alt="Client" />
                                <div>
                                    <div>{{ client.name }}</div>
                                    <div class="text-xs text-gray-500">{{ client.email }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Room Selection -->
                <div class="mb-4">
                    <label for="room_id" class="block text-sm font-medium text-gray-700 mb-1">Room</label>
                    <select id="room_id" v-model="form.room_id" :class="{ 'border-red-500': form.errors.room_id }"
                        @change="handleRoomChange($event.target.value)"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <!-- You would populate this dynamically -->
                        <option value="" disabled>Select a room</option>
                        <option v-for="room in availableRooms" :key="room.id" :value="room.id">
                            {{ room.name }} (Room #{{ room.number }}) - {{ formatPrice(room.price) }}
                        </option>
                    </select>
                    <p v-if="form.errors.room_id" class="mt-1 text-sm text-red-600">{{ form.errors.room_id }}</p>
                </div>

                <!-- Room Details Summary if room selected -->
                <div v-if="selectedRoom" class="mb-4 p-3 bg-gray-50 rounded-md border border-gray-200">
                    <h4 class="font-medium text-sm text-gray-700">Room Details</h4>
                    <div class="grid grid-cols-2 gap-2 mt-1 text-sm">
                        <div>Price per night:</div>
                        <div class="text-right font-medium">{{ formatPrice(selectedRoom.price) }}</div>
                        <div>Max capacity:</div>
                        <div class="text-right font-medium">{{ selectedRoom.capacity }} people</div>
                    </div>
                </div>

                <!-- Check-in Date -->
                <div class="mb-4">
                    <label for="check_in" class="block text-sm font-medium text-gray-700 mb-1">Check-in Date</label>
                    <input type="date" id="check_in" v-model="form.check_in"
                        :class="{ 'border-red-500': form.errors.check_in }"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" />
                    <p v-if="form.errors.check_in" class="mt-1 text-sm text-red-600">{{ form.errors.check_in }}</p>

                </div>

                <!-- Check-out Date -->
                <div class="mb-4">
                    <label for="check_out" class="block text-sm font-medium text-gray-700 mb-1">Check-out Date</label>
                    <input type="date" id="check_out" v-model="form.check_out"
                        :class="{ 'border-red-500': form.errors.check_out }"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" />
                    <p v-if="form.errors.check_out" class="mt-1 text-sm text-red-600">{{ form.errors.check_out }}</p>

                </div>

                <!-- Number of Guests -->
                <div class="mb-4">
                    <label for="accompanying_number" class="block text-sm font-medium text-gray-700 mb-1">
                        Number of Accompanying Guests
                    </label>
                    <input type="number" id="accompanying_number"
                        :class="{ 'border-red-500': form.errors.accompanying_number }"
                        v-model="form.accompanying_number" min="0"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" />
                    <p v-if="form.errors.accompanying_number" class="mt-1 text-sm text-red-600">{{
                        form.errors.accompanying_number
                    }}</p>
                </div>

                <!-- Price -->
                <div class="mb-4">
                    <label for="price" class="block text-sm font-medium text-gray-700 mb-1">Price</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500">$</span>
                        <input type="text" id="price" :value="centsToDollars(form.price)" readonly
                            class="w-full pl-8 px-3 py-2 bg-gray-50 border border-gray-300 rounded-md focus:outline-none cursor-not-allowed" />
                        <input type="hidden" v-model="form.price" />
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Calculated automatically based on room price and stay
                        duration.</p>
                </div>

                <!-- Form Actions -->
                <div class="flex justify-end space-x-3">
                    <button @click="cancel" type="button"
                        class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        Create Reservation
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<style>
/* Add any custom styles here */
</style>
