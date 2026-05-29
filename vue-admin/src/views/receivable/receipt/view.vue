<template>
  <div class="receipt-view">
    <el-card class="info-card">
      <template #header>
        <div class="card-header">
          <span>对账单详情</span>
          <div class="header-actions">
            <el-button @click="handleBack">
              <el-icon><Back /></el-icon>
              返回
            </el-button>
            <el-button type="primary" @click="handleEdit">
              <el-icon><Edit /></el-icon>
              编辑
            </el-button>
            <el-button v-if="detail.invoice_status === 0" type="success" @click="handleInvoice">
              <el-icon><Document /></el-icon>
              开票
            </el-button>
            <el-button v-if="detail.bill_status !== 2" type="warning" @click="handleBill">
              <el-icon><Money /></el-icon>
              收款
            </el-button>
            <el-button @click="handleExport">
              <el-icon><Download /></el-icon>
              导出
            </el-button>
          </div>
        </div>
      </template>

      <!-- 基本信息 -->
      <el-descriptions :column="3" border>
        <el-descriptions-item label="对账单号">{{ detail.receipt_no }}</el-descriptions-item>
        <el-descriptions-item label="对账日期">{{ detail.receipt_date }}</el-descriptions-item>
        <el-descriptions-item label="创建时间">{{ detail.created_at }}</el-descriptions-item>
        <el-descriptions-item label="学校/食堂">{{ detail.canteen_name }}</el-descriptions-item>
        <el-descriptions-item label="供应商">{{ detail.supp_name }}</el-descriptions-item>
        <el-descriptions-item label="状态">
          <el-tag :type="getBillStatusType(detail.bill_status)">
            {{ getBillStatusText(detail.bill_status) }}
          </el-tag>
        </el-descriptions-item>
      </el-descriptions>

      <!-- 金额信息 -->
      <el-divider content-position="left">金额信息</el-divider>
      <el-row :gutter="20">
        <el-col :span="6">
          <div class="amount-card">
            <div class="label">应收金额</div>
            <div class="value">{{ formatMoney(detail.total_amount) }}</div>
          </div>
        </el-col>
        <el-col :span="6">
          <div class="amount-card">
            <div class="label">已开票金额</div>
            <div class="value success">{{ formatMoney(detail.invoice_amount) }}</div>
          </div>
        </el-col>
        <el-col :span="6">
          <div class="amount-card">
            <div class="label">已收款金额</div>
            <div class="value primary">{{ formatMoney(detail.bill_amount) }}</div>
          </div>
        </el-col>
        <el-col :span="6">
          <div class="amount-card">
            <div class="label">待收款金额</div>
            <div class="value warning">{{ formatMoney(detail.total_amount - detail.bill_amount) }}</div>
          </div>
        </el-col>
      </el-row>

      <!-- 开票信息 -->
      <el-divider content-position="left">开票信息</el-divider>
      <el-descriptions :column="3" border>
        <el-descriptions-item label="开票状态">
          <el-tag :type="detail.invoice_status === 1 ? 'success' : 'info'">
            {{ detail.invoice_status === 1 ? '已开票' : '未开票' }}
          </el-tag>
        </el-descriptions-item>
        <el-descriptions-item label="发票号码">{{ detail.invoice_no || '-' }}</el-descriptions-item>
        <el-descriptions-item label="开票日期">{{ detail.invoice_date || '-' }}</el-descriptions-item>
      </el-descriptions>

      <!-- 学校确认信息 -->
      <el-divider content-position="left">学校确认</el-divider>
      <el-descriptions :column="3" border>
        <el-descriptions-item label="确认状态">
          <el-tag :type="getConfirmStatusType(detail.school_confirm_status)">
            {{ getConfirmStatusText(detail.school_confirm_status) }}
          </el-tag>
        </el-descriptions-item>
        <el-descriptions-item label="确认时间">{{ detail.school_confirm_time || '-' }}</el-descriptions-item>
        <el-descriptions-item label="确认人">{{ detail.school_confirm_user || '-' }}</el-descriptions-item>
        <el-descriptions-item label="异议说明" :span="3">
          {{ detail.school_remark || '-' }}
        </el-descriptions-item>
      </el-descriptions>

      <!-- 备注 -->
      <el-divider content-position="left">备注</el-divider>
      <div class="remark-content">{{ detail.remark || '暂无备注' }}</div>
    </el-card>

    <!-- 账单明细 -->
    <el-card class="detail-card">
      <template #header>
        <div class="card-header">
          <span>账单明细</span>
        </div>
      </template>

      <el-table v-loading="detailLoading" :data="accountList" border stripe show-summary :summary-method="getSummaries">
        <el-table-column type="index" label="序号" width="60" align="center" />
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
        <el-table-column prop="order_no" label="关联订单" min-width="150" show-overflow-tooltip />
        <el-table-column prop="remark" label="摘要" min-width="200" show-overflow-tooltip />
        <el-table-column prop="created_at" label="创建时间" width="180" align="center" />
      </el-table>
    </el-card>

    <!-- 收款记录 -->
    <el-card class="bill-card">
      <template #header>
        <div class="card-header">
          <span>收款记录</span>
        </div>
      </template>

      <el-table v-loading="billLoading" :data="billList" border stripe>
        <el-table-column type="index" label="序号" width="60" align="center" />
        <el-table-column prop="bill_date" label="收款日期" width="120" align="center" />
        <el-table-column prop="bill_amount" label="收款金额" width="120" align="right">
          <template #default="{ row }">
            <span class="money">{{ formatMoney(row.bill_amount) }}</span>
          </template>
        </el-table-column>
        <el-table-column prop="bill_type" label="收款方式" width="120" align="center">
          <template #default="{ row }">
            {{ getBillTypeText(row.bill_type) }}
          </template>
        </el-table-column>
        <el-table-column prop="remark" label="备注" min-width="200" show-overflow-tooltip />
        <el-table-column prop="created_at" label="创建时间" width="180" align="center" />
        <el-table-column prop="creator_name" label="操作人" width="120" align="center" />
      </el-table>
    </el-card>

    <!-- 开票对话框 -->
    <el-dialog v-model="invoiceDialogVisible" title="开票" width="500px" :close-on-click-modal="false">
      <el-form ref="invoiceFormRef" :model="invoiceForm" :rules="invoiceRules" label-width="100px">
        <el-form-item label="应收金额">
          <el-input :model-value="formatMoney(detail.total_amount)" disabled />
        </el-form-item>
        <el-form-item label="开票金额" prop="invoice_amount">
          <el-input-number
            v-model="invoiceForm.invoice_amount"
            :precision="2"
            :min="0"
            :max="detail.total_amount"
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
        <el-form-item label="应收金额">
          <el-input :model-value="formatMoney(detail.total_amount)" disabled />
        </el-form-item>
        <el-form-item label="已收款">
          <el-input :model-value="formatMoney(detail.bill_amount)" disabled />
        </el-form-item>
        <el-form-item label="待收款">
          <el-input :model-value="formatMoney(detail.total_amount - detail.bill_amount)" disabled />
        </el-form-item>
        <el-form-item label="本次收款" prop="bill_amount">
          <el-input-number
            v-model="billForm.bill_amount"
            :precision="2"
            :min="0"
            :max="detail.total_amount - detail.bill_amount"
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
import { ref, reactive, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { ElMessage } from 'element-plus'
import { Back, Edit, Document, Money, Download } from '@element-plus/icons-vue'
import { getReceiptDetail, invoiceReceipt, billReceipt } from '@/api/modules/receivable'

const router = useRouter()
const route = useRoute()

// 详情数据
const loading = ref(false)
const detail = ref({
  id: null,
  receipt_no: '',
  receipt_date: '',
  canteen_name: '',
  supp_name: '',
  total_amount: 0,
  invoice_amount: 0,
  bill_amount: 0,
  invoice_status: 0,
  invoice_no: '',
  invoice_date: '',
  bill_status: 0,
  school_confirm_status: 0,
  school_confirm_time: '',
  school_confirm_user: '',
  school_remark: '',
  remark: '',
  created_at: '',
})

// 账单明细
const detailLoading = ref(false)
const accountList = ref([])

// 收款记录
const billLoading = ref(false)
const billList = ref([])

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

// 获取收款方式文本
function getBillTypeText(type) {
  const map = {
    1: '银行转账',
    2: '现金',
    3: '支票',
    9: '其他',
  }
  return map[type] || '未知'
}

// 合计
function getSummaries(param) {
  const { columns, data } = param
  const sums = []
  columns.forEach((column, index) => {
    if (index === 0) {
      sums[index] = '合计'
      return
    }
    if (column.property === 'amount') {
      const debit = data.filter((item) => item.type === 1).reduce((sum, item) => sum + Number(item.amount), 0)
      const credit = data.filter((item) => item.type === 2).reduce((sum, item) => sum + Number(item.amount), 0)
      sums[index] = `${formatMoney(debit - credit)}`
    } else {
      sums[index] = ''
    }
  })
  return sums
}

// 获取详情
async function fetchDetail() {
  loading.value = true
  try {
    const { data } = await getReceiptDetail(route.params.id)
    detail.value = data
    accountList.value = data.accounts || []
    billList.value = data.bills || []
  } catch (error) {
    ElMessage.error('获取对账单详情失败')
    console.error(error)
  } finally {
    loading.value = false
  }
}

// 返回
function handleBack() {
  router.push('/receivable/receipt')
}

// 编辑
function handleEdit() {
  router.push(`/receivable/receipt/edit/${route.params.id}`)
}

// 开票
function handleInvoice() {
  invoiceForm.invoice_amount = detail.value.total_amount
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
    await invoiceReceipt(detail.value.id, invoiceForm)
    ElMessage.success('开票成功')
    invoiceDialogVisible.value = false
    fetchDetail()
  } catch (error) {
    console.error('开票失败:', error)
  } finally {
    submitLoading.value = false
  }
}

// 收款
function handleBill() {
  billForm.bill_amount = detail.value.total_amount - detail.value.bill_amount
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
    await billReceipt(detail.value.id, billForm)
    ElMessage.success('收款成功')
    billDialogVisible.value = false
    fetchDetail()
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

// 初始化
onMounted(() => {
  fetchDetail()
})
</script>

<style lang="scss" scoped>
.receipt-view {
  .info-card,
  .detail-card,
  .bill-card {
    margin-bottom: 20px;

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

  .amount-card {
    background: #f5f7fa;
    border-radius: 4px;
    padding: 20px;
    text-align: center;

    .label {
      font-size: 14px;
      color: #909399;
      margin-bottom: 10px;
    }

    .value {
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
    font-weight: 600;

    &.debit {
      color: #f56c6c;
    }

    &.credit {
      color: #67c23a;
    }
  }

  .remark-content {
    padding: 10px;
    background: #f5f7fa;
    border-radius: 4px;
    min-height: 60px;
  }
}
</style>
