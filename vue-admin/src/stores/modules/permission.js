import { defineStore } from 'pinia'

export const usePermissionStore = defineStore('permission', {
  state: () => ({
    permissions: [],
  }),

  getters: {
    hasPermission: (state) => (permission) => {
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
