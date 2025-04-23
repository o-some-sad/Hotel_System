<template>
    <div class="flex items-center gap-1 justify-center my-4">
      <!-- First page -->
      <Button 
        :disabled="currentPage === 1"
        @click="$emit('change', 1)"
        variant="outline" 
        size="icon"
        class="hidden sm:flex"
      >
        <ChevronsLeftIcon class="h-4 w-4" />
      </Button>
      
      <!-- Previous page -->
      <Button 
        :disabled="currentPage === 1" 
        @click="$emit('change', currentPage - 1)"
        variant="outline" 
        size="icon"
      >
        <ChevronLeftIcon class="h-4 w-4" />
      </Button>
      
      <!-- Page numbers -->
      <div class="flex">
        <div v-for="page in displayedPages" :key="page" class="mx-1">
          <Button
            v-if="page !== '...'"
            :variant="page === currentPage ? 'default' : 'outline'"
            @click="$emit('change', page)"
            size="sm"
          >
            {{ page }}
          </Button>
          <span v-else class="mx-1 flex items-center">{{ page }}</span>
        </div>
      </div>
      
      <!-- Next page -->
      <Button 
        :disabled="currentPage === lastPage" 
        @click="$emit('change', currentPage + 1)"
        variant="outline" 
        size="icon"
      >
        <ChevronRightIcon class="h-4 w-4" />
      </Button>
      
      <!-- Last page -->
      <Button 
        :disabled="currentPage === lastPage" 
        @click="$emit('change', lastPage)"
        variant="outline" 
        size="icon"
        class="hidden sm:flex"
      >
        <ChevronsRightIcon class="h-4 w-4" />
      </Button>
    </div>
  </template>
  
  <script setup>
  import { computed } from 'vue';
  import { Button } from '@/components/ui/button';
  import { 
    ChevronLeft as ChevronLeftIcon, 
    ChevronRight as ChevronRightIcon,
    ChevronsLeft as ChevronsLeftIcon,
    ChevronsRight as ChevronsRightIcon
  } from 'lucide-vue-next';
  
  const props = defineProps({
    currentPage: {
      type: Number,
      required: true
    },
    lastPage: {
      type: Number,
      required: true
    }
  });
  
  const emit = defineEmits(['change']);
  
  // Generate array of page numbers to display
  const displayedPages = computed(() => {
    const pages = [];
    const totalDisplayed = 5; // Maximum number of pages to show
    
    if (props.lastPage <= totalDisplayed) {
      // If total pages are less than or equal to max display, show all pages
      for (let i = 1; i <= props.lastPage; i++) {
        pages.push(i);
      }
    } else {
      // Always include first page
      pages.push(1);
      
      // Add ellipsis if current page is far enough from start
      if (props.currentPage > 3) {
        pages.push('...');
      }
      
      // Add pages around current page
      const startPage = Math.max(2, props.currentPage - 1);
      const endPage = Math.min(props.lastPage - 1, props.currentPage + 1);
      
      for (let i = startPage; i <= endPage; i++) {
        pages.push(i);
      }
      
      // Add ellipsis if current page is far enough from end
      if (props.currentPage < props.lastPage - 2) {
        pages.push('...');
      }
      
      // Always include last page
      if (props.lastPage > 1) {
        pages.push(props.lastPage);
      }
    }
    
    return pages;
  });
  </script>