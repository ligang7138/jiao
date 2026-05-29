<template>
  <div class="bidding-review">
    <!-- 搜索区域 -->
    <el-card class="search-card">
      <el-form :model="searchForm" inline>
        <el-form-item label="申请日期">
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
        <el-form-item label="审核状态">
          <el-select v-model="searchForm.audit_status" placeholder="请选择审核状态" clearable style="width: 140px">
            <el-option label="待审核" :value="0" />
            <el-option label="已通过" :value="1" />
            <el-option label="已拒绝" :value="2" />
          </el-select>
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
          <span>合作申请审阅列表</span>
        </div>
      </template>

      <el-table v-loading="loading" :data="tableData" border stripe>
        <el-table-column prop="id" label="ID" width="80" align="center" />
        <el-table-column prop="school_name" label="学校名称" min-width="180" show-overflow-tooltip />
        <el-table-column prop="supplier_name" label="供应商" min-width="150" show-overflow-tooltip />
        <el-table-column prop="contact_name" label="联系人" width="100" align="center" />
        <el-table-column prop="contact_phone" label="联系电话" width="130" align="center" />
        <el-table-column prop="cooperation_type" label="合作类型" width="120" align="center">
          <template #default="{ row }">
            <el-tag>{{ row.cooperation_type_text }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="apply_reason" label="申请理由" min-width="200" show-overflow-tooltip />
        <el-table-column prop="audit_status" label="审核状态" width="100" align="center">
          <template #default="{ row }">
            <el-tag :type="getAuditStatusType(row.audit_status)">
              {{ row.audit_status_text }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="review_status" label="审阅状态" width="100" align="center">
          <template #default="{ row }">
            <el-tag :type="row.review_status === 1 ? 'success' : 'warning'">
              {{ row.review_status === 1 ? '已审阅' : '未审阅' }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="created_at" label="申请时间" width="180" align="center" />
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
      title="合作申请审阅"
      width="700px"
      :close-on-click-modal="false"
      @closed="handleDialogClosed"
    >
      <el-form ref="formRef" :model="formData" :rules="formRules" label-width="120px">
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="学校名称">
              <el-input :value="currentRow.school_name" disabled />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="供应商">
              <el-input :value="currentRow.supplier_name" disabled />
            </el-form-item>
          </el-col>
        </el-row>
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="联系人">
              <el-input :value="currentRow.contact_name" disabled />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="联系电话">
              <el-input :value="currentRow.contact_phone" disabled />
            </el-form-item>
          </el-col>
        </el-row>
        <el-form-item label="合作类型">
          <el-tag>{{ currentRow.cooperation_type_text }}</el-tag>
        </el-form-item>
        <el-form-item label="申请理由">
          <el-input :value="currentRow.apply_reason" type="textarea" :rows="3" disabled />
        </el-form-item>
        <el-form-item v-if="currentRow.attachments && currentRow.attachments.length > 0" label="附件">
          <div class="attachment-list">
            <el-link
              v-for="(file, index) in currentRow.attachments"
              :key="index"
              :href="file.url"
              target="_blank"
              type="primary"
              style="margin-right: 15px"
            >
              <el-icon><Document /></el-icon>
              {{ file.name }}
            </el-link>
          </div>
        </el-form-item>
        <el-form-item label="审核状态">
          <el-tag :type="getAuditStatusType(currentRow.audit_status)">
            {{ currentRow.audit_status_text }}
          </el-tag>
        </el-form-item>
        <el-form-item v-if="currentRow.audit_remark" label="审核备注">
          <el-input :value="currentRow.audit_remark" type="textarea" :rows="2" disabled />
        </el-form-item>
        <el-divider />
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
import { Search, Refresh, Document } from '@element-plus/icons-vue'
import { getBiddingList, reviewBidding } from '@/api/modules/approve'

// 搜索表单
const searchForm = reactive({
  date_range: null,
  school_name: '',
  audit_status: null,
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

// 获取审核状态类型
function getAuditStatusType(status) {
  const map = {
    0: 'warning',
    1: 'success',
    2: 'danger',
  }
  return map[status] || 'info'
}

// 获取合作申请列表
async function fetchData() {
  loading.value = true
  try {
    const params = {
      page: pagination.page,
      page_size: pagination.pageSize,
      school_name: searchForm.school_name,
      audit_status: searchForm.audit_status,
      review_status: searchForm.review_status,
    }

    if (searchForm.date_range && searchForm.date_range.length === 2) {
      params.start_date = searchForm.date_range[0]
      params.end_date = searchForm.date_range[1]
    }

    const { data } = await getBiddingList(params)
    tableData.value = data.list || []
    pagination.total = data.total || 0
  } catch (error) {
    console.error('获取合作申请列表失败:', error)
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
    audit_status: null,
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

    await reviewBidding(currentRow.value.id, {
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
.bidding-review {
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

  .attachment-list {
    display: flex;
    flex-wrap: wrap;
  }

  .el-pagination {
    margin-top: 20px;
    justify-content: flex-end;
  }
}
</style>
