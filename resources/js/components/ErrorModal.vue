<script setup>
import { defineProps, defineEmits } from 'vue';

let props = defineProps(['showError', 'errorMessage', 'title']);
let emit = defineEmits(['update:showError']);

function hideError(){
    emit('update:showError', false);
}

</script>

<template>
   <Transition name="error">
      <div v-if="showError" class="error-modal">
        <div class="error-content">
          <div class="error-icon">⚠️</div>
          <h3>{{ title }}</h3>
          <p>{{ errorMessage }}</p>
          <button @click="hideError" class="close-btn">Закрыть</button>
        </div>
      </div>
    </Transition>
</template>

<style scoped>
    .error-modal {
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

.error-content {
  background: white;
  padding: 2rem;
  border-radius: 12px;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
  max-width: 400px;
  width: 90%;
  text-align: center;
  border-left: 4px solid #e74c3c;
}

.error-icon {
  font-size: 3rem;
  margin-bottom: 1rem;
}

.close-btn {
  background: #e74c3c;
  color: white;
  border: none;
  padding: 0.5rem 1.5rem;
  border-radius: 6px;
  cursor: pointer;
  margin-top: 1rem;
}

/* Анимации */
.error-enter-active,
.error-leave-active {
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.error-enter-from {
  opacity: 0;
  transform: scale(0.8) translateY(-20px);
}

.error-leave-to {
  opacity: 0;
  transform: scale(0.9) translateY(10px);
}

</style>
