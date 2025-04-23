<template>
    <div class="min-h-screen flex flex-col justify-center items-center bg-gray-100 p-4">
      <div class="bg-white rounded-lg shadow-xl p-8 max-w-md w-full">
        <div class="flex justify-center mb-6">
          <div class="bg-red-100 p-4 rounded-full">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
          </div>
        </div>
        
        <h1 class="text-2xl font-bold text-center text-red-600 mb-4">Account Restricted</h1>
        
        <div class="text-gray-700 mb-6">
          <p class="mb-4">{{ banMessage }}</p>
          <p class="text-sm text-gray-500 text-center">
            You will be redirected to the login page in <span class="font-bold">{{ countdown }}</span> seconds.
          </p>
        </div>
        
        <div class="mt-6">
          <a href="/login" class="block w-full py-2 px-4 bg-gray-800 hover:bg-gray-900 text-white font-medium rounded text-center">
            Go to Login Now
          </a>
        </div>
      </div>
    </div>
  </template>
  
  <script setup>
  import { ref, onMounted } from 'vue';
  
  const props = defineProps({
    banMessage: String,
    redirectAfter: {
      type: Number,
      default: 10
    },
    redirectTo: {
      type: String,
      default: '/login'
    }
  });
  
  const countdown = ref(props.redirectAfter);
  
  onMounted(() => {
    const timer = setInterval(() => {
      countdown.value -= 1;
      
      if (countdown.value <= 0) {
        clearInterval(timer);
        window.location.href = props.redirectTo;
      }
    }, 1000);
  });
  </script>