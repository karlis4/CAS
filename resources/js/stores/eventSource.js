import { ref, computed, reactive } from 'vue'
import { defineStore } from 'pinia'

export const useMarkersStore = defineStore('markers', () => {
  const is_provided = ref('');

  return { is_provided };
})
