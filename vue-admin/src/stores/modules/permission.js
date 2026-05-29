import { defineStore } from 'pinia'
import { useUserStore } from '@/stores/modules/user'

export const usePermissionStore = defineStore('permission', {
  state: () => ({
    permissions: [],
  }),

  getters: {
    hasPermission: (state) => (permission) => {
      const userStore = useUserStore()
      if (userStore.isSuper) return true
      if (state.permissions.includes('*')) return true
      return state.permissions.includes(permission)
    },
  },

  actions: {
    setPermissions(permissions) {
      this.permissions = permissions
    },

    clearPermissions() {
      this.permissions = []
    },
  },
})
