<script setup>

import { ref } from 'vue'
import { useForm, Link, router } from '@inertiajs/vue3'
import {
  Table,
  TableBody,
  TableCaption,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table'
import { Button } from '@/components/ui/button'
import AppLayout from '@/layouts/AppLayout.vue'

const props = defineProps({
  managers: Object,
  isAdmin: Boolean,
  id: Number,
})

// Modal and form state
const showFormModal = ref(false)
const formMode = ref('create')
const verifyPassword = ref('')
const form = useForm({
  id: null,
  name: '',
  email: '',
  nationalId: '',
  password: '',
  image: null,
})
const resultMessage = ref('')

// Table columns
const columns = [
  { id: 'image', label: 'Profile' },
  { id: 'name', label: 'Name' },
  { id: 'email', label: 'Virtual Email' },
  { id: 'actual_email', label: 'Real Email' },
  { id: 'nationalId', label: 'National ID' },
  { id: 'actions', label: 'Actions' },
]

const breadcrumbs = [
  {
    title: 'Dashboard', 
    href: props.isAdmin ? route('admin.dashboard') : route('manager.dashboard') 
  },
  { title: 'Managers', href: route('managers.index') },
]

// Open modal (create or edit)
function openFormModal(manager = null) {
  form.clearErrors()
  form.reset()
  verifyPassword.value = ''
  resultMessage.value = ''
  if (manager) {
    formMode.value = 'edit'
    form.id = manager.id
    form.name = manager.name
    form.email = manager.actual_email
    form.nationalId = manager.nationalId
    form.password = ''
  } else {
    formMode.value = 'create'
  }
  showFormModal.value = true
}

// Close modal
function closeModal() {
  showFormModal.value = false
  form.reset()
  verifyPassword.value = ''
  resultMessage.value = ''
}

// Validation
function validateForm() {
  form.clearErrors()
  let valid = true
  if (!/^[A-Za-z ]{3,30}$/.test(form.name)) {
    form.errors.name = 'Name must be 3–30 letters and spaces only.'
    valid = false
  }
  if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email)) {
    form.errors.email = 'Email is not valid.'
    valid = false
  }
  if (!/^\d{14}$/.test(form.nationalId)) {
    form.errors.nationalId = 'National ID must be exactly 14 digits.'
    valid = false
  }
  // Password validation: required on create, optional on edit
  if (formMode.value === 'create' || form.password) {
    if (form.password.length < 6) {
      form.errors.password = 'Password must be at least 6 characters.'
      valid = false
    }
    if (form.password !== verifyPassword.value) {
      form.errors.verifyPassword = 'Passwords do not match.'
      valid = false
    }
  }
  return valid
}

// Submit form
function submitForm() {
  if (!validateForm()) return
  const data = new FormData()
  data.append('name', form.name)
  data.append('email', form.email)
  data.append('nationalId', form.nationalId)
  if (form.password) {
    data.append('password', form.password)
  }
  if (form.image) {
    data.append('image', form.image)
  }
  if (formMode.value === 'edit') {
    data.append('_method', 'PATCH')
  }
  router.post(
    formMode.value === 'create' ? '/managers' : `/managers/${form.id}`,
    data,
    {
      preserveScroll: true,
      forceFormData: true,
      onSuccess: (page) => {
        resultMessage.value = page.props.flash.success || 'Operation successful.'
        showFormModal.value = false
      }
    }
  )
}

// Delete manager
function deleteManager(mgr) {
  if (!confirm(`Delete manager ${mgr.name}?`)) return
  router.delete(`/managers/${mgr.id}`, {
    onSuccess: (page) => alert(page.props.flash.success || 'Deleted.'),
    onError: (error) => alert(error.response.data.message || 'Error deleting.'),
  })
}
</script>

<template>
  <AppLayout :title="'Manage Managers'" :breadcrumbs="breadcrumbs">
    <div class="container py-6">
      <!-- Flash -->
      <div v-if="$page.props.flash.success" class="alert-success mb-4">
        {{ $page.props.flash.success }}
      </div>
      <div v-if="$page.props.flash.error" class="alert-danger mb-4">
        {{ $page.props.flash.error }}
      </div>

      <!-- Header -->
      <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Managers</h1>
        <Button @click="openFormModal()" v-if="isAdmin">Create Manager</Button>
      </div>

      <!-- Managers Table -->
      <Table>
        <TableCaption>All managers</TableCaption>
        <TableHeader>
          <TableRow>
            <TableHead v-for="col in columns" :key="col.id">{{ col.label }}</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          <TableRow v-for="mgr in managers.data" :key="mgr.id">
            <TableCell><img :src="mgr.image" class="h-10 w-10 rounded-full"/></TableCell>
            <TableCell>{{ mgr.name }}</TableCell>
            <TableCell>{{ mgr.email }}</TableCell>
            <TableCell>{{ mgr.actual_email }}</TableCell>
            <TableCell>{{ mgr.nationalId }}</TableCell>
            <TableCell>
              <div class="flex space-x-2">
                <Button size="sm" variant="outline" @click="() => openFormModal(mgr)" v-if="isAdmin || mgr.id === id">
                  Edit
                </Button>
                <Button size="sm" variant="destructive" @click="() => deleteManager(mgr)" v-if="isAdmin">
                  Delete
                </Button>
              </div>
            </TableCell>
          </TableRow>
        </TableBody>
      </Table>

      <!-- Pagination -->
      <nav class="mt-6 flex justify-center">
        <ul class="inline-flex space-x-1">
          <li v-for="link in managers.links" :key="link.label">
            <Link v-if="link.url" :href="link.url" v-html="link.label"
              :class="['px-3 py-1 border rounded', link.active ? 'bg-blue-500 text-white' : '']" />
            <span v-else v-html="link.label" class="px-3 py-1 border text-gray-500"></span>
          </li>
        </ul>
      </nav>

      <div v-if="showFormModal" class="modal-overlay">
      <div class="modal-content max-w-lg w-full">
        <h2 class="text-xl font-semibold mb-4">
          {{ formMode === 'create' ? 'Create Receptionist' : 'Edit Receptionist' }}
        </h2>

        <!-- Result Message -->
        <div v-if="resultMessage" class="text-green-700 bg-green-100 border border-green-300 p-3 rounded mb-4">
          <p>{{ resultMessage }}</p>
          <div class="text-right mt-3">
            <Button variant="outline" @click="closeModal">Close</Button>
          </div>
        </div>

        <!-- Form -->
        <form v-else @submit.prevent="submitForm" class="space-y-4" enctype="multipart/form-data">
          <div>
            <input
              v-model="form.name"
              type="text"
              placeholder="Name"
              class="input w-full"
              :class="{ 'error': form.errors.name }"
            />
            <p v-if="form.errors.name" class="text-red-600 text-sm">{{ form.errors.name }}</p>
          </div>

          <div>
            <input
              v-model="form.email"
              type="email"
              placeholder="Email"
              class="input w-full"
              :class="{ 'error': form.errors.email }"
            />
            <p v-if="form.errors.email" class="text-red-600 text-sm">{{ form.errors.email }}</p>
          </div>

          <div>
            <input
              v-model="form.nationalId"
              type="text"
              placeholder="National ID"
              class="input w-full"
              :class="{ 'error': form.errors.nationalId }"
            />
            <p v-if="form.errors.nationalId" class="text-red-600 text-sm">{{ form.errors.nationalId }}</p>
          </div>

          <div v-if="formMode === 'create' || form.password">
            <input
              v-model="form.password"
              type="password"
              placeholder="Password"
              class="input w-full"
              :class="{ 'error': form.errors.password }"
            />
            <p v-if="form.errors.password" class="text-red-600 text-sm">{{ form.errors.password }}</p>
          </div>

          <div v-if="formMode === 'create' || form.password">
            <input
              v-model="verifyPassword"
              type="password"
              placeholder="Verify Password"
              class="input w-full"
              :class="{ 'error': form.errors.verifyPassword }"
            />
            <p v-if="form.errors.verifyPassword" class="text-red-600 text-sm">{{ form.errors.verifyPassword }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium mb-1">Image (optional)</label>
            <img
              v-if="formMode === 'edit' && form.image === null"
              :src="managers.data.find(m => m.id === form.id)?.image"
              class="h-20 w-20 rounded-full object-cover mb-2"
            />
            <input type="file" @change="e => form.image = e.target.files[0]" class="input w-full"/>

            <p v-if="form.errors.image" class="text-red-600 text-sm">{{ form.errors.image }}</p>
          </div>

          <div class="flex justify-end gap-3 pt-4">
            <Button type="button" variant="outline" @click="closeModal">Cancel</Button>
            <Button type="submit" variant="default" :disabled="form.processing">
              {{ form.processing
                ? (formMode === 'create' ? 'Creating...' : 'Updating...')
                : (formMode === 'create' ? 'Create' : 'Update') }}
            </Button>
          </div>
        </form>
      </div>
    </div>
    </div>
  </AppLayout>
</template>

<style scoped>
.container { max-width: 1200px; }
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.4);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 50;
}
.modal-content {
  background: #fff;
  /* use viewport units so ‘90%’ always means 90% of the window height */
  max-height: 90vh;           
  display: flex;              
  flex-direction: column;     
  overflow: hidden;           /* hide any overflow at the wrapper level */
  border-radius: .5rem;
  box-shadow: 0 10px 25px rgba(0,0,0,0.1);
  padding: 0;                 /* we’ll push padding into the children */
}

/* keep your title/header always visible */
.modal-content > h2 {
  padding: 1rem;
  flex: 0 0 auto;
  margin: 0;
}

/* let the form fill the rest and scroll internally */
.modal-content > form {
  padding: 1rem;
  flex: 1 1 auto;
  overflow-y: auto;           /* only vertical scroll */
}

/* optional: slim scroll-bar so it’s less obtrusive */
.modal-content > form::-webkit-scrollbar {
  width: 6px;
}
.modal-content > form::-webkit-scrollbar-thumb {
  background-color: rgba(0,0,0,0.2);
  border-radius: 3px;
}

.input {
  padding: .5rem;
  border: 1px solid #cbd5e0;
  border-radius: .375rem;
  width: 100%;
}
.input:focus {
  outline: none;
  border-color: #3b82f6;
}
.alert-success {
  background: #d1fae5;
  border: 1px solid #10b981;
  color: #065f46;
  padding: .75rem;
  border-radius: .375rem;
}
.alert-danger {
  background: #fce7f3;
  border: 1px solid #db2777;
  color: #831843;
  padding: .75rem;
  border-radius: .375rem;
}
</style>