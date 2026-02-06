<template>
    <div>
        <h2 class="text-2xl font-semibold text-gray-800 mb-6">Dashboard</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Stats Cards -->
            <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-blue-500">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-blue-100 text-blue-500 mr-4">
                        💰
                    </div>
                    <div>
                        <p class="text-gray-500 text-sm font-medium">Total Sales</p>
                        <p class="text-2xl font-bold text-gray-800">$24,500</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-green-500">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-green-100 text-green-500 mr-4">
                        📦
                    </div>
                    <div>
                        <p class="text-gray-500 text-sm font-medium">Orders</p>
                        <p class="text-2xl font-bold text-gray-800">145</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-purple-500">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-purple-100 text-purple-500 mr-4">
                        👥
                    </div>
                    <div>
                        <p class="text-gray-500 text-sm font-medium">Customers</p>
                        <p class="text-2xl font-bold text-gray-800">1,240</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-orange-500">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-orange-100 text-orange-500 mr-4">
                        ⚠️
                    </div>
                    <div>
                        <p class="text-gray-500 text-sm font-medium">Low Stock</p>
                        <p class="text-2xl font-bold text-gray-800">12</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Recent Orders -->
            <div class="bg-white rounded-lg shadow-sm">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="font-semibold text-gray-800">Recent Orders</h3>
                </div>
                <div class="p-6">
                    <!-- Placeholder Table -->
                    <div class="text-center text-gray-500 py-4">Loading orders...</div>
                </div>
            </div>

            <!-- Stock Alerts -->
            <div class="bg-white rounded-lg shadow-sm">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="font-semibold text-gray-800">Stock Alerts</h3>
                </div>
                <div class="p-6">
                    <!-- Placeholder List -->
                    <div class="text-center text-gray-500 py-4">No critical alerts</div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
// Dashboard logic
</script>
