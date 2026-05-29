import { defineStore } from 'pinia'
import { getMenus } from '@/api/modules/menu'
import { resolveMenuRoute } from '@/utils/menuPathMap'

export const useMenuStore = defineStore('menu', {
  state: () => ({
    menuTree: [],
    loaded: false,
  }),

  getters: {
    modules: (state) => state.menuTree.map((item) => item.module).filter(Boolean),
  },

  actions: {
    async fetchMenus(force = false) {
      if (this.loaded && !force) {
        return this.menuTree
      }

      const res = await getMenus()
      const rows = res.data || []

      this.menuTree = rows
        .map((group) => ({
          id: group.id,
          module: group.module,
          children: (group.menu || [])
            .filter((item) => !item.path?.startsWith('jiagewang.'))
            .map((item) => {
              const menuItem = {
                id: item.id,
                func: item.func,
                path: item.path,
                legacyUrl: item.legacy_url,
                route: item.route,
              }
              menuItem.route = resolveMenuRoute(menuItem)
              return menuItem
            })
            .filter((item) => item.path),
        }))
        .filter((group) => group.children.length > 0)

      this.loaded = true
      return this.menuTree
    },

    clearMenus() {
      this.menuTree = []
      this.loaded = false
    },
  },
})
