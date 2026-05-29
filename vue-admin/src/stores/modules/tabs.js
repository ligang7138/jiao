import { defineStore } from 'pinia'

export const useTabsStore = defineStore('tabs', {
  state: () => ({
    visited: [],
  }),
  actions: {
    addTab(tab) {
      if (!tab?.path || this.visited.some((item) => item.path === tab.path)) return
      this.visited.push(tab)
    },
    removeTab(path) {
      this.visited = this.visited.filter((item) => item.path !== path)
    },
    resetTabs() {
      this.visited = []
    },
  },
})
