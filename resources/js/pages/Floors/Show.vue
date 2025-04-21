<template>
    <div class="floor-show">
      <h1>Floor Details</h1>
      <div v-if="floor">
        <h2>Floor Number: {{ floor.number }}</h2>
        <p>Description: {{ floor.description }}</p>
        <!-- Add more details as necessary -->
      </div>
      <div v-else>
        <p>Loading floor details...</p>
      </div>
    </div>
  </template>
  
  <script>
  export default {
    data() {
      return {
        floor: null,
      };
    },
    created() {
      this.fetchFloorDetails();
    },
    methods: {
      fetchFloorDetails() {
        const floorId = this.$route.params.id; // Assuming the floor ID is passed as a route parameter
        // Fetch the floor details from the API
        axios.get(`/api/floors/${floorId}`)
          .then(response => {
            this.floor = response.data;
          })
          .catch(error => {
            console.error("There was an error fetching the floor details:", error);
          });
      },
    },
  };
  </script>
  
  <style scoped>
  .floor-show {
    padding: 20px;
  }
  </style>