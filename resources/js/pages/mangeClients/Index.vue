<script setup lang="ts">
import { defineProps } from 'vue';
import {
    Table,
    TableBody,
    TableCaption,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table'
import { Button } from '@/components/ui/button';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    clients: Object,
    currentUser: Object
});

// Authorization check function
const canManageClient = (client) => {
    // Admin can manage any client
    if (props.currentUser?.isAdmin) {

        return true;
    }

    // Other staff members can only manage clients they created
    return client.created_by_id === props.currentUser?.id &&
        client.created_by_type === props.currentUser?.type;
};


const columns = [
    { id: "image", label: "Profile" },
    { id: "name", label: "Name" },
    { id: "email", label: "Email" },
    { id: "nationalId", label: "National ID" },
    { id: "country", label: "Country" },
    { id: "verified_at", label: "Status" },
    { id: "actions", label: "Actions" }
];

const createClient = () => {
    router.visit('/clients/create');
}

const editClient = (clientId: number) => {
    router.visit(`/clients/${clientId}/edit`)
}

const deleteClient = (clientId: number) => {
    if (confirm('Are you sure you want to delete this client? This action cannot be undone.')) {
        router.visit(`/clients/${clientId}/delete`);
    }
}

const approveClient = (clientId: number) => {
    router.post(`/clients/${clientId}/approve`);
}

const handlePageChange = (url: string) => {
    router.visit(url);
}
</script>

<template>
    <div class="container mx-auto py-6 px-4">
        <!-- Flash Messages -->
        <div v-if="$page.props.flash?.success"
            class="mb-4 p-4 bg-green-100 border border-green-200 text-green-800 rounded-md flex items-center justify-between">
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
            class="mb-4 p-4 bg-red-100 border border-red-200 text-red-800 rounded-md flex items-center justify-between">
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
            <h1 class="text-2xl font-bold">Manage Clients</h1>
            <Button @click="createClient" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md">
                Create Client
            </Button>
        </div>

        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <Table>
                <TableCaption>A list of all clients in the system.</TableCaption>
                <TableHeader>
                    <TableRow class="bg-gray-50">
                        <TableHead v-for="column in columns" :key="column.id" class="font-medium">
                            {{ column.label }}
                        </TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-for="client in clients?.data" :key="client.id" class="hover:bg-gray-50">
                        <TableCell>
                            <img v-if="client.image" :src="`/storage/${client.image}`" alt="Profile"
                                class="w-10 h-10 rounded-full object-cover border border-gray-200" />
                            <div v-else
                                class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center border border-gray-200">
                                <span class="text-gray-500 text-xs">{{ client.name?.charAt(0) }}</span>
                            </div>
                        </TableCell>
                        <TableCell>{{ client.name }}</TableCell>
                        <TableCell>{{ client.email }}</TableCell>
                        <TableCell>{{ client.nationalId }}</TableCell>
                        <TableCell>{{ client.country }}</TableCell>
                        <TableCell>
                            <span
                                :class="client.verified_at ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'"
                                class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold">
                                {{ client.verified_at ? 'Verified' : 'Unverified' }}
                            </span>
                        </TableCell>
                        <TableCell>
                            <div class="flex space-x-2">
                                <!-- Only show Edit/Delete buttons if user has permission -->
                                <button v-if="canManageClient(client)" @click="editClient(client.id)"
                                    class="p-1 border border-gray-300 rounded-md hover:bg-gray-100">
                                    <span class="sr-only">Edit</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z" />
                                        <path d="m15 5 4 4" />
                                    </svg>
                                </button>
                                <button v-if="canManageClient(client)" @click="deleteClient(client.id)"
                                    class="p-1 border border-red-300 bg-red-50 text-red-700 rounded-md hover:bg-red-100">
                                    <span class="sr-only">Delete</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path d="M3 6h18" />
                                        <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6" />
                                        <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2" />
                                        <line x1="10" y1="11" x2="10" y2="17" />
                                        <line x1="14" y1="11" x2="14" y2="17" />
                                    </svg>
                                </button>
                                <!-- Show approve button for unverified clients (admin only) -->
                                <button v-if="!client.verified_at && currentUser?.isAdmin"
                                    @click="approveClient(client.id)"
                                    class="p-1 border border-green-300 bg-green-50 text-green-700 rounded-md hover:bg-green-100">
                                    <span class="sr-only">Approve</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path d="M20 6 9 17l-5-5" />
                                    </svg>
                                </button>
                            </div>
                        </TableCell>
                    </TableRow>
                    <!-- Empty state row -->
                    <TableRow v-if="!clients?.data || clients.data.length === 0">
                        <TableCell colspan="7" class="text-center py-8 text-gray-500">
                            <div class="flex flex-col items-center justify-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" class="text-gray-400">
                                    <circle cx="12" cy="8" r="5" />
                                    <path d="M20 21a8 8 0 0 0-16 0" />
                                </svg>
                                <span>No clients found</span>
                                <button @click="createClient"
                                    class="mt-2 px-3 py-1 border border-gray-300 text-sm rounded-md hover:bg-gray-50">
                                    Create a client
                                </button>
                            </div>
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>

        <!-- Pagination -->
        <div class="mt-6 flex justify-center gap-2" v-if="clients?.links && clients.links.length > 3">
            <!-- Previous link -->
            <button @click="clients.links[0].url && handlePageChange(clients.links[0].url)"
                class="pagination-btn prev-btn" :disabled="!clients.links[0].url">
                Previous
            </button>

            <!-- Page links -->
            <template v-for="(link, i) in clients.links" :key="i">
                <button v-if="i > 0 && i < clients.links.length - 1" @click="link.url && handlePageChange(link.url)"
                    class="pagination-btn page-num" :class="{ 'active': link.active }" v-html="link.label"
                    :disabled="!link.url">
                </button>
            </template>

            <!-- Next link -->
            <button
                @click="clients.links[clients.links.length - 1].url && handlePageChange(clients.links[clients.links.length - 1].url)"
                class="pagination-btn next-btn" :disabled="!clients.links[clients.links.length - 1].url">
                Next
            </button>
        </div>

        <!-- Page information -->
        <div class="text-sm text-gray-500 mt-2 text-center" v-if="clients?.meta">
            Showing {{ clients.meta.from }} to {{ clients.meta.to }} of {{ clients.meta.total }}
            clients
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
