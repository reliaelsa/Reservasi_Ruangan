<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
      <div>
        <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
          Create a New Account
        </h2>
        <p class="mt-2 text-center text-sm text-gray-600">
          Or
          <router-link to="/login" class="font-medium text-indigo-600 hover:text-indigo-500">
            sign in to your account
          </router-link>
        </p>
      </div>

      <form class="mt-8 space-y-6" @submit.prevent="handleRegister">
        <div class="rounded-md shadow-sm -space-y-px">
          <div>
            <label for="name" class="sr-only">Full Name</label>
            <input
              id="name"
              name="name"
              type="text"
              required
              class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300
                     placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-indigo-500
                     focus:border-indigo-500 focus:z-10 sm:text-sm"
              placeholder="Full Name"
              v-model="form.name"
            />
          </div>

          <div>
            <label for="email" class="sr-only">Email</label>
            <input
              id="email"
              name="email"
              type="email"
              required
              class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300
                     placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-indigo-500
                     focus:border-indigo-500 focus:z-10 sm:text-sm"
              placeholder="Email Address"
              v-model="form.email"
            />
          </div>

          <div>
            <label for="password" class="sr-only">Password</label>
            <input
              id="password"
              name="password"
              type="password"
              required
              class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300
                     placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-indigo-500
                     focus:border-indigo-500 focus:z-10 sm:text-sm"
              placeholder="Password"
              v-model="form.password"
            />
          </div>

          <div>
            <label for="password_confirmation" class="sr-only">Confirm Password</label>
            <input
              id="password_confirmation"
              name="password_confirmation"
              type="password"
              required
              class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300
                     placeholder-gray-500 text-gray-900 rounded-b-md focus:outline-none focus:ring-indigo-500
                     focus:border-indigo-500 focus:z-10 sm:text-sm"
              placeholder="Confirm Password"
              v-model="form.password_confirmation"
            />
          </div>
        </div>

        <div v-if="errorMessage" class="text-red-600 text-sm">
          {{ errorMessage }}
        </div>

        <ul v-if="Object.keys(validationErrors).length" class="text-red-500 text-sm mt-2">
          <li v-for="(msg, field) in validationErrors" :key="field">
            {{ msg[0] }}
          </li>
        </ul>

        <div>
          <button
            type="submit"
            :disabled="loading"
            class="group relative w-full flex justify-center py-2 px-4 border border-transparent
                   text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700
                   focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500
                   disabled:opacity-50"
          >
            <span v-if="loading" class="animate-spin rounded-full h-4 w-4 border-b-2 border-white"></span>
            <span v-else>Register</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from "vue";
import { useRouter } from "vue-router";
import { useUserStore } from "@/stores/user";

const router = useRouter();
const userStore = useUserStore();

// Gunakan satu objek form agar mudah diikat ke input
const form = ref({
  name: "",
  email: "",
  password: "",
  password_confirmation: "",
});

const loading = ref(false);
const errorMessage = ref("");
const validationErrors = ref({});

async function handleRegister() {
  loading.value = true;
  errorMessage.value = "";
  validationErrors.value = {};

  const result = await userStore.register(form.value);

  loading.value = false;

  if (result.success) {
    alert(result.message);
    router.push("/login");
  } else {
    errorMessage.value = result.message;
    validationErrors.value = result.errors;
  }
}
</script>
