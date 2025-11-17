import { defineStore } from "pinia";
import axios from "axios";

export const useUserStore = defineStore("user", {
    state: () => ({
        user: null,
        role: localStorage.getItem("role") | null,
        token: localStorage.getItem("token") | null,
    }),
    actions: {
        init() {
            if (localStorage.getItem("token")) {
                axios.defaults.headers.common["Authorization"] =
                    `Bearer ${localStorage.getItem("token")}`;
            }
        },
        async login(email, password) {
            const { data } = await axios.post("/api/login", {
                email,
                password,
            });

            this.user = data.user;
            this.role = data.data.user.role;
            this.token = data.token;

            localStorage.setItem("token", this.token);
            localStorage.setItem("role", this.role);

            axios.defaults.headers.common["Authorization"] =
                `Bearer ${this.token}`;

            return this.role;
        },
        async register(payload) {
            try {
                const { data } = await axios.post(
                    "/api/register",
                    payload,
                );

                // Register hanya nyimpen user baru → tidak simpan token
                return {
                    success: true,
                    message: "Registrasi berhasil, silakan login.",
                    data,
                };
            } catch (err) {
                return {
                    success: false,
                    message: err.response?.data?.message || "Registrasi gagal.",
                    errors: err.response?.data?.errors || {},
                };
            }
        },
        logout() {
            this.user = null;
            this.role = null;
            this.token = null;
            localStorage.clear();
        },
    },
});
