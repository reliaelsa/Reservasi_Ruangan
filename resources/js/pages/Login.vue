<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
      <div>
        <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
          Sign In to Your Account
        </h2>
        <p class="mt-2 text-center text-sm text-gray-600">
          Or
          <router-link to="/register" class="font-medium text-indigo-600 hover:text-indigo-500">
            create a new account
          </router-link>
        </p>
      </div>

      <form class="mt-8 space-y-6" @submit.prevent="handleLogin">
        <div class="rounded-md shadow-sm -space-y-px">
          <div>
            <label for="email" class="sr-only">Email</label>
            <input id="email" name="email" type="email" required
              v-model="email"
              placeholder="Email Address"
              class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm" />
          </div>
          <div>
            <label for="password" class="sr-only">Password</label>
            <input id="password" name="password" type="password" required
              v-model="password"
              placeholder="Password"
              class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-b-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm" />
          </div>
        </div>

        <div v-if="errorMessage" class="text-red-600 text-sm">
          {{ errorMessage }}
        </div>

        <div>
          <button type="submit" :disabled="loading"
            class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50">
            <span v-if="loading" class="animate-spin rounded-full h-4 w-4 border-b-2 border-white"></span>
            <span v-else>Sign In</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useUserStore } from '../stores/user'

const email = ref('')
const password = ref('')
const loading = ref(false)
const errorMessage = ref('')

const router = useRouter()
const userStore = useUserStore()

async function handleLogin() {
  loading.value = true
  errorMessage.value = ''
  try {
    const role = await userStore.login(email.value, password.value)

    if (role === 'admin') router.push('/admin')
    else if (role === 'karyawan') router.push('/karyawan')
    else router.push('/dashboard')

  } catch (err) {
    errorMessage.value = err.response?.data?.message || 'Login gagal'
  } finally {
    loading.value = false
  }
}
</script>
