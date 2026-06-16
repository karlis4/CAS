<script setup>
import { ref, onMounted, onUnmounted, reactive } from 'vue'
import { useMarkersStore } from '../stores/markers'
import axios from 'axios'
import ErrorModal from '../components/ErrorModal.vue';
import SuccessModal from '../components/SuccessModal.vue';
import OpacityWindow from '../components/OpacityWindow.vue';
import Loader from '../components/Loader.vue';
import CamerasListModal from '../components/CamerasListModal.vue';
import GeocodingComponent from '../components/GeocodingComponent.vue';
import Logout from '../components/Logout.vue';
import ExcelRustService from '../components/ExcelRustService.vue';
import router from '../router';

import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

delete L.Icon.Default.prototype._getIconUrl;
L.Icon.Default.mergeOptions({
    iconRetinaUrl: new URL('leaflet/dist/images/marker-icon-2x.png', import.meta.url).href,
    iconUrl: new URL('leaflet/dist/images/marker-icon.png', import.meta.url).href,
    shadowUrl: new URL('leaflet/dist/images/marker-shadow.png', import.meta.url).href,
});

const mapContainer = ref(null);
const map = ref(null);
const store = useMarkersStore();
const isAddMode = ref(false);
const mapLoaded = ref(false);

const showError = ref(false);
const errorMessage = ref('');
const title = ref('');

const showSuccess = ref(false);
const successTitle = ref('');
const successMessage = ref('');

const showLoader = ref(false);
const showOpacityWindow = ref(false);

const isHide = ref(true);
const isHideCamerasList = ref(true);
const isHideExcelForm = ref(true);

let createdMarker = reactive({
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
    inventNumber: '',
    instance: null
});

onMounted(() => {
    initMap();
    initMarkers();
})

onUnmounted(() => {
    cleanup()
})

function initMap() {
    try {
        map.value = L.map(mapContainer.value).setView([45.0428, 41.9734], 13)

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors',
            maxZoom: 18
        }).addTo(map.value)

        map.value.on('click', (e) => {
            if (isAddMode.value) {
                hideForm();
                createdMarker.latitude = e.latlng.lat;
                createdMarker.longitude = e.latlng.lng;
            }
        })

        mapLoaded.value = true
        console.log('Leaflet карта загружена!')

    } catch (error) {
        console.error('Ошибка инициализации Leaflet:', error)
    }
}

async function initMarkers() {
    showOpacityWindow.value = true;
    showLoader.value = true;

    try {
        const response = await axios.get('/api/cameras');

        if (response.data.length == 0) {
            showError.value = true;
            errorMessage.value = response.data.error;
            title.value = 'Ошибка загрузки';
            return;
        }

        response.data.forEach(camera => {

            let isInclude = false;

            isInclude = store.markers.some(storeCamera => camera.id == storeCamera.id);

            if (!isInclude) {
                store.markers.push({ ...camera, instance: null });
            }

        });

        store.markers.forEach(camera => {
            camera.instance = L.marker([camera.latitude, camera.longitude])
                .addTo(map.value)
                .bindPopup(`
                    <div style="width: 310px; font-family: Arial, sans-serif; background: white;">
                    <div style="display: block;">
                        <div>
                            <h4 style="margin: 0 0 10px 0; color: #2563eb; border-bottom: 2px solid #2563eb; padding-bottom: 5px;">
                                Базовая идентификация и местоположение
                            </h4>
                            <div style="font-size: 13px; line-height: 1.6;">
                                <div><strong>ID камеры:</strong> ${camera.real_camera_id || 'Не указан'}</div>
                                <div><strong>Название/Алиас:</strong> ${camera.name || 'Без названия'}</div>
                                <div><strong>Точный адрес:</strong> ${camera.adress || 'Не указан'}</div>
                                <div><strong>Статус:</strong> ${camera.status}</div>
                                <div><strong>Геолокация (координаты):</strong></div>
                                <div style="margin-left: 10px;">
                                    <strong>Ш:</strong> ${parseFloat(camera.latitude).toFixed(6)}<br>
                                    <strong>Д:</strong> ${parseFloat(camera.longitude).toFixed(6)}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                 `)
        });

        showOpacityWindow.value = false;
        showLoader.value = false;

    } catch (error) {
        showOpacityWindow.value = false;
        showLoader.value = false;

        showError.value = true;
        errorMessage.value = 'Ошибка сервера или камер не найдено';
        title.value = 'Ошибка загрузки';
    }
}

async function addMarker() {
    showOpacityWindow.value = true;
    showLoader.value = true;

    try {
        const response = await axios.post('/api/cameras', {
            real_camera_id: createdMarker.real_camera_id,
            name: createdMarker.name,
            adress: createdMarker.adress,
            latitude: `${createdMarker.latitude}`,
            longitude: `${createdMarker.longitude}`,
            status: createdMarker.status,
            currentCorp: createdMarker.currentCorp,
            currentPerson: createdMarker.currentPerson,
            dateExpluatation: createdMarker.dateExpluatation,
            dateGuarantee: createdMarker.dateGuarantee,
            inventNumber: createdMarker.inventNumber,
        });

        if (response.status >= 200 && response.status <= 299) {

            createdMarker.instance = L.marker([createdMarker.latitude, createdMarker.longitude]).addTo(map.value)
                .bindPopup(`
                    <div style="width: 310px; font-family: Arial, sans-serif; background: white;">
                    <div style="display: block;">
                        <div>
                            <h4 style="margin: 0 0 10px 0; color: #2563eb; border-bottom: 2px solid #2563eb; padding-bottom: 5px;">
                                Базовая идентификация и местоположение
                            </h4>
                            <div style="font-size: 13px; line-height: 1.6;">
                                <div><strong>ID камеры:</strong> ${createdMarker.real_camera_id || 'Не указан'}</div>
                                <div><strong>Название/Алиас:</strong> ${createdMarker.name || 'Без названия'}</div>
                                <div><strong>Точный адрес:</strong> ${createdMarker.adress || 'Не указан'}</div>
                                <div><strong>Статус:</strong> ${createdMarker.status}</div>
                                <div><strong>Геолокация (координаты):</strong></div>
                                <div style="margin-left: 10px;">
                                    <strong>Ш:</strong> ${parseFloat(createdMarker.latitude).toFixed(6)}<br>
                                    <strong>Д:</strong> ${parseFloat(createdMarker.longitude).toFixed(6)}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                 `);

            store.markers.push({ id: response.data.id, ...createdMarker });

            createdMarker.real_camera_id = '';
            createdMarker.name = '';
            createdMarker.adress = '';
            createdMarker.latitude = '';
            createdMarker.longitude = '';
            createdMarker.status = '';
            createdMarker.currentCorp = '';
            createdMarker.currentPerson = '';
            createdMarker.dateExpluatation = '';
            createdMarker.dateGuarantee = '';
            createdMarker.inventNumber = '';
            createdMarker.instance = null;

            showLoader.value = false;

            if(!isHide){
                showOpacityWindow.value = false;
            }

            showSuccess.value = true;
            successMessage.value = 'Камера добавлена';
            successTitle.value = 'Добавление камеры';
        } else {
            showLoader.value = false;

            if(!isHide){
                showOpacityWindow.value = false;
            }

            showError.value = true;
            errorMessage.value = 'Ошибка добавления камеры';
            title.value = 'Добавление камеры';
        }

    } catch (error) {
        showLoader.value = false;

        if(!isHide){
            showOpacityWindow.value = false;
        }

        showError.value = true;
        errorMessage.value = 'Ошибка на стороне сервера';
        title.value = 'Добавление камеры';
    }

}

async function removeMarker(cameraId) {
    showOpacityWindow.value = true;
    showLoader.value = true;

    try {
        const response = await axios.delete(`/api/cameras/${cameraId}`);

        if (response.status >= 200 && response.status <= 299) {
            const camera = store.markers.findIndex(camera => camera.id == cameraId);

            map.value.removeLayer(store.markers[camera].instance);
            store.markers.splice(camera, 1);

            if(isHideCamerasList.value){
                showOpacityWindow.value = false;
            }

            showLoader.value = false;

            showSuccess.value = true;
            successMessage.value = 'Камера удалена';
            successTitle.value = 'Удаление камеры';
        } else {
            if(isHideCamerasList.value){
                showOpacityWindow.value = false;
            }

            showLoader.value = false;

            showError.value = true;
            errorMessage.value = 'Ошибка удаления камеры';
            title.value = 'Удаление камеры';
        }

    } catch (error) {
        if(isHideCamerasList.value){
            showOpacityWindow.value = false;
        }

        showLoader.value = false;

        showError.value = true;
        errorMessage.value = 'Ошибка на стороне сервера или камера не существует';
        title.value = 'Удаление камеры';
    }
}

function toggleAddMode() {
    isAddMode.value = !isAddMode.value
}

async function clearMarkers() {
    showOpacityWindow.value = true;
    showLoader.value = true;

    try {
        const response = await axios.delete('/api/cameras');

        if (response.status >= 200 && response.status <= 299) {
            store.markers.forEach(camera => {
                map.value.removeLayer(camera.instance);
            });

            store.markers = [];

            showOpacityWindow.value = false;
            showLoader.value = false;

            showSuccess.value = true;
            successMessage.value = `${response.data.message}`;
            successTitle.value = 'Удаление камер';
        } else {

            showOpacityWindow.value = false;
            showLoader.value = false;

            showSuccess.value = true;
            successMessage.value = 'Ошибка при удалении камер';
            successTitle.value = 'Удаление камер';
        }

    } catch (error) {

        showOpacityWindow.value = false;
        showLoader.value = false;

        showSuccess.value = true;
        successMessage.value = 'Ошибка на стороне сервера или камер не существует';
        successTitle.value = 'Удаление камер';
    }

}

function hideForm() {
    showOpacityWindow.value = !showOpacityWindow.value;
    isHide.value = !isHide.value;
}

const hideCamerasList = () => {
    showOpacityWindow.value = !showOpacityWindow.value;
    isHideCamerasList.value = !isHideCamerasList.value;
}

const hideExcelFileForm = () => {
    showOpacityWindow.value = !showOpacityWindow.value;
    isHideExcelForm.value = !isHideExcelForm.value;
}

const goPhotosSide = () => {
    router.push({ name: 'photos'});
}

const goVideosSide = () => {
    router.push({ name: 'videos' });
}

function cleanup() {
    if (map.value) {
        map.value.remove()
    }
}
</script>

<template>
    <div>
        <div>
            <div class="controls">
                <button @click="toggleAddMode" :class="{ active: isAddMode }" class="action-btn">
                    {{ isAddMode ? '✅ Режим добавления' : '➕ Добавить метку' }}
                </button>
                <button @click="clearMarkers" class="clear-btn">🗑️ Очистить все</button>
                <button class="show-btn" @click="hideCamerasList">
                    <svg width="32" height="17" viewBox="0 0 32 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect x="6" y="10" width="16" height="12" rx="2" fill="#3B82F6" stroke="#1D4ED8"
                            stroke-width="2" />
                        <path d="M22 12L26 9V23L22 20" stroke="#1D4ED8" stroke-width="2" fill="#3B82F6" />
                        <circle cx="14" cy="16" r="2" fill="#1D4ED8" />
                    </svg>Список камер
                </button>
                <Logout></Logout>
                <span class="counter">Меток: {{ store.markers.length }}</span>
            </div>
            <div id="leaflet-map" ref="mapContainer"></div>
            <button class="excelFile-btn" @click="hideExcelFileForm">Excel-файл</button>
            <button class="photos-btn" @click="goPhotosSide">Снимки</button>
            <button class="videos-btn" @click="goVideosSide">Видео</button>
        </div>

        <Transition name="slide-fade">
        <div v-if="!isHide" class="addForm">
            <h2>Добавить метку</h2>
            <form @submit.prevent="addMarker">
                <div class="parentContainer">
                    <div class="num_one">
                        <h6>Базовая идентификация и <br></br>местоположкение</h6>
                        <ul>
                            <li>
                                <label for="cameraId">ID камеры:</label><br></br>
                                <input type="text" name="cameraId" v-model="createdMarker.real_camera_id"></input>
                            </li>
                            <li>
                                <label for="alias">Название/Алиас:</label><br></br>
                                <input type="text" name="alias" v-model="createdMarker.name"></input>
                            </li>
                            <li>
                                <label for="correctAdress">Точный адрес:</label><br></br>
                                <input type="text" name="correctAdress" v-model="createdMarker.adress"></input>
                            </li>
                            <li>
                                <span>Геолокация (координаты):</span><br></br>
                                <label for="latitude">Ш:</label><input type="text" name="latitude"
                                    v-model="createdMarker.latitude"></input>
                                <label for="longitude">Д:</label><input type="text" name="longitude"
                                    v-model="createdMarker.longitude"></input>
                            </li>
                            <li>
                                <label for="status">Статус:</label>
                                <select name="status" v-model="createdMarker.status">
                                    <option value="online">Онлайн</option>
                                    <option value="offline">Оффлайн</option>
                                    <option value="recording">Запись</option>
                                </select>
                            </li>
                        </ul>
                        <p class="geoText">Для точного поределения адреса вы можете воспользоваться функцией
                            геодекодирования:</p>
                        <GeocodingComponent :created-marker="createdMarker"
                            @update:address="(newAddress) => createdMarker.adress = newAddress">
                        </GeocodingComponent>
                    </div>

                    <div class="num_two">
                        <h6>Административная и <br></br>эксплуатационная информация</h6>
                        <ul>
                            <li>
                                <label for="currentSquad">Ответственное подразделение:</label><br></br>
                                <input type="text" name="currentSquad" v-model="createdMarker.currentCorp"></input>
                            </li>
                            <li>
                                <label for="currentPerson">Ответственное лицо:</label><br></br>
                                <input type="text" name="currentPerson" v-model="createdMarker.currentPerson"></input>
                            </li>
                            <li>
                                <label for="dateExpluatation">Дата ввода в эксплуатацию:</label><br></br>
                                <input type="date" name="dateExpluatation"
                                    v-model="createdMarker.dateExpluatation"></input>
                            </li>
                            <li>
                                <label for="guarantee">Гарантийный срок:</label><br></br>
                                <input type="text" name="guarantee" v-model="createdMarker.dateGuarantee"></input>
                            </li>
                            <li>
                                <label for="inventNum">Балансовая стоимость/<br></br>Инвентарный номер:</label><br></br>
                                <input type="text" name="inventNum" v-model="createdMarker.inventNumber"></input>
                            </li>
                        </ul>
                        <button class="cancelButton" @click.stop="hideForm">Отмена</button><button
                            class="addButton">Добавить</button>
                    </div>
                </div>
            </form>
            <img src="../assests/images/xmark.svg" class="closeAddForm" @click="hideForm"></img>
        </div>
</Transition>

        <ErrorModal v-model:show-error="showError" :error-message="errorMessage" :title="title">
        </ErrorModal>

        <SuccessModal v-model:show-success="showSuccess" :success-title="successTitle"
            :success-message="successMessage">

        </SuccessModal>

        <OpacityWindow v-if="showOpacityWindow"></OpacityWindow>
        <Loader v-if="showLoader"></Loader>

        <CamerasListModal @hide-cameras-window="hideCamerasList" @remove-camera="removeMarker"
            :hide="isHideCamerasList"></CamerasListModal>

        <ExcelRustService @hide-excel-window="hideExcelFileForm" :hide-excel="isHideExcelForm"></ExcelRustService>
    </div>
</template>

<style scoped>
.addForm {
    position: absolute;
    top: 20px;
    left: 300px;
    width: 800px;
    height: 630px;
    background-color: #FFFFFF;
    z-index: 10000;
    border-radius: 10px;
    padding-left: 30px;
    color: #000000;
    border: 1px solid #080b8b;
}

.parentContainer {
    width: 100%;
    height: 576px;
    display: flex;
}

input[name="latitude"],
input[name="longitude"] {
    width: 100px;
    margin-left: 5px;
}

label[for="longitude"] {
    margin-left: 20px;
}

select{
    margin-left: 20px;
    width: 170px;
}

.closeAddForm {
    position: absolute;
    top: 30px;
    left: 770px;
    width: 20px;
    height: 20px;
    cursor: pointer;
}

.parentContainer input {
    border-radius: 10px;
}

h6 {
    margin-left: 50px;
}

.num_one {
    width: 50%;
}

.num_one li {
    font-size: 15px;
}

.num_two {
    width: 50%;
}

.num_two li {
    font-size: 15px;
}

.cancelButton,
.addButton {
    width: 120px;
    height: 50px;
    margin-top: 20px;
    border-radius: 10px;
    background-color: #080b8b;
    color: #FFFFFF;
}

.cancelButton {
    margin-left: 45px;
}

.addButton {
    margin-left: 20px;
}

ul {
    list-style: none;
    margin: 0px;
}

ul li {
    margin-top: 25px;
}

#leaflet-map {
    height: 500px;
    width: 100%;
    border: 2px solid #ccc;
    border-radius: 8px;
}

.controls {
    margin-bottom: 15px;
    padding: 15px;
    background: #f5f5f5;
    border-radius: 8px;
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}

.action-btn {
    padding: 10px 16px;
    border: 2px solid #007bff;
    background: white;
    color: #007bff;
    border-radius: 6px;
    cursor: pointer;
    font-weight: bold;
}

.action-btn.active {
    background: #28a745;
    border-color: #28a745;
    color: white;
}

.clear-btn {
    padding: 10px 16px;
    border: 2px solid #dc3545;
    background: white;
    color: #dc3545;
    border-radius: 6px;
    cursor: pointer;
    font-weight: bold;
}

.clear-btn:hover {
    background: #dc3545;
    color: white;
}

.excelFile-btn {
    padding: 10px 16px;
    border: 2px solid #be6c19;
    background: white;
    color: #be6c19;
    border-radius: 6px;
    cursor: pointer;
    font-weight: bold;
    margin-left: 10px;
    margin-top: 10px;
}

.excelFile-btn:hover {
    background: #be6c19;
    color: white;
}

.photos-btn {
    padding: 10px 16px;
    border: 2px solid #091689;
    background: white;
    color: #091689;
    border-radius: 6px;
    cursor: pointer;
    font-weight: bold;
    margin-left: 10px;
    margin-top: 10px;
}

.photos-btn:hover {
    background: #091689;
    color: white;
}

.videos-btn {
    padding: 10px 16px;
    border: 2px solid #cf4d4d;
    background: white;
    color: #cf4d4d;
    border-radius: 6px;
    cursor: pointer;
    font-weight: bold;
    margin-left: 10px;
    margin-top: 10px;
}

.videos-btn:hover {
    background: #cf4d4d;
    color: white;
}

.show-btn {
    padding: 10px 16px;
    border: 2px solid #36d134;
    background: white;
    color: #36d134;
    border-radius: 6px;
    cursor: pointer;
    font-weight: bold;
}

.show-btn:hover {
    background: #36d134;
    color: white;
}

.counter {
    font-weight: bold;
    color: #495057;
    margin-left: auto;
}

.coordinates-panel {
    margin-top: 20px;
    padding: 15px;
    background: #f1eeee;
    border-radius: 8px;
}

.coordinate-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px;
    background: white;
    border: 1px solid #dee2e6;
    border-radius: 6px;
    margin: 8px 0;
}

.remove-btn {
    background: #6c757d;
    color: white;
    border: none;
    border-radius: 50%;
    width: 28px;
    height: 28px;
    cursor: pointer;
    font-size: 16px;
}

.remove-btn:hover {
    background: #dc3545;
}

.geoText {
    margin-top: 30px;
    font-size: 16px;
}

.slide-fade-enter-active {
    transition: all 0.3s ease;
}

.slide-fade-leave-active {
    transition: all 0.3s cubic-bezier(1, 0.5, 0.8, 1);
}

.slide-fade-enter-from {
    transform: scale(0.9);
    opacity: 0;
}

.slide-fade-leave-to {
    transform: scale(0.9);
    opacity: 0;
}
</style>
