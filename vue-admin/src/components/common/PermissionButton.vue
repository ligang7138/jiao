<template>
  <el-button v-if="allowed" v-bind="$attrs">
    <slot />
  </el-button>
</template>

<script setup>
import { computed } from 'vue'
import { usePermissionStore } from '@/stores/modules/permission'

const props = defineProps({
  permission: {
    type: [String, Array],
    required: true,
  },
})

const permissionStore = usePermissionStore()
const allowed = computed(() => {
  if (Array.isArray(props.permission)) {
    return props.permission.some((item) => permissionStore.hasPermission(item))
  }
  return permissionStore.hasPermission(props.permission)
})
</script>
