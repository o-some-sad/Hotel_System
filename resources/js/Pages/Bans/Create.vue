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
              <Select 
                v-model="form.user_id" 
                id="userId" 
                :disabled="!form.user_type"
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
                    {{ user.name }} ({{ user.email }})
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
              <Textarea 
                id="reason" 
                v-model="form.reason"
                placeholder="Explain why this user is being banned..."
                class="min-h-[100px]"
                required
              />
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
  } from '@/Components/ui/dialog'
  import { Button } from '@/Components/ui/button'
  import { Label } from '@/Components/ui/label'
  //import { Textarea } from '@/Components/ui/textarea'
  import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select'
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
  
  // Fetch users of a specific type
  const fetchUsers = async (type) => {
    if (type === 'client' && clients.value.length === 0) {
      await loadUsers(type, clients)
    } else if (type === 'receptionist' && receptionists.value.length === 0) {
      await loadUsers(type, receptionists)
    } else if (type === 'manager' && managers.value.length === 0 && props.isAdmin) {
      await loadUsers(type, managers)
    }
  }
  
  // Helper to load users from API
  const loadUsers = async (type, targetRef) => {
    isLoading.value = true
    
    try {
      const route = props.isAdmin ? 'admin' : 'manager'
      const response = await axios.get(`/api/${route}/users/${type}`)
      
      if (response.data && response.data.users) {
        targetRef.value = response.data.users
      }
    } catch (error) {
      console.error(`Failed to load ${type}s:`, error)
    } finally {
      isLoading.value = false
    }
  }
  
  // Form submission handler
  const submitForm = () => {
    const route = props.isAdmin ? 'admin.bans.store' : 'manager.bans.store'
    
    form.post(route, {
      onSuccess: () => {
        emit('update:show', false)
        emit('created')
        form.reset()
      }
    })
  }
  
  // Load all user types when component is mounted
  if (props.show) {
    fetchUsers('client')
    fetchUsers('receptionist')
    if (props.isAdmin) {
      fetchUsers('manager')
    }
  }
  </script>