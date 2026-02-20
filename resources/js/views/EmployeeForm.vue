<template>
    <div class="space-y-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ isEditing ? 'Edit Employee' : 'New Employee' }}</h1>
                <p class="text-gray-500 text-sm mt-1">Create or update employee records.</p>
            </div>
            <div class="flex gap-3">
                <button @click="$router.back()" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">Cancel</button>
                <button @click="submit" :disabled="submitting" class="bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700 transition-colors flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                    <span v-if="submitting" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                    <span>{{ isEditing ? 'Update Employee' : 'Create Employee' }}</span>
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="md:col-span-2 space-y-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4 border-b pb-2">Basic Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Employee Number</label>
                            <input v-model="form.employee_number" type="text" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">User</label>
                            <select v-model="form.user_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                <option :value="null">Select user</option>
                                <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }} ({{ u.email }})</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Department</label>
                            <select v-model="form.department_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                <option :value="null">None</option>
                                <option v-for="d in departments" :key="d.id" :value="d.id">{{ d.name }}</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Position</label>
                            <input v-model="form.position" type="text" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Hire Date</label>
                            <input v-model="form.hire_date" type="date" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Termination Date</label>
                            <input v-model="form.termination_date" type="date" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Salary</label>
                            <input v-model="form.salary" type="number" step="0.01" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Employment Type</label>
                            <select v-model="form.employment_type" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="full_time">Full time</option>
                                <option value="part_time">Part time</option>
                                <option value="contract">Contract</option>
                                <option value="intern">Intern</option>
                            </select>
                        </div>

                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Emergency Contact</label>
                            <div class="grid grid-cols-2 gap-2">
                                <input v-model="form.emergency_contact_name" placeholder="Name" class="px-4 py-2 border border-gray-300 rounded-lg">
                                <input v-model="form.emergency_contact_phone" placeholder="Phone" class="px-4 py-2 border border-gray-300 rounded-lg">
                            </div>
                        </div>

                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                            <textarea v-model="form.notes" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <!-- Photo card (compact, lateral) -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 flex flex-col items-center">
                    <h3 class="text-lg font-medium text-gray-900 mb-3">Photo</h3>
                    <div class="h-24 w-24 rounded-full overflow-hidden border border-gray-200 mb-3">
                        <img v-if="form.image" :src="form.image" :alt="form.position || 'Employee image'" class="object-cover h-full w-full" @error="onImageError" />
                        <div v-else class="h-full w-full bg-gray-100 flex items-center justify-center">
                            <i class="fas fa-image text-gray-300"></i>
                        </div>
                    </div>

                    <div class="flex gap-2 mb-2">
                        <button @click="showUploader = !showUploader" type="button" class="px-3 py-1 text-sm bg-indigo-600 text-white rounded">Editar</button>
                        <button v-if="form.image" @click="confirmRemoveImage" type="button" class="px-3 py-1 text-sm border rounded">Eliminar</button>
                    </div>

                    <p class="text-xs text-gray-400 text-center">Recomendado 400×400 • ≤2MB</p>

                    <div v-show="showUploader" class="w-full mt-3">
                        <ImageUploader v-model="form.image" folder="employees" />
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Settings</h3>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <label class="text-sm font-medium text-gray-700">Active Status</label>
                                <p class="text-xs text-gray-500">Enable or disable this employee.</p>
                            </div>
                            <Switch v-model="statusActive" :class="statusActive ? 'bg-indigo-600' : 'bg-gray-200'" class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                <span :class="statusActive ? 'translate-x-6' : 'translate-x-1'" class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform" />
                            </Switch>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, reactive, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import api from '../axios';
import ImageUploader from '../components/ImageUploader.vue';
import { Switch } from '@headlessui/vue';

const route = useRoute();
const router = useRouter();

const isEditing = ref(!!route.params.id);
const submitting = ref(false);
const errors = reactive({});

const users = ref([]);
const departments = ref([]);

const statusActive = ref(true);
watch(statusActive, (val) => {
    form.status = val ? 'active' : 'inactive';
});

// Sidebar uploader toggle
const showUploader = ref(false);

const onImageError = (event) => {
    event.target.src = 'data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 24 24%22 fill=%22%23d1d5db%22%3E%3Cpath d=%22M21 19V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z%22/%3E%3C/svg%3E';
};

const confirmRemoveImage = () => {
    if (!confirm('Are you sure you want to remove the image?')) return;
    form.image = '';
};

const form = reactive({
    employee_number: '',
    user_id: null,
    department_id: null,
    position: '',
    hire_date: '',
    termination_date: '',
    salary: '',
    employment_type: 'full_time',
    emergency_contact_name: '',
    emergency_contact_phone: '',
    image: '',
    notes: '',
    status: 'active'
});



const fetchUsers = async () => {
    try { const res = await api.get('/users', { params: { per_page: 100 } }); users.value = res.data.data.data || res.data.data; } catch (e) { console.error(e); }
};

const fetchDepartments = async () => {
    try { const res = await api.get('/departments'); departments.value = res.data.data.data || res.data.data; } catch (e) { console.error(e); }
};

const fetchEmployee = async () => {
    if (!isEditing.value) return;
    try {
        const response = await api.get(`/employees/${route.params.id}`);
        const emp = response.data.data;
        Object.assign(form, {
            employee_number: emp.employee_number,
            user_id: emp.user_id,
            department_id: emp.department_id,
            position: emp.position,
            hire_date: emp.hire_date,
            termination_date: emp.termination_date,
            salary: emp.salary,
            employment_type: emp.employment_type,
            emergency_contact_name: emp.emergency_contact_name,
            emergency_contact_phone: emp.emergency_contact_phone,
            image: emp.image || '',
            notes: emp.notes,
            status: emp.status
        });
        statusActive.value = emp.status === 'active';
    } catch (error) {
        console.error('Error fetching employee:', error);
        alert('Failed to load employee details.');
    }
};

const submit = async () => {
    errors.value = {};
    submitting.value = true;
    try {
        if (isEditing.value) await api.put(`/employees/${route.params.id}`, form);
        else await api.post('/employees', form);
        router.push({ name: 'Employees' });
    } catch (error) {
        if (error.response?.status === 422) Object.assign(errors, error.response.data.errors || {});
        else { console.error('Error saving employee:', error); alert('Failed to save employee'); }
    } finally { submitting.value = false; }
};

onMounted(() => { fetchUsers(); fetchDepartments(); fetchEmployee(); });
</script>
