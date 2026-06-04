<script setup>
import { ref, onMounted } from 'vue'
import OpacityWindow from '../components/OpacityWindow.vue';
import Loader from '../components/Loader.vue';
import ErrorModal from '../components/ErrorModal.vue';
import SuccessModal from '../components/SuccessModal.vue';

const localPhotos = ref([]);
const selectedPhoto = ref(null);
const dbPhotoName = ref('');
const localFileInput = ref(null);
let selectedLocalFiles = [];
const message = ref('');

const showError = ref(false);
const errorMessage = ref('');
const title = ref('');

const showSuccess = ref(false);
const successTitle = ref('');
const successMessage = ref('');

const showLoader = ref(false);
const showOpacityWindow = ref(false);

let db = null;

const initDB = async () => {
  return new Promise((resolve, reject) => {
    const request = indexedDB.open('PhotosDB', 1);

    request.onerror = () => reject(request.error);
    request.onsuccess = () => {
      db = request.result;
      loadPhotosFromDB();
      resolve(db);
    };

    request.onupgradeneeded = (event) => {
      const database = event.target.result;
      if (!database.objectStoreNames.contains('photos')) {
        database.createObjectStore('photos', { keyPath: 'id' });
      }
    };
  });
};

const loadPhotosFromDB = async () => {
  return new Promise((resolve, reject) => {
    const transaction = db.transaction(['photos'], 'readonly');
    const store = transaction.objectStore('photos');
    const request = store.getAll();

    request.onsuccess = () => {
      localPhotos.value = request.result;
      console.log('Загружено фото из DB:', localPhotos.value.length);
      resolve(request.result);
    };

    request.onerror = () => reject(request.error);
  });
};

const savePhotoToDB = async (photo) => {
  return new Promise((resolve, reject) => {
    const transaction = db.transaction(['photos'], 'readwrite');
    const store = transaction.objectStore('photos');
    const request = store.add(photo);

    request.onsuccess = () => resolve(request.result);
    request.onerror = () => reject(request.error);
  });
};

const deletePhotoFromDB = async (photoId) => {
  return new Promise((resolve, reject) => {
    const transaction = db.transaction(['photos'], 'readwrite');
    const store = transaction.objectStore('photos');
    const request = store.delete(photoId);

    request.onsuccess = () => resolve();
    request.onerror = () => reject(request.error);
  });
};

onMounted(() => {
  initDB();
});

const handleDbFiles = (event) => {
  console.log('Файлы для БД:', event.target.files)
}

const uploadToDb = async () => {
  if (!dbPhotoName.value.trim() || dbPhotoName.value.trim().includes(' ')) {
    showMessage('Введите название файла', 'Ошибка')
    return;
  }

  try {
    const form = document.querySelector("form");
    const formData = new FormData(form);
    formData.append('callback_url', 'http://localhost:8080/api/photo-callback')

    const response = await axios.post('/api/rust-photos', formData, {
        timeout: 1800000
    });

    if(response.data.success){
        message.value = response.data.data.status;
    }

    proccesingPhotos(response.data.data.report_id);
  } catch (error) {
    if(error?.response?.status == 422){
        showMessage("Имя не задано", 'Ошибка отправки');
    } else {
         showMessage('ошибка на стороне сервера', 'Ошибка ошибка');
    }
  }
}

const showMessage = (text, type) => {
    showError.value = true;
    errorMessage.value = text;
    title.value = type;
}

const proccesingPhotos = async (reportId) => {
  try {
    const eventSource = new EventSource(`/api/photo-events?report_id=${reportId}`);

    eventSource.onmessage = (event) => {
      const data = JSON.parse(event.data);

      if (data.status === 'closed') {
        eventSource.close();
        message.value = '';
        message.value = "Файлы отправлены";
        message.value += data.error_files.trim() != "" ? `.Файлы с ошибкой отправки: ${data.error_files}` : "";

        showSuccess.value = true;
        successMessage.value = message;
        successTitle.value = "Отправка файлов";

      } else if (data.status === 'failed'){
        eventSource.close();
        message.value = '';
        message.value = "Ошибка отправки";
        message.value += data.error_files.trim() != "" ? `.Файлы с ошибкой отправки: ${data.error_files}` : "";

        showError.value = true;
        errorMessage.value = message.value;
        title.value = "Отправка файлов";
      }
    }

    eventSource.onerror = () => {
      eventSource.close();

      showError.value = true;
      errorMessage.value = "Ошибка соединения";
      title.value = "Создание отчёта";
    }

  } catch (error) {
    showError.value = true;
    errorMessage.value = "Ошибка на стороне сервера";
    title.value = "Создание отчёта";
  }
}


const handleLocalFiles = (event) => {
  selectedLocalFiles = Array.from(event.target.files)
  console.log('Выбрано файлов:', selectedLocalFiles.length)
}

const uploadLocal = async () => {
  if (selectedLocalFiles.length === 0) {
    showError.value = true;
    errorMessage.value = 'Выберите файлы';
    title.value = 'Ошибка';
    return
  }

  showLoader.value = true;

  try {
    for (const file of selectedLocalFiles) {
      const photoObj = {
        id: Date.now() + Math.random(),
        name: file.name,
        size: file.size,
        type: file.type,
        uploadDate: new Date().toISOString(),
        data: await readFileAsDataURL(file)
      }

      await savePhotoToDB(photoObj);
      localPhotos.value.push(photoObj);
    }

    selectedLocalFiles = [];
    if (localFileInput.value) {
      localFileInput.value.value = '';
    }

    showSuccess.value = true;
    successMessage.value = `Добавлено ${localPhotos.value.length} снимков`;
    successTitle.value = '';

  } catch (error) {
    console.error('Ошибка загрузки:', error);
    showError.value = true;
    errorMessage.value = 'Ошибка при загрузке снимков';
    title.value = 'Ошибка';
  } finally {
    showLoader.value = false;
  }
}

const readFileAsDataURL = (file) => {
  return new Promise((resolve) => {
    const reader = new FileReader();
    reader.onload = (e) => resolve(e.target.result);
    reader.readAsDataURL(file);
  });
}

const selectPhoto = (photo) => {
  selectedPhoto.value = photo;
}

const deletePhoto = async (photoId) => {
  try {
    await deletePhotoFromDB(photoId);
    localPhotos.value = localPhotos.value.filter(p => p.id !== photoId);

    showSuccess.value = true;
    successMessage.value = 'Снимок удален';
    successTitle.value = '';
  } catch (error) {
    console.error('Ошибка удаления:', error);
    showError.value = true;
    errorMessage.value = 'Ошибка при удалении снимка';
    title.value = 'Ошибка';
  }
}
</script>

<template>
<div>
  <div class="photos-page">
    <h1>Снимки ({{ localPhotos.length }})</h1>

<div class="menu">
    <!-- <form @submit.prevent="uploadToDb" enctype="multipart/form-data">
      <div class="upload-option">
        <h3>Отправка</h3>
        <input
          type="file"
          name="photos[]"
          @change="handleDbFiles"
          accept="image/*"
          multiple
        />
        <input
          type="text"
          v-model="dbPhotoName"
          name="title"
          placeholder="Название для всех фото"
        />
        <button class="btn db-btn">Отправить</button>
      </div>
    </form> -->

      <div class="upload-option">
        <h3>Локально</h3>
        <input
          type="file"
          ref="localFileInput"
          @change="handleLocalFiles"
          accept="image/*"
          multiple
        />
        <button @click="uploadLocal" class="btn local-btn">Загрузить</button>
      </div>
    </div>

    <div class="gallery">
      <div
        v-for="photo in localPhotos"
        :key="photo.id"
        class="photo-card"
        @click="selectPhoto(photo)"
      >
        <img :src="photo.data" :alt="photo.name" />
        <span>{{ photo.name }}</span>
        <button @click.stop="deletePhoto(photo.id)" class="delete-btn">🗑️</button>
      </div>
    </div>

    <div v-if="localPhotos.length === 0" class="empty-state">
      <p>Нет загруженных снимков</p>
    </div>

    <div v-if="selectedPhoto" class="viewer" @click="selectedPhoto = null">
      <img :src="selectedPhoto.data" :alt="selectedPhoto.name" />
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
.photos-page {
  max-width: 1200px;
  margin: 0 auto;
  padding: 20px;
}

.menu {
  display: flex;
  gap: 40px;
  margin-bottom: 30px;
  padding: 20px;
  background: #f5f5f5;
  border-radius: 10px;
}

.upload-option {
  flex: 1;
}

.upload-option h3 {
  margin-top: 0;
}

.upload-option input,
.upload-option select {
  display: block;
  width: 100%;
  margin: 10px 0;
  padding: 8px;
  border: 1px solid #ddd;
  border-radius: 5px;
}

form {
    width: 50%;
}

.btn {
  padding: 10px 20px;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  color: white;
  margin-top: 10px;
}

.db-btn {
  background: #007bff;
}

.local-btn {
  background: #28a745;
}

.gallery {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 15px;
}

.photo-card {
  position: relative;
  border: 1px solid #ddd;
  border-radius: 8px;
  overflow: hidden;
  cursor: pointer;
  background: white;
}

.photo-card img {
  width: 100%;
  height: 150px;
  object-fit: cover;
}

.photo-card span {
  display: block;
  padding: 8px;
  font-size: 14px;
  text-align: center;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.delete-btn {
  position: absolute;
  top: 5px;
  right: 5px;
  background: rgba(220, 53, 69, 0.8);
  color: white;
  border: none;
  border-radius: 50%;
  width: 25px;
  height: 25px;
  cursor: pointer;
  font-size: 12px;
}

.viewer {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0,0,0,0.8);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.viewer img {
  max-width: 90%;
  max-height: 90%;
  object-fit: contain;
}

.empty-state {
  text-align: center;
  padding: 40px;
  color: #666;
}
</style>
