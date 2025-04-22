<template>
    <AppLayout :title="'Manage Rooms'" :breadcrumbs="breadcrumbs">
      <div class="container py-6">
        <!-- Header with title and add button -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
          <div>
            <h1 class="text-2xl font-bold tracking-tight">Room Management</h1>
            <p class="text-muted-foreground">Manage all hotel rooms, capacities, and pricing</p>
          </div>
          
          <Button @click="showCreateDialog = true" class="shrink-0">
            <PlusIcon class="h-4 w-4 mr-2" />
            Add New Room
          </Button>
        </div>
        
        <!-- Stats Row (Admin Only) -->
        <div v-if="isAdmin" class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
          <StatCard title="Total Rooms" :value="roomStats.total" icon="Door" />
          <StatCard title="Reserved Rooms" :value="roomStats.reserved" icon="Calendar" />
          <StatCard title="Available Rooms" :value="roomStats.available" icon="CheckCircle" />
        </div>
        
        <!-- Search and Filters -->
        <div class="bg-card p-4 rounded-lg shadow-sm mb-6">
          <div class="flex flex-col md:flex-row gap-4">
            <div class="flex-1 relative">
              <SearchIcon class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground" />
              <Input 
                v-model="searchQuery" 
                placeholder="Search rooms..." 
                class="pl-9"
              />
            </div>
            
            <Select v-model="selectedFloor" class="w-full md:w-48">
              <template #trigger>
                <SelectTrigger>
                  <SelectValue placeholder="Filter by floor" />
                </SelectTrigger>
              </template>
              <SelectContent>
                <SelectItem :value="null">All Floors</SelectItem>
                <SelectItem v-for="floor in floors" :key="floor.id" :value="floor.id">
                  {{ floor.name }} ({{ floor.number }})
                </SelectItem>
              </SelectContent>
            </Select>
            
            <Select v-if="isAdmin" v-model="selectedManager" class="w-full md:w-48">
              <template #trigger>
                <SelectTrigger>
                  <SelectValue placeholder="Filter by manager" />
                </SelectTrigger>
              </template>
              <SelectContent>
                <SelectItem :value="null">All Managers</SelectItem>
                <SelectItem v-for="manager in managers" :key="manager.id" :value="manager.id">
                  {{ manager.name }}
                </SelectItem>
              </SelectContent>
            </Select>
            
            <div class="hidden md:flex">
              <Button variant="outline" @click="resetFilters" size="icon">
                <FilterXIcon class="h-4 w-4" />
              </Button>
            </div>
          </div>
        </div>
        
        <!-- Table View -->
        <div class="bg-card rounded-lg shadow-sm overflow-hidden">
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Room Name</TableHead>
                <TableHead>Number</TableHead>
                <TableHead>Capacity</TableHead>
                <TableHead>Price</TableHead>
                <TableHead>Floor</TableHead>
                <TableHead v-if="isAdmin">Manager</TableHead>
                <TableHead class="text-right">Actions</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow v-for="room in filteredRooms" :key="room.id">
                <TableCell>{{ room.name }}</TableCell>
                <TableCell>{{ room.number }}</TableCell>
                <TableCell>{{ room.capacity }} person<span v-if="room.capacity > 1">s</span></TableCell>
                <TableCell>${{ room.price_in_dollars }}</TableCell>
                <TableCell>{{ room.floor.name }} ({{ room.floor.number }})</TableCell>
                <TableCell v-if="isAdmin">{{ room.created_by.name }}</TableCell>
                <TableCell class="text-right">
                  <div class="flex justify-end gap-2">
                    <Button v-if="canManage(room)" variant="ghost" size="icon" @click="editRoom(room)">
                      <PencilIcon class="h-4 w-4" />
                    </Button>
                    <Button v-if="canManage(room)" variant="ghost" size="icon" @click="confirmDelete(room)">
                      <TrashIcon class="h-4 w-4" />
                    </Button>
                  </div>
                </TableCell>
              </TableRow>
              <TableRow v-if="filteredRooms.length === 0">
                <TableCell colspan="6" class="text-center py-8">
                  <div class="flex flex-col items-center gap-2">
                    <DoorIcon class="h-8 w-8 text-muted-foreground" />
                    <h3 class="text-lg font-medium">No rooms found</h3>
                    <p class="text-muted-foreground">Try adjusting your search or create a new room.</p>
                  </div>
                </TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </div>
        
        <!-- Pagination -->
        <div v-if="rooms.links && rooms.links.length > 3" class="mt-6 flex justify-center">
          <div class="flex items-center gap-1">
            <!-- First/Prev page links -->
            <Button 
              :disabled="!rooms.prev_page_url" 
              @click="changePage(rooms.current_page - 1)"
              variant="outline" 
              size="icon"
            >
              <ChevronLeftIcon class="h-4 w-4" />
            </Button>
            
            <!-- Page links -->
            <div class="flex">
              <div v-for="(link, i) in rooms.links" :key="i">
                <!-- Skip prev/next links (first and last items) -->
                <div v-if="i > 0 && i < rooms.links.length - 1">
                  <Button
                    :variant="link.active ? 'default' : 'outline'"
                    @click="changePage(link.label)"
                    class="mx-1"
                  >
                    {{ link.label }}
                  </Button>
                </div>
              </div>
            </div>
            
            <!-- Next/Last page links -->
            <Button 
              :disabled="!rooms.next_page_url" 
              @click="changePage(rooms.current_page + 1)"
              variant="outline" 
              size="icon"
            >
              <ChevronRightIcon class="h-4 w-4" />
            </Button>
          </div>
        </div>
        
        <!-- Empty State -->
        <div v-if="filteredRooms.length === 0 && !rooms.data" class="bg-card rounded-lg shadow-sm p-8 text-center">
          <div class="flex flex-col items-center gap-2">
            <DoorIcon class="h-12 w-12 text-muted-foreground" />
            <h3 class="text-lg font-medium">No rooms found</h3>
            <p class="text-muted-foreground">Create your first room to get started managing your hotel.</p>
            <Button @click="showCreateDialog = true" class="mt-4">
              <PlusIcon class="h-4 w-4 mr-2" />
              Add New Room
            </Button>
          </div>
        </div>
      </div>
      
      <!-- Create Room Dialog -->
      <CreateRoomDialog 
        v-model:show="showCreateDialog"
        :floors="floors"
        :is-admin="isAdmin"
        @created="fetchRooms"
      />
      
      <!-- Edit Room Dialog -->
      <EditRoomDialog 
        v-if="selectedRoom"
        v-model:show="showEditDialog"
        :room="selectedRoom"
        :floors="floors"
        :is-admin="isAdmin"
        @updated="fetchRooms"
      />
      
      <!-- Delete Confirmation Dialog -->
      <DeleteConfirmationDialog
        v-if="selectedRoom"
        v-model:show="showDeleteDialog"
        :room="selectedRoom"
        @deleted="fetchRooms"
      />
    </AppLayout>
  </template>
  
  <script setup>
  import { ref, computed, onMounted, watch } from 'vue'
  import { usePage, router } from '@inertiajs/vue3'
  import axios from 'axios'
  import AppLayout from '@/Layouts/AppLayout.vue'
  import StatCard from '@/Components/StatCard.vue'
  import CreateRoomDialog from './Create.vue'
  import EditRoomDialog from './Edit.vue'
  import DeleteConfirmationDialog from './Delete.vue'
  import { 
    Plus as PlusIcon, 
    Pencil as PencilIcon, 
    Trash as TrashIcon, 
    DoorOpen as DoorIcon,
    Search as SearchIcon, 
    FilterX as FilterXIcon, 
    CheckCircle as CheckCircleIcon, 
    Calendar as CalendarIcon,
    ChevronLeft as ChevronLeftIcon,
    ChevronRight as ChevronRightIcon
  } from 'lucide-vue-next'
  
  // UI Component imports
  import { Button } from '@/Components/ui/button'
  import { Input } from '@/Components/ui/input'
  import { Badge } from '@/Components/ui/badge'
  import { Table, TableHeader, TableBody, TableHead, TableRow, TableCell } from '@/Components/ui/table'
  import { Card, CardHeader, CardContent, CardFooter, CardTitle, CardDescription } from '@/Components/ui/card'
  import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select'
  
  // Get auth info directly from Inertia
  const page = usePage();
  const auth = computed(() => page.props.auth || {});
  
  // Improved isAdmin check that works with your auth structure
  const isAdmin = computed(() => {
    // Check both possible auth structures based on your app
    return auth.value.guard === 'admin' || 
           (auth.value.user && auth.value.user.guard === 'admin') ||
           page.props.isAdmin === true;
  });
  
  const props = defineProps({
    rooms: {
      type: Object,
      required: true
    },
    floors: {
      type: Array,
      required: true
    },
    userId: Number,
    userType: String,
    roomStats: Object,
    filters: Object
  });
  
  // UI state
  const searchQuery = ref(props.filters?.search || '');
  const selectedFloor = ref(props.filters?.floor || null);
  const selectedManager = ref(props.filters?.manager || null);
  const showCreateDialog = ref(false);
  const showEditDialog = ref(false);
  const showDeleteDialog = ref(false);
  const selectedRoom = ref(null);
  
  // Fetch managers for admin
  const managers = ref([]);
  
  // Improved fetchManagers function with better error handling
  const fetchManagers = async () => {
    if (isAdmin.value) {
      try {
        console.log('Fetching managers as admin...');
        const response = await axios.get(route('admin.managers.index'), {
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
          }
        });
        
        if (response.data && response.data.managers) {
          managers.value = response.data.managers;
          console.log('Managers loaded:', managers.value.length);
        } else {
          console.error('Unexpected manager data format:', response.data);
        }
      } catch (error) {
        console.error('Failed to fetch managers:', error);
        
        if (error.response) {
          console.error('Error status:', error.response.status);
          console.error('Error data:', error.response.data);
          
          // Handle auth errors
          if (error.response.status === 401 || error.response.status === 419) {
            console.log('Authentication error - refreshing page');
            window.location.reload();
          }
        }
      }
    }
  };
  
  // Filter rooms based on search, floor, and manager selection
  const filteredRooms = computed(() => {
    // If we have pagination, use the items directly from pagination data
    const items = props.rooms?.data || [];
    
    if (!items || items.length === 0) {
      return [];
    }
    
    let filtered = [...items];
    
    if (searchQuery.value) {
      const query = searchQuery.value.toLowerCase();
      filtered = filtered.filter(room => 
        room.number.toLowerCase().includes(query)
      );
    }
    
    if (selectedFloor.value) {
      filtered = filtered.filter(room => 
        room.floor_id === parseInt(selectedFloor.value)
      );
    }
    
    if (selectedManager.value) {
      filtered = filtered.filter(room => 
        room.created_by_id === parseInt(selectedManager.value)
      );
    }
    
    return filtered;
  });
  
  // Reset all filters
  const resetFilters = () => {
    searchQuery.value = '';
    selectedFloor.value = null;
    selectedManager.value = null;
  };
  
  // Check if user can manage a room
  const canManage = (room) => {
    if (isAdmin.value) return true;
    return room.created_by_id === props.userId && room.created_by_type === props.userType;
  };
  
  // Edit and delete actions
  const editRoom = (room) => {
    selectedRoom.value = room;
    showEditDialog.value = true;
  };
  
  const confirmDelete = (room) => {
    selectedRoom.value = room;
    showDeleteDialog.value = true;
  };
  
  // Fetch data with enhanced error handling
  const fetchRooms = () => {
    console.log('Fetching rooms...', isAdmin.value ? 'as admin' : 'as manager');
    
    // Determine which route to use based on auth
    const routeName = isAdmin.value ? 'admin.rooms.index' : 'manager.rooms.index';
    
    // Use Inertia visit for a more complete refresh
    router.visit(route(routeName), {
      preserveScroll: true,
      onBefore: () => {
        console.log('Before fetching rooms');
        return true;
      },
      onSuccess: () => {
        console.log('Rooms reloaded successfully');
        resetFilters(); // Reset filters on successful reload
      },
      onError: (errors) => {
        console.error('Failed to reload rooms:', errors);
        
        // Check if we need to redirect to login
        if (errors.response && (errors.response.status === 401 || errors.response.status === 419)) {
          console.log('Authentication error - redirecting to login');
          window.location.href = route('login');
        }
      }
    });
  };
  
  // Pagination method
  const changePage = (page) => {
    // Determine which route to use based on auth
    const routeName = isAdmin.value ? 'admin.rooms.index' : 'manager.rooms.index';
    
    router.get(
      route(routeName, {
        page: page,
        search: searchQuery.value || undefined,
        floor: selectedFloor.value || undefined,
        manager: selectedManager.value || undefined
      }),
      {},
      { preserveScroll: true, preserveState: true }
    );
  };
  
  // Breadcrumbs
  const breadcrumbs = computed(() => [
    { 
      title: 'Dashboard', 
      href: isAdmin.value ? route('admin.dashboard') : route('manager.dashboard') 
    },
    { 
      title: 'Rooms', 
      href: isAdmin.value ? route('admin.rooms.index') : route('manager.rooms.index') 
    }
  ]);
  
  // Add a watcher for filters
  watch([searchQuery, selectedFloor, selectedManager], () => {
    // Implement server-side filtering by reloading with query params
    if (searchQuery.value || selectedFloor.value || selectedManager.value) {
      // Determine which route to use based on auth
      const routeName = isAdmin.value ? 'admin.rooms.index' : 'manager.rooms.index';
      
      router.get(
        route(routeName, {
          search: searchQuery.value || undefined,
          floor: selectedFloor.value || undefined,
          manager: selectedManager.value || undefined,
          page: 1 // Reset to first page when filtering
        }),
        {},
        { preserveScroll: true }
      );
    }
  }, { debounce: 500 }); // Add debounce to avoid too many requests
  
  onMounted(() => {
    console.log('Room component mounted', { 
      isAdmin: isAdmin.value,
      auth: auth.value
    });
    fetchManagers();
  });
  </script>