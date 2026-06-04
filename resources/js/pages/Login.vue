<script setup>
import { ref } from 'vue'
import axios from 'axios'
import { useRouter } from 'vue-router'
import { useUserStore } from '../stores/user'
import ErrorModal from '../components/ErrorModal.vue'
import OpacityWindow from '../components/OpacityWindow.vue'
import Loader from '../components/Loader.vue'

const router = useRouter()
const loading = ref(false)
const form = ref({
  email: '',
  password: ''
})

const userStore = useUserStore();

const showError = ref(false);
const errorMessage = ref('');
const title = ref('');

const showLoader = ref(false);
const showOpacityWindow = ref(false);

const login = async () => {
  loading.value = true;

  showOpacityWindow.value = true;
  showLoader.value = true;

  try {
    const response = await axios.post('/api/login', form.value)

    const token = response.data.token

    userStore.userInfo.auth_token = token;
    userStore.userInfo.name = form.value.name;
    userStore.userInfo.email = form.value.email;
    userStore.userInfo.isAuthenticated = true;

    localStorage.setItem('isAuthenticated', userStore.userInfo.isAuthenticated);
    localStorage.setItem('token', userStore.userInfo.auth_token);

    axios.defaults.headers.common['Authorization'] = `Bearer ${token}`

    showOpacityWindow.value = false;
    showLoader.value = false;
    router.push({ name: 'cameras' });

  } catch (error) {
    showOpacityWindow.value = false;
    showLoader.value = false;

    const message = error.response?.data?.message || 'Ошибка входа'
    showError.value = true;
    errorMessage.value = message;
    title.value = 'Вход';
  } finally {
    loading.value = false
  }
}
</script>

<template>
<div>
  <div class="login-page">
    <h2>Вход в систему</h2>
    <form @submit.prevent="login">
      <!-- Всего 2 поля -->
      <div class="form-group">
        <label>Email</label>
        <input
          v-model="form.email"
          type="email"
          placeholder="your@email.com"
          required
        >
      </div>

      <div class="form-group">
        <label>Пароль</label>
        <input
          v-model="form.password"
          type="password"
          placeholder="Ваш пароль"
          required
        >
      </div>

      <button type="submit" :disabled="loading">
        {{ loading ? 'Вход...' : 'Войти' }}
      </button>

      <div class="links">
        <router-link to="/forgot-password">Забыли пароль?</router-link>
        <router-link to="/register">Нет аккаунта? Зарегистрироваться</router-link>
      </div>
    </form>
  </div>

  <ErrorModal v-model:show-error="showError"
              :error-message="errorMessage"
              :title="title">
    </ErrorModal>

  <OpacityWindow v-if="showOpacityWindow"></OpacityWindow>
  <Loader v-if="showLoader"></Loader>
  </div>
</template>

<style scoped>
.login-page {
  max-width: 400px;
  margin: 2rem auto;
  padding: 2rem;
  border: 1px solid #ddd;
  border-radius: 8px;
  box-shadow:  0 8px 30px rgba(0, 0, 0, 0.25);
}

.form-group {
  margin-bottom: 1.5rem;
}

label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: bold;
}

input {
  width: 100%;
  padding: 0.55rem;
  border: 1px solid #ccc;
  border-radius: 4px;
  font-size: 1rem;
}

input:focus {
  outline: none;
  border-color: #007bff;
}

button {
  width: 100%;
  padding: 0.75rem;
  background: #007bff;
  color: white;
  border: none;
  border-radius: 4px;
  font-size: 1rem;
  cursor: pointer;
  margin-bottom: 1rem;
}

button:disabled {
  background: #6c757d;
  cursor: not-allowed;
}

button:hover:not(:disabled) {
  background: #0056b3;
}

.links {
  display: flex;
  justify-content: space-between;
  font-size: 0.9rem;
}

.links a {
  color: #007bff;
  text-decoration: none;
}

.links a:hover {
  text-decoration: underline;
}
</style>

