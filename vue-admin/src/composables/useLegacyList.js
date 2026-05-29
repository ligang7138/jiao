import { reactive } from 'vue'

export function useLegacyList(defaultQuery = {}) {
  const query = reactive({
    p: 1,
    page_size: 20,
    ...defaultQuery,
  })
  return { query }
}
