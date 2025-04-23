<script setup>
import { Head, useForm } from '@inertiajs/vue3';

defineProps({
  status: String,
  canResetPassword: Boolean,
});

const form = useForm({
  email: '',
  password: '',
  remember: false,
});

const submit = () => {
  form.post(route('login'), {
    onFinish: () => form.reset('password'),
  });
};
</script>

<template>
  <Head title="welcome to Laralive" />

  <div class="flex min-h-screen">
    <!-- Left Side Image -->
    <div class="hidden w-1/2 bg-cover bg-center lg:block" style="background-image: url('http://localhost:8000/storage/image.png')">
      <div class="flex h-full flex-col justify-center p-16 text-white bg-black/30">
        <h2 class="text-4xl font-bold mb-4">Travelista Tours</h2>
        <p class="text-lg max-w-md">Travel is the only purchase that enriches you in ways beyond material wealth</p>
      </div>
    </div>

    <!-- Right Side Form -->
    <div class="flex w-full flex-col justify-center px-8 py-12 lg:w-1/2">
      <div class="mx-auto w-full max-w-md space-y-8">
        <div class="text-center">
          <h1 class="text-3xl font-bold text-blue-600">Welcome</h1>
          <p class="text-gray-500">Login with Email</p>
        </div>

        <form @submit.prevent="submit" class="space-y-6">
          <div>
            <label for="email" class="block text-sm font-medium">Email</label>
            <input
              id="email"
              type="email"
              v-model="form.email"
              required
              class="mt-1 w-full rounded-lg border px-4 py-2 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
              placeholder="you@example.com"
            />
            <p v-if="form.errors.email" class="mt-1 text-sm text-red-500">{{ form.errors.email }}</p>
          </div>

          <div>
            <div class="flex justify-between items-center">
              <label for="password" class="block text-sm font-medium">Password</label>
              <a v-if="canResetPassword" :href="route('password.request')" class="text-sm text-blue-500 hover:underline">Forgot?</a>
            </div>
            <input
              id="password"
              type="password"
              v-model="form.password"
              required
              class="mt-1 w-full rounded-lg border px-4 py-2 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
              placeholder="••••••••"
            />
            <p v-if="form.errors.password" class="mt-1 text-sm text-red-500">{{ form.errors.password }}</p>
          </div>

          <div class="flex items-center space-x-2">
            <input id="remember" type="checkbox" v-model="form.remember" class="rounded border-gray-300 text-blue-600 shadow-sm" />
            <label for="remember" class="text-sm text-gray-600">Remember me</label>
          </div>

          <button
            type="submit"
            :disabled="form.processing"
            class="w-full rounded-lg bg-blue-600 px-4 py-2 text-white hover:bg-blue-700 transition disabled:opacity-50"
          >
            {{ form.processing ? 'Logging in...' : 'Log In' }}
          </button>
        </form>

        <p class="text-center text-sm text-gray-500">
          Don't have an account?
          <a :href="route('register')" class="text-blue-500 hover:underline">Register Now</a>
        </p>
      </div>
    </div>
  </div>
</template>
