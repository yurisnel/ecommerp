import axios from 'axios';
import i18n from './i18n/index';

const api = axios.create({
    baseURL: '/api/v1',
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
    }
});

// Add interceptor for auth token (placeholder for now)
api.interceptors.request.use(config => {
    // const token = localStorage.getItem('token');
    // if (token) {
    //     config.headers.Authorization = `Bearer ${token}`;
    // }

    // Set Accept-Language header from i18n locale
    config.headers['Accept-Language'] = i18n.global.locale.value || 'en';

    return config;
});

export default api;
