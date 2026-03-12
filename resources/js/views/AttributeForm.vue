<template>
    <div class="space-y-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">              
                    {{ isEdit ? t('attributes.editAttribute') : t('attributes.newAttribute') }}
                  
                </h1>
                <p class="text-gray-500 text-sm mt-1">         
                    {{ t('attributes.formDescription') }}
                </p>
            </div>
            <router-link 
                :to="{ name: 'AttributeList' }"
                class="text-gray-600 hover:text-gray-900 flex items-center gap-2"
            >
                <i class="fas fa-arrow-left"></i>
                <span>{{ t('attributes.back') }}</span>
            </router-link>
        </div>

        <form @submit.prevent="submitForm" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{t('common.name')}} *</label>
                    <input 
                        v-model="form.name" 
                        type="text" 
                        required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                        :placeholder="t('attributes.attributeName')"
                    >
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{t('common.code')}}</label>
                    <input 
                        v-model="form.code" 
                        type="text" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                        :placeholder="t('attributes.code')"
                    >
                    <p class="text-xs text-gray-500 mt-1">{{ t('attributes.codeHelp') }}</p>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{t('attributes.descriptionField')}}</label>
                    <textarea 
                        v-model="form.description" 
                        rows="2"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    ></textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{t('common.type')}}</label>
                    <select 
                        v-model="form.type"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    >
                        <option value="select">{{ t('attributes.selectOption') }}</option>
                        <option value="radio">{{ t('attributes.radioButtons') }}</option>
                        <option value="checkbox">{{ t('attributes.checkbox') }}</option>
                        <option value="text">{{ t('attributes.freeText') }}</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{t('attributes.sortOrder')}}</label>
                    <input 
                        v-model.number="form.sort_order" 
                        type="number" 
                        min="0"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    >
                </div>

                <div class="flex items-center gap-6">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input 
                            v-model="form.is_required" 
                            type="checkbox" 
                            class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
                        >
                        <span class="text-sm text-gray-700">{{ t('attributes.required') }}</span>
                    </label>

                    <label class="flex items-center gap-2 cursor-pointer">
                        <input 
                            v-model="form.is_filterable" 
                            type="checkbox" 
                            class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
                        >
                        <span class="text-sm text-gray-700">{{ t('attributes.filterable') }}</span>
                    </label>
                </div>
            </div>

            <!-- Attribute Values -->
            <div class="border-t border-gray-200 pt-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900">{{ t('attributes.attributeValues') }}</h3>
                    <button 
                        type="button"
                        @click="addValue"
                        class="text-indigo-600 hover:text-indigo-900 text-sm font-medium flex items-center gap-1"
                    >
                        <i class="fas fa-plus"></i>
                        {{ t('attributes.addValue') }}
                    </button>
                </div>

                <div v-if="form.values.length === 0" class="text-center py-8 text-gray-500">
                    <i class="fas fa-list text-3xl mb-2 text-gray-200"></i>
                    <p>{{ t('attributes.noValuesDefined') }}</p>
                </div>

                <div v-else class="space-y-3">
                    <div 
                        v-for="(value, index) in form.values" 
                        :key="index"
                        class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg"
                    >
                        <div class="flex-1 grid grid-cols-1 md:grid-cols-3 gap-3">
                            <input 
                                v-model="value.value" 
                                type="text" 
                                :placeholder="t('attributes.valuePlaceholder')"
                                class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                            >
                            <input 
                                v-model="value.value_es" 
                                type="text" 
                                :placeholder="t('attributes.valueEs')"
                                class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                            >
                            <div class="flex items-center gap-2">
                                <input 
                                    v-model="value.color_code" 
                                    type="color"
                                    class="w-10 h-10 border border-gray-300 rounded cursor-pointer"
                                >
                                <input 
                                    v-model.number="value.sort_order" 
                                    type="number" 
                                    min="0"
                                    :placeholder="t('attributes.sortOrder')"
                                    class="w-20 px-2 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                >
                            </div>
                        </div>
                        <button 
                            type="button"
                            @click="removeValue(index)"
                            class="text-red-600 hover:text-red-900 p-2"
                        >
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
                <router-link 
                    :to="{ name: 'AttributeList' }"
                    class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50"
                >
                    {{ t('common.cancel') }}
                </router-link>
                <button 
                    type="submit"
                    :disabled="saving"
                    class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:opacity-50"
                >
                    {{ saving ? t('attributes.saving') : (isEdit ? t('attributes.update') : t('attributes.create')) }}
                </button>
            </div>
        </form>
    </div>
</template>

<script>
import { ref, onMounted, computed } from 'vue'
import { useI18n } from 'vue-i18n';
import { useRoute, useRouter } from 'vue-router'

import axios from 'axios'

export default {
    name: 'AttributeForm',
    setup() {
        const { t } = useI18n()
        const route = useRoute()
        const router = useRouter()
        
        const form = ref({
            name: '',
            code: '',
            description: '',
            type: 'select',
            is_required: false,
            is_filterable: false,
            sort_order: 0,
            values: []
        })
        
        const saving = ref(false)
        const isEdit = computed(() => !!route.params.id)

        const addValue = () => {
            form.value.values.push({
                value: '',
                value_es: '',
                color_code: '#000000',
                sort_order: form.value.values.length
            })
        }

        const removeValue = (index) => {
            form.value.values.splice(index, 1)
        }

        const fetchAttribute = async () => {
            if (!route.params.id) return
            
            try {
                const response = await axios.get(`/api/v1/attributes/${route.params.id}`)
                const data = response.data.data
                
                form.value = {
                    name: data.name,
                    code: data.code,
                    description: data.description || '',
                    type: data.type,
                    is_required: data.is_required,
                    is_filterable: data.is_filterable,
                    sort_order: data.sort_order,
                    values: data.values ? data.values.map(v => ({
                        value: v.value,
                        value_es: v.value_es,
                        color_code: v.color_code,
                        sort_order: v.sort_order
                    })) : []
                }
            } catch (error) {
                console.error('Error fetching attribute:', error)
                alert(t('attributes.errorLoading'))
            }
        }

        const submitForm = async () => {
            saving.value = true
            try {
                if (isEdit.value) {
                    await axios.put(`/api/v1/attributes/${route.params.id}`, form.value)
                } else {
                    await axios.post('/api/v1/attributes', form.value)
                }
                router.push({ name: 'AttributeList' })
            } catch (error) {
                console.error('Error saving attribute:', error)
                alert(t('attributes.errorSaving'))
            } finally {
                saving.value = false
            }
        }

        onMounted(() => {
            fetchAttribute()
        })

        return {
            form,
            saving,
            isEdit,
            addValue,
            removeValue,
            submitForm,
            t
        }
    }
}
</script>
