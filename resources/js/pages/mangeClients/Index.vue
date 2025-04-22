<script setup lang="ts">
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
    router.visit(`/clients/${clientId}/delete`);
}

const approveClient = (clientId: number) => {
    router.post(`/clients/${clientId}/approve`);
}

defineProps<{ clients: any }>()

const handlePageChange = (url: string) => {
    router.visit(url);
}

</script>

<template>
    <div class="p-4">
        <div class="flex justify-between items-center mb-4">
            <h1 class="text-2xl font-bold">Manage Clients</h1>
            <Button @click="createClient">Create Client</Button>
        </div>

        <Table>
            <TableCaption>A list of all clients</TableCaption>
            <TableHeader>
                <TableRow>
                    <TableHead v-for="column in columns" :key="column.id">
                        {{ column.label }}
                    </TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                <TableRow v-for="client in clients.data" :key="client.id">
                    <TableCell>
                        <img :src="`http://localhost:8000/storage/${client.image }`" alt="Profile" class="w-10 h-10 rounded-full" />
                    </TableCell>
                    <TableCell>{{ client.name }}</TableCell>
                    <TableCell>{{ client.email }}</TableCell>
                    <TableCell>{{ client.nationalId }}</TableCell>
                    <TableCell>{{ client.country }}</TableCell>
                    <TableCell>
                        <span :class="client.verified_at ? 'text-green-500' : 'text-red-500'">
                            {{ client.verified_at ? 'Verified' : 'Unverified' }}
                        </span>
                    </TableCell>
                    <TableCell>
                        <div class="flex space-x-2">
                            <Button variant="outline" @click="editClient(client.id)">Edit</Button>
                            <Button variant="destructive" @click="deleteClient(client.id)">Delete</Button>
                            <Button v-if="!client.verified_at" variant="secondary" @click="approveClient(client.id)">
                                Approve
                            </Button>
                        </div>
                    </TableCell>
                </TableRow>
            </TableBody>
        </Table>

        <div class="mt-4 flex justify-center">
            <div class="flex space-x-2">
                <Button 
                    v-for="link in clients.links" 
                    :key="link.label"
                    variant="outline"
                    :disabled="!link.url"
                    @click="handlePageChange(link.url)"
                >
                    {{ link.label }}
                </Button>
            </div>
        </div>
    </div>
</template>

<style scoped>
.container {
    max-width: 1200px;
}

/* Additional styling for the table */
:deep(.hover\:bg-muted\/50:hover) {
    background-color: rgba(0, 0, 0, 0.05);
}

/* Make sure actions buttons are properly aligned */
:deep(td:last-child) {
    text-align: right;
}

/* Add these pagination styles */
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
