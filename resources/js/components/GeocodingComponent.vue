<script setup>
import { defineProps, defineEmits, ref } from 'vue'
import axios from 'axios';
import ErrorModal from './ErrorModal.vue';

const props = defineProps(['createdMarker']);
const emit = defineEmits(['update:address']);

const showError = ref(false);
const errorMessage = ref('');
const title = ref('');

async function getAddressFromCoords(lat, lng) {

  try {
    const response = await axios.get(
      'https://nominatim.openstreetmap.org/reverse',
      {
        params: {
          format: 'json',
          lat: lat,
          lon: lng,
          addressdetails: 1
        },
        timeout: 10000
      }
    )

    if (response.data && response.data.address) {
      const address =  formatAddress(response.data.address);
      emit('update:address', address);
      return;
    }

    showError.value = true;
    errorMessage.value = 'Адрес не найден';
    title.value = 'Ошибка геокодирования';

    return;
  } catch (err) {
    showError.value = true;
    errorMessage.value = "Функция временно недоступна. Сбой в работе сторонних сервисов";
    title.value = 'Ошибка геокодирования';

    return;
  }
}

function formatAddress(addr) {
  const parts = []

  if (addr.road) parts.push(addr.road)
  if (addr.house_number) parts.push(`д. ${addr.house_number}`)
  if (addr.city) parts.push(addr.city)
  else if (addr.town) parts.push(addr.town)
  else if (addr.village) parts.push(addr.village)
  if (addr.suburb) parts.push(addr.suburb)

  return parts.join(', ')
}

</script>

<template>
<button type="button" class="getAddress" @click="() => getAddressFromCoords(createdMarker.latitude, createdMarker.longitude)"><img src="../assests/images/location-dot-solid-full.svg" class="geoIcon"></img></button>
<ErrorModal :show-error="showError" :title="title" :error-message="errorMessage"></ErrorModal>
</template>

<style scoped>
.getAddress{
  width: 60px;
  height: 60px;
  border-radius: 50px;
  border: none;
  margin-left: 5px;
  background-color: #2cce29;
}

.geoIcon{
    padding-top: 5px;
    width: 20px;
    height: 20px;
}

.getAddress:hover{
    border: 5px solid #af5609;
}
</style>
