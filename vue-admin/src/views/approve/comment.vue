<template>
  <div class="comment-review">
    <!-- 搜索区域 -->
    <el-card class="search-card">
      <el-form :model="searchForm" inline>
        <el-form-item label="评价时间">
          <el-date-picker
            v-model="searchForm.date_range"
            type="daterange"
            range-separator="至"
            start-placeholder="开始日期"
            end-placeholder="结束日期"
            value-format="YYYY-MM-DD"
            style="width: 260px"
          />
        </el-form-item>
        <el-form-item label="学校名称">
          <el-input v-model="searchForm.school_name" placeholder="请输入学校名称" clearable style="width: 200px" />
        </el-form-item>
        <el-form-item label="审阅状态">
          <el-select v-model="searchForm.review_status" placeholder="请选择审阅状态" clearable style="width: 140px">
            <el-option label="未审阅" :value="0" />
            <el-option label="已审阅" :value="1" />
          </el-select>
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="handleSearch">
            <el-icon><Search /></el-icon>
            搜索
          </el-button>
          <el-button @click="handleReset">
            <el-icon><Refresh /></el-icon>
            重置
          </el-button>
        </el-form-item>
      </el-form>
    </el-card>

    <!-- 表格区域 -->
    <el-card class="table-card">
      <template #header>
        <div class="card-header">
          <span>评论审阅列表</span>
        </div>
      </template>

      <el-table v-loading="loading" :data="tableData" border stripe>
        <el-table-column prop="id" label="ID" width="80" align="center" />
        <el-table-column prop="school_name" label="学校名称" min-width="180" show-overflow-tooltip />
        <el-table-column prop="supplier_name" label="供应商" min-width="150" show-overflow-tooltip />
        <el-table-column prop="order_no" label="订单编号" width="160" align="center" />
        <el-table-column prop="score" label="评分" width="100" align="center">
          <template #default="{ row }">
            <el-rate v-model="row.score" disabled />
          </template>
        </el-table-column>
        <el-table-column prop="content" label="评价内容" min-width="200" show-overflow-tooltip />
        <el-table-column prop="images" label="评价图片" width="120" align="center">
          <template #default="{ row }">
            <el-image
              v-if="row.images && row.images.length > 0"
              :src="row.images[0]"
              :preview-src-list="row.images"
              fit="cover"
              style="width: 60px; height: 60px"
            />
            <span v-else class="text-muted">无图片</span>
          </template>
        </el-table-column>
        <el-table-column prop="created_at" label="评价时间" width="180" align="center" />
        <el-table-column prop="review_status" label="审阅状态" width="100" align="center">
          <template #default="{ row }">
            <el-tag :type="row.review_status === 1 ? 'success' : 'warning'">
              {{ row.review_status === 1 ? '已审阅' : '未审阅' }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column label="操作" width="120" align="center" fixed="right">
          <template #default="{ row }">
            <el-button
              v-if="row.review_status === 0"
              type="primary"
              link
              @click="handleReview(row)"
            >
              审阅
            </el-button>
            <el-button
              v-else
              type="info"
              link
              disabled
            >
              已审阅
            </el-button>
          </template>
        </el-table-column>
      </el-table>

      <!-- 分页 -->
      <el-pagination
        v-model:current-page="pagination.page"
        v-model:page-size="pagination.pageSize"
        :total="pagination.total"
        :page-sizes="[10, 20, 50, 100]"
        layout="total, sizes, prev, pager, next, jumper"
        @size-change="handleSizeChange"
        @current-change="handlePageChange"
      />
    </el-card>

    <!-- 审阅对话框 -->
    <el-dialog
      v-model="dialogVisible"
      title="评论审阅"
      width="600px"
      :close-on-click-modal="false"
      @closed="handleDialogClosed"
    >
      <el-form ref="formRef" :model="formData" :rules="formRules" label-width="100px">
        <el-form-item label="学校名称">
          <el-input :value="currentRow.school_name" disabled />
        </el-form-item>
        <el-form-item label="供应商">
          <el-input :value="currentRow.supplier_name" disabled />
        </el-form-item>
        <el-form-item label="订单编号">
          <el-input :value="currentRow.order_no" disabled />
        </el-form-item>
        <el-form-item label="评分">
          <el-rate :value="currentRow.score" disabled />
        </el-form-item>
        <el-form-item label="评价内容">
          <el-input :value="currentRow.content" type="textarea" :rows="3" disabled />
        </el-form-item>
        <el-form-item v-if="currentRow.images && currentRow.images.length > 0" label="评价图片">
          <div class="image-list">
            <el-image
              v-for="(img, index) in currentRow.images"
              :key="index"
              :src="img"
              :preview-src-list="currentRow.images"
              fit="cover"
              style="width: 80px; height: 80px; margin-right: 10px"
            />
          </div>
        </el-form-item>
        <el-form-item label="审阅结果" prop="status">
          <el-radio-group v-model="formData.status">
            <el-radio :value="1">通过</el-radio>
            <el-radio :value="2">驳回</el-radio>
          </el-radio-group>
        </el-form-item>
        <el-form-item label="审阅备注" prop="remark">
          <el-input v-model="formData.remark" type="textarea" :rows="3" placeholder="请输入审阅备注" maxlength="500" />
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="dialogVisible = false">取消</el-button>
        <el-button type="primary" :loading="submitLoading" @click="handleSubmit">确定</el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { ElMessage } from 'element-plus'
import { Search, Refresh } from '@element-plus/icons-vue'
import { getCommentList, reviewComment } from '@/api/modules/approve'

// 搜索表单
const searchForm = reactive({
  date_range: null,
  school_name: '',
  review_status: null,
})

// 表格数据
const loading = ref(false)
const tableData = ref([])

// 分页
const pagination = reactive({
  page: 1,
  pageSize: 20,
  total: 0,
})

// 对话框
const dialogVisible = ref(false)
const formRef = ref()
const submitLoading = ref(false)
const currentRow = ref({})

// 表单数据
const formData = reactive({
  status: 1,
  remark: '',
})

// 表单验证规则
const formRules = {
  status: [{ required: true, message: '请选择审阅结果', trigger: 'change' }],
}

// 获取评论列表
async function fetchData() {
  loading.value = true
  try {
    const params = {
      page: pagination.page,
      page_size: pagination.pageSize,
      school_name: searchForm.school_name,
      review_status: searchForm.review_status,
    }

    if (searchForm.date_range && searchForm.date_range.length === 2) {
      params.start_date = searchForm.date_range[0]
      params.end_date = searchForm.date_range[1]
    }

    const { data } = await getCommentList(params)
    tableData.value = data.list || []
    pagination.total = data.total || 0
  } catch (error) {
    console.error('获取评论列表失败:', error)
  } finally {
    loading.value = false
  }
}

// 搜索
function handleSearch() {
  pagination.page = 1
  fetchData()
}

// 重置
function handleReset() {
  Object.assign(searchForm, {
    date_range: null,
    school_name: '',
    review_status: null,
  })
  handleSearch()
}

// 审阅
function handleReview(row) {
  currentRow.value = { ...row }
  formData.status = 1
  formData.remark = ''
  dialogVisible.value = true
}

// 提交审阅
async function handleSubmit() {
  try {
    await formRef.value.validate()
    submitLoading.value = true

    await reviewComment(currentRow.value.id, {
      status: formData.status,
      remark: formData.remark,
    })

    ElMessage.success('审阅成功')
    dialogVisible.value = false
    fetchData()
  } catch (error) {
    console.error('审阅失败:', error)
  } finally {
    submitLoading.value = false
  }
}

// 对话框关闭
function handleDialogClosed() {
  formRef.value?.resetFields()
  currentRow.value = {}
}

// 分页大小变化
function handleSizeChange(size) {
  pagination.pageSize = size
  fetchData()
}

// 页码变化
function handlePageChange(page) {
  pagination.page = page
  fetchData()
}

// 初始化
onMounted(() => {
  fetchData()
})
</script>

<style lang="scss" scoped>
.comment-review {
  .search-card {
    margin-bottom: 20px;
  }

  .table-card {
    .card-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
  }

  .text-muted {
    color: #909399;
    font-size: 12px;
  }

  .image-list {
    display: flex;
    flex-wrap: wrap;
  }

  .el-pagination {
    margin-top: 20px;
    justify-content: flex-end;
  }
}
</style>
