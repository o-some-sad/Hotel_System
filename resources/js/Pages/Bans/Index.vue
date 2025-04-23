<template>
    <AppLayout :title="'Ban Management'" :breadcrumbs="breadcrumbs">
      <div class="container py-6">
        <!-- Header with title and add button -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
          <div>
            <h1 class="text-2xl font-bold tracking-tight">Ban Management</h1>
            <p class="text-muted-foreground">Manage user bans across the platform</p>
          </div>
          
          <Button @click="showCreateDialog = true" class="shrink-0">
            <PlusIcon class="h-4 w-4 mr-2" />
            Create New Ban
          </Button>
        </div>
        
        <!-- Stats Row (Admin Only) -->
        <div v-if="isAdmin" class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
          <StatCard title="Active Bans" :value="banStats.active" icon="Shield" variant="destructive" />
          <StatCard title="Expired Bans" :value="banStats.expired" icon="Clock" />
          <StatCard title="Total Bans" :value="banStats.total" icon="Users" />
        </div>
        
        <!-- Search and Filters -->
        <div class="bg-card p-4 rounded-lg shadow-sm mb-6">
          <div class="flex flex-col md:flex-row gap-4">
            <div class="flex-1 relative">
              <SearchIcon class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground" />
              <Input 
                v-model="searchQuery" 
                placeholder="Search bans by user or reason..." 
                class="pl-9"
              />
            </div>
            
            <Select v-model="selectedStatus" class="w-full md:w-48">
              <template #trigger>
                <SelectTrigger>
                  <SelectValue placeholder="Filter by status" />
                </SelectTrigger>
              </template>
              <SelectContent>
                <SelectItem :value="null">All Statuses</SelectItem>
                <SelectItem value="active">Active</SelectItem>
                <SelectItem value="permanent">Permanent</SelectItem>
                <SelectItem value="expired">Expired</SelectItem>
                <SelectItem value="revoked">Revoked</SelectItem>
              </SelectContent>
            </Select>
            
            <Select v-model="selectedUserType" class="w-full md:w-48">
              <template #trigger>
                <SelectTrigger>
                  <SelectValue placeholder="Filter by user type" />
                </SelectTrigger>
              </template>
              <SelectContent>
                <SelectItem :value="null">All User Types</SelectItem>
                <SelectItem value="manager">Managers</SelectItem>
                <SelectItem value="receptionist">Receptionists</SelectItem>
                <SelectItem value="client">Clients</SelectItem>
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
                <TableHead>User</TableHead>
                <TableHead>User Type</TableHead>
                <TableHead>Reason</TableHead>
                <TableHead>Banned By</TableHead>
                <TableHead>Status</TableHead>
                <TableHead>Duration</TableHead>
                <TableHead class="text-right">Actions</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow v-for="ban in filteredBans" :key="ban.id">
                <TableCell>
                  <div class="flex items-center gap-2">
                    <div class="h-8 w-8 rounded-full bg-muted flex items-center justify-center">
                      {{ ban.banned?.name?.charAt(0) || '?' }}
                    </div>
                    <div>
                      <div class="font-medium">{{ ban.banned?.name || 'Unknown User' }}</div>
                      <div class="text-xs text-muted-foreground">{{ ban.banned?.email }}</div>
                    </div>
                  </div>
                </TableCell>
                <TableCell>
                  <Badge :variant="getUserTypeBadgeVariant(ban.banned_type)">
                    {{ formatUserType(ban.banned_type) }}
                  </Badge>
                </TableCell>
                <TableCell>
                  <div class="max-w-xs truncate" :title="ban.reason">
                    {{ ban.reason }}
                  </div>
                </TableCell>
                <TableCell>
                  {{ ban.banned_by?.name || 'Unknown' }}
                </TableCell>
                <TableCell>
                  <Badge :variant="getBanStatusVariant(ban)">
                    {{ getBanStatus(ban) }}
                  </Badge>
                </TableCell>
                <TableCell>
                  <div v-if="ban.is_permanent">Permanent</div>
                  <div v-else-if="ban.expires_at">
                    <div>{{ formatDate(ban.expires_at) }}</div>
                    <div class="text-xs text-muted-foreground">{{ formatTimeAgo(ban.expires_at) }}</div>
                  </div>
                  <div v-else>-</div>
                </TableCell>
                <TableCell class="text-right">
                  <div class="flex justify-end gap-2">
                    <Button 
                      v-if="canManage(ban) && !ban.deleted_at && getBanStatus(ban) !== 'Expired'"
                      variant="destructive" 
                      size="sm"
                      @click="revokeBan(ban)"
                    >
                      Revoke
                    </Button>
                  </div>
                </TableCell>
              </TableRow>
              <TableRow v-if="filteredBans.length === 0">
                <TableCell colspan="7" class="text-center py-8">
                  <div class="flex flex-col items-center gap-2">
                    <ShieldIcon class="h-8 w-8 text-muted-foreground" />
                    <h3 class="text-lg font-medium">No bans found</h3>
                    <p class="text-muted-foreground">Try adjusting your search or create a new ban.</p>
                  </div>
                </TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </div>
        
        <!-- Pagination -->
        <div v-if="bans.links && bans.links.length > 3" class="mt-6 flex justify-center">
          <div class="flex items-center gap-1">
            <!-- Previous page button -->
            <Button 
              :disabled="!bans.prev_page_url" 
              @click="changePage(bans.current_page - 1)"
              variant="outline" 
              size="icon"
            >
              <ChevronLeftIcon class="h-4 w-4" />
            </Button>
            
            <!-- Page links -->
            <div class="flex">
              <div v-for="(link, i) in bans.links" :key="i">
                <!-- Skip prev/next links (first and last items) -->
                <div v-if="i > 0 && i < bans.links.length - 1">
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
            
            <!-- Next page button -->
            <Button 
              :disabled="!bans.next_page_url" 
              @click="changePage(bans.current_page + 1)"
              variant="outline" 
              size="icon"
            >
              <ChevronRightIcon class="h-4 w-4" />
            </Button>
          </div>
        </div>
        
        <!-- Empty State -->
        <div v-if="filteredBans.length === 0 && (!bans.data || bans.data.length === 0)" class="bg-card rounded-lg shadow-sm p-8 text-center">
          <div class="flex flex-col items-center gap-2">
            <ShieldIcon class="h-12 w-12 text-muted-foreground" />
            <h3 class="text-lg font-medium">No bans created yet</h3>
            <p class="text-muted-foreground">Ban users who violate platform policies to restrict their access.</p>
            <Button @click="showCreateDialog = true" class="mt-4">
              <PlusIcon class="h-4 w-4 mr-2" />
              Create New Ban
            </Button>
          </div>
        </div>
      </div>
      
      <!-- Create Ban Dialog -->
      <CreateBanDialog 
        v-model:show="showCreateDialog"
        :is-admin="isAdmin"
        @created="fetchBans"
      />
    </AppLayout>
  </template>
  
  <script setup>
  import { ref, computed, onMounted, watch } from 'vue'
  import { usePage, router } from '@inertiajs/vue3'
  import AppLayout from '@/Layouts/AppLayout.vue'
  import StatCard from '@/Components/StatCard.vue'
  import CreateBanDialog from './Create.vue'
  import { 
    Plus as PlusIcon, 
    Search as SearchIcon, 
    FilterX as FilterXIcon, 
    Shield as ShieldIcon,
    Clock as ClockIcon,
    Users as UsersIcon,
    ChevronLeft as ChevronLeftIcon,
    ChevronRight as ChevronRightIcon
  } from 'lucide-vue-next'
  
  // UI Component imports
  import { Button } from '@/Components/ui/button'
  import { Input } from '@/Components/ui/input'
  import { Badge } from '@/Components/ui/badge'
  import { Table, TableHeader, TableBody, TableHead, TableRow, TableCell } from '@/Components/ui/table'
  import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select'
  
  const props = defineProps({
    bans: Object,
    isAdmin: Boolean,
  })
  
  // UI state
  const searchQuery = ref('')
  const selectedStatus = ref(null)
  const selectedUserType = ref(null)
  const showCreateDialog = ref(false)
  
  // Computed statistics for admin dashboard
  const banStats = computed(() => {
    if (!props.bans || !props.bans.data) {
      return { active: 0, expired: 0, total: 0 }
    }
    
    const bans = props.bans.data
    
    return {
      active: bans.filter(ban => 
        !ban.deleted_at && (ban.is_permanent || (ban.expires_at && new Date(ban.expires_at) > new Date()))
      ).length,
      expired: bans.filter(ban => 
        !ban.deleted_at && !ban.is_permanent && ban.expires_at && new Date(ban.expires_at) <= new Date()
      ).length,
      total: props.bans.total || bans.length
    }
  })
  
  // Filter bans based on search and status
  const filteredBans = computed(() => {
    const items = props.bans?.data || []
    
    if (!items || items.length === 0) {
      return []
    }
    
    let filtered = [...items]
    
    // Apply client-side filters
    if (searchQuery.value) {
      const query = searchQuery.value.toLowerCase()
      filtered = filtered.filter(ban => 
        (ban.banned?.name && ban.banned.name.toLowerCase().includes(query)) || 
        (ban.banned?.email && ban.banned.email.toLowerCase().includes(query)) || 
        (ban.reason && ban.reason.toLowerCase().includes(query))
      )
    }
    
    if (selectedStatus.value) {
      filtered = filtered.filter(ban => {
        const status = getBanStatus(ban).toLowerCase()
        return status === selectedStatus.value.toLowerCase()
      })
    }
    
    if (selectedUserType.value) {
      filtered = filtered.filter(ban => {
        const userType = formatUserType(ban.banned_type).toLowerCase()
        return userType === selectedUserType.value.toLowerCase()
      })
    }
    
    return filtered
  })
  
  // Reset all filters
  const resetFilters = () => {
    searchQuery.value = ''
    selectedStatus.value = null
    selectedUserType.value = null
  }
  
  // Helper method to get user type from banned_type string
  const formatUserType = (bannedType) => {
    if (!bannedType) return 'Unknown'
    
    const parts = bannedType.split('\\')
    return parts[parts.length - 1]
  }
  
  // Helper to get appropriate badge variant for user type
  const getUserTypeBadgeVariant = (bannedType) => {
    const userType = formatUserType(bannedType)
    
    switch(userType.toLowerCase()) {
      case 'manager':
        return 'default'
      case 'receptionist':
        return 'secondary'
      case 'client':
        return 'outline'
      default:
        return 'outline'
    }
  }
  
  // Get ban status based on properties
  const getBanStatus = (ban) => {
    if (ban.deleted_at) return 'Revoked'
    if (ban.is_permanent) return 'Permanent'
    if (!ban.expires_at) return 'Indefinite'
    
    return new Date(ban.expires_at) > new Date() ? 'Active' : 'Expired'
  }
  
  // Helper to get appropriate badge variant for ban status
  const getBanStatusVariant = (ban) => {
    const status = getBanStatus(ban)
    
    switch(status) {
      case 'Active':
      case 'Permanent':
        return 'destructive'
      case 'Expired':
        return 'outline'
      case 'Revoked':
        return 'success'
      default:
        return 'secondary'
    }
  }
  
  // Format date in a user-friendly format
  const formatDate = (dateString) => {
    if (!dateString) return ''
    
    const date = new Date(dateString)
    return date.toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'short',
      day: 'numeric'
    })
  }
  
  // Format relative time (e.g., "2 days ago", "in 3 hours")
  const formatTimeAgo = (dateString) => {
    if (!dateString) return ''
    
    const date = new Date(dateString)
    const now = new Date()
    
    // If date is in the past
    if (date < now) {
      const seconds = Math.floor((now - date) / 1000)
      if (seconds < 60) return 'Just now'
      
      const minutes = Math.floor(seconds / 60)
      if (minutes < 60) return `${minutes} minute${minutes !== 1 ? 's' : ''} ago`
      
      const hours = Math.floor(minutes / 60)
      if (hours < 24) return `${hours} hour${hours !== 1 ? 's' : ''} ago`
      
      const days = Math.floor(hours / 24)
      if (days < 30) return `${days} day${days !== 1 ? 's' : ''} ago`
      
      const months = Math.floor(days / 30)
      return `${months} month${months !== 1 ? 's' : ''} ago`
    } 
    // If date is in the future
    else {
      const seconds = Math.floor((date - now) / 1000)
      if (seconds < 60) return 'In a few seconds'
      
      const minutes = Math.floor(seconds / 60)
      if (minutes < 60) return `In ${minutes} minute${minutes !== 1 ? 's' : ''}`
      
      const hours = Math.floor(minutes / 60)
      if (hours < 24) return `In ${hours} hour${hours !== 1 ? 's' : ''}`
      
      const days = Math.floor(hours / 24)
      if (days < 30) return `In ${days} day${days !== 1 ? 's' : ''}`
      
      const months = Math.floor(days / 30)
      return `In ${months} month${months !== 1 ? 's' : ''}`
    }
  }
  
  // Check if user can manage a ban
  const canManage = (ban) => {
    // Admin can manage all bans
    if (props.isAdmin) return true
    
    // For other roles, they can only manage bans they created
    const page = usePage()
    const user = page.props.auth?.user
    
    return user && 
      ban.banned_by_id === user.id && 
      ban.banned_by_type === `App\\Models\\${user.role}`
  }
  
  // Revoke a ban
  const revokeBan = (ban) => {
    if (confirm(`Are you sure you want to revoke this ban for ${ban.banned?.name || 'this user'}?`)) {
      router.delete(route(props.isAdmin ? 'admin.bans.revoke' : 'manager.bans.revoke', ban.id), {
        onSuccess: () => {
          fetchBans()
        }
      })
    }
  }
  
  // Pagination handler
  const changePage = (page) => {
    router.get(
      route(props.isAdmin ? 'admin.bans.index' : 'manager.bans.index', {
        page: page,
        search: searchQuery.value || undefined,
        status: selectedStatus.value || undefined,
        user_type: selectedUserType.value || undefined
      }),
      {},
      { preserveScroll: true, preserveState: true }
    )
  }
  
  // Fetch bans from server
  const fetchBans = () => {
    router.visit(route(props.isAdmin ? 'admin.bans.index' : 'manager.bans.index'), {
      preserveScroll: true,
      onSuccess: () => {
        console.log('Bans reloaded successfully')
      },
      onError: (errors) => {
        console.error('Failed to reload bans:', errors)
      }
    })
  }
  
  // Breadcrumbs
  const breadcrumbs = [
    { title: 'Dashboard', href: props.isAdmin ? route('admin.dashboard') : route('manager.dashboard') },
    { title: 'Ban Management', href: props.isAdmin ? route('admin.bans.index') : route('manager.bans.index') }
  ]
  
  // Initialize component
  onMounted(() => {
    console.log('Ban management component mounted, isAdmin:', props.isAdmin)
  })
  
  // Add server-side filtering
  watch([searchQuery, selectedStatus, selectedUserType], () => {
    // Debounced server-side filtering
    router.get(
      route(props.isAdmin ? 'admin.bans.index' : 'manager.bans.index', {
        search: searchQuery.value || undefined,
        status: selectedStatus.value || undefined,
        user_type: selectedUserType.value || undefined,
        page: 1 // Reset to first page when filtering
      }),
      {},
      { preserveScroll: true }
    )
  }, { debounce: 500 })
  </script>