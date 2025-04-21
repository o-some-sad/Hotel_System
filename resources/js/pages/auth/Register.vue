<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthBase from '@/layouts/AuthLayout.vue';
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
    <AuthBase title="Create an account" description="Enter your details below to create your account">
        <Head title="Register" />

        <form @submit.prevent="submit" class="flex flex-col gap-6">
            <div class="grid gap-6">
                <!-- Name field -->
                <div class="grid gap-2">
                    <Label for="name">Name</Label>
                    <Input id="name" type="text" required v-model="form.name" />
                    <InputError :message="form.errors.name" />
                </div>

                <!-- Email field -->
                <div class="grid gap-2">
                    <Label for="email">Email address</Label>
                    <Input id="email" type="email" required v-model="form.email" />
                    <InputError :message="form.errors.email" />
                </div>

                <!-- National ID field -->
                <div class="grid gap-2">
                    <Label for="nationalId">National ID</Label>
                    <Input id="nationalId" type="text" required v-model="form.nationalId" />
                    <InputError :message="form.errors.nationalId" />
                </div>

                <!-- Country field -->
                <div class="grid gap-2">
                    <Label for="country">Country</Label>
                    <select 
                        id="country" 
                        v-model="form.country" 
                        required 
                        class="w-full rounded-md border bg-white px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        <option value="">Select Country</option>
                        <option 
                            v-for="country in props.countries" 
                            :key="country" 
                            :value="country"
                        >
                            {{ country }}
                        </option>
                    </select>
                    <InputError :message="form.errors.country" />
                </div>

                <!-- Gender field -->
                <div class="grid gap-2">
                    <Label for="gender">Gender</Label>
                    <select 
                        id="gender" 
                        v-model="form.gender" 
                        required 
                        class="w-full rounded-md border bg-white px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        <option value="">Select Gender</option>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                    </select>
                    <InputError :message="form.errors.gender" />
                </div>

                <!-- Image upload field -->
                <div class="grid gap-2">
                    <Label for="image">Profile Image</Label>
                    <Input id="image" type="file" @input="handleImageUpload" accept="image/*" />
                    <div v-if="imagePreview" class="mt-2">
                        <img :src="imagePreview" alt="Preview" class="w-32 h-32 object-cover rounded-full" />
                    </div>
                    <InputError :message="form.errors.image" />
                </div>

                <!-- Password fields -->
                <div class="grid gap-2">
                    <Label for="password">Password</Label>
                    <Input id="password" type="password" required v-model="form.password" />
                    <InputError :message="form.errors.password" />
                </div>

                <div class="grid gap-2">
                    <Label for="password_confirmation">Confirm password</Label>
                    <Input id="password_confirmation" type="password" required v-model="form.password_confirmation" />
                    <InputError :message="form.errors.password_confirmation" />
                </div>

                <Button type="submit" class="mt-2 w-full" :disabled="form.processing">
                    <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
                    Create account
                </Button>
            </div>

            <div class="text-center text-sm text-muted-foreground">
                Already have an account?
                <TextLink :href="route('login')" class="underline underline-offset-4">Log in</TextLink>
            </div>
        </form>
    </AuthBase>
</template>

<style scoped>
select {
    height: 40px;
}
</style>