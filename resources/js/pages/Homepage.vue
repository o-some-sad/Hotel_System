<template>
    <div class="min-h-screen bg-gray-100 p-8">
      <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Your Reservations</h1>
        <button
          @click="openModal()"
          class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded shadow"
        >
          + Create Reservation
        </button>
      </div>

      <div v-if="reservations.data.length === 0" class="text-gray-500">
        You have no reservations.
      </div>

      <div v-else class="space-y-4">
        <div
          v-for="reservation in reservations.data"
          :key="reservation.id"
          class="bg-white rounded-xl shadow p-4 border border-gray-200"
        >
          <div class="flex justify-between items-center">
            <div>
              <h2 class="text-xl font-semibold text-indigo-600">
                Room: {{ reservation.room?.name || 'N/A' }}
              </h2>
              <p class="text-gray-700">Reservation ID: {{ reservation.id }}</p>
              <p class="text-gray-500">Check-in: {{ reservation.check_in }}</p>
              <p class="text-gray-500">Check-out: {{ reservation.check_out }}</p>
            </div>

            <div class="space-x-2">
              <button
                @click="openModal(reservation)"
                class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded"
              >
                Edit
              </button>
              <button
                @click="removeReservation(reservation.id)"
                class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded"
              >
                Remove
              </button>
            </div>
          </div>
        </div>

        <!-- Pagination -->
        <div class="flex justify-between items-center mt-6">
          <button
            :disabled="!reservations.prev_page_url"
            @click="goToPage(reservations.current_page - 1)"
            class="px-4 py-2 bg-indigo-500 text-white rounded disabled:opacity-50"
          >
            Previous
          </button>
          <span class="text-gray-700">
            Page {{ reservations.current_page }} of {{ reservations.last_page }}
          </span>
          <button
            :disabled="!reservations.next_page_url"
            @click="goToPage(reservations.current_page + 1)"
            class="px-4 py-2 bg-indigo-500 text-white rounded disabled:opacity-50"
          >
            Next
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
          <form @submit.prevent="submitReservation">
            <div class="mb-4">
              <label class="block mb-1 text-gray-700">Room</label>
              <select
                v-model="form.room_id"
                class="w-full border-gray-300 rounded p-2"
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
            </div>

            <div class="mb-4">
              <label class="block mb-1 text-gray-700">Check-in</label>
              <input
                type="date"
                v-model="form.check_in"
                class="w-full border-gray-300 rounded p-2"
                required
              />
            </div>

            <div class="mb-4">
              <label class="block mb-1 text-gray-700">Check-out</label>
              <input
                type="date"
                v-model="form.check_out"
                class="w-full border-gray-300 rounded p-2"
                required
              />
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
    </div>
  </template>

  <script setup>
  import { ref } from 'vue'
  import { router } from '@inertiajs/vue3'

  defineProps({
    reservations: Object,
    rooms: Array, // passed from controller
  })

  // Modal State
  const showModal = ref(false)
  const isEditing = ref(false)
  const editingId = ref(null)

  const form = ref({
    room_id: '',
    check_in: '',
    check_out: ''
  })

  function openModal(reservation = null) {
    showModal.value = true
    if (reservation) {
      isEditing.value = true
      editingId.value = reservation.id
      form.value = {
        room_id: reservation.room_id,
        check_in: reservation.check_in,
        check_out: reservation.check_out
      }
    } else {
      isEditing.value = false
      editingId.value = null
      form.value = {
        room_id: '',
        check_in: '',
        check_out: ''
      }
    }
  }

  function closeModal() {
    showModal.value = false
  }

  function submitReservation() {
    if (isEditing.value) {
      router.put(route('client.reservations.update', editingId.value), form.value, {
        onSuccess: closeModal
      })
    } else {
      router.post(route('client.reservations.store'), form.value, {
        onSuccess: closeModal
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
  </script>
