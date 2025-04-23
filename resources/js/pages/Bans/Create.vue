<template>
    <Dialog :open="show" @update:open="$emit('update:show', $event)">
      <DialogContent class="sm:max-w-[500px]">
        <DialogHeader>
          <DialogTitle>Create New Ban</DialogTitle>
          <DialogDescription>
            Ban a user to restrict their access to the platform. This action will log out the user immediately.
          </DialogDescription>
        </DialogHeader>
        
        <form @submit.prevent="submitForm">
          <div class="grid gap-4 py-4">
            <!-- User Type Selection -->
            <div class="grid gap-2">
              <Label for="userType">User Type</Label>
              <Select v-model="form.user_type" id="userType" required>
                <SelectTrigger>
                  <SelectValue placeholder="Select user type" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="client">Client</SelectItem>
                  <SelectItem value="receptionist">Receptionist</SelectItem>
                  <SelectItem v-if="isAdmin" value="manager">Manager</SelectItem>
                </SelectContent>
              </Select>
              <p v-if="form.errors.user_type" class="text-sm text-destructive">
                {{ form.errors.user_type }}
              </p>
            </div>
            
            <!-- User Selection based on type -->
            <div class="grid gap-2">
              <Label for="userId">Select User</Label>
              <div v-if="isLoading" class="flex items-center text-sm text-muted-foreground p-2">
                <span class="animate-spin mr-2">⟳</span> Loading users...
              </div>
              <div v-else-if="!form.user_type" class="text-sm text-muted-foreground p-2">
                Select a user type first
              </div>
              <div v-else-if="availableUsers.length === 0" class="text-sm text-muted-foreground p-2">
                No users available for this type
              </div>
              <Select 
                v-else
                v-model="form.user_id" 
                id="userId" 
                required
              >
                <SelectTrigger>
                  <SelectValue placeholder="Select user to ban" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem 
                    v-for="user in availableUsers" 
                    :key="user.id" 
                    :value="user.id"
                  >
                    {{ user.name }}
                  </SelectItem>
                </SelectContent>
              </Select>
              <p v-if="form.errors.user_id" class="text-sm text-destructive">
                {{ form.errors.user_id }}
              </p>
            </div>
            
            <!-- Ban Reason -->
            <div class="grid gap-2">
              <Label for="reason">Reason</Label>
              <textarea 
                id="reason" 
                v-model="form.reason"
                placeholder="Explain why this user is being banned..."
                class="min-h-[100px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                required
              ></textarea>
              <p class="text-xs text-muted-foreground">
                Please provide a detailed explanation for this ban. This will be recorded in the ban history.
              </p>
              <p v-if="form.errors.reason" class="text-sm text-destructive">
                {{ form.errors.reason }}
              </p>
            </div>
            
            <!-- Ban Duration -->
            <div class="grid gap-2">
              <Label for="duration">Duration</Label>
              <Select v-model="form.duration" id="duration" required>
                <SelectTrigger>
                  <SelectValue placeholder="Select ban duration" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="1day">1 Day</SelectItem>
                  <SelectItem value="1week">1 Week</SelectItem>
                  <SelectItem value="1month">1 Month</SelectItem>
                  <SelectItem value="permanent">Permanent</SelectItem>
                </SelectContent>
              </Select>
              <p class="text-xs text-muted-foreground">
                <span v-if="form.duration === 'permanent'" class="text-destructive font-medium">
                  Warning: Permanent bans cannot be automatically lifted and require manual revocation.
                </span>
                <span v-else>
                  The ban will automatically expire after the selected time period.
                </span>
              </p>
              <p v-if="form.errors.duration" class="text-sm text-destructive">
                {{ form.errors.duration }}
              </p>
            </div>
          </div>
          
          <DialogFooter>
            <Button type="button" variant="outline" @click="$emit('update:show', false)">
              Cancel
            </Button>
            <Button type="submit" :disabled="form.processing">
              <Loader2Icon v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
              Create Ban
            </Button>
          </DialogFooter>
        </form>
      </DialogContent>
    </Dialog>
  </template>
  
  <script setup>
  import { ref, computed, watch } from 'vue'
  import { useForm } from '@inertiajs/vue3'
  import { 
    Dialog, DialogContent, DialogHeader, DialogTitle, 
    DialogFooter, DialogDescription 
  } from '@/components/ui/dialog'
  import { Button } from '@/components/ui/button'
  import { Label } from '@/components/ui/label'
  import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
  import { Loader2 as Loader2Icon } from 'lucide-vue-next'
  import axios from 'axios'
  
  const props = defineProps({
    show: Boolean,
    isAdmin: Boolean
  })
  
  const emit = defineEmits(['update:show', 'created'])
  
  // Form state
  const form = useForm({
    user_type: '',
    user_id: '',
    reason: '',
    duration: ''
  })
  
  // Available users based on selected type
  const clients = ref([])
  const receptionists = ref([])
  const managers = ref([])
  const isLoading = ref(false)
  
  // Computed property for available users based on selected type
  const availableUsers = computed(() => {
    switch (form.user_type) {
      case 'client':
        return clients.value
      case 'receptionist':
        return receptionists.value
      case 'manager':
        return managers.value
      default:
        return []
    }
  })
  
  // Watch for changes in user type to load appropriate users
  watch(() => form.user_type, async (newType) => {
    if (!newType) return
    
    // Reset user id when type changes
    form.user_id = ''
    
    // Load users based on type if not already loaded
    await fetchUsers(newType)
  })
  
  // Fetch users of a specific type - using Inertia routes instead of direct API calls
  const fetchUsers = async (type) => {
    if (type === 'client' && clients.value.length === 0) {
      await loadUsers(type, clients)
    } else if (type === 'receptionist' && receptionists.value.length === 0) {
      await loadUsers(type, receptionists)
    } else if (type === 'manager' && managers.value.length === 0 && props.isAdmin) {
      await loadUsers(type, managers)
    }
  }
  
  // Updated to use axios with proper routes like in the Floor component
  const loadUsers = async (type, targetRef) => {
    isLoading.value = true
    
    try {
      let url;
      
      if (type === 'manager' && props.isAdmin) {
        // For managers, use the existing route that works in your Floor component
        url = route('admin.managers.index');
      } else {
        // For other user types, we'll need equivalent routes
        const routePrefix = props.isAdmin ? 'admin' : 'manager';
        url = route(`${routePrefix}.${type}s.index`);
      }
      
      const response = await axios.get(url);
      
      console.log(`Response for ${type}:`, response.data);
      
      if (response.data) {
        // Adapt this based on your API response structure
        if (type === 'manager' && response.data.managers) {
          targetRef.value = response.data.managers;
        } else if (response.data[`${type}s`]) {
          targetRef.value = response.data[`${type}s`];
        } else if (Array.isArray(response.data)) {
          targetRef.value = response.data;
        } else {
          console.warn(`Unexpected data format for ${type}:`, response.data);
          targetRef.value = [];
        }
      }
    } catch (error) {
      console.error(`Failed to load ${type}s:`, error);
      if (error.response) {
        console.error('Error response:', error.response.data);
      }
      targetRef.value = []; // Set to empty array on error
    } finally {
      isLoading.value = false;
    }
  };
  
  // Form submission handler
  const submitForm = () => {
    const routeName = props.isAdmin ? 'admin.bans.store' : 'manager.bans.store';
    
    console.log('Submitting form with data:', {
      user_type: form.user_type,
      user_id: form.user_id,
      reason: form.reason,
      duration: form.duration,
    });
    
    form.post(route(routeName), {
      onSuccess: () => {
        console.log('Ban created successfully');
        emit('update:show', false);
        emit('created');
        form.reset();
      },
      onError: (errors) => {
        console.error('Form submission errors:', errors);
      }
    });
  };
  
  // Initialize component
  watch(() => props.show, (isShowing) => {
    if (isShowing) {
      // Reset form when dialog opens
      form.reset();
      
      // Load users only when dialog is shown
      if (form.user_type) {
        fetchUsers(form.user_type);
      }
    }
  });
  </script>