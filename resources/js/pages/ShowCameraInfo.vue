<script setup>
import { ref } from 'vue';
import { useMarkersStore } from '../stores/markers';
import { useRoute, useRouter } from 'vue-router';

const store = useMarkersStore();
const route = useRoute();
const router = useRouter();

const findedCamera = store.markers.find(camera => camera.id == route.params.id);
document.title = findedCamera.real_camera_id;

const camera = ref({
    ...findedCamera
});

const goBack = () => {
    router.go(-1);
}

</script>

<template>
<div class="camera-info-page">
    <div class="container">
      <h1>Информация о камере</h1>

      <div class="camera-info-grid">
        <!-- Первый столбец -->
        <div class="info-column">
          <h4 class="column-title">Базовая идентификация и местоположение</h4>
          <div class="info-content">
            <div class="info-item">
              <strong>ID камеры:</strong> {{ camera.real_camera_id || 'Не указан' }}
            </div>
            <div class="info-item">
              <strong>Название/Алиас:</strong> {{ camera.name || 'Без названия' }}
            </div>
            <div class="info-item">
              <strong>Точный адрес:</strong> {{ camera.adress || 'Не указан' }}
            </div>
            <div class="info-item">
              <strong>Статус:</strong> {{ camera.status || 'Не указан' }}
            </div>
            <div class="info-item">
              <strong>Геолокация (координаты):</strong>
            </div>
            <div class="sub-info">
              <strong>Ш:</strong> {{ parseFloat(camera.latitude).toFixed(6) }}<br>
              <strong>Д:</strong> {{ parseFloat(camera.longitude).toFixed(6) }}
            </div>
          </div>
        </div>

        <!-- Второй столбец -->
        <div class="info-column">
          <h4 class="column-title secondary">Административная и эксплуатационная информация</h4>
          <div class="info-content">
            <div class="info-item">
              <strong>Ответственное подразделение:</strong> {{ camera.exploitation_info.currentCorp || 'Не указано' }}
            </div>
            <div class="info-item">
              <strong>Ответственное лицо:</strong> {{ camera.exploitation_info.currentPerson || 'Не указано' }}
            </div>
            <div class="info-item">
              <strong>Дата ввода в эксплуатацию:</strong>
            </div>
            <div class="sub-info">
              <strong>дд.мм.гггг</strong> {{ camera.exploitation_info.dateExpluatation || 'Не указана' }}
            </div>
            <div class="info-item">
              <strong>Гарантийный срок:</strong> {{ camera.exploitation_info.dateGuarantee || 'Не указан' }}
            </div>
            <div class="info-item">
              <strong>Балансовая стоимость/</strong>
            </div>
            <div class="sub-info">
              <strong>Инвентарный номер:</strong> {{ camera.exploitation_info.inventNumber || 'Не указан' }}
            </div>
          </div>
        </div>
      </div>

      <div class="actions">
        <button @click="goBack" class="back-button">Назад к списку</button>
      </div>
    </div>
  </div>
</template>

<style scoped>
.camera-info-page {
  min-height: 100vh;
  background-color: #f5f5f5;
  padding: 20px;
}

.container {
  max-width: 900px;
  margin: 0 auto;
  background: white;
  border-radius: 10px;
  padding: 30px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

h1 {
  text-align: center;
  color: #333;
  margin-bottom: 30px;
  font-size: 28px;
}

.camera-info-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 30px;
  margin-bottom: 30px;
}

.info-column {
  background: #f8f9fa;
  padding: 20px;
  border-radius: 8px;
  border: 1px solid #e9ecef;
}

.column-title {
  margin: 0 0 15px 0;
  padding-bottom: 10px;
  border-bottom: 2px solid #2563eb;
  font-size: 16px;
  color: #2563eb;
}

.column-title.secondary {
  border-bottom-color: #dc2626;
  color: #dc2626;
}

.info-content {
  font-size: 14px;
  line-height: 1.6;
}

.info-item {
  margin-bottom: 12px;
}

.sub-info {
  margin-left: 15px;
  margin-bottom: 12px;
  color: #666;
}

.actions {
  text-align: center;
}

.back-button {
  background-color: #2563eb;
  color: white;
  border: none;
  padding: 12px 24px;
  border-radius: 6px;
  cursor: pointer;
  font-size: 16px;
  transition: background-color 0.3s;
}

.back-button:hover {
  background-color: #1d4ed8;
}

@media (max-width: 768px) {
  .camera-info-grid {
    grid-template-columns: 1fr;
    gap: 20px;
  }

  .container {
    padding: 20px;
    margin: 10px;
  }
}
</style>
