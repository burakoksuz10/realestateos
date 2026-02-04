import axios from 'axios';

window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// CSRF Token
const token = document.head.querySelector('meta[name="csrf-token"]');
if (token) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
}

// Response interceptor for error handling
window.axios.interceptors.response.use(
    response => response,
    error => {
        if (error.response?.status === 401) {
            window.location.href = '/login';
        }
        
        if (error.response?.status === 403) {
            window.REOS?.notify('error', 'Yetkisiz İşlem', 'Bu işlem için yetkiniz bulunmuyor.');
        }
        
        if (error.response?.status === 422) {
            const errors = error.response.data.errors;
            if (errors) {
                const firstError = Object.values(errors)[0][0];
                window.REOS?.notify('error', 'Doğrulama Hatası', firstError);
            }
        }
        
        if (error.response?.status >= 500) {
            window.REOS?.notify('error', 'Sunucu Hatası', 'Bir hata oluştu. Lütfen tekrar deneyin.');
        }
        
        return Promise.reject(error);
    }
);
