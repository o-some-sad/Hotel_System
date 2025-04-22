<script setup>
import { ref, computed } from 'vue'
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

// Columns
const columns = [
  { id: 'image', label: 'Profile' },
  { id: 'name', label: 'Name' },
  { id: 'email', label: 'Virtual Email' },
  { id: 'actual_email', label: 'Real Email' },
  { id: 'nationalId', label: 'National ID' },
  { id: 'actions', label: 'Actions' },
]

// Modal controls
const openFormModal = (manager = null) => {
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
    form.image = null
  } else {
    formMode.value = 'create'
  }

  showFormModal.value = true
}

const closeModal = () => {
  showFormModal.value = false
  form.reset()
  verifyPassword.value = ''
  resultMessage.value = ''
}

// Validation helpers
const validateForm = () => {
  form.clearErrors()
  let isValid = true

  if (!/^[A-Za-z ]{3,30}$/.test(form.name)) {
    form.errors.name = 'Name must be 3–30 letters and spaces only.'
    isValid = false
  }

  if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email)) {
    form.errors.email = 'Email is not valid.'
    isValid = false
  }

  if (!/^\d{14}$/.test(form.nationalId)) {
    form.errors.nationalId = 'National ID must be exactly 14 digits.'
    isValid = false
  }

  if (formMode.value === 'create' || form.password) {
    if (form.password.length < 6) {
      form.errors.password = 'Password must be at least 6 characters.'
      isValid = false
    }
    if (form.password !== verifyPassword.value) {
      form.errors.verifyPassword = 'Passwords do not match.'
      isValid = false
    }
  }

  return isValid
}

// Submit logic
const submitForm = () => {
  if (!validateForm()) return

  const formData = new FormData()

  formData.append('name', form.name)
  formData.append('email', form.email)
  formData.append('nationalId', form.nationalId)

  if (form.password) {
    formData.append('password', form.password)
  }

  if (form.image) {
    formData.append('image', form.image)
  }

  if (formMode.value === 'edit') {
    formData.append('_method', 'PATCH')
  }

  const options = {
    preserveScroll: true,
    forceFormData: true,
    onSuccess: (page) => {
      resultMessage.value = page.props.flash?.success || 'Operation completed successfully.'
    }
  }

  if (formMode.value === 'create') {
    router.post('/managers', formData, options)
  } else {
    router.post(`/managers/${form.id}`, formData, options)
  }
}

const deleteManager = mgr => {
  if (confirm(`Are you sure you want to delete this manager?\nID: ${mgr.id}\nName: ${mgr.name}\nEmail: ${mgr.email}`)) {
    router.delete(`/managers/${mgr.id}`, {
      onSuccess: (page) => {
        alert(page.props.flash?.success || 'Manager deleted successfully.')
      },
      onError: (error) => {
        alert(error.response.data.message || 'An error occurred while deleting the manager.')
      }
    })
  }
}
</script>

<template>
  <div class="container mx-auto py-6 px-4">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold">Manager Management</h1>
      <Button @click="openFormModal()" v-if="isAdmin">Create Manager</Button>
    </div>

    <Table>
      <TableCaption>A list of all managers.</TableCaption>
      <TableHeader>
        <TableRow>
          <TableHead v-for="col in columns" :key="col.id">{{ col.label }}</TableHead>
        </TableRow>
      </TableHeader>
      <TableBody>
        <TableRow v-for="mgr in managers.data" :key="mgr.id" class="hover:bg-muted/50">
          <TableCell>
            <img :src="mgr.image" class="h-10 w-10 rounded-full object-cover" />
          </TableCell>
          <TableCell>{{ mgr.name }}</TableCell>
          <TableCell>{{ mgr.email }}</TableCell>
          <TableCell>{{ mgr.actual_email }}</TableCell>
          <TableCell>{{ mgr.nationalId }}</TableCell>
          <TableCell>
            <div class="flex space-x-2">
              <Button @click="openFormModal(mgr)" v-if="isAdmin || mgr.id === id" size="sm" variant="outline">
                Edit
              </Button>
              <Button @click="deleteManager(mgr)" v-if="isAdmin" size="sm" variant="destructive">
                Delete
              </Button>
            </div>
          </TableCell>
        </TableRow>
      </TableBody>
    </Table>

    <!-- Pagination -->
    <nav class="mt-6 flex justify-center" aria-label="Page navigation">
      <ul class="inline-flex items-center -space-x-px">
        <li v-for="link in managers.links" :key="link.label">
          <Link
            v-if="link.url"
            :href="link.url"
            v-html="link.label"
            :class="[
              'px-3 py-1 border rounded mx-1',
              link.active ? 'bg-blue-500 text-white' : 'bg-white text-blue-600'
            ]"
          />
          <span
            v-else
            v-html="link.label"
            class="px-3 py-1 border rounded mx-1 text-gray-500 cursor-default"
          />
        </li>
      </ul>
    </nav>

    <!-- Modal -->
    <div v-if="showFormModal" class="modal-overlay">
      <div class="modal-content max-w-lg w-full">
        <h2 class="text-xl font-semibold mb-4">
          {{ formMode === 'create' ? 'Create Manager' : 'Edit Manager' }}
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
</template>

<style scoped>
.container {
  max-width: 1200px;
}
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.4);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 50;
}
.modal-content {
  background: white;
  padding: 2rem;
  border-radius: 0.5rem;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
}
.input {
  padding: 0.5rem;
  border: 1px solid #cbd5e0;
  border-radius: 0.375rem;
  transition: border 0.2s;
}
.input:focus {
  border-color: #3b82f6;
  outline: none;
}
.input.error {
  border-color: #ef4444;
}
</style>
