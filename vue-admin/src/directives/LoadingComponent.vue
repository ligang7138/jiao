<template>
  <div class="loading-mask">
    <div class="loading-spinner">
      <el-icon class="is-loading">
        <Loading />
      </el-icon>
      <span class="loading-text">加载中...</span>
    </div>
  </div>
</template>

<script setup>
import { Loading } from '@element-plus/icons-vue'
</script>

<style scoped>
.loading-mask {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(255, 255, 255, 0.9);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.loading-spinner {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 10px;
}

.loading-spinner .el-icon {
  font-size: 32px;
  color: var(--el-color-primary);
}

.loading-text {
  color: var(--el-color-primary);
  font-size: 14px;
}
</style>
