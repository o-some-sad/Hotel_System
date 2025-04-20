<script setup>
defineProps({ clients: Array })
</script>

<template>
    <div>
        <h1>Clients</h1>
        <ul>
            <li v-for="client in clients" :key="client.id">
                {{ client.name }} ({{ client.email }})
            </li>
        </ul>
    </div>
</template>



<style scoped></style>
