<template>
  <div class="backorder-list">
    <!-- 搜索区域 -->
    <el-card class="search-card">
      <el-form :model="searchForm" inline>
        <el-form-item label="日期范围">
          <el-date-picker
            v-model="dateRange"
            type="daterange"
            range-separator="-"
            start-placeholder="开始日期"
            end-placeholder="结束日期"
            value-format="YYYY-MM-DD"
            style="width: 240px"
          />
        </el-form-item>
        <el-form-item label="订单编号">
          <el-input
            v-model="searchForm.order_sn"
            placeholder="请输入订单编号"
            clearable
            @keyup.enter="handleSearch"
          />
        </el-form-item>
        <el-form-item label="食堂名称">
          <el-input
            v-model="searchForm.canteen_name"
            placeholder="请输入食堂名称"
            clearable
            @keyup.enter="handleSearch"
          />
        </el-form-item>
        <el-form-item label="退货状态">
          <el-select
            v-model="searchForm.status"
            placeholder="请选择状态"
            clearable
            style="width: 120px"
          >
            <el-option label="取消" :value="1" />
            <el-option label="审核拒绝" :value="2" />
            <el-option label="待审核" :value="3" />
            <el-option label="通过" :value="4" />
          </el-select>
        </el-form-item>
        <el-form-item label="退货类型">
          <el-select
            v-model="searchForm.type"
            placeholder="请选择类型"
            clearable
            style="width: 120px"
          >
            <el-option label="质量问题" :value="1" />
            <el-option label="数量问题" :value="2" />
            <el-option label="配送问题" :value="3" />
            <el-option label="其他" :value="4" />
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
          <span>退货单列表</span>
        </div>
      </template>

      <!-- 表格 -->
      <el-table v-loading="loading" :data="tableData" border stripe>
        <el-table-column prop="id" label="ID" width="80" align="center" />
        <el-table-column prop="backorder_sn" label="退货单号" width="180" />
        <el-table-column prop="order_sn" label="订单编号" width="180" />
        <el-table-column prop="canteen_name" label="食堂名称" min-width="150" show-overflow-tooltip />
        <el-table-column prop="supplier_name" label="供应商" min-width="150" show-overflow-tooltip />
        <el-table-column prop="type_text" label="退货类型" width="100" align="center" />
        <el-table-column prop="total_amount" label="退货金额" width="120" align="right">
          <template #default="{ row }">
            <span class="amount">¥{{ row.total_amount }}</span>
          </template>
        </el-table-column>
        <el-table-column prop="status" label="状态" width="100" align="center">
          <template #default="{ row }">
            <el-tag :type="getStatusType(row.status)">{{ row.status_text }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="created_at" label="创建时间" width="180" align="center" />
        <el-table-column label="操作" width="200" align="center" fixed="right">
          <template #default="{ row }">
            <el-button type="primary" link @click="handleView(row)">详情</el-button>
            <el-button
              v-if="row.status === 3"
              type="success"
              link
              @click="handleAudit(row)"
            >
              审核
            </el-button>
            <el-button
              v-if="row.status === 3"
              type="warning"
              link
              @click="handleCancel(row)"
            >
              取消
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

    <!-- 详情对话框 -->
    <el-dialog
      v-model="detailDialogVisible"
      title="退货单详情"
      width="800px"
      :close-on-click-modal="false"
    >
      <el-descriptions :column="3" border>
        <el-descriptions-item label="退货单号">{{ detail.backorder_sn }}</el-descriptions-item>
        <el-descriptions-item label="订单编号">{{ detail.order_sn }}</el-descriptions-item>
        <el-descriptions-item label="状态">
          <el-tag :type="getStatusType(detail.status)">{{ detail.status_text }}</el-tag>
        </el-descriptions-item>
        <el-descriptions-item label="食堂名称">{{ detail.canteen_name }}</el-descriptions-item>
        <el-descriptions-item label="供应商">{{ detail.supplier_name }}</el-descriptions-item>
        <el-descriptions-item label="退货类型">{{ detail.type_text }}</el-descriptions-item>
        <el-descriptions-item label="退货金额">
          <span class="amount">¥{{ detail.total_amount }}</span>
        </el-descriptions-item>
        <el-descriptions-item label="创建时间">{{ detail.created_at }}</el-descriptions-item>
        <el-descriptions-item label="处理时间">{{ detail.processed_at || '-' }}</el-descriptions-item>
      </el-descriptions>

      <el-divider content-position="left">退货商品明细</el-divider>
      <el-table :data="detail.goods" border>
        <el-table-column prop="goods_name" label="商品名称" min-width="150" />
        <el-table-column prop="unit" label="单位" width="80" align="center" />
        <el-table-column prop="spec" label="规格" width="100" />
        <el-table-column prop="price" label="单价" width="100" align="right">
          <template #default="{ row }">¥{{ row.price }}</template>
        </el-table-column>
        <el-table-column prop="quantity" label="退货数量" width="100" align="center" />
        <el-table-column prop="amount" label="退货金额" width="100" align="right">
          <template #default="{ row }">¥{{ row.amount }}</template>
        </el-table-column>
      </el-table>

      <el-descriptions :column="1" border style="margin-top: 20px">
        <el-descriptions-item label="退货原因">{{ detail.reason }}</el-descriptions-item>
        <el-descriptions-item v-if="detail.solution" label="解决方案">{{ detail.solution }}</el-descriptions-item>
        <el-descriptions-item v-if="detail.remark" label="备注">{{ detail.remark }}</el-descriptions-item>
      </el-descriptions>

      <!-- 审核操作 -->
      <div v-if="detail.status === 3" class="audit-actions">
        <el-divider content-position="left">审核操作</el-divider>
        <el-form :model="auditForm" label-width="100px">
          <el-form-item label="解决方案">
            <el-radio-group v-model="auditForm.solution_type">
              <el-radio :value="1">退款</el-radio>
              <el-radio :value="2">换货</el-radio>
              <el-radio :value="3">补发</el-radio>
            </el-radio-group>
          </el-form-item>
          <el-form-item label="处理说明">
            <el-input
              v-model="auditForm.remark"
              type="textarea"
              :rows="3"
              placeholder="请输入处理说明"
              maxlength="500"
            />
          </el-form-item>
        </el-form>
      </div>

      <template #footer>
        <el-button @click="detailDialogVisible = false">关闭</el-button>
        <el-button
          v-if="detail.status === 3"
          type="danger"
          :loading="rejectLoading"
          @click="handleReject"
        >
          拒绝
        </el-button>
        <el-button
          v-if="detail.status === 3"
          type="primary"
          :loading="auditLoading"
          @click="handleAuditSubmit"
        >
          审核通过
        </el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { Search, Refresh } from '@element-plus/icons-vue'
import {
  getBackorderList,
  getBackorderDetail,
  auditBackorder,
  rejectBackorder,
  cancelBackorder,
} from '@/api/modules/backorder'

// 搜索表单
const searchForm = reactive({
  order_sn: '',
  canteen_name: '',
  status: null,
  type: null,
})
const dateRange = ref([])

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
const detail = ref({})
const auditLoading = ref(false)
const rejectLoading = ref(false)
const auditForm = reactive({
  solution_type: 1,
  remark: '',
})

// 获取状态类型
function getStatusType(status) {
  const map = {
    1: 'info',      // 取消
    2: 'danger',    // 审核拒绝
    3: 'warning',   // 待审核
    4: 'success',   // 通过
  }
  return map[status] || 'info'
}

// 获取退货单列表
async function fetchData() {
  loading.value = true
  try {
    const params = {
      ...searchForm,
      page: pagination.page,
      page_size: pagination.pageSize,
    }
    if (dateRange.value && dateRange.value.length === 2) {
      params.start_date = dateRange.value[0]
      params.end_date = dateRange.value[1]
    }
    const { data } = await getBackorderList(params)
    tableData.value = data.list || []
    pagination.total = data.total || 0
  } catch (error) {
    console.error('获取退货单列表失败:', error)
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
    order_sn: '',
    canteen_name: '',
    status: null,
    type: null,
  })
  dateRange.value = []
  handleSearch()
}

// 查看详情
async function handleView(row) {
  try {
    const { data } = await getBackorderDetail(row.id)
    detail.value = data
    auditForm.solution_type = 1
    auditForm.remark = ''
    detailDialogVisible.value = true
  } catch (error) {
    ElMessage.error('获取退货单详情失败')
  }
}

// 审核按钮（打开详情对话框）
function handleAudit(row) {
  handleView(row)
}

// 取消退货单
async function handleCancel(row) {
  try {
    await ElMessageBox.confirm('确定要取消该退货单吗？', '提示', {
      type: 'warning',
    })
    await cancelBackorder(row.id)
    ElMessage.success('退货单已取消')
    fetchData()
  } catch (error) {
    if (error !== 'cancel') {
      ElMessage.error(error.message || '取消失败')
    }
  }
}

// 审核通过
async function handleAuditSubmit() {
  try {
    await ElMessageBox.confirm('确定审核通过该退货单吗？', '提示', {
      type: 'warning',
    })
    auditLoading.value = true
    await auditBackorder(detail.value.id, {
      solution_type: auditForm.solution_type,
      remark: auditForm.remark,
    })
    ElMessage.success('审核通过')
    detailDialogVisible.value = false
    fetchData()
  } catch (error) {
    if (error !== 'cancel') {
      ElMessage.error(error.message || '审核失败')
    }
  } finally {
    auditLoading.value = false
  }
}

// 审核拒绝
async function handleReject() {
  if (!auditForm.remark) {
    ElMessage.warning('请输入拒绝原因')
    return
  }
  try {
    await ElMessageBox.confirm('确定拒绝该退货单吗？', '提示', {
      type: 'warning',
    })
    rejectLoading.value = true
    await rejectBackorder(detail.value.id, {
      remark: auditForm.remark,
    })
    ElMessage.success('已拒绝')
    detailDialogVisible.value = false
    fetchData()
  } catch (error) {
    if (error !== 'cancel') {
      ElMessage.error(error.message || '操作失败')
    }
  } finally {
    rejectLoading.value = false
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
.backorder-list {
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

  .amount {
    color: #f56c6c;
    font-weight: 600;
  }

  .el-pagination {
    margin-top: 20px;
    justify-content: flex-end;
  }

  .audit-actions {
    margin-top: 20px;
  }
}
</style>
