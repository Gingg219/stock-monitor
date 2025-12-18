// src/stores/auth.js
import { defineStore } from 'pinia'
import api from '@/services/axios'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    loading: false,
  }),

  getters: {
    isAuthenticated: (state) => !!state.user,
  },

  actions: {
    async getCsrf() {
      await api.get('/sanctum/csrf-cookie')
    },

    async login(form) {
      this.loading = true
      await this.getCsrf()
      await api.post('/login', form)
      await this.fetchUser()
      this.loading = false
    },

    async fetchUser() {
      const res = await api.get('/api/user')
      this.user = res.data
    },

    // async logout() {
    //   try {
    //     await api.post('/logout')
    //   } catch (e) {}
    //   this.user = null
    // },
  },
})
