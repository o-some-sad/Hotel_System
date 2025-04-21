<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    client: Object,
    errors: Object
});

const form = useForm({
    name: props.client.name,
    email: props.client.email,
    password: '',
    nationalId: props.client.nationalId,
    country: props.client.country,
    gender: props.client.gender,
    image: null
});

// Track if new image is selected
const newImageSelected = ref(false);
const newImagePreview = ref(null);

// Handle image selection
const handleImageChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        form.image = file;
        newImageSelected.value = true;
        newImagePreview.value = URL.createObjectURL(file);
    }
};

const submit = () => {
    form.post(`/clients/${props.client.id}/update`, {
        onSuccess: () => {
            router.visit('/clients');
        }
    })
}


const cancel = () => {
    router.visit('/clients');
}

</script>

<template>
    <div class="container mx-auto py-8 px-4">
        <div class="max-w-2xl mx-auto bg-white rounded-lg shadow-md p-6">
            <h1 class="text-2xl font-bold mb-6 text-center">Update Client</h1>

            <form @submit.prevent="submit" enctype="multipart/form-data">
                <!-- Name field -->
                <div class="mb-4">
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                    <input v-model="form.name" id="name" type="text"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Enter client name" />
                    <p class="mt-1 text-sm text-red-600 hidden">Name is required</p>
                    <p v-if="props.errors.name" class="mt-1 text-sm text-red-600">{{ props.errors.name }}</p>
                </div>

                <!-- Email field -->
                <div class="mb-4">
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input v-model="form.email" id="email" type="email"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Enter client email" />
                    <p class="mt-1 text-sm text-red-600 hidden">Please enter a valid email</p>
                    <p v-if="props.errors.email" class="mt-1 text-sm text-red-600">{{ props.errors.email }}</p>
                </div>

                <div class="mb-4">
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <input v-model="form.password" id="password" type="password"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Leave blank to keep current password" autocomplete="new-password" />
                    <p class="text-xs text-gray-500 mt-1">Leave blank to keep the current password</p>
                    <p v-if="props.errors.password" class="mt-1 text-sm text-red-600">{{ props.errors.password }}</p>
                </div>

                <!-- National ID field -->
                <div class="mb-4">
                    <label for="nationalId" class="block text-sm font-medium text-gray-700 mb-1">National ID</label>
                    <input v-model="form.nationalId" id="nationalId" type="text"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Enter national ID" />
                    <p class="mt-1 text-sm text-red-600 hidden">National ID is required</p>
                    <p v-if="props.errors.nationalId" class="mt-1 text-sm text-red-600">{{ props.errors.nationalId }}
                    </p>
                </div>

                <!-- Country field -->
                <div class="mb-4">
                    <label for="country" class="block text-sm font-medium text-gray-700 mb-1">Country</label>
                    <input v-model="form.country" id="country" type="text"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Enter country" />
                    <p class="mt-1 text-sm text-red-600 hidden">Country is required</p>
                    <p v-if="props.errors.country" class="mt-1 text-sm text-red-600">{{ props.errors.country }}</p>
                </div>

                <!-- Gender field -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Gender</label>
                    <div class="flex gap-4">
                        <div class="flex items-center">
                            <input id="male" type="radio" name="gender" value="male" v-model="form.gender"
                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300" />
                            <label for="male" class="ml-2 text-sm text-gray-700">Male</label>
                        </div>
                        <div class="flex items-center">
                            <input id="female" type="radio" name="gender" value="female" v-model="form.gender"
                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300" />
                            <label for="female" class="ml-2 text-sm text-gray-700">Female</label>
                        </div>
                    </div>
                    <p class="mt-1 text-sm text-red-600 hidden">Please select a gender</p>
                    <p v-if="props.errors.gender" class="mt-1 text-sm text-red-600">{{ props.errors.gender }}</p>
                </div>

                <!-- Current Profile Image -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Current Profile Image</label>
                    <div class="flex items-center space-x-2">
                        <img :src="props.client.image ? `/storage/${props.client.image}` : '/images/default.jpg'"
                            alt="Current profile" class="h-16 w-16 rounded-full object-cover border border-gray-200" />
                        <span class="text-sm text-gray-500">Current profile image</span>
                    </div>
                </div>

                <!-- Image upload field -->
                <div class="mb-6">
                    <label for="image" class="block text-sm font-medium text-gray-700 mb-1">Update Profile Image</label>
                    <input id="image" type="file"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        accept="image/*" @input="handleImageChange" />
                    <p class="text-xs text-gray-500 mt-1">Leave empty to keep the current image</p>

                    <!-- New image preview - only shows when a new image is selected -->
                    <div v-if="newImageSelected" class="mt-3">
                        <p class="text-sm font-medium text-gray-700 mb-1">New Image Preview:</p>
                        <img :src="newImagePreview" alt="New profile image preview"
                            class="h-16 w-16 rounded-full object-cover border border-gray-200" />
                    </div>
                    <p v-if="props.errors.image" class="mt-1 text-sm text-red-600">{{ props.errors.image }}</p>
                </div>

                <!-- Form buttons -->
                <div class="flex justify-end space-x-3 mt-6">
                    <button @click="cancel" type="button"
                        class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-400">
                        Cancel
                    </button>
                    <button type="submit" :disabled="form.processing"
                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        {{ form.processing ? 'Updating...' : 'Update Client' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<style scoped>
.container {
    max-width: 1200px;
}

/* Form specific styles */
input.error,
select.error {
    border-color: #ef4444;
}

/* Show validation messages */
p.text-red-600:not(.hidden) {
    display: block;
}

/* File input styling */
input[type="file"] {
    padding-top: 0.375rem;
    padding-bottom: 0.375rem;
}

/* Add animation for form submission */
@keyframes pulse {

    0%,
    100% {
        opacity: 1;
    }

    50% {
        opacity: 0.5;
    }
}

button[type="submit"]:disabled {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
</style>
