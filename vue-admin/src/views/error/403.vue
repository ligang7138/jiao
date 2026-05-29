<template>
  <div class="error-page">
    <div class="error-content">
      <el-icon :size="120" color="#F56C6C">
        <Lock />
      </el-icon>
      <h1>403</h1>
      <p>抱歉，您没有权限访问此页面</p>
      <el-button type="primary" @click="router.push('/')">返回首页</el-button>
    </div>
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router'
import { Lock } from '@element-plus/icons-vue'

const router = useRouter()
</script>

<style lang="scss" scoped>
.error-page {
  height: 100vh;
  display: flex;
  justify-content: center;
  align-items: center;
  background: linear-gradient(135deg, #f5f7fa 0%, #e4e7ed 100%);
}

.error-content {
  text-align: center;

  h1 {
    font-size: 72px;
    font-weight: 600;
    color: #F56C6C;
    margin: 20px 0 10px;
  }

  p {
    font-size: 18px;
    color: #606266;
    margin-bottom: 30px;
  }
}
</style>
