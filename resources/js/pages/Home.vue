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
                        </div>
                    </div>
                </div>
            </div>
        </nav>
    </div>
</template>
