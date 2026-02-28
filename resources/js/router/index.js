import { createRouter, createWebHistory } from 'vue-router';

// Layouts
import AdminLayout from '../layouts/AdminLayout.vue';

// Views
import Dashboard from '../views/Dashboard.vue';
import CategoryList from '../views/CategoryList.vue';
import CategoryForm from '../views/CategoryForm.vue';
import OrderList from '../views/OrderList.vue';
import CustomerList from '../views/CustomerList.vue';
import InventoryView from '../views/InventoryView.vue';
import InventoryEntry from '../views/InventoryEntry.vue';
import ProductForm from '../views/ProductForm.vue';
import SupplierList from '../views/SupplierList.vue';
import SupplierForm from '../views/SupplierForm.vue';
import EmployeeList from '../views/EmployeeList.vue';
import EmployeeForm from '../views/EmployeeForm.vue';
import CustomerForm from '../views/CustomerForm.vue';
import OrderForm from '../views/OrderForm.vue';
import OrderStatusList from '../views/OrderStatusList.vue';
import OrderStatusForm from '../views/OrderStatusForm.vue';

const routes = [
    {
        path: '/admin',
        component: AdminLayout,
        children: [
            { path: '', name: 'Dashboard', component: Dashboard },      
            { path: 'products/create', name: 'ProductCreate', component: ProductForm },
            { path: 'products/:id/edit', name: 'ProductEdit', component: ProductForm },
            { path: 'inventory', name: 'Inventory', component: InventoryView },
            { path: 'inventory/new', name: 'InventoryEntry', component: InventoryEntry },
            { path: 'inventory/entry/:id/edit', name: 'InventoryEntryEdit', component: InventoryEntry, props: true },
            { path: 'categories', name: 'Categories', component: CategoryList },
            { path: 'categories/create', name: 'CategoryCreate', component: CategoryForm },
            { path: 'categories/:id/edit', name: 'CategoryEdit', component: CategoryForm },
            { path: 'suppliers', name: 'Suppliers', component: SupplierList },
            { path: 'suppliers/create', name: 'SupplierCreate', component: SupplierForm },
            { path: 'suppliers/:id/edit', name: 'SupplierEdit', component: SupplierForm },
            { path: 'employees', name: 'Employees', component: EmployeeList },
            { path: 'employees/create', name: 'EmployeeCreate', component: EmployeeForm },
            { path: 'employees/:id/edit', name: 'EmployeeEdit', component: EmployeeForm },
            { path: 'orders', name: 'Orders', component: OrderList },
            { path: 'orders/create', name: 'OrderCreate', component: OrderForm },
            { path: 'orders/:id', name: 'OrderView', component: OrderForm },
            { path: 'order-statuses', name: 'OrderStatuses', component: OrderStatusList },
            { path: 'order-statuses/create', name: 'OrderStatusCreate', component: OrderStatusForm },
            { path: 'order-statuses/:id/edit', name: 'OrderStatusEdit', component: OrderStatusForm },
            { path: 'customers', name: 'Customers', component: CustomerList },
            { path: 'customers/create', name: 'CustomerCreate', component: CustomerForm },
            { path: 'customers/:id/edit', name: 'CustomerEdit', component: CustomerForm },
            // Fallback for not implemented yet
            { path: ':pathMatch(.*)*', component: { template: '<div class="p-8 text-center text-gray-500">Construction in progress 🚧</div>' } }
        ]
    }
];

const router = createRouter({
    history: createWebHistory(),
    routes
});

export default router;
