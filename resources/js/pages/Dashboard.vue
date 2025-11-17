<template>
  <div class="min-h-screen bg-gray-100 p-6">
    <h1 class="text-2xl font-bold mb-4">Dashboard Reservasi Ruangan</h1>

    <div v-if="loading" class="text-center">Loading data...</div>
    <div v-if="error" class="text-red-600">{{ error }}</div>

    <div v-if="!loading && !error">
      <section class="mb-6" v-if="roles.includes('admin')">
        <h2 class="text-xl font-semibold mb-2">Reservasi Admin</h2>
        <ul>
          <li v-for="reservation in reservations" :key="reservation.id" class="mb-1 p-2 bg-white rounded shadow">
            Ruangan: {{ reservation.room.name }} | Tanggal: {{ reservation.date }} | Status: {{ reservation.status }}
          </li>
          <li v-if="reservations.length === 0">Belum ada reservasi.</li>
        </ul>
      </section>

      <section class="mb-6" v-if="roles.includes('admin') || roles.includes('karyawan')">
        <h2 class="text-xl font-semibold mb-2">Daftar Ruangan</h2>
        <ul>
          <li v-for="room in rooms" :key="room.id" class="mb-1 p-2 bg-white rounded shadow">
            {{ room.name }} - Kapasitas: {{ room.capacity }}
          </li>
        </ul>
      </section>

      <section class="mb-6" v-if="roles.includes('admin') || roles.includes('karyawan')">
        <h2 class="text-xl font-semibold mb-2">Jadwal Tetap</h2>
        <ul>
          <li v-for="schedule in fixedSchedules" :key="schedule.id" class="mb-1 p-2 bg-white rounded shadow">
            Ruangan: {{ schedule.room.name }} | Hari: {{ schedule.day }} | Jam: {{ schedule.start_time }} - {{ schedule.end_time }}
          </li>
        </ul>
      </section>

      <section class="mb-6" v-if="!roles.includes('admin') && !roles.includes('karyawan')">
        <h2 class="text-xl font-semibold mb-2">Reservasi Anda</h2>
        <ul>
          <li v-for="reservation in reservations" :key="reservation.id" class="mb-1 p-2 bg-white rounded shadow">
            Ruangan: {{ reservation.room.name }} | Tanggal: {{ reservation.date }} | Status: {{ reservation.status }}
          </li>
          <li v-if="reservations.length === 0">Belum ada reservasi.</li>
        </ul>
      </section>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

const router = useRouter()

const reservations = ref([])
const rooms = ref([])
const fixedSchedules = ref([])
const loading = ref(false)
const error = ref('')
const roles = ref([])

const fetchData = async () => {
  loading.value = true
  error.value = ''

  try {
    // Ambil token dari localStorage
    const token = localStorage.getItem('token')
    if (!token) {
      error.value = 'Token tidak ditemukan. Silakan login kembali.'
      loading.value = false
      return
    }

    axios.defaults.headers.common['Authorization'] = `Bearer ${token}`

    // Ambil data profil user untuk mendapatkan role
    const profileRes = await axios.get('/profile')
    const user = profileRes.data

    // Perbaiki pengecekan role, karena backend menggunakan spatie roles, properti adalah roles bukan roles[]
    // Jadi cek user.roles sebagai array objek role, ambil nama role
    roles.value = user.roles ? user.roles.map(r => r.name) : []

    // Ambil data reservasi berdasarkan role
    if (roles.value.includes('admin')) {
      const [resReservations, resRooms, resFixedSchedules] = await Promise.all([
        axios.get('/admin/reservations'),
        axios.get('/admin/rooms'),
        axios.get('/admin/fixed-schedules'),
      ])
      reservations.value = resReservations.data.data || []
      rooms.value = resRooms.data.data || []
      fixedSchedules.value = resFixedSchedules.data.data || []
    } else if (roles.value.includes('karyawan')) {
      const [resReservations, resRooms, resFixedSchedules] = await Promise.all([
        axios.get('/karyawan/reservations'),
        axios.get('/karyawan/rooms'),
        axios.get('/karyawan/fixed-schedules'),
      ])
      reservations.value = resReservations.data.data || []
      rooms.value = resRooms.data.data || []
      fixedSchedules.value = resFixedSchedules.data.data || []
    } else {
      // Untuk user biasa, hanya ambil reservasi sendiri dan daftar ruangan
      const [resReservations, resRooms] = await Promise.all([
        axios.get('/karyawan/reservations'),
        axios.get('/karyawan/rooms'),
      ])
      reservations.value = resReservations.data.data || []
      rooms.value = resRooms.data.data || []
      fixedSchedules.value = []
    }
  } catch (err) {
    error.value = 'Gagal mengambil data: ' + (err.response?.data?.message || err.message)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  const token = localStorage.getItem('token')
  if (!token) {
    // Jika tidak ada token, redirect ke login
    router.push('/login')
    return
  }
  fetchData()
})
</script>

<style scoped>
/* Tambahan styling jika perlu */
</style>
