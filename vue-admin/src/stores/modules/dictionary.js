import { defineStore } from 'pinia'

export const useDictionaryStore = defineStore('dictionary', {
  state: () => ({
    dict: {},
  }),
  actions: {
    setDictionary(key, values) {
      this.dict[key] = values || []
    },
    batchSetDictionary(payload = {}) {
      this.dict = { ...this.dict, ...payload }
    },
    getDictionary(key) {
      return this.dict[key] || []
    },
    clearDictionary() {
      this.dict = {}
    },
  },
})
