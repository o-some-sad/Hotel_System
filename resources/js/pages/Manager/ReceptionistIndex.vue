<script setup>
import { Button } from '@/components/ui/button';
import { Table, TableBody, TableCaption, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import { Link, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    receptionists: Object,
    isAdmin: Boolean,
    ids: Array,
});

const breadcrumbs = [{ title: 'Receptionists', href: route('receptionists.index') }];

// Modal & form state
const showFormModal = ref(false);
const formMode = ref('create');
const verifyPassword = ref('');
const resultMessage = ref('');
const showSuccessInModal = ref(false);

const form = useForm({
    id: null,
    name: '',
    email: '',
    nationalId: '',
    password: '',
    image: null,
});

function openFormModal(receptionist = null) {
    form.reset();
    form.clearErrors();
    verifyPassword.value = '';
    resultMessage.value = '';
    showSuccessInModal.value = false;

    if (receptionist) {
        formMode.value = 'edit';
        form.id = receptionist.id;
        form.name = receptionist.name;
        form.email = receptionist.actual_email;
        form.nationalId = receptionist.nationalId;
    } else {
        formMode.value = 'create';
    }

    showFormModal.value = true;
}

function closeModal() {
    showFormModal.value = false;
    form.reset();
    verifyPassword.value = '';
    showSuccessInModal.value = false;
}

function validateForm() {
    form.clearErrors();
    let ok = true;

    if (!/^[A-Za-z ]{3,30}$/.test(form.name)) {
        form.errors.name = 'Name must be 3–30 letters and spaces only.';
        ok = false;
    }
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email)) {
        form.errors.email = 'Email is not valid.';
        ok = false;
    }
    if (!/^\d{14}$/.test(form.nationalId)) {
        form.errors.nationalId = 'National ID must be exactly 14 digits.';
        ok = false;
    }
    if (formMode.value === 'create' || form.password) {
        if (form.password.length < 6) {
            form.errors.password = 'Password must be at least 6 characters.';
            ok = false;
        }
        if (form.password !== verifyPassword.value) {
            form.errors.verifyPassword = 'Passwords do not match.';
            ok = false;
        }
    }

    return ok;
}

function submitForm() {
    if (!validateForm()) return;
    const data = new FormData();
    data.append('name', form.name);
    data.append('email', form.email);
    data.append('nationalId', form.nationalId);
    if (form.password) {
        data.append('password', form.password);
    }
    if (form.image) {
        data.append('image', form.image);
    }
    if (formMode.value === 'edit') {
        data.append('_method', 'PATCH');
    }
    router.post(formMode.value === 'create' ? '/receptionists' : `/receptionists/${form.id}`, data, {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: (page) => {
            resultMessage.value = page.props.flash.success || 'Operation successful.';
            showFormModal.value = false;
        },
    });
}

function deleteReceptionist(r) {
    if (confirm(`Are you sure you want to delete this receptionist?\nID: ${r.id}\nName: ${r.name}\nEmail: ${r.email}`)) {
        router.delete(route('receptionists.destroy', r.id), {
            onSuccess: (page) => {
                alert(page.props.flash.success || 'Receptionist deleted successfully.');
            },
            onError: (error) => {
                alert(error.response?.data?.message || 'An error occurred while deleting.');
            },
        });
    }
}
</script>

<template>
    <AppLayout :title="'Manage Receptionists'" :breadcrumbs="breadcrumbs">
        <div class="container mx-auto px-4 py-6">
            <!-- Global Flash Messages - only show these if not showing modal -->
            <div
                v-if="$page.props.flash.success && !showFormModal"
                class="mb-6 flex items-center justify-between rounded-lg border border-green-200 bg-green-50 p-4 text-green-700"
            >
                <div class="flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="mr-2 h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path
                            fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                            clip-rule="evenodd"
                        />
                    </svg>
                    <span>{{ $page.props.flash.success }}</span>
                </div>
                <button @click="$page.props.flash.success = null" class="text-green-700 hover:text-green-900">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path
                            fill-rule="evenodd"
                            d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                            clip-rule="evenodd"
                        />
                    </svg>
                </button>
            </div>

            <div
                v-if="$page.props.flash.error && !showFormModal"
                class="mb-6 flex items-center justify-between rounded-lg border border-red-200 bg-red-50 p-4 text-red-700"
            >
                <div class="flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="mr-2 h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path
                            fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                            clip-rule="evenodd"
                        />
                    </svg>
                    <span>{{ $page.props.flash.error }}</span>
                </div>
                <button @click="$page.props.flash.error = null" class="text-red-700 hover:text-red-900">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path
                            fill-rule="evenodd"
                            d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                            clip-rule="evenodd"
                        />
                    </svg>
                </button>
            </div>

            <div class="mb-6 flex items-center justify-between">
                <h1 class="text-2xl font-bold">Receptionist Management</h1>
                <Button @click="openFormModal()">Create Receptionist</Button>
            </div>

            <Table>
                <TableCaption>A list of all receptionists.</TableCaption>
                <TableHeader>
                    <TableRow>
                        <TableHead>Profile</TableHead>
                        <TableHead>Name</TableHead>
                        <TableHead>Virtual Email</TableHead>
                        <TableHead>Real Email</TableHead>
                        <TableHead>National ID</TableHead>
                        <TableHead>Actions</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-for="mgr in receptionists.data" :key="mgr.id" class="hover:bg-muted/50">
                        <TableCell>
                            <img :src="mgr.image" class="h-10 w-10 rounded-full object-cover" />
                        </TableCell>
                        <TableCell>{{ mgr.name }}</TableCell>
                        <TableCell>{{ mgr.email }}</TableCell>
                        <TableCell>{{ mgr.actual_email }}</TableCell>
                        <TableCell>{{ mgr.nationalId }}</TableCell>
                        <TableCell>
                            <div class="flex space-x-2">
                                <Button v-if="isAdmin || ids?.includes(mgr.id)" size="sm" variant="outline" @click="openFormModal(mgr)">Edit</Button>
                                <Button v-if="isAdmin || ids?.includes(mgr.id)" size="sm" variant="destructive" @click="deleteReceptionist(mgr)"
                                    >Delete</Button
                                >
                            </div>
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>

            <!-- Pagination -->
            <nav class="mt-6 flex justify-center" aria-label="Page navigation">
                <ul class="inline-flex items-center -space-x-px">
                    <li v-for="link in receptionists.links" :key="link.label">
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            v-html="link.label"
                            :class="['mx-1 rounded border px-3 py-1', link.active ? 'bg-blue-500 text-white' : 'bg-white text-blue-600']"
                        />
                        <span v-else v-html="link.label" class="mx-1 cursor-default rounded border px-3 py-1 text-gray-500" />
                    </li>
                </ul>
            </nav>

            <!-- Modal -->
            <div v-if="showFormModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
                <div class="w-full max-w-lg rounded-lg bg-white shadow-lg">
                    <div class="p-6">
                        <h2 class="mb-4 text-xl font-semibold">
                            {{ formMode === 'create' ? 'Create Receptionist' : 'Edit Receptionist' }}
                        </h2>

                        <!-- Success Message in Modal -->
                        <div v-if="showSuccessInModal" class="mb-4 rounded-lg border border-green-300 bg-green-50 p-4">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path
                                            fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                            clip-rule="evenodd"
                                        />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-green-800">{{ resultMessage }}</p>
                                </div>
                            </div>
                            <div class="mt-4 flex justify-end">
                                <Button variant="outline" @click="closeModal">Close</Button>
                            </div>
                        </div>

                        <!-- Form -->
                        <form v-else @submit.prevent="submitForm" class="space-y-4">
                            <div>
                                <input v-model="form.name" type="text" placeholder="Name" class="input w-full" :class="{ error: form.errors.name }" />
                                <p v-if="form.errors.name" class="text-sm text-red-600">
                                    {{ form.errors.name }}
                                </p>
                            </div>

                            <div>
                                <input
                                    v-model="form.email"
                                    type="email"
                                    placeholder="Email"
                                    class="input w-full"
                                    :class="{ error: form.errors.email }"
                                />
                                <p v-if="form.errors.email" class="text-sm text-red-600">
                                    {{ form.errors.email }}
                                </p>
                            </div>

                            <div>
                                <input
                                    v-model="form.nationalId"
                                    type="text"
                                    placeholder="National ID"
                                    class="input w-full"
                                    :class="{ error: form.errors.nationalId }"
                                />
                                <p v-if="form.errors.nationalId" class="text-sm text-red-600">
                                    {{ form.errors.nationalId }}
                                </p>
                            </div>

                            <div v-if="formMode === 'create' || form.password">
                                <input
                                    v-model="form.password"
                                    type="password"
                                    placeholder="Password"
                                    class="input w-full"
                                    :class="{ error: form.errors.password }"
                                />
                                <p v-if="form.errors.password" class="text-sm text-red-600">
                                    {{ form.errors.password }}
                                </p>
                            </div>

                            <div v-if="formMode === 'create' || form.password">
                                <input
                                    v-model="verifyPassword"
                                    type="password"
                                    placeholder="Verify Password"
                                    class="input w-full"
                                    :class="{ error: form.errors.verifyPassword }"
                                />
                                <p v-if="form.errors.verifyPassword" class="text-sm text-red-600">
                                    {{ form.errors.verifyPassword }}
                                </p>
                            </div>

                            <div>
                                <label class="mb-1 block text-sm font-medium">Image (optional)</label>
                                <img
                                    v-if="formMode === 'edit' && !form.image"
                                    :src="receptionists.data.find((m) => m.id === form.id)?.image"
                                    class="mb-2 h-20 w-20 rounded-full object-cover"
                                />
                                <input type="file" @change="(e) => (form.image = e.target.files[0] || null)" class="input w-full" accept="image/*" />
                                <p v-if="form.errors.image" class="text-sm text-red-600">
                                    {{ form.errors.image }}
                                </p>
                            </div>

                            <div class="flex justify-end gap-3 pt-4">
                                <Button type="button" variant="outline" @click="closeModal">Cancel</Button>
                                <Button type="submit" variant="default" :disabled="form.processing">
                                    {{
                                        form.processing
                                            ? formMode === 'create'
                                                ? 'Creating...'
                                                : 'Updating...'
                                            : formMode === 'create'
                                              ? 'Create'
                                              : 'Update'
                                    }}
                                </Button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.container {
    max-width: 1200px;
}
.input {
    padding: 0.5rem;
    border: 1px solid #cbd5e0;
    border-radius: 0.375rem;
    transition: border 0.2s;
    width: 100%;
}
.input:focus {
    border-color: #3b82f6;
    outline: none;
}
.input.error {
    border-color: #ef4444;
}
</style>
