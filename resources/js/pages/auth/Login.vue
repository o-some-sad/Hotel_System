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
  <Head title="Welcome to Laralive" />

  <div class="flex min-h-screen font-sans">
    <!-- Left Side Image with Overlay -->
    <div class="hidden lg:flex w-1/2 relative">
      <div
        class="absolute inset-0 bg-cover bg-center"
        style="background-image: url('http://localhost:8000/storage/image.png')"
      ></div>
      <div class="absolute inset-0 bg-gradient-to-br from-black/60 to-black/30"></div>
      <div class="relative z-10 flex flex-col justify-center p-16 text-white space-y-6">
        <h2 class="text-5xl font-extrabold tracking-wide text-blue-300">Laralive Hotel</h2>
        <p class="text-lg max-w-md leading-relaxed">
          Travel is the only purchase that enriches you in ways beyond material wealth.
        </p>
      </div>
    </div>

    <!-- Right Side Login Form -->
    <div  class="flex w-full flex-col justify-center px-8 py-16 lg:w-1/2 bg-white">
      <div class="mx-auto w-full max-w-md space-y-10">
        <div class="text-center">
          <h1 class="text-4xl font-bold text-blue-700">Welcome Back</h1>
          <p class="text-gray-500 text-sm mt-1">Log in to continue to Laralive Hotel</p>
        </div>

        <form @submit.prevent="submit" class="space-y-6">
          <div>
            <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
            <input
              id="email"
              type="email"
              v-model="form.email"
              required
              class="mt-1 w-full rounded-lg border border-gray-300 px-4 py-2 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
              placeholder="you@example.com"
            />
            <p v-if="form.errors.email" class="mt-1 text-sm text-red-500">{{ form.errors.email }}</p>
          </div>

          <div>
            <div class="flex justify-between items-center">
              <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
              <a
                v-if="canResetPassword"
                :href="route('password.request')"
                class="text-sm text-blue-500 hover:underline"
              >Forgot?</a>
            </div>
            <input
              id="password"
              type="password"
              v-model="form.password"
              required
              class="mt-1 w-full rounded-lg border border-gray-300 px-4 py-2 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
              placeholder="••••••••"
            />
            <p v-if="form.errors.password" class="mt-1 text-sm text-red-500">{{ form.errors.password }}</p>
          </div>

          <div class="flex items-center space-x-2">
            <input
              id="remember"
              type="checkbox"
              v-model="form.remember"
              class="rounded border-gray-300 text-blue-600 shadow-sm"
            />
            <label for="remember" class="text-sm text-gray-600">Remember me</label>
          </div>

          <button
            type="submit"
            :disabled="form.processing"
            class="w-full rounded-lg bg-blue-600 px-4 py-2 text-white font-semibold hover:bg-blue-700 transition disabled:opacity-50"
          >
            {{ form.processing ? 'Logging in...' : 'Log In' }}
          </button>
        </form>

        <p class="text-center text-sm text-gray-500">
          Don’t have an account?
          <a :href="route('register')" class="text-blue-600 hover:underline">Register now</a>
        </p>
      </div>
    </div>
  </div>
</template>
