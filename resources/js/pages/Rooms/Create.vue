<template>
    <Dialog :open="show" @update:open="$emit('update:show', $event)">
      <DialogContent class="sm:max-w-[425px]">
        <DialogHeader>
          <DialogTitle>Add New Room</DialogTitle>
          <DialogDescription>
            Create a new room in your hotel with assigned floor, capacity and pricing.
          </DialogDescription>
        </DialogHeader>
        
        <form @submit.prevent="submitForm">
            <div class="grid gap-4 py-4">
            
            <div class="grid gap-2">
              <Label for="room-name">Room Name</Label>
              <Input 
                id="room-number" 
                v-model="form.name" 
                placeholder="e.g. Aqua"
              />
              <InputError :message="form.errors.name" />
            </div>
            
            <div class="grid gap-2">
              <Label for="room-number">Room Number</Label>
              <Input 
                id="room-number" 
                v-model="form.number" 
                placeholder="e.g. 101"
              />
              <InputError :message="form.errors.number" />
            </div>
            
            <div class="grid gap-2">
              <Label for="capacity">Capacity</Label>
              <Input 
                id="capacity" 
                v-model="form.capacity" 
                type="number"
                min="1" 
                placeholder="Number of guests"
              />
              <InputError :message="form.errors.capacity" />
            </div>
            
            <div class="grid gap-2">
              <Label for="price">Price per Night ($)</Label>
              <Input 
                id="price" 
                v-model="form.price_in_dollars" 
                type="number"
                min="0"
                step="0.01"
                placeholder="0.00"
              />
              <InputError :message="form.errors.price_in_dollars" />
            </div>
            
            <div class="grid gap-2">
              <Label for="floor">Floor</Label>
              <Select v-model="form.floor_id">
                <SelectTrigger id="floor" class="w-full">
                  <SelectValue placeholder="Select a floor" />
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
            
            <!-- Manager assignment (admin only) -->
            <div v-if="isAdmin && managers && managers.length > 0" class="grid gap-2">
              <Label for="manager">Assign Manager</Label>
              <Select v-model="form.manager_id">
                <SelectTrigger id="manager" class="w-full">
                  <SelectValue placeholder="Select a manager" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem :value="null">Assign to me</SelectItem>
                  <SelectItem 
                    v-for="manager in managers" 
                    :key="manager.id" 
                    :value="manager.id"
                  >
                    {{ manager.name }}
                  </SelectItem>
                </SelectContent>
              </Select>
              <InputError :message="form.errors.manager_id" />
            </div>
          </div>
          
          <DialogFooter>
            <Button type="button" variant="outline" @click="$emit('update:show', false)">
              Cancel
            </Button>
            <Button type="submit" :disabled="form.processing">
              Create Room
            </Button>
          </DialogFooter>
        </form>
      </DialogContent>
    </Dialog>
  </template>
  
  <script setup>
  import { ref, onMounted } from 'vue'
  import { useForm } from '@inertiajs/vue3'
  import axios from 'axios'
  import { 
    Dialog, DialogContent, DialogHeader, DialogTitle, 
    DialogFooter, DialogDescription 
  } from '@/components/ui/dialog'
  import { Button } from '@/components/ui/button'
  import { Label } from '@/components/ui/label'
  import { Input } from '@/components/ui/input'
  import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
  import InputError from '@/components/InputError.vue'
  
  const props = defineProps({
    show: Boolean,
    floors: Array,
    isAdmin: {
      type: Boolean,
      default: false
    }
  })
  
  const emit = defineEmits(['update:show', 'created'])
  
  // State for managers
  const managers = ref([])
  
  // Fetch managers if admin
  const fetchManagers = async () => {
    if (props.isAdmin) {
      try {
        const response = await axios.get(route('admin.managers.index'))
        if (response.data.managers) {
          managers.value = response.data.managers
        }
      } catch (error) {
        console.error('Failed to fetch managers:', error)
      }
    }
  }
  
  const form = useForm({
    name: '',
    number: '',
    capacity: 1,
    price_in_dollars: 100,
    floor_id: '',
    manager_id: null
  })
  
  const submitForm = () => {
    console.log('Submitting form with data:', form.data())
    
    const routeName = props.isAdmin ? 'admin.rooms.store' : 'manager.rooms.store'
    
    form.post(route(routeName), {
      onSuccess: () => {
        form.reset()
        emit('update:show', false)
        emit('created')
      }
    })
  }
  
  onMounted(() => {
    fetchManagers()
  })
  </script>