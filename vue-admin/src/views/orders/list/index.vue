<template>
  <div class="order-list">
    <!-- 搜索区域 - 与旧系统完全一致的搜索条件 -->
    <el-card class="search-card">
      <el-form :model="searchForm" inline label-width="80px">
        <el-form-item label="">
          <el-select v-model="searchForm.date_type" style="width: 130px">
            <el-option label="送货日期" :value="1" />
            <el-option label="下单时间" :value="2" />
          </el-select>
          <el-date-picker
            v-model="dateRange"
            type="daterange"
            range-separator="-"
            start-placeholder="开始日期"
            end-placeholder="结束日期"
            value-format="YYYY-MM-DD"
            style="width: 280px"
          />
        </el-form-item>

        <el-form-item label="订单编号">
          <el-input
            v-model="searchForm.order_sn"
            placeholder="订单编号"
            clearable
            style="width: 160px"
            @keyup.enter="handleSearch"
          />
        </el-form-item>

        <el-form-item label="食堂名称">
          <el-input
            v-model="searchForm.canteen_name"
            placeholder="食堂名称"
            clearable
            style="width: 200px"
            @keyup.enter="handleSearch"
          />
        </el-form-item>

        <el-form-item label="食堂类型">
          <el-select v-model="searchForm.canteen_type" placeholder="请选择" clearable style="width: 120px">
            <el-option label="教师食堂" :value="1" />
            <el-option label="学生食堂" :value="2" />
          </el-select>
        </el-form-item>

        <el-form-item label="订单类型">
          <el-select v-model="searchForm.order_type" placeholder="请选择" clearable style="width: 120px">
            <el-option label="正常" :value="1" />
            <el-option label="补单" :value="2" />
          </el-select>
        </el-form-item>

        <el-form-item label="订单状态">
          <el-select v-model="searchForm.status" placeholder="请选择" clearable style="width: 120px">
            <el-option label="已取消" :value="10" />
            <el-option label="已下单" :value="20" />
            <el-option label="已配货" :value="30" />
            <el-option label="已发货" :value="40" />
            <el-option label="已收货" :value="50" />
          </el-select>
        </el-form-item>

        <el-form-item label="当前供货商">
          <el-select v-model="searchForm.supp_id" placeholder="全部" clearable style="width: 180px">
            <el-option v-for="item in supplierList" :key="item.id" :label="item.name" :value="item.id" />
          </el-select>
        </el-form-item>

        <el-form-item label="主账号审核">
          <el-select v-model="searchForm.audit_status" style="width: 120px">
            <el-option label="全部" :value="-1" />
            <el-option label="待审核" :value="0" />
            <el-option label="通过" :value="1" />
            <el-option label="拒绝" :value="2" />
            <el-option label="无" :value="3" />
          </el-select>
        </el-form-item>

        <el-form-item label="是否迟到">
          <el-select v-model="searchForm.is_send_late" style="width: 120px">
            <el-option label="全部" :value="-1" />
            <el-option label="未迟到" :value="0" />
            <el-option label="迟到" :value="1" />
          </el-select>
        </el-form-item>

        <el-form-item label="索票索证">
          <el-select v-model="searchForm.inspection_report_status" style="width: 120px">
            <el-option label="全部" :value="-1" />
            <el-option label="缺失" :value="0" />
            <el-option label="完整" :value="1" />
          </el-select>
        </el-form-item>

        <el-form-item>
          <el-button type="primary" @click="handleSearch">
            <el-icon><Search /></el-icon>
            搜索
          </el-button>
        </el-form-item>
      </el-form>
    </el-card>

    <!-- 操作栏与数据统计 - 与旧系统完全一致 -->
    <el-card class="table-card">
      <template #header>
        <div class="card-header">
          <div class="header-actions">
            <el-button @click="handleRefresh">
              <el-icon><RefreshRight /></el-icon>
              刷新
            </el-button>
            <el-button @click="handleExport">
              <el-icon><Download /></el-icon>
              订单导出
            </el-button>
            <el-button @click="handleDetailExport">
              <el-icon><Document /></el-icon>
              订单明细导出
            </el-button>
          </div>
          <div class="statistics">
            数据统计：订单数<b>{{ totalData.order_count }}</b> &nbsp;&nbsp;
            学校数量<b>{{ totalData.school_amount }}</b> &nbsp;&nbsp;
            食堂数量<b>{{ totalData.canteen_amount }}</b> &nbsp;&nbsp;
            总金额<b>{{ totalData.total_amount }}元</b>
          </div>
          <span class="total-count">共 <b>{{ pagination.total }}</b> 条数据</span>
        </div>
      </template>

      <!-- 表格 - 列与旧系统完全一致 -->
      <el-table v-loading="loading" :data="tableData" border stripe>
        <el-table-column prop="order_sn" label="订单编号" width="180" />
        <el-table-column prop="canteen_name" label="食堂名称" min-width="150" show-overflow-tooltip />
        <el-table-column prop="school_linkman" label="联系人" width="100" />
        <el-table-column prop="school_mobile" label="联系电话" width="120" />
        <el-table-column label="订单类型" width="80" align="center">
          <template #default="{ row }">
            {{ row.order_type === 1 ? '正常' : '补单' }}
          </template>
        </el-table-column>
        <el-table-column label="食堂类型" width="100" align="center">
          <template #default="{ row }">
            {{ row.canteen_type === 1 ? '教师食堂' : '学生食堂' }}
          </template>
        </el-table-column>
        <el-table-column label="下单时间" width="180" align="center">
          <template #default="{ row }">
            {{ formatTime(row.add_time) }}
          </template>
        </el-table-column>
        <el-table-column prop="send_date" label="送货日期" width="120" align="center" />
        <el-table-column label="发货时间" width="180" align="center">
          <template #default="{ row }">
            {{ row.send_time ? formatTime(row.send_time) : '-' }}
          </template>
        </el-table-column>
        <el-table-column label="收货时间" width="180" align="center">
          <template #default="{ row }">
            {{ row.receive_time ? formatTime(row.receive_time) : '-' }}
          </template>
        </el-table-column>
        <el-table-column label="下单金额" width="100" align="right">
          <template #default="{ row }">
            <span class="amount">{{ row.total_price }}</span>
          </template>
        </el-table-column>
        <el-table-column label="发货金额" width="100" align="right">
          <template #default="{ row }">
            <span class="amount">{{ row.send_price }}</span>
          </template>
        </el-table-column>
        <el-table-column label="结算金额" width="100" align="right">
          <template #default="{ row }">
            <span class="amount">{{ row.settle_price }}</span>
          </template>
        </el-table-column>
        <el-table-column label="主账号审核" width="100" align="center">
          <template #default="{ row }">
            <template v-if="row.audit_status === 0">
              <el-tag type="warning">待审核</el-tag>
            </template>
            <template v-else-if="row.audit_status === 1">
              <el-tag type="success">通过</el-tag>
            </template>
            <template v-else-if="row.audit_status === 2">
              <el-tag type="danger">拒绝</el-tag>
            </template>
            <template v-else>
              <el-tag type="info">无</el-tag>
            </template>
          </template>
        </el-table-column>
        <el-table-column label="订单状态" width="100" align="center">
          <template #default="{ row }">
            <el-tag :type="getStatusType(row.status)">{{ getStatusText(row.status) }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="supp_name" label="所属供货商" min-width="150" show-overflow-tooltip />
        <el-table-column label="是否迟到" width="80" align="center">
          <template #default="{ row }">
            <el-tag :type="row.is_send_late === 1 ? 'danger' : 'success'">
              {{ row.is_send_late === 1 ? '迟到' : '未迟到' }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column label="索票索证" width="80" align="center">
          <template #default="{ row }">
            <el-tag :type="row.inspection_report_status === 1 ? 'success' : 'warning'">
              {{ row.inspection_report_status === 1 ? '完整' : '缺失' }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column label="操作" width="150" align="center" fixed="right">
          <template #default="{ row }">
            <el-button type="primary" link @click="handleView(row)">详情</el-button>
            <el-button type="info" link @click="handleTraceSource(row)">溯源</el-button>
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

    <!-- 详情弹窗 -->
    <el-dialog v-model="detailDialogVisible" title="订单详情" width="90%">
      <el-descriptions :column="4" border>
        <el-descriptions-item label="订单编号">{{ orderDetail.order_sn }}</el-descriptions-item>
        <el-descriptions-item label="食堂名称">{{ orderDetail.canteen_name }}</el-descriptions-item>
        <el-descriptions-item label="联系人">{{ orderDetail.school_linkman }}</el-descriptions-item>
        <el-descriptions-item label="联系电话">{{ orderDetail.school_mobile }}</el-descriptions-item>
        <el-descriptions-item label="订单类型">{{ orderDetail.order_type === 1 ? '正常' : '补单' }}</el-descriptions-item>
        <el-descriptions-item label="食堂类型">{{ orderDetail.canteen_type === 1 ? '教师食堂' : '学生食堂' }}</el-descriptions-item>
        <el-descriptions-item label="下单时间">{{ formatTime(orderDetail.add_time) }}</el-descriptions-item>
        <el-descriptions-item label="送货日期">{{ orderDetail.send_date }}</el-descriptions-item>
        <el-descriptions-item label="订单状态">
          <el-tag :type="getStatusType(orderDetail.status)">{{ getStatusText(orderDetail.status) }}</el-tag>
        </el-descriptions-item>
        <el-descriptions-item label="所属供货商">{{ orderDetail.supp_name }}</el-descriptions-item>
        <el-descriptions-item label="下单金额">{{ orderDetail.total_price }}</el-descriptions-item>
        <el-descriptions-item label="发货金额">{{ orderDetail.send_price }}</el-descriptions-item>
      </el-descriptions>

      <el-divider content-position="left">商品明细</el-divider>
      <el-table :data="orderDetail.goods" border stripe>
        <el-table-column prop="goods_sn" label="商品编号" width="80" />
        <el-table-column prop="goods_name" label="商品名称" min-width="150" />
        <el-table-column prop="spec" label="规格" width="100" />
        <el-table-column prop="unit" label="单位" width="80" />
        <el-table-column prop="sale_price" label="售价" width="100" align="right" />
        <el-table-column prop="needqty" label="下单量" width="100" align="center" />
        <el-table-column prop="sendqty" label="发货量" width="100" align="center" />
        <el-table-column prop="receiveqty" label="收货量" width="100" align="center" />
        <el-table-column prop="backqty" label="退货量" width="100" align="center" />
        <el-table-column prop="settleqty" label="结算量" width="100" align="center" />
        <el-table-column label="下单金额" width="100" align="right">
          <template #default="{ row }">
            {{ (row.needqty * row.sale_price).toFixed(2) }}
          </template>
        </el-table-column>
        <el-table-column label="收货金额" width="100" align="right">
          <template #default="{ row }">
            {{ (row.receiveqty * row.sale_price).toFixed(2) }}
          </template>
        </el-table-column>
      </el-table>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { ElMessage } from 'element-plus'
import { Search, RefreshRight, Download, Document } from '@element-plus/icons-vue'
import { getOrderList, getOrderDetail } from '@/api/modules/order'
import { getSupplierList } from '@/api/modules/supplier'
import { formatTime } from '@/utils/format'

const router = useRouter()

// 搜索表单 - 与旧系统完全一致
const searchForm = reactive({
  date_type: 1,
  order_sn: '',
  canteen_name: '',
  canteen_type: null,
  order_type: null,
  status: null,
  supp_id: null,
  audit_status: -1,
  is_send_late: -1,
  inspection_report_status: -1
})

// 日期范围
const today = new Date()
const tomorrow = new Date(Date.now() + 86400000)
const formatDate = (d) => {
  const y = d.getFullYear()
  const m = String(d.getMonth() + 1).padStart(2, '0')
  const day = String(d.getDate()).padStart(2, '0')
  return `${y}-${m}-${day}`
}
const dateRange = ref([formatDate(today), formatDate(tomorrow)])

// 供应商列表
const supplierList = ref([])

// 表格数据
const loading = ref(false)
const tableData = ref([])

// 数据统计
const totalData = reactive({
  order_count: 0,
  school_amount: 0,
  canteen_amount: 0,
  total_amount: 0
})

// 分页
const pagination = reactive({
  page: 1,
  pageSize: 20,
  total: 0
})

// 详情弹窗
const detailDialogVisible = ref(false)
const orderDetail = ref({})

// 获取订单状态类型
function getStatusType(status) {
  const map = {
    10: 'info',    // 已取消
    20: 'warning', // 已下单
    30: 'primary', // 已配货
    40: 'success', // 已发货
    50: 'success'  // 已收货
  }
  return map[status] || 'info'
}

// 获取订单状态文本
function getStatusText(status) {
  const map = {
    10: '已取消',
    20: '已下单',
    30: '已配货',
    40: '已发货',
    50: '已收货'
  }
  return map[status] || '未知'
}

// 获取供应商列表
async function fetchSupplierList() {
  try {
    const { data } = await getSupplierList({ status: 1 })
    supplierList.value = data.list || []
  } catch (error) {
    console.error('获取供应商失败:', error)
  }
}

// 获取订单列表 - 与旧系统查询逻辑一致
async function fetchData() {
  loading.value = true
  try {
    const params = {}

    // 订单编号优先
    if (searchForm.order_sn) {
      params.order_sn = searchForm.order_sn
    } else {
      // 日期筛选
      if (dateRange.value && dateRange.value.length === 2) {
        params.date_type = searchForm.date_type
        params.start_date = dateRange.value[0]
        params.end_date = dateRange.value[1]
      }
      // 其他搜索条件
      if (searchForm.canteen_name) params.canteen_name = searchForm.canteen_name
      if (searchForm.canteen_type) params.canteen_type = searchForm.canteen_type
      if (searchForm.order_type) params.order_type = searchForm.order_type
      if (searchForm.status) params.status = searchForm.status
      if (searchForm.supp_id) params.supp_id = searchForm.supp_id
      if (searchForm.audit_status > -1) params.audit_status = searchForm.audit_status
      if (searchForm.is_send_late > -1) params.is_send_late = searchForm.is_send_late
      if (searchForm.inspection_report_status > -1) params.inspection_report_status = searchForm.inspection_report_status
    }

    params.page = pagination.page
    params.page_size = pagination.pageSize

    const { data } = await getOrderList(params)
    tableData.value = data.list || []
    pagination.total = data.total || 0

    // 统计数据
    if (data.statistics) {
      totalData.order_count = data.statistics.order_count
      totalData.school_amount = data.statistics.school_amount
      totalData.canteen_amount = data.statistics.canteen_amount
      totalData.total_amount = data.statistics.total_amount
    }
  } catch (error) {
    console.error('获取订单列表失败:', error)
  } finally {
    loading.value = false
  }
}

// 搜索
function handleSearch() {
  pagination.page = 1
  fetchData()
}

// 刷新
function handleRefresh() {
  fetchData()
}

// 导出订单
function handleExport() {
  ElMessage.info('订单导出功能开发中')
}

// 导出订单明细
function handleDetailExport() {
  ElMessage.info('订单明细导出功能开发中')
}

// 查看详情
async function handleView(row) {
  try {
    const { data } = await getOrderDetail(row.id)
    orderDetail.value = data
    detailDialogVisible.value = true
  } catch (error) {
    ElMessage.error('获取订单详情失败')
  }
}

// 查看溯源
function handleTraceSource(row) {
  router.push(`/orders/trace-source/${row.id}`)
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
  fetchSupplierList()
  fetchData()
})
</script>

<style lang="scss" scoped>
.order-list {
  .search-card {
    margin-bottom: 20px;
  }

  .table-card {
    .card-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      gap: 10px;

      .header-actions {
        display: flex;
        gap: 10px;
      }

      .statistics {
        font-size: 14px;
        color: #666;
        b {
          color: #f56c6c;
          font-weight: 600;
        }
      }

      .total-count {
        font-size: 14px;
        color: #666;
      }
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
}
</style>