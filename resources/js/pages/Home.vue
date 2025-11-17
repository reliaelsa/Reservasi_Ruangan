<template>
    <div class="min-h-screen bg-gray-100">
        <nav class="bg-white shadow">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex">
                        <div class="shrink-0 flex items-center">
                            <img class="h-8 w-auto" src="" alt="Logo">
                        </div>
                        <div class="hidden sm:-my-px sm:ml-6 sm:flex sm space-x-8">
                            <RouterLink to="/"
                                class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                Home
                            </RouterLink>
                            <RouterLink to="/about"
                                class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                About
                            </RouterLink>
                            <RouterLink v-if="isLoggedIn" to="/dashboard"
                                class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                Dashboard
                            </RouterLink>
                        </div>
                    </div>
                    <div class="hidden sm:ml-6 sm:flex sm:items-center">
                        <RouterLink v-if="!isLoggedIn" to="/login"
                            class="text-gray-500 hover:text-gray-700 px-3 py-2 rounded-md text-sm font-medium">
                            Masuk
                        </RouterLink>
                        <RouterLink v-if="!isLoggedIn" to="/register"
                            class="bg-indigo-600 text-white hover:bg-indigo-700 px-3 py-2 rounded-md text-sm font-medium ml-3">
                            Daftar
                        </RouterLink>
                        <button v-if="isLoggedIn" @click="handleLogout"
                            class="text-gray-500 hover:text-gray-700 px-3 py-2 rounded-md text-sm font-medium ml-3">
                            Keluar
                        </button>
                    </div>
                </div>
            </div>
        </nav>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const user = ref(JSON.parse(localStorage.getItem('user')))
const isLoggedIn = computed(() => !!user.value)

const handleLogout = () => {
  localStorage.removeItem('token')
  localStorage.removeItem('user')
  user.value = null
  router.push('/login')
}
</script>
