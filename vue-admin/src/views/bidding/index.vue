<template>
  <div class="bidding-history">
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
          <el-input v-model="searchForm.school_name" placeholder="请输入学校名称" clearable style="width: 200px" @keyup.enter="handleSearch" />
        </el-form-item>
        <el-form-item label="审核状态">
          <el-select v-model="searchForm.audit_status" placeholder="请选择审核状态" clearable style="width: 140px">
            <el-option label="待审核" :value="1" />
            <el-option label="拒绝" :value="2" />
            <el-option label="通过" :value="3" />
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
          <span>合作申请列表</span>
        </div>
      </template>

      <!-- 表格 -->
      <el-table v-loading="loading" :data="tableData" border stripe>
        <el-table-column prop="id" label="ID" width="80" align="center" />
        <el-table-column prop="school_name" label="学校名称" min-width="180" show-overflow-tooltip />
        <el-table-column prop="supplier_name" label="供应商名称" min-width="180" show-overflow-tooltip />
        <el-table-column prop="apply_type" label="申请类型" width="120" align="center">
          <template #default="{ row }">
            <el-tag :type="row.apply_type === 1 ? 'primary' : 'success'">
              {{ row.apply_type === 1 ? '新增合作' : '续约合作' }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="discount" label="折扣(%)" width="100" align="center">
          <template #default="{ row }">
            <span class="discount">{{ row.discount }}%</span>
          </template>
        </el-table-column>
        <el-table-column prop="audit_status" label="审核状态" width="100" align="center">
          <template #default="{ row }">
            <el-tag :type="getAuditStatusType(row.audit_status)">
              {{ getAuditStatusText(row.audit_status) }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="review_status" label="审阅状态" width="100" align="center">
          <template #default="{ row }">
            <el-tag :type="row.review_status === 1 ? 'success' : 'info'">
              {{ row.review_status === 1 ? '已审阅' : '未审阅' }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="created_at" label="申请时间" width="180" align="center" />
        <el-table-column prop="auditor_name" label="审核人" width="120" align="center" />
        <el-table-column prop="audited_at" label="审核时间" width="180" align="center" />
        <el-table-column label="操作" width="180" align="center" fixed="right">
          <template #default="{ row }">
            <el-button type="primary" link @click="handleViewDetail(row)">详情</el-button>
            <el-button v-if="row.audit_status === 1" type="success" link @click="handleAudit(row)">审核</el-button>
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

    <!-- 详情对话框 -->
    <el-dialog v-model="detailDialogVisible" title="合作申请详情" width="700px">
      <el-descriptions :column="2" border>
        <el-descriptions-item label="学校名称">{{ detailData.school_name }}</el-descriptions-item>
        <el-descriptions-item label="供应商名称">{{ detailData.supplier_name }}</el-descriptions-item>
        <el-descriptions-item label="申请类型">
          <el-tag :type="detailData.apply_type === 1 ? 'primary' : 'success'">
            {{ detailData.apply_type === 1 ? '新增合作' : '续约合作' }}
          </el-tag>
        </el-descriptions-item>
        <el-descriptions-item label="折扣">{{ detailData.discount }}%</el-descriptions-item>
        <el-descriptions-item label="审核状态">
          <el-tag :type="getAuditStatusType(detailData.audit_status)">
            {{ getAuditStatusText(detailData.audit_status) }}
          </el-tag>
        </el-descriptions-item>
        <el-descriptions-item label="审阅状态">
          <el-tag :type="detailData.review_status === 1 ? 'success' : 'info'">
            {{ detailData.review_status === 1 ? '已审阅' : '未审阅' }}
          </el-tag>
        </el-descriptions-item>
        <el-descriptions-item label="申请人">{{ detailData.applicant_name }}</el-descriptions-item>
        <el-descriptions-item label="申请时间">{{ detailData.created_at }}</el-descriptions-item>
        <el-descriptions-item label="审核人">{{ detailData.auditor_name || '-' }}</el-descriptions-item>
        <el-descriptions-item label="审核时间">{{ detailData.audited_at || '-' }}</el-descriptions-item>
        <el-descriptions-item label="申请说明" :span="2">{{ detailData.apply_remark || '-' }}</el-descriptions-item>
        <el-descriptions-item v-if="detailData.audit_remark" label="审核备注" :span="2">{{ detailData.audit_remark }}</el-descriptions-item>
      </el-descriptions>
    </el-dialog>

    <!-- 审核对话框 -->
    <el-dialog v-model="auditDialogVisible" title="审核合作申请" width="500px" :close-on-click-modal="false">
      <el-form ref="auditFormRef" :model="auditForm" :rules="auditRules" label-width="100px">
        <el-form-item label="审核结果" prop="audit_status">
          <el-radio-group v-model="auditForm.audit_status">
            <el-radio :value="3">通过</el-radio>
            <el-radio :value="2">拒绝</el-radio>
          </el-radio-group>
        </el-form-item>
        <el-form-item label="审核备注" prop="audit_remark">
          <el-input v-model="auditForm.audit_remark" type="textarea" :rows="4" placeholder="请输入审核备注" maxlength="500" show-word-limit />
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="auditDialogVisible = false">取消</el-button>
        <el-button type="primary" :loading="auditLoading" @click="handleSubmitAudit">确定</el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { ElMessage } from 'element-plus'
import { Search, Refresh } from '@element-plus/icons-vue'
import { getBiddingHistories, getBiddingHistoryDetail, auditBiddingHistory } from '@/api/modules/bidding'

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

// 详情对话框
const detailDialogVisible = ref(false)
const detailData = ref({})

// 审核对话框
const auditDialogVisible = ref(false)
const auditFormRef = ref()
const auditLoading = ref(false)
const auditForm = reactive({
  id: null,
  audit_status: 3,
  audit_remark: '',
})

// 审核表单验证规则
const auditRules = {
  audit_status: [{ required: true, message: '请选择审核结果', trigger: 'change' }],
}

// 获取审核状态类型
function getAuditStatusType(status) {
  const map = {
    1: 'warning',
    2: 'danger',
    3: 'success',
  }
  return map[status] || 'info'
}

// 获取审核状态文本
function getAuditStatusText(status) {
  const map = {
    1: '待审核',
    2: '拒绝',
    3: '通过',
  }
  return map[status] || '未知'
}

// 获取列表数据
async function fetchData() {
  loading.value = true
  try {
    const params = {
      page: pagination.page,
      page_size: pagination.pageSize,
    }

    // 处理日期范围
    if (searchForm.date_range && searchForm.date_range.length === 2) {
      params.start_date = searchForm.date_range[0]
      params.end_date = searchForm.date_range[1]
    }

    // 处理其他搜索条件
    if (searchForm.school_name) {
      params.school_name = searchForm.school_name
    }
    if (searchForm.audit_status !== null && searchForm.audit_status !== '') {
      params.audit_status = searchForm.audit_status
    }
    if (searchForm.review_status !== null && searchForm.review_status !== '') {
      params.review_status = searchForm.review_status
    }

    const { data } = await getBiddingHistories(params)
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

// 查看详情
async function handleViewDetail(row) {
  try {
    const { data } = await getBiddingHistoryDetail(row.id)
    detailData.value = data
    detailDialogVisible.value = true
  } catch (error) {
    ElMessage.error('获取详情失败')
  }
}

// 审核
function handleAudit(row) {
  auditForm.id = row.id
  auditForm.audit_status = 3
  auditForm.audit_remark = ''
  auditDialogVisible.value = true
}

// 提交审核
async function handleSubmitAudit() {
  try {
    await auditFormRef.value.validate()
    auditLoading.value = true

    await auditBiddingHistory(auditForm.id, {
      audit_status: auditForm.audit_status,
      audit_remark: auditForm.audit_remark,
    })

    ElMessage.success('审核成功')
    auditDialogVisible.value = false
    fetchData()
  } catch (error) {
    console.error('审核失败:', error)
  } finally {
    auditLoading.value = false
  }
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
.bidding-history {
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

  .discount {
    color: #e6a23c;
    font-weight: 600;
  }

  .el-pagination {
    margin-top: 20px;
    justify-content: flex-end;
  }
}
</style>
