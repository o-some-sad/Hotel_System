<template>
  <Dialog :open="show" @update:open="$emit('update:show', $event)">
    <DialogContent class="sm:max-w-[425px]">
      <DialogHeader>
        <DialogTitle class="flex items-center text-red-600">
          <AlertTriangleIcon class="h-5 w-5 mr-2" />
          Confirm Deletion
        </DialogTitle>
        <DialogDescription>
          Are you sure you want to delete this floor? This action cannot be undone.
        </DialogDescription>
      </DialogHeader>
      
      <div class="py-4">
        <div v-if="floor" class="bg-muted p-3 rounded-md">
          <p><strong>Floor:</strong> {{ floor.name }}</p>
          <p><strong>Number:</strong> {{ floor.number }}</p>
          <p v-if="floor.rooms_count > 0" class="text-red-500 mt-2">
            This floor has {{ floor.rooms_count }} rooms associated with it. Please remove or reassign these rooms before deleting.
          </p>
        </div>
      </div>
      
      <DialogFooter>
        <Button type="button" variant="outline" @click="$emit('update:show', false)">
          Cancel
        </Button>
        <Button 
          type="button" 
          variant="destructive" 
          :disabled="isDisabled"
          @click="deleteFloor"
        >
          Delete Floor
        </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>

<script setup>
import { computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import { 
  Dialog, DialogContent, DialogHeader, DialogTitle, 
  DialogFooter, DialogDescription 
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { AlertTriangle as AlertTriangleIcon } from 'lucide-vue-next';

const props = defineProps({
  show: {
    type: Boolean,
    default: false
  },
  floor: Object,
});

const emit = defineEmits(['update:show', 'deleted']);

// Check if delete button should be disabled
const isDisabled = computed(() => {
  if (!props.floor) return true;
  // Disable if floor has rooms
  return props.floor.rooms_count > 0;
});

// Get current auth guard
const page = usePage();
const isAdmin = computed(() => 
  page.props.auth?.guard === 'admin' || 
  page.props.isAdmin === true
);

// Delete the floor using Inertia instead of axios
const deleteFloor = () => {
  if (!props.floor || isDisabled.value) return;
  
  // Use Inertia.delete with preserveState: false to allow page refresh
  router.delete(route(
    isAdmin.value 
      ? 'admin.floors.destroy' 
      : 'manager.floors.destroy', 
    props.floor.id
  ), {
    preserveScroll: true,
    preserveState: false,  // Allow the page to refresh with new data and flash messages
    onSuccess: () => {
      // Just close the dialog - the flash message will be handled by the layout
      emit('update:show', false);
    },
    onError: (errors) => {
      console.error('Error deleting floor:', errors);
    }
  });
};
</script>