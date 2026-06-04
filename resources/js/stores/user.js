import { ref, computed, reactive } from 'vue'
import { defineStore } from 'pinia'

export const useUserStore = defineStore('user', () => {
  const userInfo = ref({
    name: '',
    email: '',
    auth_token: '',
    isAuthenticated: false
  });

  return { userInfo };
})
