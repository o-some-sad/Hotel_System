<template>
    <div class="floor-list">
      <h2>Floor List</h2>
      <div class="floor-cards">
        <FloorCard
          v-for="floor in floors"
          :key="floor.id"
          :floor="floor"
        />
      </div>
    </div>
  </template>
  
  <script>
  import FloorCard from '@/components/Floor/FloorCard.vue';
  
  export default {
    name: 'FloorList',
    components: {
      FloorCard,
    },
    data() {
      return {
        floors: [],
      };
    },
    mounted() {
      this.fetchFloors();
    },
    methods: {
      fetchFloors() {
        // Fetch the list of floors from the API
        // This is a placeholder for the actual API call
        this.floors = [
          { id: 1, name: 'First Floor', description: 'Description of first floor' },
          { id: 2, name: 'Second Floor', description: 'Description of second floor' },
          // Add more floors as needed
        ];
      },
    },
  };
  </script>
  
  <style scoped>
  .floor-list {
    padding: 20px;
  }
  
  .floor-cards {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
  }
  </style>