<template>
  <Dialog :open="show" @update:open="$emit('update:show', $event)">
    <DialogContent class="sm:max-w-[425px]">
      <DialogHeader>
        <DialogTitle>Edit Floor</DialogTitle>
        <DialogDescription>
          Update floor information. Floor number cannot be changed.
        </DialogDescription>
      </DialogHeader>
      
      <form v-if="floor" @submit.prevent="submitForm">
        <div class="grid gap-4 py-4">
          <div class="grid gap-2">
            <Label for="name">Floor Name</Label>
            <Input 
              id="name" 
              v-model="form.name" 
              placeholder="e.g. Executive Floor"
              :error="form.errors.name"
            />
            <InputError :message="form.errors.name" />
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
          
          <div v-if="isAdmin" class="rounded-md bg-muted p-4">
            <div class="text-sm flex items-center">
              <InfoIcon class="h-4 w-4 mr-2 text-muted-foreground" />
              <span>Created by {{ floor.created_by.name }} on {{ formatDate(floor.created_at) }}</span>
            </div>
          </div>
        </div>
        
        <DialogFooter>
          <Button type="button" variant="outline" @click="$emit('update:show', false)">
            Cancel
          </Button>
          <Button type="submit" :disabled="form.processing">
            Update Floor
          </Button>
        </DialogFooter>
      </form>
    </DialogContent>
  </Dialog>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { 
  Dialog, DialogContent, DialogHeader, DialogTitle, 
  DialogFooter, DialogDescription 
} from '@/Components/ui/dialog'
import { Button } from '@/Components/ui/button'
import { Label } from '@/Components/ui/label'
import { Input } from '@/Components/ui/input'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select'
import { InfoIcon } from 'lucide-vue-next'
import InputError from '@/Components/InputError.vue'

const props = defineProps({
  show: {
    type: Boolean,
    default: false
  },
  floor: Object,
  isAdmin: Boolean,
  managers: Array
})

const emit = defineEmits(['update:show', 'updated'])

const form = useForm({
  name: '',
  manager_id: null
})

// Watch for floor changes to update form - fix the manager_id initialization
watch(() => props.floor, (newFloor) => {
  if (newFloor) {
    form.name = newFloor.name;
    
    // Only set manager_id if created by a manager
    if (newFloor.created_by_type === 'App\\Models\\Manager') {
      form.manager_id = newFloor.created_by_id;
    } else {
      // If created by admin, default to null (Assign to me option)
      form.manager_id = null;
    }
    
    console.log('Form initialized with floor data:', form.data());
  }
}, { immediate: true });

const formatDate = (dateString) => {
  if (!dateString) return '';
  const date = new Date(dateString);
  return date.toLocaleDateString('en-US', { 
    year: 'numeric', 
    month: 'short', 
    day: 'numeric' 
  });
};

// Update submit form to handle successful update better
const submitForm = () => {
  if (!props.floor) return;
  
  const routeName = props.isAdmin ? 'admin.floors.update' : 'manager.floors.update';
  
  console.log('Submitting floor update:', form.data());
  
  form.patch(route(routeName, props.floor.id), {
    preserveScroll: true,
    onSuccess: () => {
      console.log('Floor updated successfully');
      emit('update:show', false);
      emit('updated');
    },
    onError: (errors) => {
      console.error('Update errors:', errors);
    }
  });
};
</script>