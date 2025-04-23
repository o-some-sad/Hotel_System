<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Head, useForm } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';
import { ref } from 'vue';

interface Props {
    countries: string[];
}
const props = defineProps<Props>();

const imagePreview = ref<string | null>(null);

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    nationalId: '',
    country: '',
    gender: '',
    image: null as File | null,
});

const handleImageUpload = (e: Event) => {
    const input = e.target as HTMLInputElement;
    const file = input.files?.[0];
    if (file) {
        form.image = file;
        const reader = new FileReader();
        reader.onload = (e) => {
            imagePreview.value = e.target?.result as string;
        };
        reader.readAsDataURL(file);
    }
};

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head title="Register" />
  
    <div class="flex min-h-screen bg-gray-100 font-[Inter]">
      <!-- Registration Form (Now on the left) -->
      <div class="flex w-full flex-col justify-center px-8 py-16 lg:w-1/2 bg-white">
        <div class="mx-auto w-full max-w-md space-y-10">
          <div class="text-center">
            <h1 class="text-4xl font-extrabold text-blue-800">Create an account</h1>
            <p class="text-sm text-gray-500 mt-1">Enter your details below to register</p>
          </div>
  
          <form @submit.prevent="submit" class="space-y-6">
            <div class="grid gap-4">
              <div>
                <Label for="name">Name</Label>
                <Input id="name" type="text" required v-model="form.name" />
                <InputError :message="form.errors.name" />
              </div>
  
              <div>
                <Label for="email">Email</Label>
                <Input id="email" type="email" required v-model="form.email" />
                <InputError :message="form.errors.email" />
              </div>
  
              <div>
                <Label for="nationalId">National ID</Label>
                <Input id="nationalId" type="text" required v-model="form.nationalId" />
                <InputError :message="form.errors.nationalId" />
              </div>
  
              <div>
                <Label for="country">Country</Label>
                <select
                  id="country"
                  v-model="form.country"
                  required
                  class="w-full border rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none"
                >
                  <option value="">Select Country</option>
                  <option v-for="country in props.countries" :key="country" :value="country">{{ country }}</option>
                </select>
                <InputError :message="form.errors.country" />
              </div>
  
              <div>
                <Label for="gender">Gender</Label>
                <select
                  id="gender"
                  v-model="form.gender"
                  required
                  class="w-full border rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none"
                >
                  <option value="">Select Gender</option>
                  <option value="male">Male</option>
                  <option value="female">Female</option>
                </select>
                <InputError :message="form.errors.gender" />
              </div>
  
              <div>
                <Label for="image">Profile Image</Label>
                <Input id="image" type="file" @input="handleImageUpload" accept="image/*" />
                <div v-if="imagePreview" class="mt-2">
                  <img :src="imagePreview" alt="Preview" class="w-24 h-24 object-cover rounded-full border" />
                </div>
                <InputError :message="form.errors.image" />
              </div>
  
              <div>
                <Label for="password">Password</Label>
                <Input id="password" type="password" required v-model="form.password" />
                <InputError :message="form.errors.password" />
              </div>
  
              <div>
                <Label for="password_confirmation">Confirm Password</Label>
                <Input id="password_confirmation" type="password" required v-model="form.password_confirmation" />
                <InputError :message="form.errors.password_confirmation" />
              </div>
            </div>
  
            <Button
              type="submit"
              class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-lg font-semibold transition disabled:opacity-50"
              :disabled="form.processing"
            >
              <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin inline-block mr-2" />
              Register
            </Button>
          </form>
  
          <p class="text-center text-sm text-gray-500">
            Already have an account?
            <TextLink :href="route('login')" class="text-blue-600 hover:underline">Log in</TextLink>
          </p>
        </div>
      </div>
  
      <!-- Image Section (Now on the right) -->
      <div class="hidden lg:flex w-1/2 relative">
        <div
          class="absolute inset-0 bg-cover bg-center"
          style="background-image: url('http://localhost:8000/storage/image.png')"
        ></div>
        <div class="absolute inset-0 bg-gradient-to-br from-black/60 to-black/30"></div>
        <div class="relative z-10 flex flex-col justify-center p-16 text-white space-y-6">
          <h2 class="text-5xl font-bold tracking-wide text-blue-400">Laralive Hotel</h2>
          <p class="text-lg max-w-md leading-relaxed">
            Where comfort meets luxury. Create your account and begin your journey.
          </p>
        </div>
      </div>
    </div>
  </template>
  
<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap');

select {
  height: 40px;
  font-family: 'Inter', sans-serif;
}
</style>
