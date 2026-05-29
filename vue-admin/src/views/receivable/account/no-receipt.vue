<template>
  <div class="no-receipt-list">
    <!-- 搜索区域 -->
    <el-card class="search-card">
      <el-form :model="searchForm" inline>
        <el-form-item label="学校名称">
          <el-input
            v-model="searchForm.canteen_name"
            placeholder="请输入学校名称"
            clearable
            style="width: 200px"
            @keyup.enter="handleSearch"
          />
        </el-form-item>
        <el-form-item label="供应商">
          <el-select
            v-model="searchForm.supp_id"
            placeholder="请选择供应商"
            clearable
            filterable
            style="width: 200px"
          >
            <el-option
              v-for="item in supplierList"
              :key="item.id"
              :label="item.supplier_name"
              :value="item.id"
            />
          </el-select>
        </el-form-item>
        <el-form-item label="日期范围">
          <el-date-picker
            v-model="dateRange"
            type="daterange"
            range-separator="至"
            start-placeholder="开始日期"
            end-placeholder="结束日期"
            value-format="YYYY-MM-DD"
            style="width: 240px"
            @change="handleDateChange"
          />
        </el-form-item>
        <el-form-item label="类型">
          <el-select v-model="searchForm.type" placeholder="请选择" clearable style="width: 120px">
            <el-option label="借方" :value="1" />
            <el-option label="贷方" :value="2" />
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

    <!-- 操作栏 -->
    <el-card class="table-card">
      <template #header>
        <div class="card-header">
          <span>未入账账单列表</span>
          <div class="header-actions">
            <el-button type="primary" :disabled="selectedRows.length === 0" @click="handleBatchReceipt">
              <el-icon><Check /></el-icon>
              批量入账
            </el-button>
          </div>
        </div>
      </template>

      <!-- 统计卡片 -->
      <el-row :gutter="20" class="stats-row">
        <el-col :span="8">
          <div class="stat-card">
            <div class="stat-label">未入账笔数</div>
            <div class="stat-value">{{ stats.total_count }}</div>
          </div>
        </el-col>
        <el-col :span="8">
          <div class="stat-card">
            <div class="stat-label">借方金额</div>
            <div class="stat-value danger">{{ formatMoney(stats.debit_amount) }}</div>
          </div>
        </el-col>
        <el-col :span="8">
          <div class="stat-card">
            <div class="stat-label">贷方金额</div>
            <div class="stat-value success">{{ formatMoney(stats.credit_amount) }}</div>
          </div>
        </el-col>
      </el-row>

      <!-- 表格 -->
      <el-table
        ref="tableRef"
        v-loading="loading"
        :data="tableData"
        border
        stripe
        @selection-change="handleSelectionChange"
        @select="handleSelect"
        @select-all="handleSelectAll"
      >
        <el-table-column type="selection" width="55" align="center" :selectable="checkSelectable" />
        <el-table-column prop="id" label="ID" width="80" align="center" />
        <el-table-column prop="account_date" label="日期" width="120" align="center" />
        <el-table-column prop="type" label="类型" width="100" align="center">
          <template #default="{ row }">
            <el-tag :type="row.type === 1 ? 'danger' : 'success'">
              {{ row.type === 1 ? '借方' : '贷方' }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="amount" label="金额" width="120" align="right">
          <template #default="{ row }">
            <span :class="['money', row.type === 1 ? 'debit' : 'credit']">
              {{ row.type === 1 ? '+' : '-' }}{{ formatMoney(row.amount) }}
            </span>
          </template>
        </el-table-column>
        <el-table-column prop="canteen_name" label="学校/食堂" min-width="150" show-overflow-tooltip />
        <el-table-column prop="supp_name" label="供应商" min-width="150" show-overflow-tooltip />
        <el-table-column prop="order_no" label="关联订单" width="180" align="center" show-overflow-tooltip />
        <el-table-column prop="remark" label="摘要" min-width="200" show-overflow-tooltip />
        <el-table-column prop="created_at" label="创建时间" width="180" align="center" />
        <el-table-column label="操作" width="120" align="center" fixed="right">
          <template #default="{ row }">
            <el-button type="primary" link @click="handleEdit(row)">编辑</el-button>
            <el-button type="danger" link @click="handleDelete(row)">删除</el-button>
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

    <!-- 编辑对话框 -->
    <el-dialog
      v-model="editDialogVisible"
      title="编辑账单明细"
      width="500px"
      :close-on-click-modal="false"
      @closed="handleDialogClosed"
    >
      <el-form ref="formRef" :model="formData" :rules="formRules" label-width="100px">
        <el-form-item label="日期" prop="account_date">
          <el-date-picker
            v-model="formData.account_date"
            type="date"
            placeholder="选择日期"
            value-format="YYYY-MM-DD"
            style="width: 100%"
          />
        </el-form-item>
        <el-form-item label="类型" prop="type">
          <el-select v-model="formData.type" placeholder="请选择" style="width: 100%">
            <el-option label="借方" :value="1" />
            <el-option label="贷方" :value="2" />
          </el-select>
        </el-form-item>
        <el-form-item label="金额" prop="amount">
          <el-input-number
            v-model="formData.amount"
            :precision="2"
            :min="0"
            style="width: 100%"
          />
        </el-form-item>
        <el-form-item label="摘要">
          <el-input v-model="formData.remark" type="textarea" :rows="3" placeholder="请输入摘要" maxlength="500" />
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="editDialogVisible = false">取消</el-button>
        <el-button type="primary" :loading="submitLoading" @click="handleSubmit">确定</el-button>
      </template>
    </el-dialog>

    <!-- 批量入账对话框 -->
    <el-dialog v-model="batchDialogVisible" title="批量入账" width="700px" :close-on-click-modal="false">
      <el-alert
        title="提示：只能对同一学校和供应商的账单进行批量入账"
        type="info"
        :closable="false"
        show-icon
        style="margin-bottom: 20px"
      />

      <el-form ref="batchFormRef" :model="batchForm" :rules="batchRules" label-width="100px">
        <el-form-item label="已选明细">
          <el-tag>{{ selectedRows.length }} 条</el-tag>
        </el-form-item>
        <el-form-item label="学校/食堂">
          <el-input :model-value="selectedSchoolName" disabled />
        </el-form-item>
        <el-form-item label="供应商">
          <el-input :model-value="selectedSupplierName" disabled />
        </el-form-item>
        <el-form-item label="入账金额">
          <span :class="['money', batchTotalAmount >= 0 ? 'debit' : 'credit']">
            {{ formatMoney(Math.abs(batchTotalAmount)) }}
            ({{ batchTotalAmount >= 0 ? '应收' : '应付' }})
          </span>
        </el-form-item>
        <el-form-item label="对账日期" prop="receipt_date">
          <el-date-picker
            v-model="batchForm.receipt_date"
            type="date"
            placeholder="选择日期"
            value-format="YYYY-MM-DD"
            style="width: 100%"
          />
        </el-form-item>
        <el-form-item label="备注">
          <el-input v-model="batchForm.remark" type="textarea" :rows="3" placeholder="请输入备注" />
        </el-form-item>
      </el-form>

      <el-divider content-position="left">已选明细预览</el-divider>
      <el-table :data="selectedRows" border stripe max-height="300px">
        <el-table-column type="index" label="序号" width="60" align="center" />
        <el-table-column prop="account_date" label="日期" width="120" align="center" />
        <el-table-column prop="type" label="类型" width="80" align="center">
          <template #default="{ row }">
            <el-tag :type="row.type === 1 ? 'danger' : 'success'" size="small">
              {{ row.type === 1 ? '借' : '贷' }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="amount" label="金额" width="120" align="right">
          <template #default="{ row }">
            <span :class="row.type === 1 ? 'debit' : 'credit'">
              {{ row.type === 1 ? '+' : '-' }}{{ formatMoney(row.amount) }}
            </span>
          </template>
        </el-table-column>
        <el-table-column prop="remark" label="摘要" min-width="150" show-overflow-tooltip />
      </el-table>

      <template #footer>
        <el-button @click="batchDialogVisible = false">取消</el-button>
        <el-button type="primary" :loading="submitLoading" @click="submitBatchReceipt">确定入账</el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { Search, Refresh, Check } from '@element-plus/icons-vue'
import {
  getNoReceiptAccounts,
  updateAccount,
  deleteAccount,
  batchReceipt,
} from '@/api/modules/receivable'
import { getSupplierList } from '@/api/modules/supplier'

// 搜索表单
const searchForm = reactive({
  canteen_name: '',
  supp_id: null,
  type: null,
  start_date: '',
  end_date: '',
})

const dateRange = ref([])

// 供应商列表
const supplierList = ref([])

// 表格数据
const loading = ref(false)
const tableData = ref([])
const selectedRows = ref([])
const tableRef = ref()

// 统计数据
const stats = reactive({
  total_count: 0,
  debit_amount: 0,
  credit_amount: 0,
})

// 分页
const pagination = reactive({
  page: 1,
  pageSize: 20,
  total: 0,
})

// 编辑对话框
const editDialogVisible = ref(false)
const formRef = ref()
const submitLoading = ref(false)

// 表单数据
const formData = reactive({
  id: null,
  account_date: '',
  type: 1,
  amount: 0,
  remark: '',
})

// 表单验证规则
const formRules = {
  account_date: [{ required: true, message: '请选择日期', trigger: 'change' }],
  type: [{ required: true, message: '请选择类型', trigger: 'change' }],
  amount: [{ required: true, message: '请输入金额', trigger: 'blur' }],
}

// 批量入账对话框
const batchDialogVisible = ref(false)
const batchFormRef = ref()
const batchForm = reactive({
  receipt_date: '',
  remark: '',
})

const batchRules = {
  receipt_date: [{ required: true, message: '请选择对账日期', trigger: 'change' }],
}

// 已选学校和供应商名称
const selectedSchoolName = computed(() => {
  if (selectedRows.value.length === 0) return ''
  return selectedRows.value[0].canteen_name
})

const selectedSupplierName = computed(() => {
  if (selectedRows.value.length === 0) return ''
  return selectedRows.value[0].supp_name
})

// 批量入账金额合计
const batchTotalAmount = computed(() => {
  return selectedRows.value.reduce((sum, item) => {
    return sum + (item.type === 1 ? Number(item.amount) : -Number(item.amount))
  }, 0)
})

// 格式化金额
function formatMoney(value) {
  if (!value && value !== 0) return '0.00'
  return Number(value).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',')
}

// 检查是否可选择（只有同一学校和供应商的才能一起选择）
function checkSelectable(row, index) {
  if (selectedRows.value.length === 0) return true

  const firstRow = selectedRows.value[0]
  return row.canteen_id === firstRow.canteen_id && row.supp_id === firstRow.supp_id
}

// 选择处理
function handleSelect(selection, row) {
  // 如果选择了不同学校或供应商的，清空之前的选择
  if (selectedRows.value.length > 0) {
    const firstRow = selectedRows.value[0]
    if (row.canteen_id !== firstRow.canteen_id || row.supp_id !== firstRow.supp_id) {
      tableRef.value.clearSelection()
      tableRef.value.toggleRowSelection(row, true)
      selectedRows.value = [row]
      return
    }
  }
  selectedRows.value = selection
}

// 全选处理
function handleSelectAll(selection) {
  if (selection.length > 0) {
    // 检查是否都属于同一学校和供应商
    const canteenIds = [...new Set(selection.map((item) => item.canteen_id))]
    const suppIds = [...new Set(selection.map((item) => item.supp_id))]

    if (canteenIds.length > 1 || suppIds.length > 1) {
      ElMessage.warning('只能选择同一学校和供应商的账单')
      tableRef.value.clearSelection()
      selectedRows.value = []
      return
    }
  }
  selectedRows.value = selection
}

// 选择变化
function handleSelectionChange(selection) {
  selectedRows.value = selection
}

// 日期范围变化
function handleDateChange(val) {
  if (val) {
    searchForm.start_date = val[0]
    searchForm.end_date = val[1]
  } else {
    searchForm.start_date = ''
    searchForm.end_date = ''
  }
}

// 获取未入账账单列表
async function fetchData() {
  loading.value = true
  try {
    const { data } = await getNoReceiptAccounts({
      ...searchForm,
      page: pagination.page,
      page_size: pagination.pageSize,
    })
    tableData.value = data.list || []
    pagination.total = data.total || 0

    // 统计数据
    stats.total_count = data.total || 0
    stats.debit_amount = data.debit_amount || 0
    stats.credit_amount = data.credit_amount || 0
  } catch (error) {
    console.error('获取未入账账单列表失败:', error)
  } finally {
    loading.value = false
  }
}

// 获取供应商列表
async function fetchSupplierList() {
  try {
    const { data } = await getSupplierList({ page_size: 1000, status: 1 })
    supplierList.value = data.list || []
  } catch (error) {
    console.error('获取供应商列表失败:', error)
  }
}

// 搜索
function handleSearch() {
  pagination.page = 1
  fetchData()
}

// 重置
function handleReset() {
  dateRange.value = []
  Object.assign(searchForm, {
    canteen_name: '',
    supp_id: null,
    type: null,
    start_date: '',
    end_date: '',
  })
  handleSearch()
}

// 编辑
function handleEdit(row) {
  Object.assign(formData, {
    id: row.id,
    account_date: row.account_date,
    type: row.type,
    amount: row.amount,
    remark: row.remark,
  })
  editDialogVisible.value = true
}

// 删除
async function handleDelete(row) {
  try {
    await ElMessageBox.confirm('确定要删除该账单明细吗？', '提示', {
      type: 'warning',
    })
    await deleteAccount(row.id)
    ElMessage.success('删除成功')
    fetchData()
  } catch (error) {
    if (error !== 'cancel') {
      ElMessage.error(error.message || '删除失败')
    }
  }
}

// 批量入账
function handleBatchReceipt() {
  if (selectedRows.value.length === 0) {
    ElMessage.warning('请选择要入账的明细')
    return
  }

  batchForm.receipt_date = new Date().toISOString().split('T')[0]
  batchForm.remark = ''
  batchDialogVisible.value = true
}

// 提交批量入账
async function submitBatchReceipt() {
  try {
    await batchFormRef.value.validate()
    submitLoading.value = true

    const data = {
      account_ids: selectedRows.value.map((item) => item.id),
      receipt_date: batchForm.receipt_date,
      remark: batchForm.remark,
    }

    await batchReceipt(data)
    ElMessage.success('批量入账成功')
    batchDialogVisible.value = false
    selectedRows.value = []
    fetchData()
  } catch (error) {
    console.error('批量入账失败:', error)
  } finally {
    submitLoading.value = false
  }
}

// 提交表单
async function handleSubmit() {
  try {
    await formRef.value.validate()
    submitLoading.value = true

    await updateAccount(formData.id, formData)
    ElMessage.success('更新成功')
    editDialogVisible.value = false
    fetchData()
  } catch (error) {
    console.error('提交失败:', error)
  } finally {
    submitLoading.value = false
  }
}

// 对话框关闭
function handleDialogClosed() {
  formRef.value?.resetFields()
  Object.assign(formData, {
    id: null,
    account_date: '',
    type: 1,
    amount: 0,
    remark: '',
  })
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
  fetchSupplierList()
})
</script>

<style lang="scss" scoped>
.no-receipt-list {
  .search-card {
    margin-bottom: 20px;
  }

  .table-card {
    .card-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .header-actions {
      display: flex;
      gap: 10px;
    }
  }

  .stats-row {
    margin-bottom: 20px;
  }

  .stat-card {
    background: #f5f7fa;
    border-radius: 4px;
    padding: 20px;
    text-align: center;

    .stat-label {
      font-size: 14px;
      color: #909399;
      margin-bottom: 10px;
    }

    .stat-value {
      font-size: 24px;
      font-weight: 600;
      color: #303133;

      &.danger {
        color: #f56c6c;
      }

      &.success {
        color: #67c23a;
      }
    }
  }

  .money {
    font-weight: 600;

    &.debit {
      color: #f56c6c;
    }

    &.credit {
      color: #67c23a;
    }
  }

  .el-pagination {
    margin-top: 20px;
    justify-content: flex-end;
  }
}
</style>
