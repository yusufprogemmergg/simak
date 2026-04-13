import Axios from 'axios';

const axios = Axios.create({
    baseURL: import.meta.env.VITE_APP_URL || 'http://localhost:8000',
    headers: {
        'X-Requested-With': 'XMLHttpRequest',
    },
    withCredentials: true, // Wajib untuk laravel sanctum SPA Auth
});

// Interceptor untuk menghandle CSRF token secara otomatis
axios.interceptors.request.use(async (config) => {
    // Jika method adalah operasi write, kita pastikan CSRF cookie sudah ada
    if (['post', 'put', 'patch', 'delete'].includes(config.method)) {
        // Kita bisa mengecek apakah cookie XSRF-TOKEN ada (ini bergantung implementasi JS browser, 
        // tapi secara umum lebih aman kita biarkan Sanctum yang urus selagi web sama origin.
        // Jika belum pernah call sanctum/csrf-cookie, sebaiknya app call dulu sebelum login).
    }
    return config;
});



axios.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response && (error.response.status === 401 || error.response.status === 419)) {
            localStorage.removeItem('isLoggedIn');
            if (window.location.pathname !== '/login' && window.location.pathname !== '/register' && window.location.pathname !== '/') {
                window.location.href = '/login';
            }
        }
        return Promise.reject(error);
    }
);

export default axios;
