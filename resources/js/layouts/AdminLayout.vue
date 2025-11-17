<script setup>
import { useRouter } from 'vue-router';
import { useUserStore } from '@/stores/user';
// import { Button } from '@/components/ui/button';

const router = useRouter();
const userStore = useUserStore();

function handleLogout() {
    userStore.logout();
    router.push({name:'Login'});
}

</script>

<template>
    <div class="flex min-h-screen bg-gray-100">
        <!-- Sidebar -->
        <aside class="w-64 bg-white shadow-md">
            <div class="p-4 text-xl font-bold border-b">Admin Panel</div>
            <nav class="p-4 space-y-2">
                <RouterLink to="/admin/dashboard" class="block px-3 py-2 rounded hover:bg-gray-200">
                    Dashboard
                </RouterLink>
                <RouterLink to="/admin/reservations" class="block px-3 py-2 rounded hover:bg-gray-200">
                    Reservasi
                </RouterLink>
                <RouterLink to="/admin/rooms" class="block px-3 py-2 rounded hover:bg-gray-200">
                    Rooms
                </RouterLink>
                <RouterLink to="/admin/fixed-schedules" class="block px-3 py-2 rounded hover:bg-gray-200">
                    Fixed Schedules
                </RouterLink>
            </nav>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col">
            <!-- Navbar -->
            <header class="bg-white shadow p-4 flex justify-between items-center">
                <h1 class="text-lg font-semibold">Admin Area</h1>
                <button @click="handleLogout" variant="destructive"
                    class="text-sm bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded cursor-pointer">
                    Logout
                        </button>
            </header>

            <!-- Halaman -->
            <main class="p-6 flex-1">
                <RouterView />
            </main>
        </div>
    </div>
</template>
