<script setup>
import { ref, computed } from 'vue';
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
    { id: "name", label: "Name" },
    { id: "email", label: "Email" },
    { id: "nationalId", label: "National ID" },
    { id: "country", label: "Country" },
    { id: "actions", label: "Actions" }
];

const createClient = () => {
    router.visit('/clients/create');
}

defineProps({ clients: Object })

const handlePageChange = (url) => {
    router.visit(url);
}

</script>

<template>
    <div class="container mx-auto py-6 px-4">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Client Management</h1>
            <Button @click="createClient" variant="default">Create Client</Button>
        </div>

        <Table>
            <TableCaption>A list of all clients.</TableCaption>
            <TableHeader>
                <TableRow>
                    <TableHead v-for="column in columns" :key="column.id">{{ column.label }}</TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                <TableRow v-for="client in clients.data" :key="client.id" class="hover:bg-muted/50">
                    <TableCell>{{ client.name }}</TableCell>
                    <TableCell>{{ client.email }}</TableCell>
                    <TableCell>{{ client.nationalId }}</TableCell>
                    <TableCell>{{ client.country }}</TableCell>
                    <TableCell>
                        <div class="flex space-x-2">
                            <Button variant="outline" size="sm">Edit</Button>
                            <Button variant="destructive" size="sm">Delete</Button>
                        </div>
                    </TableCell>
                </TableRow>
            </TableBody>
        </Table>
        <!-- Handle the pagination -->
        <div class="mt-6 flex justify-center gap-2" v-if="clients.links && clients.links.length > 3">
            <!-- Previous link -->
            <button @click="clients.links[0].url && handlePageChange(clients.links[0].url)"
                class="pagination-btn prev-btn" :disabled="!clients.links[0].url">
                Previous
            </button>

            <!-- Page links -->
            <template v-for="(link, i) in clients.links" :key="i">
                <button v-if="i > 0 && i < clients.links.length - 1" @click="link.url && handlePageChange(link.url)"
                    class="pagination-btn page-num" :class="{ 'active': link.active }" v-html="link.label"
                    :disabled="!link.url"></button>
            </template>

            <!-- Next link -->
            <button
                @click="clients.links[clients.links.length - 1].url && handlePageChange(clients.links[clients.links.length - 1].url)"
                class="pagination-btn next-btn" :disabled="!clients.links[clients.links.length - 1].url">
                Next
            </button>
        </div>

        <!-- Add page information -->
        <div class="text-sm text-gray-500 mt-2 text-center" v-if="clients.meta">
            Showing {{ clients.meta.from }} to {{ clients.meta.to }} of {{ clients.meta.total }} clients
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
