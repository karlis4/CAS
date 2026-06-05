<script setup>
import { ref, defineEmits, defineProps } from 'vue'
import ErrorModal from '../components/ErrorModal.vue';
import OpacityWindow from '../components/OpacityWindow.vue';
import Loader from '../components/Loader.vue';
import SuccessModal from '../components/SuccessModal.vue';
import { useMarkersStore } from '../stores/markers';
import axios from 'axios';

const emit = defineEmits(['hideExcelWindow']);
const props = defineProps(['hideExcel']);

const fileName = ref('');
const message = ref('');

const showError = ref(false);
const errorMessage = ref('');
const title = ref('');

const showSuccess = ref(false);
const successTitle = ref('');
const successMessage = ref('');

const showLoader = ref(false);
const showOpacityWindow = ref(false);

const store = useMarkersStore();

const hideForm = () => {
    fileName.value = '';
    message.value = '';
    emit('hideExcelWindow');
}

const generateExcel = async () => {
    if (!fileName.value.trim()) {
        showMessage('Введите название файла', 'Ошибка');
        return;
    }

    if (fileName.value.trim().includes(' ')) {
        showMessage('Название файла не должно содержать пробелов', 'Ошибка');
        return;
    }

    showLoader.value = true;
    showOpacityWindow.value = true;
    message.value = 'Отправка запроса...';

    try {
        const sendedCameras = store.markers.map(camera => ({
            real_camera_id: camera.real_camera_id,
            name: camera.name,
            adress: camera.adress,
            latitude: `${camera.latitude}`,
            longitude: `${camera.longitude}`,
            status: camera.status,
            current_corp: camera?.exploitation_info?.currentCorp ?? camera.currentCorp,
            current_person: camera?.exploitation_info?.currentPerson ?? camera.currentPerson,
            date_expluatation: camera?.exploitation_info?.dateExpluatation ?? camera.dateExpluatation,
            date_guarantee: camera?.exploitation_info?.dateGuarantee ?? camera.dateGuarantee,
            invent_number: camera?.exploitation_info?.inventNumber ?? camera.inventNumber,
        }));

        const response = await axios.post('/api/rust-excel', {
            cameras: sendedCameras,
            fileName: fileName.value,
            callback_url: 'http://localhost:8080/api/report-callback'
        });

        if (response.data.success) {
            message.value = 'Отчёт создаётся...';
            generateReport(response.data.data.report_id);
        } else {
            throw new Error('Ошибка при создании отчёта');
        }

    } catch (error) {
        console.error(error);
        showMessage('Ошибка создания отчёта', 'Ошибка');
        showLoader.value = false;
        showOpacityWindow.value = false;
    }
}

const showMessage = (text, type) => {
    showError.value = true;
    errorMessage.value = text;
    title.value = type;
}

const generateReport = async (reportId) => {
    try {
        const eventSource = new EventSource(`/api/report-events?report_id=${reportId}`);
        let downloadTriggered = false;

        eventSource.onmessage = (event) => {
            const data = JSON.parse(event.data);
            console.log('EventSource data:', data);  // <-- добавить
            console.log('Status:', data.status);      // <-- добавить
            if (data.status === 'closed' && !downloadTriggered) {
                console.log('Download triggered!');
                downloadTriggered = true;
                eventSource.close();
                message.value = '';

                // Скачиваем файл
                const link = document.createElement('a');
                link.href = `/api/download-report/${reportId}`;
                link.click();

                showSuccess.value = true;
                successMessage.value = "Отчёт создан и скачан!";
                successTitle.value = "Создание отчёта";
                hideForm();

            } else if (data.status === 'failed') {
                eventSource.close();
                message.value = '';
                showMessage("Ошибка при создании отчёта", 'Ошибка');
            }
        };

        eventSource.onerror = () => {
            eventSource.close();
            showMessage("Ошибка соединения с сервером", 'Ошибка');
        };

    } catch (error) {
        showMessage("Ошибка на стороне сервера", 'Ошибка');
    } finally {
        showLoader.value = false;
        showOpacityWindow.value = false;
    }
};
</script>

<template>
    <Transition name="slide-fade">
        <div v-if="!hideExcel" class="excel-save-form">
            <h3>Создание отчёта Excel</h3>

            <div class="form-group">
                <label for="fileName">Название файла:</label>
                <input
                    id="fileName"
                    v-model="fileName"
                    type="text"
                    placeholder="Введите название файла"
                    class="form-input"
                >
                <span class="file-extension">.xlsx</span>
            </div>

            <div class="form-actions">
                <button @click="generateExcel" :disabled="!fileName" class="btn btn-primary">
                    Создать Excel-файл
                </button>
                <button @click="hideForm" class="btn btn-secondary">
                    Отмена
                </button>
            </div>

            <div v-if="message" class="message">{{ message }}</div>
        </div>
    </Transition>

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
</template>

<style scoped>
.excel-save-form {
    position: absolute;
    top: 100px;
    left: 530px;
    z-index: 10000;
    max-width: 500px;
    margin: 0 auto;
    padding: 20px;
    border: 1px solid #ddd;
    border-radius: 8px;
    background: white;
}

.excel-save-form h3 {
    margin-bottom: 20px;
    color: #333;
    text-align: center;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 500;
    color: #333;
}

.form-input {
    width: 400px;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
}

.form-input:focus {
    outline: none;
    border-color: #007bff;
}

.file-extension {
    margin-left: 8px;
    color: #666;
    font-size: 14px;
}

.form-actions {
    display: flex;
    gap: 12px;
    justify-content: center;
    margin-top: 24px;
}

.btn {
    padding: 10px 20px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
    transition: all 0.2s;
}

.btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.btn-primary {
    background: #007bff;
    color: white;
}

.btn-primary:hover:not(:disabled) {
    background: #0056b3;
}

.btn-secondary {
    background: #6c757d;
    color: white;
}

.btn-secondary:hover {
    background: #545b62;
}

.message {
    margin-top: 16px;
    padding: 12px;
    border-radius: 4px;
    text-align: center;
    background: #d4edda;
    color: #155724;
}

@media (max-width: 600px) {
    .excel-save-form {
        margin: 10px;
        padding: 15px;
    }

    .form-actions {
        flex-direction: column;
    }

    .btn {
        width: 100%;
    }
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
