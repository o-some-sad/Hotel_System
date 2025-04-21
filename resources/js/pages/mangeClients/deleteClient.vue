<script setup>
import { router } from '@inertiajs/vue3';

const props = defineProps({
    client: Object, // Pass the client data from your controller
});

// Function to confirm deletion
const confirmDelete = () => {
    if (confirm("Are you sure you want to delete this client? This action cannot be undone.")) {
        router.delete(`/clients/${props.client.id}`, {
            onSuccess: () => {
                // Will automatically redirect to clients.index if your controller redirects there
            }
        });
    }
};

// Function to cancel and go back
const cancel = () => {
    router.visit('/clients');
};
</script>

<template>
    <div class="container mx-auto py-8 px-4">
        <div class="max-w-2xl mx-auto bg-white rounded-lg shadow-md p-6">
            <div class="text-center mb-6">
                <h1 class="text-2xl font-bold text-red-600">Delete Client</h1>
                <p class="mt-2 text-gray-600">Are you sure you want to delete this client? This action cannot be undone.
                </p>
            </div>

            <div class="border border-red-200 rounded-lg p-4 mb-6 bg-red-50">
                <h2 class="font-semibold text-gray-700 mb-4">Client Information</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Client profile image -->
                    <div class="col-span-full flex justify-center mb-4">
                        <img :src="props.client.image ? `/storage/${props.client.image}` : '/images/default.jpg'"
                            alt="Client profile" class="h-24 w-24 rounded-full object-cover border border-gray-300" />
                    </div>

                    <!-- Client details in a grid -->
                    <div class="info-group">
                        <span class="label">Name:</span>
                        <span class="value">{{ props.client.name }}</span>
                    </div>

                    <div class="info-group">
                        <span class="label">Email:</span>
                        <span class="value">{{ props.client.email }}</span>
                    </div>

                    <div class="info-group">
                        <span class="label">National ID:</span>
                        <span class="value">{{ props.client.nationalId }}</span>
                    </div>

                    <div class="info-group">
                        <span class="label">Country:</span>
                        <span class="value">{{ props.client.country }}</span>
                    </div>

                    <div class="info-group">
                        <span class="label">Gender:</span>
                        <span class="value capitalize">{{ props.client.gender }}</span>
                    </div>

                    <div class="info-group">
                        <span class="label">Created:</span>
                        <span class="value">{{ new Date(props.client.created_at).toLocaleString() }}</span>
                    </div>

                    <!-- Add more fields as needed -->
                </div>
            </div>

            <!-- Confirmation buttons -->
            <div class="flex justify-end space-x-3 mt-6">
                <button @click="cancel"
                    class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-400">
                    Cancel
                </button>
                <button @click="confirmDelete"
                    class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                    Delete Client
                </button>
            </div>
        </div>
    </div>
</template>

<style scoped>
.info-group {
    display: flex;
    flex-direction: column;
    margin-bottom: 0.5rem;
}

.label {
    font-size: 0.875rem;
    color: #4b5563;
    font-weight: 500;
}

.value {
    font-size: 1rem;
    color: #111827;
}

@media (min-width: 768px) {
    .info-group {
        margin-bottom: 1rem;
    }
}
</style>
