<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import axios from 'axios'
import ErrorModal from '../components/ErrorModal.vue'
import OpacityWindow from '../components/OpacityWindow.vue'
import Loader from '../components/Loader.vue'

const route = useRoute()
const router = useRouter()
const loading = ref(false)
const form = ref({
  token: '',
  email: '',
  password: '',
  password_confirmation: ''
})

const showError = ref(false);
const errorMessage = ref('');
const title = ref('');

const showLoader = ref(false);
const showOpacityWindow = ref(false);

// Получаем token и email из URL (из письма)
onMounted(() => {
  form.value.token = route.query.token || ''
  form.value.email = route.query.email || ''
})

const resetPassword = async () => {
  loading.value = true;

  showOpacityWindow.value = true;
  showLoader.value = true;

  try {
    await axios.post('/api/reset-password', form.value)

    showOpacityWindow.value = false;
    showLoader.value = false;
    router.push({ name: 'login' });
  } catch (error) {
    showOpacityWindow.value = false;
    showLoader.value = false;

    const message = 'Ошибка сброса пароля';
    showError.value = true;
    errorMessage.value = message;
    title.value = 'Смена пароля';
  } finally {
    loading.value = false
  }
}
</script>

<template>
<div>
  <div class="register-page">
    <h2>Сброс пароля</h2>
    <form @submit.prevent="resetPassword">
      <div class="form-group">
        <label>Email</label>
        <input
          v-model="form.email"
          type="email"
          placeholder="your@email.com"
          required
          :readonly="!!route.query.email"
        >
      </div>

      <div class="form-group">
        <label>Новый пароль</label>
        <input
          v-model="form.password"
          type="password"
          placeholder="Новый пароль"
          required
          minlength="8"
        >
      </div>

      <div class="form-group">
        <label>Подтверждение пароля</label>
        <input
          v-model="form.password_confirmation"
          type="password"
          placeholder="Подтверждение пароля"
          required
        >
      </div>

      <input v-model="form.token" type="hidden">

      <button type="submit" :disabled="loading">
        {{ loading ? 'Сброс...' : 'Сбросить пароль' }}
      </button>

      <div class="login-link">
        <router-link to="/login">← Назад к входу</router-link>
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
.register-page {
  position: absolute;
  top: 30px;
  left: 35%;
  width: 440px;
  font-size: 15px;
  box-shadow:  0 8px 30px rgba(0, 0, 0, 0.25);
  padding-left: 30px;
  padding-top: 20px;
  padding-bottom: 30px;
  padding-right: 30px;
  background: white;
  border-radius: 8px;
}

.form-group {
  margin-bottom: 1rem;
  margin-left: 25px;
}

h2 {
  margin-left: 120px;
  margin-bottom: 30px;
  color: #333;
}

label {
  display: block;
  font-weight: bold;
  color: #333;
  margin-bottom: 5px;
}

input {
  width: 370px;
  height: 20px;
  padding: 0.55rem;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 1rem;
  margin-top: 10px;
}

input:focus {
  outline: none;
  border-color: #007bff;
  box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.25);
}

input[readonly] {
  background-color: #f8f9fa;
  cursor: not-allowed;
}

button {
  width: 400px;
  padding: 0.75rem;
  background: #007bff;
  color: white;
  border: none;
  border-radius: 4px;
  font-size: 1rem;
  cursor: pointer;
  margin-top: 10px;
  transition: background-color 0.2s;
  margin-left: 25px;
}

button:hover:not(:disabled) {
  background: #0056b3;
}

button:disabled {
  background: #6c757d;
  cursor: not-allowed;
}

.login-link {
  margin-top: 1rem;
  margin-left: 280px;
  color: #9fa3a6;
  text-align: center;
}

.login-link a {
  color: #007bff;
  text-decoration: none;
}

.login-link a:hover {
  text-decoration: underline;
}
</style>
