import './bootstrap'
import { createApp } from 'vue'
import { createPinia } from 'pinia'
import App from './App.vue'
import router from './router' // ← Импортируем роутер
import axios from 'axios'

const app = createApp(App);
const pinia = createPinia();

axios.interceptors.request.use(config => {
    const token = localStorage.getItem('token');

    if(token){
        config.headers.Authorization = `Bearer ${token}`;
    }

    return config;
})

app.use(pinia)
app.use(router) // ← Используем роутер
app.mount('#app')
