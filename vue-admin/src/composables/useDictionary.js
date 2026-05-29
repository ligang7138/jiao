import { getDictionaries } from '@/api/modules/dictionary'
import { useDictionaryStore } from '@/stores/modules/dictionary'

export function useDictionary() {
  const dictionaryStore = useDictionaryStore()

  async function loadDictionaries(keys = []) {
    const res = await getDictionaries(keys)
    dictionaryStore.batchSetDictionary(res.data || {})
    return res.data || {}
  }

  return {
    dict: dictionaryStore.dict,
    loadDictionaries,
    getDictionary: dictionaryStore.getDictionary,
  }
}
