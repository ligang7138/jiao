<template>
  <div class="jiagewang-import-container">
    <el-card shadow="never">
      <template #header>
        <div class="card-header">
          <span>指导价导入</span>
          <el-button @click="handleBack">
            <el-icon><Back /></el-icon>
            返回列表
          </el-button>
        </div>
      </template>

      <!-- 导入说明 -->
      <el-alert
        title="导入说明"
        type="info"
        :closable="false"
        class="import-tips"
      >
        <ol>
          <li>请先下载导入模板，按照模板格式填写数据</li>
          <li>支持文件格式：CSV、XLS、XLSX</li>
          <li>商品编码必须与系统中的商品编码一致</li>
          <li>指导价必须为数字，且不能小于0</li>
          <li>单次导入数据量不超过1000条</li>
        </ol>
      </el-alert>

      <!-- 下载模板 -->
      <div class="template-section">
        <el-button type="primary" @click="handleDownloadTemplate">
          <el-icon><Download /></el-icon>
          下载导入模板
        </el-button>
      </div>

      <!-- 文件上传 -->
      <el-upload
        ref="uploadRef"
        class="upload-area"
        drag
        :action="uploadUrl"
        :headers="uploadHeaders"
        :accept="acceptTypes"
        :limit="1"
        :auto-upload="false"
        :on-change="handleFileChange"
        :on-success="handleUploadSuccess"
        :on-error="handleUploadError"
        :before-upload="handleBeforeUpload"
      >
        <el-icon class="el-icon--upload"><UploadFilled /></el-icon>
        <div class="el-upload__text">
          将文件拖到此处，或<em>点击上传</em>
        </div>
        <template #tip>
          <div class="el-upload__tip">
            只能上传 {{ acceptTypes }} 文件，且文件大小不超过 10MB
          </div>
        </template>
      </el-upload>

      <!-- 上传按钮 -->
      <div class="upload-actions">
        <el-button
          type="primary"
          :loading="uploading"
          :disabled="!selectedFile"
          @click="handleUpload"
        >
          开始导入
        </el-button>
        <el-button @click="handleClear">清空文件</el-button>
      </div>

      <!-- 导入结果 -->
      <el-card v-if="importResult" class="result-card" shadow="never">
        <template #header>
          <span>导入结果</span>
        </template>
        <el-descriptions :column="3" border>
          <el-descriptions-item label="总条数">
            {{ importResult.total }}
          </el-descriptions-item>
          <el-descriptions-item label="成功条数">
            <span class="success-count">{{ importResult.success }}</span>
          </el-descriptions-item>
          <el-descriptions-item label="失败条数">
            <span class="error-count">{{ importResult.failed }}</span>
          </el-descriptions-item>
        </el-descriptions>

        <!-- 错误列表 -->
        <div v-if="importResult.errors && importResult.errors.length > 0" class="error-list">
          <el-divider content-position="left">错误详情</el-divider>
          <el-table :data="importResult.errors" border stripe max-height="400">
            <el-table-column prop="row" label="行号" width="80" align="center" />
            <el-table-column prop="goods_sn" label="商品编码" width="150" />
            <el-table-column prop="message" label="错误信息" show-overflow-tooltip />
          </el-table>
        </div>
      </el-card>
    </el-card>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { ElMessage } from 'element-plus'
import { Back, Download, UploadFilled } from '@element-plus/icons-vue'
import { importJiagewang, getImportTemplate } from '@/api/modules/jiagewang'
import { getToken } from '@/utils/auth'

const router = useRouter()

// 上传组件引用
const uploadRef = ref()

// 上传状态
const uploading = ref(false)
const selectedFile = ref(null)

// 导入结果
const importResult = ref(null)

// 上传地址
const uploadUrl = computed(() => {
  const baseUrl = import.meta.env.VITE_API_BASE_URL || '/api/v1'
  return `${baseUrl}/admin/jiagewang/import`
})

// 上传请求头
const uploadHeaders = computed(() => ({
  Authorization: `Bearer ${getToken()}`,
}))

// 接受的文件类型
const acceptTypes = '.csv,.xls,.xlsx'

// 返回列表
function handleBack() {
  router.push('/jiagewang')
}

// 下载模板
async function handleDownloadTemplate() {
  try {
    const res = await getImportTemplate()
    const blob = new Blob([res], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' })
    const url = window.URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = url
    link.download = '指导价导入模板.xlsx'
    link.click()
    window.URL.revokeObjectURL(url)
    ElMessage.success('模板下载成功')
  } catch (error) {
    console.error('下载模板失败:', error)
  }
}

// 文件选择变化
function handleFileChange(file) {
  selectedFile.value = file.raw
}

// 上传前校验
function handleBeforeUpload(file) {
  const isValidType = ['text/csv', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'].includes(file.type)
  const isLt10M = file.size / 1024 / 1024 < 10

  if (!isValidType) {
    ElMessage.error('只能上传 CSV、XLS、XLSX 格式的文件')
    return false
  }
  if (!isLt10M) {
    ElMessage.error('文件大小不能超过 10MB')
    return false
  }
  return true
}

// 手动上传
async function handleUpload() {
  if (!selectedFile.value) {
    ElMessage.warning('请先选择文件')
    return
  }

  uploading.value = true
  importResult.value = null

  try {
    const formData = new FormData()
    formData.append('file', selectedFile.value)

    const res = await importJiagewang(formData)
    importResult.value = res.data

    if (res.data.success > 0) {
      ElMessage.success(`成功导入 ${res.data.success} 条数据`)
    }
    if (res.data.failed > 0) {
      ElMessage.warning(`${res.data.failed} 条数据导入失败，请查看错误详情`)
    }
  } catch (error) {
    console.error('导入失败:', error)
    ElMessage.error('导入失败，请检查文件格式')
  } finally {
    uploading.value = false
  }
}

// 上传成功
function handleUploadSuccess(response) {
  if (response.code === 200) {
    importResult.value = response.data
    if (response.data.success > 0) {
      ElMessage.success(`成功导入 ${response.data.success} 条数据`)
    }
  } else {
    ElMessage.error(response.message || '导入失败')
  }
  uploading.value = false
}

// 上传失败
function handleUploadError(error) {
  console.error('上传失败:', error)
  ElMessage.error('上传失败，请重试')
  uploading.value = false
}

// 清空文件
function handleClear() {
  uploadRef.value?.clearFiles()
  selectedFile.value = null
  importResult.value = null
}
</script>

<style lang="scss" scoped>
.jiagewang-import-container {
  padding: 20px;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.import-tips {
  margin-bottom: 20px;

  ol {
    margin: 10px 0 0 0;
    padding-left: 20px;

    li {
      line-height: 1.8;
    }
  }
}

.template-section {
  margin-bottom: 20px;
}

.upload-area {
  margin-bottom: 20px;

  :deep(.el-upload-dragger) {
    width: 100%;
  }
}

.upload-actions {
  display: flex;
  gap: 10px;
  margin-bottom: 20px;
}

.result-card {
  margin-top: 20px;
}

.success-count {
  color: #67c23a;
  font-weight: 600;
}

.error-count {
  color: #f56c6c;
  font-weight: 600;
}

.error-list {
  margin-top: 20px;
}
</style>
