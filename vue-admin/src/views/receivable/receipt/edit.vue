<template>
  <div class="receipt-edit">
    <el-card>
      <template #header>
        <div class="card-header">
          <span>{{ isEdit ? '编辑对账单' : '新增对账单' }}</span>
          <div class="header-actions">
            <el-button @click="handleBack">
              <el-icon><Back /></el-icon>
              返回
            </el-button>
            <el-button type="primary" :loading="submitLoading" @click="handleSubmit">
              <el-icon><Check /></el-icon>
              保存
            </el-button>
          </div>
        </div>
      </template>

      <el-form ref="formRef" :model="formData" :rules="formRules" label-width="120px">
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="学校/食堂" prop="canteen_id">
              <el-select
                v-model="formData.canteen_id"
                placeholder="请选择学校/食堂"
                filterable
                style="width: 100%"
                @change="handleCanteenChange"
              >
                <el-option
                  v-for="item in canteenList"
                  :key="item.id"
                  :label="item.canteen_name"
                  :value="item.id"
                />
              </el-select>
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="供应商" prop="supp_id">
              <el-select
                v-model="formData.supp_id"
                placeholder="请选择供应商"
                filterable
                style="width: 100%"
              >
                <el-option
                  v-for="item in supplierList"
                  :key="item.id"
                  :label="item.supplier_name"
                  :value="item.id"
                />
              </el-select>
            </el-form-item>
          </el-col>
        </el-row>

        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="对账日期" prop="receipt_date">
              <el-date-picker
                v-model="formData.receipt_date"
                type="date"
                placeholder="选择日期"
                value-format="YYYY-MM-DD"
                style="width: 100%"
              />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="应收金额">
              <el-input :model-value="formatMoney(totalAmount)" disabled>
                <template #prepend>¥</template>
              </el-input>
            </el-form-item>
          </el-col>
        </el-row>

        <el-form-item label="备注">
          <el-input v-model="formData.remark" type="textarea" :rows="3" placeholder="请输入备注" maxlength="500" />
        </el-form-item>
      </el-form>
    </el-card>

    <!-- 账单明细 -->
    <el-card class="detail-card">
      <template #header>
        <div class="card-header">
          <span>账单明细</span>
          <div class="header-actions">
            <el-button type="primary" @click="handleAddAccount">
              <el-icon><Plus /></el-icon>
              添加明细
            </el-button>
            <el-button @click="handleImportOrder">
              <el-icon><Upload /></el-icon>
              从订单导入
            </el-button>
          </div>
        </div>
      </template>

      <el-table :data="formData.accounts" border stripe show-summary :summary-method="getSummaries">
        <el-table-column type="index" label="序号" width="60" align="center" />
        <el-table-column prop="account_date" label="日期" width="120" align="center">
          <template #default="{ row }">
            <el-date-picker
              v-model="row.account_date"
              type="date"
              placeholder="选择日期"
              value-format="YYYY-MM-DD"
              size="small"
              style="width: 100%"
            />
          </template>
        </el-table-column>
        <el-table-column prop="type" label="类型" width="100" align="center">
          <template #default="{ row }">
            <el-select v-model="row.type" size="small">
              <el-option label="借方" :value="1" />
              <el-option label="贷方" :value="2" />
            </el-select>
          </template>
        </el-table-column>
        <el-table-column prop="amount" label="金额" width="140" align="right">
          <template #default="{ row }">
            <el-input-number
              v-model="row.amount"
              :precision="2"
              :min="0"
              size="small"
              controls-position="right"
              style="width: 100%"
            />
          </template>
        </el-table-column>
        <el-table-column prop="order_no" label="关联订单" min-width="150">
          <template #default="{ row }">
            <el-input v-model="row.order_no" placeholder="订单号" size="small" />
          </template>
        </el-table-column>
        <el-table-column prop="remark" label="摘要" min-width="200">
          <template #default="{ row }">
            <el-input v-model="row.remark" placeholder="请输入摘要" size="small" />
          </template>
        </el-table-column>
        <el-table-column label="操作" width="80" align="center" fixed="right">
          <template #default="{ $index }">
            <el-button type="danger" link @click="handleDeleteAccount($index)">
              删除
            </el-button>
          </template>
        </el-table-column>
      </el-table>
    </el-card>

    <!-- 从订单导入对话框 -->
    <el-dialog v-model="importDialogVisible" title="从订单导入" width="80%" :close-on-click-modal="false">
      <el-form :model="importForm" inline class="import-search">
        <el-form-item label="订单日期">
          <el-date-picker
            v-model="importDateRange"
            type="daterange"
            range-separator="至"
            start-placeholder="开始日期"
            end-placeholder="结束日期"
            value-format="YYYY-MM-DD"
            style="width: 240px"
          />
        </el-form-item>
        <el-form-item label="订单号">
          <el-input v-model="importForm.order_no" placeholder="请输入订单号" clearable />
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="fetchOrderList">搜索</el-button>
        </el-form-item>
      </el-form>

      <el-table
        ref="orderTableRef"
        v-loading="orderLoading"
        :data="orderList"
        border
        stripe
        @selection-change="handleOrderSelectionChange"
      >
        <el-table-column type="selection" width="55" align="center" />
        <el-table-column prop="order_no" label="订单号" width="180" align="center" />
        <el-table-column prop="order_date" label="订单日期" width="120" align="center" />
        <el-table-column prop="canteen_name" label="学校/食堂" min-width="150" show-overflow-tooltip />
        <el-table-column prop="supp_name" label="供应商" min-width="150" show-overflow-tooltip />
        <el-table-column prop="total_amount" label="订单金额" width="120" align="right">
          <template #default="{ row }">
            <span class="money">{{ formatMoney(row.total_amount) }}</span>
          </template>
        </el-table-column>
        <el-table-column prop="status_text" label="状态" width="100" align="center">
          <template #default="{ row }">
            <el-tag :type="row.status === 5 ? 'success' : 'info'">{{ row.status_text }}</el-tag>
          </template>
        </el-table-column>
      </el-table>

      <el-pagination
        v-model:current-page="importPagination.page"
        v-model:page-size="importPagination.pageSize"
        :total="importPagination.total"
        :page-sizes="[10, 20, 50]"
        layout="total, sizes, prev, pager, next"
        @size-change="handleImportSizeChange"
        @current-change="handleImportPageChange"
      />

      <template #footer>
        <el-button @click="importDialogVisible = false">取消</el-button>
        <el-button type="primary" :disabled="selectedOrders.length === 0" @click="confirmImport">
          确定导入
        </el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { ElMessage, ElMessageBox } from 'element-plus'
import { Back, Check, Plus, Upload } from '@element-plus/icons-vue'
import { getReceiptDetail, createReceipt, updateReceipt } from '@/api/modules/receivable'
import { getSupplierList } from '@/api/modules/supplier'
import { getCanteenList } from '@/api/modules/school'
import { getOrderList } from '@/api/modules/order'

const router = useRouter()
const route = useRoute()

// 是否编辑模式
const isEdit = computed(() => !!route.params.id)

// 表单引用
const formRef = ref()
const submitLoading = ref(false)

// 表单数据
const formData = reactive({
  id: null,
  canteen_id: null,
  supp_id: null,
  receipt_date: new Date().toISOString().split('T')[0],
  remark: '',
  accounts: [],
})

// 表单验证规则
const formRules = {
  canteen_id: [{ required: true, message: '请选择学校/食堂', trigger: 'change' }],
  supp_id: [{ required: true, message: '请选择供应商', trigger: 'change' }],
  receipt_date: [{ required: true, message: '请选择对账日期', trigger: 'change' }],
}

// 食堂列表
const canteenList = ref([])
// 供应商列表
const supplierList = ref([])

// 计算总金额
const totalAmount = computed(() => {
  return formData.accounts.reduce((sum, item) => {
    return sum + (item.type === 1 ? Number(item.amount) : -Number(item.amount))
  }, 0)
})

// 格式化金额
function formatMoney(value) {
  if (!value && value !== 0) return '0.00'
  return Number(value).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',')
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
      const total = data.reduce((sum, item) => {
        return sum + (item.type === 1 ? Number(item.amount) : -Number(item.amount))
      }, 0)
      sums[index] = formatMoney(total)
    } else {
      sums[index] = ''
    }
  })
  return sums
}

// 获取食堂列表
async function fetchCanteenList() {
  try {
    const { data } = await getCanteenList({ page_size: 1000 })
    canteenList.value = data.list || []
  } catch (error) {
    console.error('获取食堂列表失败:', error)
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

// 获取详情
async function fetchDetail() {
  if (!isEdit.value) return

  try {
    const { data } = await getReceiptDetail(route.params.id)
    formData.id = data.id
    formData.canteen_id = data.canteen_id
    formData.supp_id = data.supp_id
    formData.receipt_date = data.receipt_date
    formData.remark = data.remark
    formData.accounts = data.accounts || []
  } catch (error) {
    ElMessage.error('获取对账单详情失败')
    console.error(error)
  }
}

// 食堂变化
function handleCanteenChange(val) {
  // 可以根据食堂筛选供应商
}

// 添加明细
function handleAddAccount() {
  formData.accounts.push({
    account_date: new Date().toISOString().split('T')[0],
    type: 1,
    amount: 0,
    order_no: '',
    remark: '',
  })
}

// 删除明细
function handleDeleteAccount(index) {
  formData.accounts.splice(index, 1)
}

// 从订单导入
const importDialogVisible = ref(false)
const importDateRange = ref([])
const importForm = reactive({
  order_no: '',
})
const orderTableRef = ref()
const orderLoading = ref(false)
const orderList = ref([])
const selectedOrders = ref([])
const importPagination = reactive({
  page: 1,
  pageSize: 10,
  total: 0,
})

function handleImportOrder() {
  importDialogVisible.value = true
  fetchOrderList()
}

async function fetchOrderList() {
  orderLoading.value = true
  try {
    const params = {
      page: importPagination.page,
      page_size: importPagination.pageSize,
      canteen_id: formData.canteen_id,
      supp_id: formData.supp_id,
      order_no: importForm.order_no,
    }

    if (importDateRange.value && importDateRange.value.length === 2) {
      params.start_date = importDateRange.value[0]
      params.end_date = importDateRange.value[1]
    }

    const { data } = await getOrderList(params)
    orderList.value = data.list || []
    importPagination.total = data.total || 0
  } catch (error) {
    console.error('获取订单列表失败:', error)
  } finally {
    orderLoading.value = false
  }
}

function handleOrderSelectionChange(selection) {
  selectedOrders.value = selection
}

function handleImportSizeChange(size) {
  importPagination.pageSize = size
  fetchOrderList()
}

function handleImportPageChange(page) {
  importPagination.page = page
  fetchOrderList()
}

function confirmImport() {
  selectedOrders.value.forEach((order) => {
    formData.accounts.push({
      account_date: order.order_date,
      type: 1,
      amount: order.total_amount,
      order_no: order.order_no,
      remark: `订单: ${order.order_no}`,
    })
  })
  ElMessage.success(`成功导入 ${selectedOrders.value.length} 条订单`)
  importDialogVisible.value = false
}

// 返回
function handleBack() {
  router.push('/receivable/receipt')
}

// 提交
async function handleSubmit() {
  try {
    await formRef.value.validate()

    if (formData.accounts.length === 0) {
      ElMessage.warning('请至少添加一条账单明细')
      return
    }

    submitLoading.value = true

    const data = {
      canteen_id: formData.canteen_id,
      supp_id: formData.supp_id,
      receipt_date: formData.receipt_date,
      remark: formData.remark,
      accounts: formData.accounts,
    }

    if (isEdit.value) {
      await updateReceipt(formData.id, data)
      ElMessage.success('更新成功')
    } else {
      await createReceipt(data)
      ElMessage.success('创建成功')
    }

    router.push('/receivable/receipt')
  } catch (error) {
    console.error('提交失败:', error)
  } finally {
    submitLoading.value = false
  }
}

// 初始化
onMounted(() => {
  fetchCanteenList()
  fetchSupplierList()
  fetchDetail()
})
</script>

<style lang="scss" scoped>
.receipt-edit {
  .card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  .header-actions {
    display: flex;
    gap: 10px;
  }

  .detail-card {
    margin-top: 20px;
  }

  .money {
    color: #e6a23c;
    font-weight: 600;
  }

  .import-search {
    margin-bottom: 20px;
  }

  .el-pagination {
    margin-top: 20px;
    justify-content: flex-end;
  }
}
</style>
