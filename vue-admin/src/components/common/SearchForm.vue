<template>
  <el-form :inline="true" :model="modelValue" class="legacy-search-form">
    <slot />
    <el-form-item>
      <el-button type="primary" @click="$emit('search')">查询</el-button>
      <el-button @click="$emit('reset')">重置</el-button>
    </el-form-item>
  </el-form>
</template>

<script setup>
defineProps({
  modelValue: {
    type: Object,
    default: () => ({}),
  },
})

defineEmits(['search', 'reset'])
</script>
