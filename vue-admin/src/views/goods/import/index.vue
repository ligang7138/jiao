<template>
  <div class="page-container">
    <el-card>
      <template #header>商品导入</template>
      <el-alert
        title="请按旧系统商品导入模板上传文件，字段、校验与导入结果保持旧系统一致。"
        type="info"
        show-icon
        :closable="false"
      />
      <el-upload
        class="upload-area"
        drag
        action=""
        :auto-upload="false"
        :limit="1"
        accept=".xlsx,.xls,.csv"
      >
        <el-icon class="el-icon--upload"><UploadFilled /></el-icon>
        <div class="el-upload__text">拖拽文件到此处，或点击选择文件</div>
      </el-upload>
    </el-card>
  </div>
</template>

<script setup>
import { UploadFilled } from '@element-plus/icons-vue'
</script>

<style scoped>
.upload-area {
  margin-top: 16px;
}
</style>
