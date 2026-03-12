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
                            <Icon icon="mdi:chart-box" class="mr-3 h-5 w-5" />
                            {{ t('menu.dashboard') }}
                        </router-link>
                    </li>
                    
                    <li class="px-4 pt-4 pb-2 text-xs font-bold text-slate-500 uppercase tracking-wider">{{ t('menu.catalogInventory') }}</li>
                    <li>
                        <router-link to="/admin/inventory" class="flex items-center px-4 py-2 text-slate-300 hover:bg-slate-700 hover:text-white rounded-md group transition-colors">
                            <Icon icon="mdi:package-variant" class="mr-3 h-5 w-5" /> {{ t('menu.products') }}
                        </router-link>
                    </li>
                    <li>
                        <router-link to="/admin/attributes" class="flex items-center px-4 py-2 text-slate-300 hover:bg-slate-700 hover:text-white rounded-md group transition-colors">
                            <Icon icon="mdi:format-list-bulleted" class="mr-3 h-5 w-5" /> {{ t('menu.attributes') }}
                        </router-link>
                    </li>
                    <li>
                        <router-link to="/admin/categories" class="flex items-center px-4 py-2 text-slate-300 hover:bg-slate-700 hover:text-white rounded-md group transition-colors">
                            <Icon icon="mdi:tag-multiple" class="mr-3 h-5 w-5" /> {{ t('menu.categories') }}
                        </router-link>
                    </li>
                    <li>
                        <router-link to="/admin/suppliers" class="flex items-center px-4 py-2 text-slate-300 hover:bg-slate-700 hover:text-white rounded-md group transition-colors">
                            <Icon icon="mdi:handshake" class="mr-3 h-5 w-5" /> {{ t('menu.suppliers') }}
                        </router-link>
                    </li>

                    <li class="px-4 pt-4 pb-2 text-xs font-bold text-slate-500 uppercase tracking-wider">{{ t('menu.sales') }}</li>
                    <li>
                        <router-link to="/admin/orders" class="flex items-center px-4 py-2 text-slate-300 hover:bg-slate-700 hover:text-white rounded-md group transition-colors">
                            <Icon icon="mdi:shopping-cart" class="mr-3 h-5 w-5" /> {{ t('menu.orders') }}
                        </router-link>
                    </li>
                    <li>
                        <router-link to="/admin/customers" class="flex items-center px-4 py-2 text-slate-300 hover:bg-slate-700 hover:text-white rounded-md group transition-colors">
                            <Icon icon="mdi:account-multiple" class="mr-3 h-5 w-5" /> {{ t('menu.customers') }}
                        </router-link>
                    </li>
                    <li>
                        <router-link to="/admin/discounts" class="flex items-center px-4 py-2 text-slate-300 hover:bg-slate-700 hover:text-white rounded-md group transition-colors">
                            <Icon icon="mdi:percent-outline" class="mr-3 h-5 w-5" /> {{ t('menu.discounts') }}
                        </router-link>
                    </li>

                    <li class="px-4 pt-4 pb-2 text-xs font-bold text-slate-500 uppercase tracking-wider">{{ t('menu.management') }}</li>
                    <li>
                        <router-link to="/admin/employees" class="flex items-center px-4 py-2 text-slate-300 hover:bg-slate-700 hover:text-white rounded-md group transition-colors">
                            <Icon icon="mdi:briefcase" class="mr-3 h-5 w-5" /> {{ t('menu.employees') }}
                        </router-link>
                    </li>
                    <li>
                        <router-link to="/admin/settings" class="flex items-center px-4 py-2 text-slate-300 hover:bg-slate-700 hover:text-white rounded-md group transition-colors">
                            <Icon icon="mdi:cog" class="mr-3 h-5 w-5" /> {{ t('menu.settings') }}
                        </router-link>
                    </li>
                </ul>
            </nav>

            <div class="p-4 border-t border-slate-700">
                <button class="flex items-center text-slate-400 hover:text-white transition-colors w-full">
                    <Icon icon="mdi:logout" class="mr-3 h-5 w-5" /> {{ t('menu.logout') }}
                </button>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Header -->
            <header class="h-16 bg-white shadow-sm flex items-center justify-between px-6 z-10">
                <button class="md:hidden text-gray-500 focus:outline-none">
                    <Icon icon="mdi:menu" class="h-6 w-6" />
                </button>
                
                <div class="flex items-center space-x-4">
                    <!-- Language Selector -->
                    <Menu as="div" class="relative">
                        <MenuButton class="flex items-center gap-2 text-sm text-gray-600 hover:text-gray-900 px-3 py-2 rounded-lg hover:bg-gray-100 transition-colors">
                            <Icon icon="mdi:translate" class="w-4 h-4" />
                            <span class="uppercase font-medium">{{ languageStore.locale }}</span>
                            <Icon icon="mdi:chevron-down" class="w-4 h-4" />
                        </MenuButton>
                        <transition
                            enter-active-class="transition duration-100 ease-out"
                            enter-from-class="transform scale-95 opacity-0"
                            enter-to-class="transform scale-100 opacity-100"
                            leave-active-class="transition duration-75 ease-in"
                            leave-from-class="transform scale-100 opacity-100"
                            leave-to-class="transform scale-95 opacity-0"
                        >
                            <MenuItems class="absolute left-0 mt-2 w-40 bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-50">
                                <MenuItem v-for="lang in languageStore.availableLocales" :key="lang.code" v-slot="{ active }">
                                    <button
                                        @click="changeLanguage(lang.code)"
                                        :class="[
                                            active ? 'bg-gray-100' : '',
                                            'flex items-center w-full px-4 py-2 text-sm text-gray-700'
                                        ]"
                                    >
                                        <span :class="['mr-2', languageStore.locale === lang.code ? 'font-bold text-indigo-600' : '']">{{ lang.nativeName }}</span>
                                        <Icon v-if="languageStore.locale === lang.code" icon="mdi:check" class="w-4 h-4 text-indigo-600 ml-auto" />
                                    </button>
                                </MenuItem>
                            </MenuItems>
                        </transition>
                    </Menu>

                    <span class="text-gray-600 text-sm">{{ t('dashboard.welcome') }}, Admin</span>
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
import { Icon } from '@iconify/vue';
import { useI18n } from 'vue-i18n';
import { useLanguageStore } from '../store/language';
import { Menu, MenuButton, MenuItems, MenuItem } from '@headlessui/vue';
import i18n from '../i18n';

const { t } = useI18n();
const languageStore = useLanguageStore();

function changeLanguage(langCode) {
    i18n.global.locale.value = langCode;
    languageStore.setLocale(langCode);
}
</script>

<style scoped>
.router-link-active {
    background-color: #1e293b;
    background-color: rgb(51 65 85);
    color: white;
}
</style>
