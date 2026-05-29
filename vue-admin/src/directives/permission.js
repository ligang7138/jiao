import { usePermissionStore } from '@/stores/modules/permission'

/**
 * 权限指令
 * 用法: v-permission="'goods.add'"
 */
export default {
  mounted(el, binding) {
    const permissionStore = usePermissionStore()
    const { value } = binding

    if (value && typeof value === 'string') {
      const hasPermission = permissionStore.hasPermission(value)

      if (!hasPermission) {
        el.parentNode?.removeChild(el)
      }
    } else if (Array.isArray(value)) {
      const hasPermission = value.some((item) => permissionStore.hasPermission(item))

      if (!hasPermission) {
        el.parentNode?.removeChild(el)
      }
    }
  },
}
