import { createRouter, createWebHistory } from "vue-router";

import AdminLayout from "@/layouts/AdminLayout.vue";
import Home from "@/pages/Home.vue";
import Login from "@/pages/Login.vue";
import Register from "@/pages/Register.vue";
import Dashboard from "@/pages/Dashboard.vue";

const routes = [
    {
        path: "/",
        name: "Home",
        component: Home,
    },
    {
        path: "/login",
        name: "Login",
        component: Login,
    },
    {
        path: "/register",
        name: "Register",
        component: Register,
    },
    {
        path: "/admin",
        name: "Admin",
        component: AdminLayout,
        children: [
            {
                path: "/dashboard",
                name: "dashboard",
                component: Dashboard,
            },
        ],
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

export default router;
