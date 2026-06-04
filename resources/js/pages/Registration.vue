<script setup>
import { ref } from 'vue'
import axios from 'axios'
import { useRouter } from 'vue-router'
import { useUserStore } from '../stores/user'
import ErrorModal from '../components/ErrorModal.vue'
import SuccessModal from '../components/SuccessModal.vue'
import OpacityWindow from '../components/OpacityWindow.vue'
import Loader from '../components/Loader.vue'

const loading = ref(false);
const form = ref({
  name: '',
  email: '',
  password: '',
  password_confirmation: ''
});

const userStore = useUserStore();

const showError = ref(false);
const errorMessage = ref('');
const title = ref('');

const showSuccess = ref(false);
const successMessage = ref('');

const showLoader = ref(false);
const showOpacityWindow = ref(false);

const register = async () => {
  loading.value = true;

  showOpacityWindow.value = true;
  showLoader.value = true;

  try {
    const response = await axios.post('/api/register', form.value)

    showOpacityWindow.value = false;
    showLoader.value = false;

    showSuccess.value = true;
    successMessage.value = response.data.message || 'Регистрация успешна! Проверьте ваш email для подтверждения.';
    title.value = 'Регистрация';

    form.value = {
      name: '',
      email: '',
      password: '',
      password_confirmation: ''
    };


  } catch (error) {
    showOpacityWindow.value = false;
    showLoader.value = false;

    const message = error.response?.data?.message || 'Ошибка регистрации'
    showError.value = true;
    errorMessage.value = message;
    title.value = 'Регистрация';
  } finally {
    loading.value = false
  }
}
</script>

<template>
<div>
<div class="register-page">
    <h2>Регистрация</h2>
    <form @submit.prevent="register">
      <div class="form-group">
        <label>Имя</label>
        <input
          v-model="form.name"
          type="text"
          placeholder="Ваше имя"
          required
        >
      </div>

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
          placeholder="Не менее 8 символов"
          required
          minlength="8"
        >
      </div>

      <div class="form-group">
        <label>Подтверждение пароля</label>
        <input
          v-model="form.password_confirmation"
          type="password"
          placeholder="Повторите пароль"
          required
        >
      </div>

      <button type="submit" :disabled="loading">
        {{ loading ? 'Регистрация..' : 'Зарегистрироваться' }}
      </button>

      <p class="login-link">
        Уже есть аккаунт? <router-link to="/login">Войти</router-link>
      </p>
    </form>
  </div>

  <ErrorModal v-model:show-error="showError"
              :error-message="errorMessage"
              :title="title">
  </ErrorModal>

  <SuccessModal v-model:show-success="showSuccess"
                :success-message="successMessage"
                :success-title="title">
  </SuccessModal>

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
  border: 1px solid #ddd;
}

.form-group {
  margin-bottom: 1rem;
}

h2{
    margin-left: 140px;
}

label {
  display: block;
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

button {
  width: 400px;
  padding: 0.75rem;
  background: #007bff;
  color: white;
  border: none;
  border-radius: 4px;
  font-size: 1rem;
  cursor: pointer;
}

button:disabled {
  background: #6c757d;
  cursor: not-allowed;
}

.login-link {
  margin-top: 1rem;
  margin-left: 120px;
  color: #9fa3a6;
}
</style>
