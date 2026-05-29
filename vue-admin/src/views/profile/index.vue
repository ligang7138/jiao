<template>
  <div class="page-placeholder">
    <el-empty description="页面开发中...">
      <el-button type="primary" @click="router.push('/')">返回首页</el-button>
    </el-empty>
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router'

const router = useRouter()
</script>

<style lang="scss" scoped>
.page-placeholder {
  height: 100%;
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 400px;
}
</style>
