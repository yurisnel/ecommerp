<template>
    <div class="flex h-screen bg-gray-100">
        <!-- Sidebar -->
        <aside class="w-64 bg-slate-800 text-white flex-shrink-0 hidden md:flex flex-col">
            <div class="p-4 flex items-center justify-center h-16 border-b border-slate-700">
                <h1 class="text-xl font-bold tracking-wider">ECOM<span class="text-blue-400">ERP</span></h1>
            </div>
            
            <nav class="flex-1 overflow-y-auto py-4">
                <ul class="space-y-1 px-2">
                    <li>
                        <router-link :to="{name: 'Dashboard'}" class="flex items-center px-4 py-2 text-slate-300 hover:bg-slate-700 hover:text-white rounded-md group transition-colors">
                            <span class="mr-3">📊</span>
                            Dashboard
                        </router-link>
                    </li>
                    
                    <li class="px-4 pt-4 pb-2 text-xs font-bold text-slate-500 uppercase tracking-wider">Catalog & Inventory</li>
                    <li>
                        <router-link to="/admin/inventory" class="flex items-center px-4 py-2 text-slate-300 hover:bg-slate-700 hover:text-white rounded-md group transition-colors">
                            <span class="mr-3">🏭</span> Inventory Dashboard
                        </router-link>
                    </li>
                    <li>
                        <router-link to="/admin/categories" class="flex items-center px-4 py-2 text-slate-300 hover:bg-slate-700 hover:text-white rounded-md group transition-colors">
                            <span class="mr-3">🏷️</span> Categories
                        </router-link>
                    </li>
                    <li>
                        <router-link to="/admin/suppliers" class="flex items-center px-4 py-2 text-slate-300 hover:bg-slate-700 hover:text-white rounded-md group transition-colors">
                            <span class="mr-3">🤝</span> Suppliers
                        </router-link>
                    </li>

                    <li class="px-4 pt-4 pb-2 text-xs font-bold text-slate-500 uppercase tracking-wider">Sales</li>
                    <li>
                        <router-link to="/admin/orders" class="flex items-center px-4 py-2 text-slate-300 hover:bg-slate-700 hover:text-white rounded-md group transition-colors">
                            <span class="mr-3">🛍️</span> Orders
                        </router-link>
                    </li>
                    <li>
                        <router-link to="/admin/customers" class="flex items-center px-4 py-2 text-slate-300 hover:bg-slate-700 hover:text-white rounded-md group transition-colors">
                            <span class="mr-3">👥</span> Customers
                        </router-link>
                    </li>
                    <li>
                        <router-link to="/admin/discounts" class="flex items-center px-4 py-2 text-slate-300 hover:bg-slate-700 hover:text-white rounded-md group transition-colors">
                            <span class="mr-3">🎫</span> Discounts
                        </router-link>
                    </li>

                    <li class="px-4 pt-4 pb-2 text-xs font-bold text-slate-500 uppercase tracking-wider">Management</li>
                    <li>
                        <router-link to="/admin/employees" class="flex items-center px-4 py-2 text-slate-300 hover:bg-slate-700 hover:text-white rounded-md group transition-colors">
                            <span class="mr-3">👔</span> Employees
                        </router-link>
                    </li>
                    <li>
                        <router-link to="/admin/settings" class="flex items-center px-4 py-2 text-slate-300 hover:bg-slate-700 hover:text-white rounded-md group transition-colors">
                            <span class="mr-3">⚙️</span> Settings
                        </router-link>
                    </li>
                </ul>
            </nav>

            <div class="p-4 border-t border-slate-700">
                <button class="flex items-center text-slate-400 hover:text-white transition-colors w-full">
                    <span class="mr-3">🚪</span> Logout
                </button>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Header -->
            <header class="h-16 bg-white shadow-sm flex items-center justify-between px-6 z-10">
                <button class="md:hidden text-gray-500 focus:outline-none">
                    ☰
                </button>
                
                <div class="flex items-center space-x-4">
                    <span class="text-gray-600 text-sm">Welcome, Admin</span>
                    <div class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center text-white font-bold">
                        A
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-6">
                <router-view></router-view>
            </main>
        </div>
    </div>
</template>

<script setup>
// Layout logic
</script>

<style scoped>
.router-link-active {
    background-color: #1e293b; /* bg-slate-800 mostly but darker */
    background-color: rgb(51 65 85); /* bg-slate-700 */
    color: white;
}
</style>
