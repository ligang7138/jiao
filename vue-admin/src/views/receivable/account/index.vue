<template>
  <div class="account-container">
    <!-- 搜索表单 -->
    <el-card class="search-card">
      <el-form :model="searchForm" inline>
        <el-form-item label="对账单">
          <el-select v-model="searchForm.receipt_id" placeholder="请选择对账单" clearable style="width: 200px">
            <el-option v-for="item in receiptOptions" :key="item.id" :label="item.voucher_sn" :value="item.id" />
          </el-select>
        </el-form-item>
        <el-form-item label="类型">
          <el-select v-model="searchForm.type" placeholder="请选择类型" clearable style="width: 120px">
            <el-option label="借方" :value="1" />
            <el-option label="贷方" :value="2" />
            <el-option label="开票" :value="3" />
            <el-option label="收款" :value="4" />
          </el-select>
        </el-form-item>
        <el-form-item label="状态">
          <el-select v-model="searchForm.status" placeholder="请选择状态" clearable style="width: 120px">
            <el-option label="未入账" :value="0" />
            <el-option label="已入账" :value="1" />
          </el-select>
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="handleSearch">搜索</el-button>
          <el-button @click="handleReset">重置</el-button>
        </el-form-item>
      </el-form>
    </el-card>

    <!-- 操作按钮 -->
    <el-card class="table-card">
      <template #header>
        <div class="card-header">
          <span>账单明细列表</span>
          <el-button type="primary" @click="handleAdd">新增明细</el-button>
        </div>
      </template>

      <!-- 数据表格 -->
      <el-table :data="tableData" v-loading="loading" border stripe>
        <el-table-column prop="id" label="ID" width="80" />
        <el-table-column prop="voucher_sn" label="对账单编号" width="180" />
        <el-table-column prop="order_sn" label="订单编号" width="180" />
        <el-table-column prop="type" label="类型" width="100">
          <template #default="{ row }">
            <el-tag :type="getTypeTagType(row.type)">{{ getTypeText(row.type) }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="price" label="金额" width="120">
          <template #default="{ row }">
            <span :class="{ 'text-danger': row.type === 2, 'text-success': row.type === 1 }">
              {{ row.type === 2 ? '-' : '+' }}{{ row.price?.toFixed(2) }}
            </span>
          </template>
        </el-table-column>
        <el-table-column prop="status" label="状态" width="100">
          <template #default="{ row }">
            <el-tag :type="row.status === 1 ? 'success' : 'info'">
              {{ row.status === 1 ? '已入账' : '未入账' }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="remark" label="备注" min-width="150" />
        <el-table-column prop="created_at" label="创建时间" width="180" />
        <el-table-column label="操作" width="100" fixed="right">
          <template #default="{ row }">
            <el-button type="danger" link @click="handleDelete(row)" v-if="row.status === 0">删除</el-button>
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
        @current-change="handleCurrentChange"
      />
    </el-card>

    <!-- 新增明细弹窗 -->
    <el-dialog v-model="dialogVisible" title="新增账单明细" width="500px">
      <el-form :model="form" :rules="rules" ref="formRef" label-width="100px">
        <el-form-item label="对账单" prop="receipt_id">
          <el-select v-model="form.receipt_id" placeholder="请选择对账单" style="width: 100%">
            <el-option v-for="item in receiptOptions" :key="item.id" :label="item.voucher_sn" :value="item.id" />
          </el-select>
        </el-form-item>
        <el-form-item label="类型" prop="type">
          <el-select v-model="form.type" placeholder="请选择类型" style="width: 100%">
            <el-option label="借方" :value="1" />
            <el-option label="贷方" :value="2" />
          </el-select>
        </el-form-item>
        <el-form-item label="金额" prop="price">
          <el-input-number v-model="form.price" :min="0" :precision="2" style="width: 100%" />
        </el-form-item>
        <el-form-item label="备注" prop="remark">
          <el-input v-model="form.remark" type="textarea" :rows="3" placeholder="请输入备注" />
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="dialogVisible = false">取消</el-button>
        <el-button type="primary" @click="handleSubmit" :loading="submitLoading">确定</el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { getAccountList, createAccount, deleteAccount } from '@/api/modules/receivable'
import { getReceiptList } from '@/api/modules/receivable'

// 搜索表单
const searchForm = reactive({
  receipt_id: '',
  type: '',
  status: ''
})

// 表格数据
const tableData = ref([])
const loading = ref(false)

// 分页
const pagination = reactive({
  page: 1,
  pageSize: 20,
  total: 0
})

// 对账单选项
const receiptOptions = ref([])

// 弹窗
const dialogVisible = ref(false)
const formRef = ref(null)
const submitLoading = ref(false)
const form = reactive({
  receipt_id: '',
  type: 1,
  price: 0,
  remark: ''
})

const rules = {
  receipt_id: [{ required: true, message: '请选择对账单', trigger: 'change' }],
  type: [{ required: true, message: '请选择类型', trigger: 'change' }],
  price: [{ required: true, message: '请输入金额', trigger: 'blur' }]
}

// 获取列表数据
const fetchData = async () => {
  loading.value = true
  try {
    const params = {
      ...searchForm,
      page: pagination.page,
      page_size: pagination.pageSize
    }
    const res = await getAccountList(params)
    if (res.code === 200) {
      tableData.value = res.data.list || []
      pagination.total = res.data.total || 0
    }
  } catch (error) {
    console.error('获取账单明细列表失败:', error)
  } finally {
    loading.value = false
  }
}

// 获取对账单选项
const fetchReceiptOptions = async () => {
  try {
    const res = await getReceiptList({ page: 1, page_size: 100 })
    if (res.code === 200) {
      receiptOptions.value = res.data.list || []
    }
  } catch (error) {
    console.error('获取对账单列表失败:', error)
  }
}

// 搜索
const handleSearch = () => {
  pagination.page = 1
  fetchData()
}

// 重置
const handleReset = () => {
  searchForm.receipt_id = ''
  searchForm.type = ''
  searchForm.status = ''
  handleSearch()
}

// 新增
const handleAdd = () => {
  form.receipt_id = ''
  form.type = 1
  form.price = 0
  form.remark = ''
  dialogVisible.value = true
}

// 提交
const handleSubmit = async () => {
  if (!formRef.value) return
  await formRef.value.validate(async (valid) => {
    if (!valid) return
    submitLoading.value = true
    try {
      const res = await createAccount(form)
      if (res.code === 200) {
        ElMessage.success('新增成功')
        dialogVisible.value = false
        fetchData()
      } else {
        ElMessage.error(res.message || '新增失败')
      }
    } catch (error) {
      ElMessage.error('新增失败')
    } finally {
      submitLoading.value = false
    }
  })
}

// 删除
const handleDelete = (row) => {
  ElMessageBox.confirm('确定要删除该账单明细吗？', '提示', {
    type: 'warning'
  }).then(async () => {
    try {
      const res = await deleteAccount(row.id)
      if (res.code === 200) {
        ElMessage.success('删除成功')
        fetchData()
      } else {
        ElMessage.error(res.message || '删除失败')
      }
    } catch (error) {
      ElMessage.error('删除失败')
    }
  }).catch(() => {})
}

// 分页
const handleSizeChange = (size) => {
  pagination.pageSize = size
  fetchData()
}

const handleCurrentChange = (page) => {
  pagination.page = page
  fetchData()
}

// 类型文本
const getTypeText = (type) => {
  const typeMap = { 1: '借方', 2: '贷方', 3: '开票', 4: '收款' }
  return typeMap[type] || '未知'
}

// 类型标签样式
const getTypeTagType = (type) => {
  const typeMap = { 1: 'success', 2: 'danger', 3: 'warning', 4: 'primary' }
  return typeMap[type] || 'info'
}

onMounted(() => {
  fetchData()
  fetchReceiptOptions()
})
</script>

<style scoped>
.account-container {
  padding: 20px;
}

.search-card {
  margin-bottom: 20px;
}

.table-card {
  margin-bottom: 20px;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.el-pagination {
  margin-top: 20px;
  justify-content: flex-end;
}

.text-danger {
  color: #f56c6c;
}

.text-success {
  color: #67c23a;
}
</style>
