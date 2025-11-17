import axios from "axios";

// Base URL akan otomatis mengikuti domain Laravel
const baseURL = window.location.origin;

const axiosInstance = axios.create({
    baseURL: baseURL,
    headers: {
        Accept: "application/json",
        "Content-Type": "application/json",
    },
});

// CSRF Token untuk Laravel
const token = document.head.querySelector('meta[name="csrf-token"]');
if (token) {
    axios.defaults.headers.common["X-CSRF-TOKEN"] = token.content;
}

// Request interceptor
axios.interceptors.request.use(
    (config) => {
        // Tambahkan token jika menggunakan authentication
        const authToken = localStorage.getItem("token");
        if (authToken) {
            config.headers.Authorization = `Bearer ${authToken}`;
        }
        return config;
    },
    (error) => {
        console.error("❌ Request Error:", error);
        return Promise.reject(error);
    },
);

// Response interceptor
axiosInstance.interceptors.response.use(
    (response) => {
        console.log("✅ Response:", response.status, response.config.url);
        console.log("📦Data:", response.data);
        return response;
    },
    (error) => {
        console.error(
            "❌ Response Error:",
            error.response?.status,
            error.config?.url,
        );
        console.error("📦 Error Data:", error.response?.data);

        // Handle 401 Unauthorized
        if (error.response?.status === 401) {
            localStorage.removeItem("token");
            window.location.href = "/login";
        }

        // Handle 403 Forbidden
        if (error.response?.status === 403) {
            console.error("Forbidden:", error.response.data.message);
        }

        // Handle 404 Not Found
        if (error.response?.status === 404) {
            console.error(
                "🚫 Route not found. Check your Laravel routes/api.php",
            );
        }

        // Handle 500 Internal Server Error
        if (error.response?.status === 500) {
            console.error("Server Error:", error.response.data);
        }

        return Promise.reject(error);
    },
);

export default axiosInstance;
