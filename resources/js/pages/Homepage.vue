<template>
  <div class="min-h-screen bg-gradient-to-b from-indigo-50 to-white">
    <!-- Hero Section -->
    <AuthenticatedLayout>
    <div class="relative h-screen flex flex-col items-center justify-center text-center px-4 md:px-8">
      <h1 class="text-6xl md:text-7xl font-bold text-indigo-800 mb-4 animate-fade-in">LaraLive</h1>
      <p class="text-xl md:text-2xl text-indigo-600 max-w-2xl mb-8 animate-fade-in-delay">
        Where luxury meets comfort, creating memories that last a lifetime
      </p>
      <button
        @click="scrollToReservations"
        class="mt-8 animate-bounce bg-indigo-600 hover:bg-indigo-700 text-white rounded-full p-3 shadow-lg transition-all duration-300"
        aria-label="Scroll to reservations"
      >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
        </svg>
      </button>
    </div>

    <!-- Reservations Section -->
    <div id="reservations-section" class="px-4 md:px-8 py-12 max-w-7xl mx-auto">
      <div class="flex justify-between items-center mb-10">
        <h1 class="text-3xl font-bold text-indigo-800">Your Reservations</h1>
        <button
          @click="openModal()"
          class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-lg shadow-md transition-all duration-300 flex items-center gap-2"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
          </svg>
          Create Reservation
        </button>
      </div>

      <div v-if="reservations.data.length === 0" class="text-gray-500 text-center py-12 bg-white rounded-xl shadow-sm">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
        </svg>
        <p class="text-lg">You have no reservations yet.</p>
        <p class="mt-2">Create your first reservation to get started!</p>
      </div>

      <div v-else class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
        <div
          v-for="reservation in reservations.data"
          :key="reservation.id"
          class="bg-white rounded-xl shadow-md hover:shadow-lg transition-shadow duration-300 overflow-hidden border border-gray-100"
        >
          <div class="p-6">
            <div class="mb-4">
              <h2 class="text-xl font-semibold text-indigo-700 mb-1">
                Room: {{ reservation.room?.name || 'N/A' }}
              </h2>
              <p class="text-sm text-gray-500">Reservation #{{ reservation.id }}</p>
            </div>

            <div class="space-y-2 mb-6">
              <div class="flex items-center text-gray-700">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <span>Check-in: {{ reservation.check_in }}</span>
              </div>
              <div class="flex items-center text-gray-700">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <span>Check-out: {{ reservation.check_out }}</span>
              </div>
              <div class="flex items-center text-gray-700">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Room Price: {{ formatPrice(reservation.room.price) }}</span>
              </div>
            </div>

            <div class="bg-indigo-50 p-4 rounded-lg mb-4">
              <p class="text-indigo-800 font-medium">
                Total Price: {{ calculateTotalPrice(reservation.room.price, reservation.check_in, reservation.check_out).formattedPrice }}
              </p>
              <p class="text-sm text-indigo-600">
                {{ calculateTotalPrice(reservation.room.price, reservation.check_in, reservation.check_out).days }} day(s)
              </p>
            </div>

            <div class="flex flex-wrap gap-2" v-if="!isCheckoutPassed(reservation.check_out)">
              <button
                v-if="reservation.payment_id == null"
                @click="openModal(reservation)"
                class="flex-1 bg-indigo-100 hover:bg-indigo-200 text-indigo-700 px-4 py-2 rounded-lg transition-colors duration-300"
              >
                <span class="flex items-center justify-center gap-1">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                  </svg>
                  Edit
                </span>
              </button>
              <button
                v-if="reservation.payment_id == null"
                @click="removeReservation(reservation.id)"
                class="flex-1 bg-red-100 hover:bg-red-200 text-red-700 px-4 py-2 rounded-lg transition-colors duration-300"
              >
                <span class="flex items-center justify-center gap-1">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                  </svg>
                  Remove
                </span>
              </button>
              <button
                v-if="reservation.payment_id == null"
                @click="redirectToCheckout(reservation.id)"
                class="w-full mt-2 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors duration-300"
              >
                <span class="flex items-center justify-center gap-1">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                  </svg>
                  Pay Now
                </span>
              </button>
              <button
                v-else
                disabled
                class="w-full mt-2 bg-gray-400 text-white px-4 py-2 rounded-lg cursor-not-allowed"
              >
                <span class="flex items-center justify-center gap-1">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                  </svg>
                  Paid
                </span>
              </button>
              <button
                v-if="reservation.is_approved == false"
                disabled
                class="w-full mt-2 bg-yellow-100 text-yellow-700 px-4 py-2 rounded-lg cursor-not-allowed"
              >
                <span class="flex items-center justify-center gap-1">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  Pending for approval
                </span>
              </button>
            </div>
            <div v-else class="text-sm text-gray-500 italic bg-gray-50 p-3 rounded-lg text-center">
              Reservation completed
            </div>
          </div>
        </div>
      </div>

      <!-- Pagination -->
      <div class="flex justify-between items-center mt-10">
        <button
          :disabled="!reservations.prev_page_url"
          @click="goToPage(reservations.current_page - 1)"
          class="px-5 py-2 bg-indigo-600 text-white rounded-lg disabled:opacity-50 transition-all duration-300 flex items-center gap-2"
          :class="{'hover:bg-indigo-700': reservations.prev_page_url}"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
          </svg>
          Previous
        </button>
        <span class="text-indigo-800 font-medium">
          Page {{ reservations.current_page }} of {{ reservations.last_page }}
        </span>
        <button
          :disabled="!reservations.next_page_url"
          @click="goToPage(reservations.current_page + 1)"
          class="px-5 py-2 bg-indigo-600 text-white rounded-lg disabled:opacity-50 transition-all duration-300 flex items-center gap-2"
          :class="{'hover:bg-indigo-700': reservations.next_page_url}"
        >
          Next
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
          </svg>
        </button>
      </div>
    </div>

    <!-- Reservation Modal -->
    <div
      v-if="showModal"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
    >
      <div class="bg-white rounded-xl shadow-lg p-6 w-full max-w-md">
        <h2 class="text-2xl font-bold mb-4">
          {{ isEditing ? 'Edit Reservation' : 'Create Reservation' }}
        </h2>

        <!-- Display validation errors -->
        <div v-if="Object.keys(errors).length > 0" class="mb-4 p-3 bg-red-50 text-red-700 rounded border border-red-200">
          <p class="font-semibold mb-1">Please fix the following errors:</p>
          <ul class="list-disc pl-5">
            <li v-for="(error, key) in errors" :key="key">{{ error }}</li>
          </ul>
        </div>

        <form @submit.prevent="submitReservation">
          <div class="mb-4">
            <label class="block mb-1 text-gray-700">Room</label>
            <select
              v-model="form.room_id"
              class="w-full border-gray-300 rounded p-2"
              :class="{'border-red-500': errors.room_id}"
              required
            >
              <option value="" disabled>Select a room</option>
              <option
                v-for="room in rooms"
                :key="room.id"
                :value="room.id"
              >
                {{ room.name }}
              </option>
            </select>
            <p v-if="errors.room_id" class="text-red-500 text-sm mt-1">{{ errors.room_id }}</p>
          </div>

          <div class="mb-4">
            <label class="block mb-1 text-gray-700">Check-in</label>
            <input
              type="date"
              v-model="form.check_in"
              class="w-full border-gray-300 rounded p-2"
              :class="{'border-red-500': errors.check_in}"
              required
            />
            <p v-if="errors.check_in" class="text-red-500 text-sm mt-1">{{ errors.check_in }}</p>
          </div>

          <div class="mb-4">
            <label class="block mb-1 text-gray-700">Check-out</label>
            <input
              type="date"
              v-model="form.check_out"
              class="w-full border-gray-300 rounded p-2"
              :class="{'border-red-500': errors.check_out}"
              required
            />
            <p v-if="errors.check_out" class="text-red-500 text-sm mt-1">{{ errors.check_out }}</p>
          </div>

          <div class="mb-4">
              <label class="block mb-1 text-gray-700">Number of Guests:</label>
              <input
                type="number"
                v-model="form.accompanying_number"
                class="w-full border-gray-300 rounded p-2"
                :class="{'border-red-500': errors.accompanying_number}"
                required
              />
              <p v-if="errors.accompanying_number" class="text-red-500 text-sm mt-1">{{ errors.accompanying_number }}</p>
          </div>

          <div class="flex justify-end gap-2">
            <button
              type="button"
              @click="closeModal"
              class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded"
            >
              Cancel
            </button>
            <button
              type="submit"
              class="bg-indigo-500 hover:bg-indigo-600 text-white px-4 py-2 rounded"
            >
              {{ isEditing ? 'Update' : 'Create' }}
            </button>
          </div>
        </form>
      </div>
    </div>
    </AuthenticatedLayout>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/layouts/clientNav.vue';


const props = defineProps({
  reservations: Object,
  rooms: Array, // passed from controller
  errors: Object,
})

// Modal State
const showModal = ref(false)
const isEditing = ref(false)
const editingId = ref(null)
const errors = ref({})

const form = ref({
  room_id: '',
  check_in: '',
  check_out: '',
  accompanying_number: '',
})

// Add smooth scrolling function
function scrollToReservations() {
  const reservationsSection = document.getElementById('reservations-section')
  if (reservationsSection) {
    reservationsSection.scrollIntoView({ behavior: 'smooth' })
  }
}

function openModal(reservation = null) {
    errors.value = {}
    showModal.value = true
    if (reservation) {
      isEditing.value = true
      editingId.value = reservation.id
      form.value = {
        room_id: Number(reservation.room_id), // Convert to number to ensure proper binding
        check_in: reservation.check_in,
        check_out: reservation.check_out,
        accompanying_number: reservation.accompanying_number || '',
      }
    } else {
      isEditing.value = false
      editingId.value = null
      form.value = {
        room_id: '',
        check_in: '',
        check_out: '',
        accompanying_number: '',
      }
    }
  }

function closeModal() {
  showModal.value = false
  errors.value = {}
}

function submitReservation() {
  if (isEditing.value) {
    router.patch(route('client.reservations.update', editingId.value), form.value, {
      onSuccess: closeModal,
      onError: (e) => {
        errors.value = e
      }
    })
  } else {
    router.post(route('client.reservations.store'), form.value, {
      onSuccess: closeModal,
      onError: (e) => {
        errors.value = e
      }
    })
  }
}

function removeReservation(id) {
  if (confirm('Are you sure you want to delete this reservation?')) {
    router.delete(route('client.reservations.destroy', id))
  }
}

function goToPage(page) {
  router.get(route('client.reservations'), { page }, { preserveState: true })
}

function formatPrice(price){
  const newPrice = price / 100;
  return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(newPrice);
}

function calculateTotalPrice(roomPrice, checkIn, checkOut) {
  // Convert dates to Date objects
  const startDate = new Date(checkIn);
  const endDate = new Date(checkOut);

  // Calculate the difference in days
  const diffTime = Math.abs(endDate - startDate);
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

  // Calculate total price (days * room price)
  // Use at least 1 day even if same-day checkout
  const days = diffDays || 1;
  const totalPrice = roomPrice * days;

  return {
    days,
    totalPrice,
    formattedPrice: formatPrice(totalPrice)
  };
}

function isCheckoutPassed(checkoutDate) {
  if (!checkoutDate) return false
  const today = new Date()
  today.setHours(0, 0, 0, 0) // Set to beginning of day for fair comparison
  const checkout = new Date(checkoutDate)
  return checkout < today
}

function redirectToCheckout(reservationId) {
  // Show a loading indicator or disable the button
  const button = event.target;
  const originalText = button.textContent;
  button.disabled = true;
  button.textContent = 'Processing...';

  // Use window.location for a full page redirect instead of Inertia
  window.location.href = route('stripe.checkout') + '?reservation_id=' + reservationId;

  // Alternative approach using Inertia
  /*
  router.post(route('stripe.checkout'), {
    reservation_id: reservationId
  }, {
    preserveScroll: true,
    onError: (errors) => {
      button.disabled = false;
      button.textContent = originalText;
      alert('Failed to initiate checkout: ' + (errors.message || 'Unknown error'));
    }
  });
  */
}
</script>

<style scoped>
.animate-fade-in {
  animation: fadeIn 1s ease-in-out;
}

.animate-fade-in-delay {
  animation: fadeIn 1s ease-in-out 0.3s both;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>