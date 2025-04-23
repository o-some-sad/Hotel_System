<template>
    <Dialog :open="show" @update:open="$emit('update:show', $event)">
      <DialogContent class="sm:max-w-[500px]">
        <DialogHeader>
          <DialogTitle>Edit Room</DialogTitle>
          <DialogDescription>
            Update room information. Room number cannot be changed.
          </DialogDescription>
        </DialogHeader>
        
        <form v-if="room" @submit.prevent="submitForm">
          <div class="grid gap-4 py-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="grid gap-2">
                <Label for="edit-number">Room Number</Label>
                <Input 
                  id="edit-number" 
                  :value="room.number" 
                  disabled
                  class="bg-muted"
                />
                <p class="text-xs text-muted-foreground">Room numbers cannot be changed once assigned.</p>
              </div>
              
              <div class="grid gap-2">
                <Label for="edit-capacity">Capacity</Label>
                <Input 
                  id="edit-capacity" 
                  v-model="form.capacity" 
                  type="number"
                  min="1"
                  placeholder="e.g. 2"
                />
                <InputError :message="form.errors.capacity" />
              </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="grid gap-2">
                <Label for="edit-price">Price (USD)</Label>
                <div class="relative">
                  <span class="absolute left-3 top-1/2 transform -translate-y-1/2">$</span>
                  <Input 
                    id="edit-price" 
                    v-model="form.price_in_dollars" 
                    type="number"
                    step="0.01"
                    min="0"
                    placeholder="e.g. 99.99"
                    class="pl-8"
                  />
                </div>
                <InputError :message="form.errors.price_in_dollars" />
              </div>
              
              <div class="grid gap-2">
                <Label for="edit-floor">Floor</Label>
                <Select v-model="form.floor_id">
                  <SelectTrigger id="edit-floor">
                    <SelectValue placeholder="Select floor" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem 
                      v-for="floor in floors" 
                      :key="floor.id" 
                      :value="floor.id"
                    >
                      {{ floor.name }} ({{ floor.number }})
                    </SelectItem>
                  </SelectContent>
                </Select>
                <InputError :message="form.errors.floor_id" />
              </div>
            </div>
            
            <div class="rounded-md bg-muted p-4">
              <div class="text-sm flex items-center">
                <InfoIcon class="h-4 w-4 mr-2 text-muted-foreground" />
                <span>Created by {{ room.created_by.name }} on {{ formatDate(room.created_at) }}</span>
              </div>
              <div v-if="room.reservations && room.reservations.length > 0" class="mt-2 text-sm flex items-center text-amber-600">
                <AlertTriangleIcon class="h-4 w-4 mr-2" />
                <span>This room has {{ room.reservations.length }} active reservation(s).</span>
              </div>
            </div>
          </div>
          
          <DialogFooter>
            <Button type="button" variant="outline" @click="$emit('update:show', false)">
              Cancel
            </Button>
            <Button type="submit" :disabled="form.processing">
              Update Room
            </Button>
          </DialogFooter>
        </form>
      </DialogContent>
    </Dialog>
  </template>
  
  <script setup>
  import { ref, watch, computed } from 'vue'
  import { useForm, usePage } from '@inertiajs/vue3'
  import { 
    Dialog, DialogContent, DialogHeader, DialogTitle, 
    DialogFooter, DialogDescription 
  } from '@/Components/ui/dialog'
  import { Button } from '@/Components/ui/button'
  import { Label } from '@/Components/ui/label'
  import { Input } from '@/Components/ui/input'
  import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select'
  import InputError from '@/Components/InputError.vue'
  import { Info as InfoIcon, AlertTriangle as AlertTriangleIcon } from 'lucide-vue-next'
  
  const props = defineProps({
    show: Boolean,
    room: Object,
    floors: Array,
    isAdmin: Boolean
  })
  
  const emit = defineEmits(['update:show', 'updated'])
  
  // Simple date formatter function
  const formatDate = (dateString) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { 
      year: 'numeric', 
      month: 'short', 
      day: 'numeric' 
    });
  };
  
  // Get current auth guard from page props
  const page = usePage();
  const currentGuard = computed(() => 
    page.props.auth?.guard || (page.props.isAdmin ? 'admin' : 'manager')
  );
  
  const form = useForm({
    name: '',
    capacity: '',
    price_in_dollars: '',
    floor_id: ''
  })
  
  // Fill form when room changes
  watch(() => props.room, (newRoom) => {
    if (newRoom) {
      form.name = newRoom.name || '';
      form.capacity = newRoom.capacity;
      form.price_in_dollars = newRoom.price_in_dollars;
      form.floor_id = newRoom.floor_id;
      console.log('Form initialized with room data:', form.data());
    }
  }, { immediate: true })
  
  const submitForm = () => {
    if (!props.room) return;
    
    console.log('Submitting form with data:', form.data());
    
    // Determine route based on current guard
    const routeName = currentGuard.value === 'admin' 
      ? 'admin.rooms.update' 
      : 'manager.rooms.update';
      
    form.patch(route(routeName, props.room.id), {
      preserveScroll: true,
      onSuccess: () => {
        console.log('Room updated successfully');
        emit('update:show', false);
        emit('updated');
      },
      onError: (errors) => {
        console.error('Update errors:', errors);
      }
    });
  }
  </script>