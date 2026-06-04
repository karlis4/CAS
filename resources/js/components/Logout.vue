<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import ErrorModal from './ErrorModal.vue';
import OpacityWindow from './OpacityWindow.vue';
import Loader from './Loader.vue';

const router = useRouter();

const showError = ref(false);
const errorMessage = ref('');
const title = ref('');

const showLoader = ref(false);
const showOpacityWindow = ref(false);


const logout = async () => {
showOpacityWindow.value = true;
showLoader.value = true;

    try{
        await axios.post('/api/logout');

        localStorage.removeItem('isAuthenticated');
        localStorage.removeItem('token');

        router.push({ name: 'login' });
    } catch(error){
        showOpacityWindow.value = false;
        showLoader.value = false;

        showError.value = true;
        errorMessage.value = 'Ошибка выхода';
        title.value = 'Выход';
    }

}
</script>

<template>
<button @click="logout" class="logout-btn"><img src="../assests/images/exit.svg"></img></button>

<ErrorModal v-if="showError"
            v-model:show-error="showError"
            :error-message="errorMessage"
            :title="title">
</ErrorModal>

<OpacityWindow v-if="showOpacityWindow"></OpacityWindow>
<Loader v-if="showLoader"></Loader>
</template>

<style scoped>
.logout-btn {
  padding: 10px 16px;
  border: 2px solid #dc3545;
  background: #dc3545;
  border-radius: 6px;
  cursor: pointer;
  font-weight: bold;
  transition: all 0.3s;
}

img{
    padding-top: 1px;
    width: 20px;
    height: 20px;
}
</style>
