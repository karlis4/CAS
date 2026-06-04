import { ref, computed, reactive } from 'vue'
import { defineStore } from 'pinia'

export const useMarkersStore = defineStore('markers', () => {
  const markers = ref([]);

  const markersCount = computed(() => {
    return markers.value.length;
  });

   const reset = () => {
    markers.value = [] // Просто присваиваем пустой массив
  }

  return { markers, markersCount, reset };
})
