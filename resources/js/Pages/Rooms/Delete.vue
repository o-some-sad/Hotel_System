<template>
    <Dialog :open="show" @update:open="$emit('update:show', $event)">
      <DialogContent class="sm:max-w-[425px]">
        <DialogHeader>
          <DialogTitle>Delete Room</DialogTitle>
          <DialogDescription>
            Are you sure you want to delete this room? This action cannot be undone.
          </DialogDescription>
        </DialogHeader>
        
        <div v-if="room" class="py-4">
          <div class="flex items-center gap-2 mb-4 text-destructive">
            <AlertTriangleIcon class="h-5 w-5" />
            <span class="font-medium">Warning: This will permanently delete room {{ room.number }}</span>
          </div>
          
          <div v-if="room.reservations && room.reservations.length > 0" class="bg-amber-50 text-amber-800 p-3 rounded-md mb-4">
            <p class="font-medium">This room has active reservations.</p>
            <p class="text-sm">You cannot delete a room with reservations. Please cancel all reservations first.</p>
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
            @click="deleteRoom"
          >
            Delete Room
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
  } from '@/Components/ui/dialog';
  import { Button } from '@/Components/ui/button';
  import { AlertTriangle as AlertTriangleIcon } from 'lucide-vue-next';
  
  const props = defineProps({
    show: Boolean,
    room: Object,
  });
  
  const emit = defineEmits(['update:show', 'deleted']);
  
  // Check if delete button should be disabled
  const isDisabled = computed(() => {
    if (!props.room) return true;
    // Disable if room has reservations
    return props.room.reservations && props.room.reservations.length > 0;
  });
  
  // Get current auth guard
  const page = usePage();
  const isAdmin = computed(() => 
    page.props.auth?.guard === 'admin' || 
    page.props.isAdmin === true
  );
  
  // Delete the room
  const deleteRoom = () => {
    if (!props.room || isDisabled.value) return;
    
    // Use correct route based on authentication
    const routeName = isAdmin.value ? 'admin.rooms.destroy' : 'manager.rooms.destroy';
    
    // Use form-based delete instead of direct router.delete 
    router.delete(route(routeName, props.room.id), {
      preserveScroll: true,
      onBefore: () => {
        console.log("Deleting room:", props.room.id, "using route:", routeName);
        return true;
      },
      onSuccess: () => {
        console.log("Room deleted successfully");
        emit('update:show', false);
        emit('deleted');
      },
      onError: (errors) => {
        console.error('Delete error:', errors);
        
        if (errors.status === 419 || errors.status === 401) {
          alert('Your session has expired. Please refresh the page and try again.');
          window.location.reload();
        } else {
          alert(`Failed to delete room: ${errors.message || 'Unknown error'}`);
        }
      }
    });
  };
  </script>