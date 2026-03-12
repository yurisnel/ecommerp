import { defineStore } from 'pinia';
import { ref, watch } from 'vue';

export const useLanguageStore = defineStore('language', () => {
    // Get stored language from localStorage or default to 'en'
    const storedLang = localStorage.getItem('app_language') || 'en';
    const locale = ref(storedLang);
    
    const availableLocales = [
        { code: 'en', name: 'English', nativeName: 'English' },
        { code: 'es', name: 'Spanish', nativeName: 'Español' }
    ];

    // Function to change language
    function setLocale(newLocale) {
        if (availableLocales.some(l => l.code === newLocale)) {
            locale.value = newLocale;
            localStorage.setItem('app_language', newLocale);
        }
    }

    // Get current locale name
    function getLocaleName() {
        const current = availableLocales.find(l => l.code === locale.value);
        return current ? current.nativeName : 'English';
    }

    // Watch for changes and persist
    watch(locale, (newVal) => {
        localStorage.setItem('app_language', newVal);
    });

    return {
        locale,
        availableLocales,
        setLocale,
        getLocaleName
    };
});
