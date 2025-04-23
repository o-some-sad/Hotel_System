<template>
  <AppLayout :title="'Manage Floors'" :breadcrumbs="breadcrumbs">
    <div class="container py-6">
      <!-- Flash Messages -->
      <div v-if="$page.props.flash.success" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
        {{ $page.props.flash.success }}
      </div>

      <div v-if="$page.props.flash.error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
        {{ $page.props.flash.error }}
      </div>
      
      <!-- Header with title and add button -->
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
          <h1 class="text-2xl font-bold tracking-tight">Floor Management</h1>
          <p class="text-muted-foreground">Manage all hotel floors and their assignments</p>
        </div>
        
        <Button @click="showCreateDialog = true" class="shrink-0">
          <PlusIcon class="h-4 w-4 mr-2" />
          Add New Floor
        </Button>
      </div>
      
      <!-- Stats Row (Admin Only) -->
      <div v-if="isAdmin" class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <StatCard title="Total Floors" :value="floorStats.total" icon="Building" />
        <StatCard title="Floors With Rooms" :value="floorStats.withRooms" icon="Users" />
        <StatCard title="Empty Floors" :value="floorStats.empty" icon="Building" />
      </div>
      
      <!-- Search and Filters -->
      <div class="bg-card p-4 rounded-lg shadow-sm mb-6">
        <div class="flex flex-col md:flex-row gap-4">
          <div class="flex-1 relative">
            <SearchIcon class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground" />
            <Input 
              v-model="searchQuery" 
              placeholder="Search floors..." 
              class="pl-9"
            />
          </div>
          
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
          
          <Select v-model="viewMode" class="w-full md:w-40">
            <template #trigger>
              <SelectTrigger>
                <SelectValue />
              </SelectTrigger>
            </template>
            <SelectContent>
              <SelectItem value="table">Table View</SelectItem>
              <SelectItem value="cards">Card View</SelectItem>
            </SelectContent>
          </Select>
        </div>
      </div>
      
      <!-- Table View -->
      <div v-if="viewMode === 'table'" class="bg-card rounded-lg shadow-sm overflow-hidden">
        <Table>
          <TableHeader>
            <TableRow>
              <TableHead>Name</TableHead>
              <TableHead>Number</TableHead>
              <TableHead>Rooms</TableHead>
              <TableHead v-if="isAdmin">Manager</TableHead>
              <TableHead v-if="isAdmin">Created</TableHead>
              <TableHead class="text-right">Actions</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow v-for="floor in filteredFloors" :key="floor.id">
              <TableCell>{{ floor.name }}</TableCell>
              <TableCell>{{ floor.number }}</TableCell>
              <TableCell>
                <Badge :variant="floor.rooms_count > 0 ? 'default' : 'secondary'">
                  {{ floor.rooms_count }} rooms
                </Badge>
              </TableCell>
              <TableCell v-if="isAdmin">{{ floor.created_by.name }}</TableCell>
              <TableCell v-if="isAdmin">{{ formatDate(floor.created_at) }}</TableCell>
              <TableCell class="text-right">
                <div class="flex justify-end gap-2">
                  <Button v-if="canManage(floor)" variant="ghost" size="icon" @click="editFloor(floor)">
                    <PencilIcon class="h-4 w-4" />
                  </Button>
                  <Button v-if="canManage(floor)" variant="ghost" size="icon" @click="confirmDelete(floor)">
                    <TrashIcon class="h-4 w-4" />
                  </Button>
                </div>
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </div>
      
      <!-- Card View -->
      <div v-if="viewMode === 'cards'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <Card v-for="floor in filteredFloors" :key="floor.id">
          <CardHeader>
            <CardTitle>{{ floor.name }}</CardTitle>
            <CardDescription>Floor #{{ floor.number }}</CardDescription>
          </CardHeader>
          <CardContent>
            <div class="flex justify-between items-center">
              <Badge :variant="floor.rooms_count > 0 ? 'default' : 'secondary'">
                {{ floor.rooms_count }} rooms
              </Badge>
              
              <div v-if="isAdmin" class="text-sm text-muted-foreground">
                Manager: {{ floor.created_by.name }}
              </div>
            </div>
          </CardContent>
          <CardFooter v-if="canManage(floor)" class="flex justify-end gap-2">
            <Button variant="outline" size="sm" @click="editFloor(floor)">
              <PencilIcon class="h-4 w-4 mr-1" /> Edit
            </Button>
            <Button variant="destructive" size="sm" @click="confirmDelete(floor)">
              <TrashIcon class="h-4 w-4 mr-1" /> Delete
            </Button>
          </CardFooter>
        </Card>
      </div>
      
      <!-- Add this after the Table or Card view section -->
      <div v-if="floors.links && floors.links.length > 3" class="mt-4 flex justify-center">
        <div class="flex items-center gap-1">
          <!-- First/Prev page links -->
          <Button 
            :disabled="!floors.prev_page_url" 
            @click="changePage(floors.current_page - 1)"
            variant="outline" 
            size="icon"
          >
            <ChevronLeftIcon class="h-4 w-4" />
          </Button>
          
          <!-- Page links -->
          <div class="flex">
            <div v-for="(link, i) in floors.links" :key="i">
              <!-- Skip prev/next links (first and last items) -->
              <div v-if="i > 0 && i < floors.links.length - 1">
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
            :disabled="!floors.next_page_url" 
            @click="changePage(floors.current_page + 1)"
            variant="outline" 
            size="icon"
          >
            <ChevronRightIcon class="h-4 w-4" />
          </Button>
        </div>
      </div>

      <!-- Empty State -->
      <div v-if="filteredFloors.length === 0" class="bg-card rounded-lg shadow-sm p-8 text-center">
        <div class="flex flex-col items-center gap-2">
          <BuildingIcon class="h-12 w-12 text-muted-foreground" />
          <h3 class="text-lg font-medium">No floors found</h3>
          <p class="text-muted-foreground">Create your first floor to get started managing your hotel.</p>
          <Button @click="showCreateDialog = true" class="mt-4">
            <PlusIcon class="h-4 w-4 mr-2" />
            Add New Floor
          </Button>
        </div>
      </div>
    </div>
    
    <!-- Create Floor Dialog -->
    <CreateFloorDialog 
      v-model:show="showCreateDialog"
      :is-admin="isAdmin"
      :managers="managers"
      @created="fetchFloors"
    />
    
    <!-- Edit Floor Dialog -->
    <EditFloorDialog 
      v-model:show="showEditDialog"
      :floor="selectedFloor"
      :managers="managers" 
      :is-admin="isAdmin"
      @updated="fetchFloors"
    />
    
    <!-- Delete Confirmation Dialog -->
    <DeleteConfirmationDialog
      v-model:show="showDeleteDialog"
      :floor="selectedFloor"
      @deleted="fetchFloors"
    />
  </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { Link, usePage, router } from '@inertiajs/vue3'
import axios from 'axios'
import AppLayout from '@/Layouts/AppLayout.vue'
import CreateFloorDialog from './Create.vue'
import EditFloorDialog from './Edit.vue'
import DeleteConfirmationDialog from './DeleteConfirmationDialog.vue' // Create this file
import { 
  PlusIcon, PencilIcon, TrashIcon, BuildingIcon, 
  SearchIcon, FilterXIcon, InfoIcon, ChevronLeftIcon, ChevronRightIcon
} from 'lucide-vue-next'

// UI Component imports
import { Button } from '@/Components/ui/button'
import { Input } from '@/Components/ui/input'
import { Badge } from '@/Components/ui/badge'
import { Table, TableHeader, TableBody, TableHead, TableRow, TableCell } from '@/Components/ui/table'
import { Card, CardHeader, CardContent, CardFooter, CardTitle, CardDescription } from '@/Components/ui/card'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select'

// Add StatCard component or use direct Card components
import StatCard from '@/Components/StatCard.vue' // Create this file if needed

// Add format date function
const formatDate = (dateString) => {
  if (!dateString) return '';
  const date = new Date(dateString);
  return date.toLocaleDateString('en-US', { 
    year: 'numeric', 
    month: 'short', 
    day: 'numeric' 
  });
};

const props = defineProps({
  floors: Object,
  isAdmin: Boolean,
  userId: Number,
  userType: String,
  floorStats: Object
})

// UI state
const viewMode = ref('table')
const searchQuery = ref('')
const selectedManager = ref(null)
const showCreateDialog = ref(false)
const showEditDialog = ref(false)
const showDeleteDialog = ref(false)
const selectedFloor = ref(null)

// Fetch managers for admin
const managers = ref([])

// Improved fetchManagers function
const fetchManagers = async () => {
  if (props.isAdmin) {
    try {
      const response = await axios.get(route('admin.managers.index'));
      
      if (response.data.managers) {
        managers.value = response.data.managers;
        console.log('Managers loaded:', managers.value.length);
      } else {
        console.error('No managers array in response');
      }
    } catch (error) {
      console.error('Failed to fetch managers:', error);
      if (error.response) {
        console.error('Error response:', error.response.data);
      }
    }
  }
};

// Filter floors based on search and manager selection
const filteredFloors = computed(() => {
  // If we have pagination, use the items directly from pagination data
  const items = props.floors.data || props.floors;
  
  let filtered = [...items];
  
  // Apply client-side filters
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    filtered = filtered.filter(floor => 
      floor.name.toLowerCase().includes(query) || 
      floor.number.toLowerCase().includes(query)
    );
  }
  
  if (selectedManager.value) {
    filtered = filtered.filter(floor => 
      floor.created_by_id === selectedManager.value
    );
  }
  
  return filtered;
})

// Reset all filters
const resetFilters = () => {
  searchQuery.value = ''
  selectedManager.value = null
}

// Check if user can manage a floor
const canManage = (floor) => {
  if (props.isAdmin) return true
  return floor.created_by_id === props.userId && floor.created_by_type === props.userType
}

// Edit and delete actions
const editFloor = (floor) => {
  selectedFloor.value = floor
  showEditDialog.value = true
}

const confirmDelete = (floor) => {
  selectedFloor.value = floor
  showDeleteDialog.value = true
}

// Fetch data
const fetchFloors = () => {
  console.log('Fetching floors...');
  // Use Inertia visit for a more complete refresh
  router.visit(route(props.isAdmin ? 'admin.floors.index' : 'manager.floors.index'), {
    preserveScroll: true,
    onSuccess: () => {
      console.log('Floors reloaded successfully');
      resetFilters(); // Reset filters on successful reload
    },
    onError: (errors) => {
      console.error('Failed to reload floors:', errors);
    }
  });
}

// Pagination method
const changePage = (page) => {
  router.get(
    route(props.isAdmin ? 'admin.floors.index' : 'manager.floors.index', {
      page: page,
      search: searchQuery.value || undefined,
      manager: selectedManager.value || undefined
    }),
    {},
    { preserveScroll: true, preserveState: true }
  );
}

// Breadcrumbs
const breadcrumbs = [
  { title: 'Dashboard', href: props.isAdmin ? route('admin.dashboard') : route('manager.dashboard') },
  { title: 'Floors', href: props.isAdmin ? route('admin.floors.index') : route('manager.floors.index') }
]

// Call this immediately 
onMounted(() => {
  fetchManagers();
  console.log('Component mounted, isAdmin:', props.isAdmin);
});

// Add a watcher for filters to refresh pagination
watch([searchQuery, selectedManager], () => {
  // Implement server-side filtering by reloading with query params
  if (searchQuery.value || selectedManager.value) {
    router.get(
      route(props.isAdmin ? 'admin.floors.index' : 'manager.floors.index', {
        search: searchQuery.value || undefined,
        manager: selectedManager.value || undefined,
        page: 1 // Reset to first page when filtering
      }),
      {},
      { preserveScroll: true }
    );
  }
}, { debounce: 500 }); // Add debounce to avoid too many requests
</script>