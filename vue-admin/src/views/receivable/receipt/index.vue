<template>
  <div class="receipt-list">
    <!-- 搜索区域 -->
    <el-card class="search-card">
      <el-form :model="searchForm" inline>
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
        <el-form-item label="开票状态">
          <el-select v-model="searchForm.invoice_status" placeholder="请选择" clearable style="width: 120px">
            <el-option label="未开票" :value="0" />
            <el-option label="已开票" :value="1" />
          </el-select>
        </el-form-item>
        <el-form-item label="收款状态">
          <el-select v-model="searchForm.bill_status" placeholder="请选择" clearable style="width: 120px">
            <el-option label="未收款" :value="0" />
            <el-option label="部分收款" :value="1" />
            <el-option label="已收款" :value="2" />
          </el-select>
        </el-form-item>
        <el-form-item label="学校确认">
          <el-select v-model="searchForm.school_confirm_status" placeholder="请选择" clearable style="width: 120px">
            <el-option label="待确认" :value="0" />
            <el-option label="已确认" :value="1" />
            <el-option label="有异议" :value="2" />
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
          <span>对账单列表</span>
          <div class="header-actions">
            <el-button type="primary" @click="handleAdd">
              <el-icon><Plus /></el-icon>
              新增对账单
            </el-button>
            <el-button @click="handleExport">
              <el-icon><Download /></el-icon>
              导出
            </el-button>
          </div>
        </div>
      </template>

      <!-- 统计卡片 -->
      <el-row :gutter="20" class="stats-row">
        <el-col :span="6">
          <div class="stat-card">
            <div class="stat-label">应收总额</div>
            <div class="stat-value">{{ formatMoney(stats.total_amount) }}</div>
          </div>
        </el-col>
        <el-col :span="6">
          <div class="stat-card">
            <div class="stat-label">已开票金额</div>
            <div class="stat-value success">{{ formatMoney(stats.invoice_amount) }}</div>
          </div>
        </el-col>
        <el-col :span="6">
          <div class="stat-card">
            <div class="stat-label">已收款金额</div>
            <div class="stat-value primary">{{ formatMoney(stats.bill_amount) }}</div>
          </div>
        </el-col>
        <el-col :span="6">
          <div class="stat-card">
            <div class="stat-label">待收款金额</div>
            <div class="stat-value warning">{{ formatMoney(stats.wait_bill_amount) }}</div>
          </div>
        </el-col>
      </el-row>

      <!-- 表格 -->
      <el-table v-loading="loading" :data="tableData" border stripe>
        <el-table-column prop="id" label="ID" width="80" align="center" />
        <el-table-column prop="receipt_no" label="对账单号" width="180" align="center" />
        <el-table-column prop="canteen_name" label="学校/食堂" min-width="150" show-overflow-tooltip />
        <el-table-column prop="supp_name" label="供应商" min-width="150" show-overflow-tooltip />
        <el-table-column prop="receipt_date" label="对账日期" width="120" align="center" />
        <el-table-column prop="total_amount" label="应收金额" width="120" align="right">
          <template #default="{ row }">
            <span class="money">{{ formatMoney(row.total_amount) }}</span>
          </template>
        </el-table-column>
        <el-table-column prop="invoice_status" label="开票状态" width="100" align="center">
          <template #default="{ row }">
            <el-tag :type="row.invoice_status === 1 ? 'success' : 'info'">
              {{ row.invoice_status === 1 ? '已开票' : '未开票' }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="bill_status" label="收款状态" width="100" align="center">
          <template #default="{ row }">
            <el-tag :type="getBillStatusType(row.bill_status)">
              {{ getBillStatusText(row.bill_status) }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="school_confirm_status" label="学校确认" width="100" align="center">
          <template #default="{ row }">
            <el-tag :type="getConfirmStatusType(row.school_confirm_status)">
              {{ getConfirmStatusText(row.school_confirm_status) }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="created_at" label="创建时间" width="180" align="center" />
        <el-table-column label="操作" width="280" align="center" fixed="right">
          <template #default="{ row }">
            <el-button type="primary" link @click="handleView(row)">查看</el-button>
            <el-button type="primary" link @click="handleEdit(row)">编辑</el-button>
            <el-button v-if="row.invoice_status === 0" type="success" link @click="handleInvoice(row)">
              开票
            </el-button>
            <el-button v-if="row.bill_status !== 2" type="warning" link @click="handleBill(row)">
              收款
            </el-button>
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

    <!-- 开票对话框 -->
    <el-dialog v-model="invoiceDialogVisible" title="开票" width="500px" :close-on-click-modal="false">
      <el-form ref="invoiceFormRef" :model="invoiceForm" :rules="invoiceRules" label-width="100px">
        <el-form-item label="对账单号">
          <el-input v-model="currentReceipt.receipt_no" disabled />
        </el-form-item>
        <el-form-item label="应收金额">
          <el-input :model-value="formatMoney(currentReceipt.total_amount)" disabled />
        </el-form-item>
        <el-form-item label="开票金额" prop="invoice_amount">
          <el-input-number
            v-model="invoiceForm.invoice_amount"
            :precision="2"
            :min="0"
            :max="currentReceipt.total_amount"
            style="width: 100%"
          />
        </el-form-item>
        <el-form-item label="发票号码" prop="invoice_no">
          <el-input v-model="invoiceForm.invoice_no" placeholder="请输入发票号码" maxlength="50" />
        </el-form-item>
        <el-form-item label="开票日期" prop="invoice_date">
          <el-date-picker
            v-model="invoiceForm.invoice_date"
            type="date"
            placeholder="选择日期"
            value-format="YYYY-MM-DD"
            style="width: 100%"
          />
        </el-form-item>
        <el-form-item label="备注">
          <el-input v-model="invoiceForm.remark" type="textarea" :rows="3" placeholder="请输入备注" />
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="invoiceDialogVisible = false">取消</el-button>
        <el-button type="primary" :loading="submitLoading" @click="submitInvoice">确定</el-button>
      </template>
    </el-dialog>

    <!-- 收款对话框 -->
    <el-dialog v-model="billDialogVisible" title="收款" width="500px" :close-on-click-modal="false">
      <el-form ref="billFormRef" :model="billForm" :rules="billRules" label-width="100px">
        <el-form-item label="对账单号">
          <el-input v-model="currentReceipt.receipt_no" disabled />
        </el-form-item>
        <el-form-item label="应收金额">
          <el-input :model-value="formatMoney(currentReceipt.total_amount)" disabled />
        </el-form-item>
        <el-form-item label="已收款">
          <el-input :model-value="formatMoney(currentReceipt.bill_amount)" disabled />
        </el-form-item>
        <el-form-item label="待收款">
          <el-input :model-value="formatMoney(currentReceipt.total_amount - currentReceipt.bill_amount)" disabled />
        </el-form-item>
        <el-form-item label="本次收款" prop="bill_amount">
          <el-input-number
            v-model="billForm.bill_amount"
            :precision="2"
            :min="0"
            :max="currentReceipt.total_amount - currentReceipt.bill_amount"
            style="width: 100%"
          />
        </el-form-item>
        <el-form-item label="收款方式" prop="bill_type">
          <el-select v-model="billForm.bill_type" placeholder="请选择" style="width: 100%">
            <el-option label="银行转账" :value="1" />
            <el-option label="现金" :value="2" />
            <el-option label="支票" :value="3" />
            <el-option label="其他" :value="9" />
          </el-select>
        </el-form-item>
        <el-form-item label="收款日期" prop="bill_date">
          <el-date-picker
            v-model="billForm.bill_date"
            type="date"
            placeholder="选择日期"
            value-format="YYYY-MM-DD"
            style="width: 100%"
          />
        </el-form-item>
        <el-form-item label="备注">
          <el-input v-model="billForm.remark" type="textarea" :rows="3" placeholder="请输入备注" />
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="billDialogVisible = false">取消</el-button>
        <el-button type="primary" :loading="submitLoading" @click="submitBill">确定</el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import { ElMessage, ElMessageBox } from 'element-plus'
import { Search, Refresh, Plus, Download } from '@element-plus/icons-vue'
import {
  getReceiptList,
  deleteReceipt,
  invoiceReceipt,
  billReceipt,
  getReceivableStats,
} from '@/api/modules/receivable'
import { getSupplierList } from '@/api/modules/supplier'

const router = useRouter()

// 搜索表单
const searchForm = reactive({
  start_date: '',
  end_date: '',
  canteen_name: '',
  supp_id: null,
  invoice_status: null,
  bill_status: null,
  school_confirm_status: null,
})

const dateRange = ref([])

// 供应商列表
const supplierList = ref([])

// 表格数据
const loading = ref(false)
const tableData = ref([])

// 统计数据
const stats = reactive({
  total_amount: 0,
  invoice_amount: 0,
  bill_amount: 0,
  wait_bill_amount: 0,
})

// 分页
const pagination = reactive({
  page: 1,
  pageSize: 20,
  total: 0,
})

// 当前操作的对账单
const currentReceipt = ref({})

// 开票对话框
const invoiceDialogVisible = ref(false)
const invoiceFormRef = ref()
const invoiceForm = reactive({
  invoice_amount: 0,
  invoice_no: '',
  invoice_date: '',
  remark: '',
})

const invoiceRules = {
  invoice_amount: [{ required: true, message: '请输入开票金额', trigger: 'blur' }],
  invoice_no: [{ required: true, message: '请输入发票号码', trigger: 'blur' }],
  invoice_date: [{ required: true, message: '请选择开票日期', trigger: 'change' }],
}

// 收款对话框
const billDialogVisible = ref(false)
const billFormRef = ref()
const billForm = reactive({
  bill_amount: 0,
  bill_type: 1,
  bill_date: '',
  remark: '',
})

const billRules = {
  bill_amount: [{ required: true, message: '请输入收款金额', trigger: 'blur' }],
  bill_type: [{ required: true, message: '请选择收款方式', trigger: 'change' }],
  bill_date: [{ required: true, message: '请选择收款日期', trigger: 'change' }],
}

const submitLoading = ref(false)

// 格式化金额
function formatMoney(value) {
  if (!value && value !== 0) return '0.00'
  return Number(value).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',')
}

// 获取收款状态类型
function getBillStatusType(status) {
  const map = {
    0: 'info',
    1: 'warning',
    2: 'success',
  }
  return map[status] || 'info'
}

// 获取收款状态文本
function getBillStatusText(status) {
  const map = {
    0: '未收款',
    1: '部分收款',
    2: '已收款',
  }
  return map[status] || '未知'
}

// 获取确认状态类型
function getConfirmStatusType(status) {
  const map = {
    0: 'info',
    1: 'success',
    2: 'danger',
  }
  return map[status] || 'info'
}

// 获取确认状态文本
function getConfirmStatusText(status) {
  const map = {
    0: '待确认',
    1: '已确认',
    2: '有异议',
  }
  return map[status] || '未知'
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

// 获取对账单列表
async function fetchData() {
  loading.value = true
  try {
    const { data } = await getReceiptList({
      ...searchForm,
      page: pagination.page,
      page_size: pagination.pageSize,
    })
    tableData.value = data.list || []
    pagination.total = data.total || 0
  } catch (error) {
    console.error('获取对账单列表失败:', error)
  } finally {
    loading.value = false
  }
}

// 获取统计数据
async function fetchStats() {
  try {
    const { data } = await getReceivableStats(searchForm)
    Object.assign(stats, data)
  } catch (error) {
    console.error('获取统计数据失败:', error)
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
  fetchStats()
}

// 重置
function handleReset() {
  dateRange.value = []
  Object.assign(searchForm, {
    start_date: '',
    end_date: '',
    canteen_name: '',
    supp_id: null,
    invoice_status: null,
    bill_status: null,
    school_confirm_status: null,
  })
  handleSearch()
}

// 新增
function handleAdd() {
  router.push('/receivable/receipt/add')
}

// 查看
function handleView(row) {
  router.push(`/receivable/receipt/view/${row.id}`)
}

// 编辑
function handleEdit(row) {
  router.push(`/receivable/receipt/edit/${row.id}`)
}

// 删除
async function handleDelete(row) {
  try {
    await ElMessageBox.confirm('确定要删除该对账单吗？', '提示', {
      type: 'warning',
    })
    await deleteReceipt(row.id)
    ElMessage.success('删除成功')
    fetchData()
    fetchStats()
  } catch (error) {
    if (error !== 'cancel') {
      ElMessage.error(error.message || '删除失败')
    }
  }
}

// 开票
function handleInvoice(row) {
  currentReceipt.value = row
  invoiceForm.invoice_amount = row.total_amount
  invoiceForm.invoice_no = ''
  invoiceForm.invoice_date = new Date().toISOString().split('T')[0]
  invoiceForm.remark = ''
  invoiceDialogVisible.value = true
}

// 提交开票
async function submitInvoice() {
  try {
    await invoiceFormRef.value.validate()
    submitLoading.value = true
    await invoiceReceipt(currentReceipt.value.id, invoiceForm)
    ElMessage.success('开票成功')
    invoiceDialogVisible.value = false
    fetchData()
    fetchStats()
  } catch (error) {
    console.error('开票失败:', error)
  } finally {
    submitLoading.value = false
  }
}

// 收款
function handleBill(row) {
  currentReceipt.value = row
  billForm.bill_amount = row.total_amount - row.bill_amount
  billForm.bill_type = 1
  billForm.bill_date = new Date().toISOString().split('T')[0]
  billForm.remark = ''
  billDialogVisible.value = true
}

// 提交收款
async function submitBill() {
  try {
    await billFormRef.value.validate()
    submitLoading.value = true
    await billReceipt(currentReceipt.value.id, billForm)
    ElMessage.success('收款成功')
    billDialogVisible.value = false
    fetchData()
    fetchStats()
  } catch (error) {
    console.error('收款失败:', error)
  } finally {
    submitLoading.value = false
  }
}

// 导出
function handleExport() {
  ElMessage.info('导出功能开发中')
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
  fetchStats()
  fetchSupplierList()
})
</script>

<style lang="scss" scoped>
.receipt-list {
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

      &.success {
        color: #67c23a;
      }

      &.primary {
        color: #409eff;
      }

      &.warning {
        color: #e6a23c;
      }
    }
  }

  .money {
    color: #e6a23c;
    font-weight: 600;
  }

  .el-pagination {
    margin-top: 20px;
    justify-content: flex-end;
  }
}
</style>
