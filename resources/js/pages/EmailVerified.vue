<script setup>
import { useRoute } from 'vue-router'
import { ref, onMounted } from 'vue'

const route = useRoute()
const success = ref(false)

onMounted(() => {
  success.value = route.query.success === 'true'
})
</script>

<template>
<div>
  <div class="email-verified">
    <div v-if="success" class="success-message">
      <h2>✅ Email подтвержден!</h2>
      <p>Ваш email успешно подтвержден. Теперь вы можете войти в систему.</p>
      <router-link to="/login" class="btn-primary">Войти</router-link>
    </div>
    <div v-else class="error-message">
      <h2>❌ Ошибка подтверждения</h2>
      <p>Ссылка подтверждения недействительна или устарела.</p>
      <router-link to="/register" class="btn-secondary">На главную</router-link>
    </div>
  </div>
</div>
</template>

<style scoped>
.email-verified {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  padding: 20px;
}

.success-message,
.error-message {
  background: white;
  padding: 3rem;
  border-radius: 12px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
  text-align: center;
  max-width: 500px;
  width: 100%;
}

.success-message {
  border-left: 4px solid #10b981;
}

.error-message {
  border-left: 4px solid #ef4444;
}

h2 {
  margin-bottom: 1rem;
  color: #1f2937;
  font-size: 1.5rem;
}

p {
  margin-bottom: 2rem;
  color: #6b7280;
  line-height: 1.6;
}

.btn-primary {
  display: inline-block;
  background: #007bff;
  color: white;
  padding: 0.75rem 2rem;
  border-radius: 6px;
  text-decoration: none;
  font-weight: 500;
  transition: background 0.2s;
}

.btn-primary:hover {
  background: #0056b3;
}

.btn-secondary {
  display: inline-block;
  background: #6b7280;
  color: white;
  padding: 0.75rem 2rem;
  border-radius: 6px;
  text-decoration: none;
  font-weight: 500;
  transition: background 0.2s;
}

.btn-secondary:hover {
  background: #4b5563;
}

/* Адаптивность */
@media (max-width: 640px) {
  .success-message,
  .error-message {
    padding: 2rem;
    margin: 1rem;
  }

  h2 {
    font-size: 1.25rem;
  }
}
</style>
