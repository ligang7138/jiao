import { usePermissionStore } from '@/stores/modules/permission'

export function usePermission() {
  const permissionStore = usePermissionStore()

  return {
    hasPermission: permissionStore.hasPermission,
  }
}
