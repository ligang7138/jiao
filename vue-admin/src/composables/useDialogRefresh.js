import { ref } from 'vue'

/**
 * 复刻旧系统弹窗关闭后刷新列表行为。
 */
export function useDialogRefresh() {
  const refreshKey = ref(0)
  const triggerRefresh = () => {
    refreshKey.value += 1
  }
  return { refreshKey, triggerRefresh }
}
