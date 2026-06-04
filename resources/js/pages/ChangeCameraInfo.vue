<script setup>
import { ref, onMounted } from 'vue';
import ErrorModal from '../components/ErrorModal.vue';
import OpacityWindow from '../components/OpacityWindow.vue';
import Loader from '../components/Loader.vue';
import SuccessModal from '../components/SuccessModal.vue';
import { useMarkersStore } from '../stores/markers';
import { useRoute, useRouter } from 'vue-router';

const store = useMarkersStore();
const route = useRoute();
const router = useRouter();
const cameraId = route.params.id;

const showError = ref(false);
const errorMessage = ref('');
const title = ref('');

const showSuccess = ref(false);
const successTitle = ref('');
const successMessage = ref('');

const showLoader = ref(false);
const showOpacityWindow = ref(false);

const camera = ref({
    real_camera_id: '',
    name: '',
    adress: '',
    latitude: '',
    longitude: '',
    status: '',
    currentCorp: '',
    currentPerson: '',
    dateExpluatation: '',
    dateGuarantee: '',
    inventNumber: ''
});

onMounted(() => {
    const findedCamera = store.markers.find(camera => camera.id == route.params.id);

    document.title = findedCamera.real_camera_id;

    camera.value.real_camera_id = findedCamera.real_camera_id;
    camera.value.name = findedCamera.name;
    camera.value.adress = findedCamera.adress;
    camera.value.latitude = findedCamera.latitude;
    camera.value.longitude = findedCamera.longitude;
    camera.value.status = findedCamera.status;
    camera.value.currentCorp = findedCamera.exploitation_info.currentCorp;
    camera.value.currentPerson = findedCamera.exploitation_info.currentPerson;
    camera.value.dateExpluatation = findedCamera.exploitation_info.dateExpluatation;
    camera.value.dateGuarantee = findedCamera.exploitation_info.dateGuarantee;
    camera.value.inventNumber = findedCamera.exploitation_info.inventNumber;
});

const updateCameraInfo = async () => {
    showOpacityWindow.value = true;
    showLoader.value = true;

    try {
        const response = await axios.put(`/api/cameras/${cameraId}`, camera.value);

        if(response.status >= 200 && response.status <= 299){
            showLoader.value = false;
            showOpacityWindow.value = false;

            showSuccess.value = true;
            successMessage.value = 'Данные обновлены';
            successTitle.value = 'Обновление данных о камере';

            store.reset();
        } else {
            showLoader.value = false;
            showOpacityWindow.value = false;

            showError.value = true;
            errorMessage.value = 'Ошибка обновления данных';
            title.value = 'Обновление данных о камере';
        }
    } catch (error) {
        showLoader.value = false;
        showOpacityWindow.value = false;

        showError.value = true;
        errorMessage.value = 'Ошибка на стороне сервера';
        title.value = 'Обновление данных о камере';
    }
}

const goBack = () => {
    router.go(-1);
}

</script>

<template>
<div>
 <div class="camera-edit-page">
    <div class="container">
      <h1>Редактирование камеры</h1>

      <form @submit.prevent="submitForm" class="camera-form">
        <div class="form-grid">
          <!-- Первый столбец -->
          <div class="form-column">
            <h4 class="column-title">Базовая идентификация и местоположение</h4>

            <div class="form-group">
              <label for="real_camera_id">ID камеры</label>
              <input
                id="real_camera_id"
                v-model="camera.real_camera_id"
                type="text"
                required
                placeholder="Введите ID камеры"
              >
            </div>

            <div class="form-group">
              <label for="name">Название/Алиас</label>
              <input
                id="name"
                v-model="camera.name"
                type="text"
                required
                placeholder="Введите название камеры"
              >
            </div>

            <div class="form-group">
              <label for="adress">Точный адрес</label>
              <textarea
                id="adress"
                v-model="camera.adress"
                rows="3"
                required
                placeholder="Введите полный адрес"
              ></textarea>
            </div>

            <div class="form-group">
              <label for="status">Статус</label>
              <select name="status" v-model="camera.status">
                <option value="online">Онлайн</option>
                <option value="offline">Оффлайн</option>
                <option value="recording">Запись</option>
              </select>
            </div>

            <div class="coordinates-group">
              <h5>Геолокация (координаты)</h5>
              <div class="coordinates-inputs">
                <div class="form-group">
                  <label for="latitude">Широта</label>
                  <input
                    id="latitude"
                    v-model="camera.latitude"
                    type="number"
                    step="any"
                    required
                    placeholder="Широта"
                  >
                </div>
                <div class="form-group">
                  <label for="longitude">Долгота</label>
                  <input
                    id="longitude"
                    v-model="camera.longitude"
                    type="number"
                    step="any"
                    required
                    placeholder="Долгота"
                  >
                </div>
              </div>
            </div>
          </div>

          <!-- Второй столбец -->
          <div class="form-column">
            <h4 class="column-title secondary">Административная и эксплуатационная информация</h4>

            <div class="form-group">
              <label for="currentCorp">Ответственное подразделение</label>
              <input
                id="currentCorp"
                v-model="camera.currentCorp"
                type="text"
                required
                placeholder="Введите название подразделения"
              >
            </div>

            <div class="form-group">
              <label for="currentPerson">Ответственное лицо</label>
              <input
                id="currentPerson"
                v-model="camera.currentPerson"
                type="text"
                required
                placeholder="ФИО ответственного лица"
              >
            </div>

            <div class="form-group">
              <label for="dateExpluatation">Дата ввода в эксплуатацию</label>
              <input
                id="dateExpluatation"
                v-model="camera.dateExpluatation"
                type="date"
                required
              >
            </div>

            <div class="form-group">
              <label for="dateGuarantee">Гарантийный срок</label>
              <input
                id="dateGuarantee"
                v-model="camera.dateGuarantee"
                type="text"
                placeholder="Например: 1 г., 2 года"
              >
            </div>

            <div class="form-group">
              <label for="inventNumber">Инвентарный номер</label>
              <input
                id="inventNumber"
                v-model="camera.inventNumber"
                type="text"
                placeholder="Введите инвентарный номер"
              >
            </div>
          </div>
        </div>

        <div class="form-actions">
          <button type="button" @click="goBack" class="btn-secondary">Отмена</button>
          <button type="submit" @click="updateCameraInfo" class="btn-primary">Сохранить изменения</button>
        </div>
      </form>
    </div>
  </div>


  <ErrorModal v-model:show-error="showError"
              :error-message="errorMessage"
              :title="title">
  </ErrorModal>

  <SuccessModal v-model:show-success="showSuccess"
                :success-title="successTitle"
                :success-message="successMessage">

  </SuccessModal>

  <OpacityWindow v-if="showOpacityWindow"></OpacityWindow>
  <Loader v-if="showLoader"></Loader>
  </div>
</template>

<style scoped>
.camera-edit-page {
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

.form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 30px;
  margin-bottom: 30px;
}

.form-column {
  background: #f8f9fa;
  padding: 20px;
  border-radius: 8px;
  border: 1px solid #e9ecef;
}

.column-title {
  margin: 0 0 20px 0;
  padding-bottom: 10px;
  border-bottom: 2px solid #2563eb;
  font-size: 16px;
  color: #2563eb;
}

.column-title.secondary {
  border-bottom-color: #dc2626;
  color: #dc2626;
}

.form-group {
  margin-bottom: 20px;
}

.form-group label {
  display: block;
  margin-bottom: 5px;
  font-weight: 600;
  color: #333;
  font-size: 14px;
}

.form-group input,
.form-group textarea,
.form-group select {
  width: 100%;
  padding: 10px;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 14px;
  box-sizing: border-box;
}

.form-group input:focus,
.form-group textarea:focus,
.form-group select:focus {
  outline: none;
  border-color: #2563eb;
  box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.1);
}

.form-group textarea {
  resize: vertical;
  min-height: 80px;
}

.coordinates-group h5 {
  margin: 0 0 15px 0;
  color: #666;
  font-size: 14px;
}

.coordinates-inputs {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 15px;
}

.form-actions {
  display: flex;
  justify-content: center;
  gap: 20px;
  margin-top: 30px;
}

.btn-primary,
.btn-secondary {
  padding: 12px 30px;
  border: none;
  border-radius: 6px;
  font-size: 16px;
  cursor: pointer;
  transition: all 0.3s;
}

.btn-primary {
  background-color: #2563eb;
  color: white;
}

.btn-primary:hover {
  background-color: #1d4ed8;
}

.btn-secondary {
  background-color: #6b7280;
  color: white;
}

.btn-secondary:hover {
  background-color: #4b5563;
}

@media (max-width: 768px) {
  .form-grid {
    grid-template-columns: 1fr;
    gap: 20px;
  }

  .coordinates-inputs {
    grid-template-columns: 1fr;
  }

  .container {
    padding: 20px;
    margin: 10px;
  }

  .form-actions {
    flex-direction: column;
  }
}
</style>
