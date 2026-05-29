<template>
  <div>
    <el-table v-bind="$attrs" :data="list" border>
      <slot />
    </el-table>
    <div style="display:flex;justify-content:flex-end;margin-top:12px">
      <el-pagination
        background
        layout="total, prev, pager, next, sizes"
        :total="total"
        :current-page="page"
        :page-size="pageSize"
        @current-change="$emit('update:page', $event)"
        @size-change="$emit('update:pageSize', $event)"
      />
    </div>
  </div>
</template>

<script setup>
defineProps({
  list: { type: Array, default: () => [] },
  total: { type: Number, default: 0 },
  page: { type: Number, default: 1 },
  pageSize: { type: Number, default: 20 },
})

defineEmits(['update:page', 'update:pageSize'])
</script>
