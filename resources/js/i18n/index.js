import { createI18n } from 'vue-i18n';
import en from '../locales/en.json';
import es from '../locales/es.json';

// Get stored language from localStorage or default to 'en'
const storedLang = localStorage.getItem('app_language') || 'en';

const i18n = createI18n({
    legacy: false,
    locale: storedLang,
    fallbackLocale: 'en',
    messages: {
        en,
        es
    }
});

export default i18n;
