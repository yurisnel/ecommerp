<template>
    <div>
        <h2 class="text-2xl font-semibold text-gray-800 mb-6">Dashboard</h2>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Total Sales -->
            <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-blue-500">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-blue-100 text-blue-500 mr-4">
                        <Icon icon="mdi:currency-usd" class="h-6 w-6" />
                    </div>
                    <div>
                        <p class="text-gray-500 text-sm font-medium">Total Sales</p>
                        <p class="text-2xl font-bold text-gray-800">${{ Number(stats.total_sales || 0).toFixed(2) }}</p>
                    </div>
                </div>
            </div>

            <!-- Orders -->
            <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-green-500">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-green-100 text-green-500 mr-4">
                        <Icon icon="mdi:cart-outline" class="h-6 w-6" />
                    </div>
                    <div>
                        <p class="text-gray-500 text-sm font-medium">Orders</p>
                        <p class="text-2xl font-bold text-gray-800">{{ stats.total_orders || 0 }}</p>
                    </div>
                </div>
            </div>

            <!-- Customers -->
            <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-purple-500">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-purple-100 text-purple-500 mr-4">
                        <Icon icon="mdi:account-group-outline" class="h-6 w-6" />
                    </div>
                    <div>
                        <p class="text-gray-500 text-sm font-medium">Customers</p>
                        <p class="text-2xl font-bold text-gray-800">{{ stats.total_customers || 0 }}</p>
                    </div>
                </div>
            </div>

            <!-- Low Stock Alert -->
            <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-orange-500">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-orange-100 text-orange-500 mr-4">
                        <Icon icon="mdi:alert-circle-outline" class="h-6 w-6" />
                    </div>
                    <div>
                        <p class="text-gray-500 text-sm font-medium">Low Stock</p>
                        <p class="text-2xl font-bold text-gray-800">{{ alerts.total_warning + alerts.total_critical || 0 }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Recent Orders -->
            <div class="bg-white rounded-lg shadow-sm">
                <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                    <h3 class="font-semibold text-gray-800">Recent Orders</h3>
                    <router-link :to="{ name: 'Orders' }" class="text-sm text-indigo-600 hover:text-indigo-800">
                        View All
                    </router-link>
                </div>
                <div class="p-6">
                    <div v-if="loadingOrders" class="text-center text-gray-500 py-4">
                        <Icon icon="mdi:loading" class="h-6 w-6 animate-spin inline" />
                        Loading...
                    </div>
                    <div v-else-if="recentOrders.length === 0" class="text-center text-gray-500 py-4">
                        No orders yet
                    </div>
                    <div v-else class="space-y-4">
                        <div 
                            v-for="order in recentOrders" 
                            :key="order.id"
                            class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors cursor-pointer"
                            @click="router.push({ name: 'OrderView', params: { id: order.id } })"
                        >
                            <div class="flex items-center">
                                <div class="p-2 rounded-full mr-3" :class="getStatusColor(order.order_status_id)">
                                    <Icon icon="mdi:receipt" class="h-4 w-4" />
                                </div>
                                <div>
                                    <p class="font-medium text-gray-800">#{{ order.order_number }}</p>
                                    <p class="text-sm text-gray-500">{{ order.customer?.name || 'Guest' }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="font-medium text-gray-800">${{ Number(order.total).toFixed(2) }}</p>
                                <span 
                                    class="text-xs px-2 py-1 rounded-full"
                                    :class="getStatusBadgeClass(order.order_status_id)"
                                >
                                    {{ order.order_status?.name || 'Unknown' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stock Alerts -->
            <div class="bg-white rounded-lg shadow-sm">
                <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                    <h3 class="font-semibold text-gray-800">Stock Alerts</h3>
                    <router-link :to="{ name: 'Inventory' }" class="text-sm text-indigo-600 hover:text-indigo-800">
                        View All
                    </router-link>
                </div>
                <div class="p-6">
                    <div v-if="loadingAlerts" class="text-center text-gray-500 py-4">
                        <Icon icon="mdi:loading" class="h-6 w-6 animate-spin inline" />
                        Loading...
                    </div>
                    <div v-else-if="alerts.out_of_stock?.length === 0 && alerts.low_stock?.length === 0" class="text-center text-gray-500 py-4">
                        <Icon icon="mdi:check-circle" class="h-8 w-8 text-green-500 mx-auto mb-2" />
                        No critical alerts
                    </div>
                    <div v-else class="space-y-3">
                        <!-- Out of Stock (Critical) -->
                        <div 
                            v-for="alert in alerts.out_of_stock" 
                            :key="'out-' + alert.product_id"
                            class="flex items-center justify-between p-3 bg-red-50 border border-red-200 rounded-lg"
                        >
                            <div class="flex items-center">
                                <Icon icon="mdi:alert-circle" class="h-5 w-5 text-red-500 mr-3" />
                                <div>
                                    <p class="font-medium text-gray-800">{{ alert.product_name }}</p>
                                    <p class="text-xs text-red-600">Out of stock</p>
                                </div>
                            </div>
                            <router-link 
                                :to="{ name: 'Inventory' }" 
                                class="text-sm text-red-600 hover:text-red-800 font-medium"
                            >
                                View
                            </router-link>
                        </div>
                        
                        <!-- Low Stock (Warning) -->
                        <div 
                            v-for="alert in alerts.low_stock" 
                            :key="'low-' + alert.product_id"
                            class="flex items-center justify-between p-3 bg-amber-50 border border-amber-200 rounded-lg"
                        >
                            <div class="flex items-center">
                                <Icon icon="mdi:alert" class="h-5 w-5 text-amber-500 mr-3" />
                                <div>
                                    <p class="font-medium text-gray-800">{{ alert.product_name }}</p>
                                    <p class="text-xs text-amber-600">{{ alert.current_quantity }} units remaining</p>
                                </div>
                            </div>
                            <router-link 
                                :to="{ name: 'Inventory' }" 
                                class="text-sm text-amber-600 hover:text-amber-800 font-medium"
                            >
                                View
                            </router-link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { Icon } from '@iconify/vue';
import api from '../axios';

const router = useRouter();

const loadingOrders = ref(true);
const loadingAlerts = ref(true);
const recentOrders = ref([]);
const stats = ref({});
const alerts = ref({ out_of_stock: [], low_stock: [], total_critical: 0, total_warning: 0 });

// Get recent orders
const fetchRecentOrders = async () => {
    try {
        const response = await api.get('/orders/recent?limit=5');
        recentOrders.value = response.data.data || [];
    } catch (error) {
        console.error('Error fetching recent orders:', error);
    } finally {
        loadingOrders.value = false;
    }
};

// Get order stats
const fetchStats = async () => {
    try {
        const [ordersRes, customersRes] = await Promise.all([
            api.get('/orders/stats'),
            api.get('/customers/stats')
        ]);
        stats.value = {
            ...ordersRes.data.data,
            total_customers: customersRes.data.data?.total_customers || 0
        };
    } catch (error) {
        console.error('Error fetching stats:', error);
    }
};

// Get stock alerts
const fetchAlerts = async () => {
    try {
        const response = await api.get('/inventory/alerts?limit=10');
        alerts.value = response.data.data || { out_of_stock: [], low_stock: [], total_critical: 0, total_warning: 0 };
    } catch (error) {
        console.error('Error fetching alerts:', error);
    } finally {
        loadingAlerts.value = false;
    }
};

// Status color helper
const getStatusColor = (statusId) => {
    const colors = {
        1: 'bg-blue-100 text-blue-500',
        2: 'bg-yellow-100 text-yellow-500',
        3: 'bg-green-100 text-green-500',
        4: 'bg-purple-100 text-purple-500',
        5: 'bg-red-100 text-red-500',
    };
    return colors[statusId] || 'bg-gray-100 text-gray-500';
};

// Status badge helper
const getStatusBadgeClass = (statusId) => {
    const classes = {
        1: 'bg-blue-100 text-blue-700',
        2: 'bg-yellow-100 text-yellow-700',
        3: 'bg-green-100 text-green-700',
        4: 'bg-purple-100 text-purple-700',
        5: 'bg-red-100 text-red-700',
    };
    return classes[statusId] || 'bg-gray-100 text-gray-700';
};

onMounted(() => {
    fetchRecentOrders();
    fetchStats();
    fetchAlerts();
});
</script>
