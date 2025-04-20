<script setup>
import { ref, computed, onMounted } from 'vue';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table'


const columns = [
    { id: "name", label: "Name" },
    { id: "email", label: "Email" },
    { id: "nationalId", label: "National ID" },
    { id: "country", label: "Country" },
    { id: "actions", label: "Actions" }
];

const props = defineProps({ clients: Object })

// Log clients object to console
onMounted(() => {
    console.log('Clients data from controller:', props.clients.data[0]);
});

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
        <!-- Example: Show pagination links -->
        <div v-if="clients.links">
            <button v-for="link in clients.links" :key="link.label" :disabled="!link.url"
                @click="$inertia.visit(link.url)" v-html="link.label" />
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
</style>
