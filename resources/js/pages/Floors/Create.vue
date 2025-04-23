<template>
  <Dialog :open="show" @update:open="$emit('update:show', $event)">
    <DialogContent class="sm:max-w-[425px]">
      <DialogHeader>
        <DialogTitle>Add New Floor</DialogTitle>
        <DialogDescription>
          Create a new floor for your hotel. The floor number will be automatically assigned.
        </DialogDescription>
      </DialogHeader>
      
      <form @submit.prevent="submitForm">
        <div class="grid gap-4 py-4">
          <div class="grid gap-2">
            <Label for="name">Floor Name</Label>
            <Input 
              id="name" 
              v-model="form.name" 
              placeholder="e.g. Executive Floor"
            />
            <InputError :message="form.errors.name" />
          </div>
          
          <!-- Debug information to check isAdmin and managers -->
          <div v-if="debug" class="text-xs bg-gray-100 p-2 rounded">
            Is Admin: {{ isAdmin ? 'Yes' : 'No' }}<br>
            Managers: {{ managers ? managers.length : 0 }} available
          </div>
          
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
          
          <!-- Show message when no managers are available -->
          <div v-else-if="isAdmin && (!managers || managers.length === 0)" class="text-sm text-amber-600">
            No managers available. You can still create the floor and it will be assigned to you.
          </div>
        </div>
        
        <DialogFooter>
          <Button type="button" variant="outline" @click="$emit('update:show', false)">
            Cancel
          </Button>
          <Button type="submit" :disabled="form.processing">
            Create Floor
          </Button>
        </DialogFooter>
      </form>
    </DialogContent>
  </Dialog>
</template>

<script setup>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
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
  show: {
    type: Boolean,
    default: false
  },
  isAdmin: {
    type: Boolean,
    default: false
  },
  managers: {
    type: Array,
    default: () => []
  },
  debug: {
    type: Boolean,
    default: false // Set to true temporarily for debugging
  }
})

const emit = defineEmits(['update:show', 'created'])

const form = useForm({
  name: '',
  manager_id: null
})

const submitForm = () => {
  console.log('Submitting form with data:', {
    name: form.name,
    manager_id: form.manager_id,
    isAdmin: props.isAdmin
  });
  
  const routeName = props.isAdmin ? 'admin.floors.store' : 'manager.floors.store';
  
  form.post(route(routeName), {
    onSuccess: () => {
      console.log('Floor created successfully');
      form.reset();
      emit('update:show', false);
      emit('created');
    },
    onError: (errors) => {
      console.error('Form submission errors:', errors);
    }
  });
}
</script>