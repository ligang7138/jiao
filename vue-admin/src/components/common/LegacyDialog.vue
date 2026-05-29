<template>
  <el-dialog v-model="visible" :title="title" :width="width" destroy-on-close @close="$emit('closed')">
    <slot />
    <template #footer>
      <slot name="footer">
        <el-button @click="visible = false">取消</el-button>
        <el-button type="primary" @click="$emit('confirm')">确定</el-button>
      </slot>
    </template>
  </el-dialog>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  modelValue: { type: Boolean, default: false },
  title: { type: String, default: '' },
  width: { type: String, default: '70%' },
})

const emit = defineEmits(['update:modelValue', 'confirm', 'closed'])

const visible = computed({
  get: () => props.modelValue,
  set: (value) => emit('update:modelValue', value),
})
</script>
