<script setup>
import { defineProps, defineEmits } from 'vue';

const props = defineProps(['showSuccess', 'successMessage', 'successTitle']);
const emit = defineEmits(['update:showSuccess']);

function hideSuccess(){
  emit('update:showSuccess', false);
}
</script>

<template>
  <Transition name="success">
    <div v-if="showSuccess" class="success-modal">
      <div class="success-content">
        <div class="success-icon">✅</div>
        <h3>{{ successTitle }}</h3>
        <p>{{ successMessage }}</p>
        <button @click="hideSuccess" class="close-btn">Закрыть</button>
      </div>
    </div>
  </Transition>
</template>

<style scoped>
.success-modal {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 10001;
}

.success-content {
  background: white;
  padding: 2rem;
  border-radius: 12px;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
  max-width: 400px;
  width: 90%;
  text-align: center;
  border-left: 4px solid #27ae60;
}

.success-icon {
  font-size: 3rem;
  margin-bottom: 1rem;
}

.close-btn {
  background: #27ae60;
  color: white;
  border: none;
  padding: 0.5rem 1.5rem;
  border-radius: 6px;
  cursor: pointer;
  margin-top: 1rem;
  transition: background-color 0.2s;
}

.close-btn:hover {
  background: #219653;
}

/* Анимации */
.success-enter-active,
.success-leave-active {
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.success-enter-from {
  opacity: 0;
  transform: scale(0.8) translateY(-20px);
}

.success-leave-to {
  opacity: 0;
  transform: scale(0.9) translateY(10px);
}
</style>
