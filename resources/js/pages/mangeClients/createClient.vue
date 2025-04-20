<script setup>
import { useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    password: '',
    nationalId: '',
    country: '',
    gender: '',
    image: null
})

const submit = () => {
    form.post('/clients/store', {
        onSuccess: () => {
            form.reset();
        }
    })
}

const cancel = () => {
    window.history.back();
}

</script>

<template>
    <div class="container mx-auto py-8 px-4">
        <div class="max-w-2xl mx-auto bg-white rounded-lg shadow-md p-6">
            <h1 class="text-2xl font-bold mb-6 text-center">Create New Client</h1>

            <form @submit.prevent="submit">
                <!-- Name field -->
                <div class="mb-4">
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                    <input v-model="form.name" id="name" type="text"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        :class="{ 'border-red-500': form.errors.name }" placeholder="Enter client name" />
                    <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
                </div>

                <!-- Email field -->
                <div class="mb-4">
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input v-model="form.email" id="email" type="email"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        :class="{ 'border-red-500': form.errors.email }" placeholder="Enter client email" />
                    <p v-if="form.errors.email" class="mt-1 text-sm text-red-600">{{ form.errors.email }}</p>
                </div>


                <!-- Password field -->
                <div class="mb-4">
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <input v-model="form.password" id="password" type="password"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        :class="{ 'border-red-500': form.errors.password }" placeholder="Enter password" />
                    <p v-if="form.errors.password" class="mt-1 text-sm text-red-600">{{ form.errors.password }}</p>
                </div>

                <!-- National ID field -->
                <div class="mb-4">
                    <label for="nationalId" class="block text-sm font-medium text-gray-700 mb-1">National ID</label>
                    <input v-model="form.nationalId" id="nationalId" type="text"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        :class="{ 'border-red-500': form.errors.nationalId }" placeholder="Enter national ID" />
                    <p v-if="form.errors.nationalId" class="mt-1 text-sm text-red-600">{{ form.errors.nationalId }}</p>
                </div>

                <!-- Country field -->
                <div class="mb-4">
                    <label for="country" class="block text-sm font-medium text-gray-700 mb-1">Country</label>
                    <input v-model="form.country" id="country" type="text"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        :class="{ 'border-red-500': form.errors.country }" placeholder="Enter country" />
                    <p v-if="form.errors.country" class="mt-1 text-sm text-red-600">{{ form.errors.country }}</p>
                </div>

                <!-- Gender field -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Gender</label>
                    <div class="flex gap-4">
                        <div class="flex items-center">
                            <input v-model="form.gender" id="male" type="radio" name="gender" value="male"
                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300" />
                            <label for="male" class="ml-2 text-sm text-gray-700">Male</label>
                        </div>
                        <div class="flex items-center">
                            <input v-model="form.gender" id="female" type="radio" name="gender" value="female"
                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300" />
                            <label for="female" class="ml-2 text-sm text-gray-700">Female</label>
                        </div>
                    </div>
                    <p v-if="form.errors.gender" class="mt-1 text-sm text-red-600">{{ form.errors.gender }}</p>
                </div>

                <!-- Image upload field -->
                <div class="mb-6">
                    <label for="image" class="block text-sm font-medium text-gray-700 mb-1">Profile Image</label>
                    <input @input="form.image = $event.target.files[0]" id="image" type="file"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        accept="image/*" />
                    <p v-if="form.errors.gender" class="mt-1 text-sm text-red-600">{{ form.errors.image }}</p>
                </div>

                <!-- Form buttons -->
                <div class="flex justify-end space-x-3 mt-6">
                    <button @click="cancel" type="button"
                        class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-400">
                        Cancel
                    </button>
                    <button type="submit" :disabled="form.processing"
                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        {{ form.processing ? 'Creating...' : 'Create Client' }}
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
