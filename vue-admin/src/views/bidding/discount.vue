<template>
  <div class="bidding-discount">
    <!-- 搜索区域 -->
    <el-card class="search-card">
      <el-form :model="searchForm" inline>
        <el-form-item label="供应商">
          <el-input v-model="searchForm.supplier_name" placeholder="请输入供应商名称" clearable style="width: 200px" @keyup.enter="handleSearch" />
        </el-form-item>
        <el-form-item label="学校">
          <el-input v-model="searchForm.school_name" placeholder="请输入学校名称" clearable style="width: 200px" @keyup.enter="handleSearch" />
        </el-form-item>
        <el-form-item label="商品分类">
          <el-cascader
            v-model="searchForm.category_id"
            :options="categoryOptions"
            :props="{ value: 'id', label: 'name', checkStrictly: true, emitPath: false }"
            placeholder="请选择分类"
            clearable
            style="width: 200px"
          />
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
          <span>供应商报价列表</span>
          <div class="header-actions">
            <el-button type="primary" @click="showHistoryDialog = true">
              <el-icon><Clock /></el-icon>
              报价历史
            </el-button>
          </div>
        </div>
      </template>

      <!-- 表格 -->
      <el-table v-loading="loading" :data="tableData" border stripe>
        <el-table-column prop="id" label="ID" width="80" align="center" />
        <el-table-column prop="supplier_name" label="供应商名称" min-width="180" show-overflow-tooltip />
        <el-table-column prop="school_name" label="学校名称" min-width="180" show-overflow-tooltip />
        <el-table-column prop="category_name" label="商品分类" width="150" align="center" />
        <el-table-column prop="goods_name" label="商品名称" min-width="200" show-overflow-tooltip />
        <el-table-column prop="unit" label="单位" width="80" align="center" />
        <el-table-column prop="price" label="报价(元)" width="120" align="right">
          <template #default="{ row }">
            <span class="price">{{ Number(row.price).toFixed(2) }}</span>
          </template>
        </el-table-column>
        <el-table-column prop="discount" label="折扣(%)" width="100" align="center">
          <template #default="{ row }">
            <span class="discount">{{ row.discount }}%</span>
          </template>
        </el-table-column>
        <el-table-column prop="final_price" label="折后价(元)" width="120" align="right">
          <template #default="{ row }">
            <span class="final-price">{{ Number(row.final_price).toFixed(2) }}</span>
          </template>
        </el-table-column>
        <el-table-column prop="effective_date" label="生效日期" width="120" align="center" />
        <el-table-column prop="expire_date" label="失效日期" width="120" align="center" />
        <el-table-column prop="status" label="状态" width="100" align="center">
          <template #default="{ row }">
            <el-tag :type="getStatusType(row.status)">
              {{ getStatusText(row.status) }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="updated_at" label="更新时间" width="180" align="center" />
        <el-table-column label="操作" width="120" align="center" fixed="right">
          <template #default="{ row }">
            <el-button type="primary" link @click="handleViewHistory(row)">历史</el-button>
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

    <!-- 报价历史对话框 -->
    <el-dialog v-model="showHistoryDialog" title="报价历史记录" width="900px" @open="loadHistoryData">
      <el-form :model="historySearchForm" inline style="margin-bottom: 20px">
        <el-form-item label="供应商">
          <el-input v-model="historySearchForm.supplier_name" placeholder="请输入供应商名称" clearable style="width: 180px" />
        </el-form-item>
        <el-form-item label="商品">
          <el-input v-model="historySearchForm.goods_name" placeholder="请输入商品名称" clearable style="width: 180px" />
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="loadHistoryData">搜索</el-button>
        </el-form-item>
      </el-form>

      <el-table v-loading="historyLoading" :data="historyData" border stripe max-height="400">
        <el-table-column prop="supplier_name" label="供应商" min-width="150" show-overflow-tooltip />
        <el-table-column prop="school_name" label="学校" min-width="150" show-overflow-tooltip />
        <el-table-column prop="goods_name" label="商品" min-width="150" show-overflow-tooltip />
        <el-table-column prop="old_price" label="原报价(元)" width="110" align="right">
          <template #default="{ row }">
            {{ Number(row.old_price).toFixed(2) }}
          </template>
        </el-table-column>
        <el-table-column prop="new_price" label="新报价(元)" width="110" align="right">
          <template #default="{ row }">
            {{ Number(row.new_price).toFixed(2) }}
          </template>
        </el-table-column>
        <el-table-column prop="old_discount" label="原折扣(%)" width="100" align="center" />
        <el-table-column prop="new_discount" label="新折扣(%)" width="100" align="center" />
        <el-table-column prop="operator_name" label="操作人" width="100" align="center" />
        <el-table-column prop="created_at" label="变更时间" width="180" align="center" />
        <el-table-column prop="remark" label="备注" min-width="150" show-overflow-tooltip />
      </el-table>

      <el-pagination
        v-model:current-page="historyPagination.page"
        v-model:page-size="historyPagination.pageSize"
        :total="historyPagination.total"
        :page-sizes="[10, 20, 50]"
        layout="total, sizes, prev, pager, next"
        style="margin-top: 20px; justify-content: flex-end"
        @size-change="handleHistorySizeChange"
        @current-change="handleHistoryPageChange"
      />
    </el-dialog>

    <!-- 单个商品报价历史对话框 -->
    <el-dialog v-model="itemHistoryDialogVisible" title="商品报价历史" width="700px">
      <el-table v-loading="itemHistoryLoading" :data="itemHistoryData" border stripe>
        <el-table-column prop="price" label="报价(元)" width="120" align="right">
          <template #default="{ row }">
            {{ Number(row.price).toFixed(2) }}
          </template>
        </el-table-column>
        <el-table-column prop="discount" label="折扣(%)" width="100" align="center" />
        <el-table-column prop="final_price" label="折后价(元)" width="120" align="right">
          <template #default="{ row }">
            {{ Number(row.final_price).toFixed(2) }}
          </template>
        </el-table-column>
        <el-table-column prop="effective_date" label="生效日期" width="120" align="center" />
        <el-table-column prop="expire_date" label="失效日期" width="120" align="center" />
        <el-table-column prop="operator_name" label="操作人" width="100" align="center" />
        <el-table-column prop="created_at" label="变更时间" width="180" align="center" />
      </el-table>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { ElMessage } from 'element-plus'
import { Search, Refresh, Clock } from '@element-plus/icons-vue'
import { getBiddingDiscounts, getBiddingDiscountHistory } from '@/api/modules/bidding'

// 搜索表单
const searchForm = reactive({
  supplier_name: '',
  school_name: '',
  category_id: null,
})

// 分类选项（实际项目中应从接口获取）
const categoryOptions = ref([])

// 表格数据
const loading = ref(false)
const tableData = ref([])

// 分页
const pagination = reactive({
  page: 1,
  pageSize: 20,
  total: 0,
})

// 报价历史对话框
const showHistoryDialog = ref(false)
const historyLoading = ref(false)
const historyData = ref([])
const historySearchForm = reactive({
  supplier_name: '',
  goods_name: '',
})
const historyPagination = reactive({
  page: 1,
  pageSize: 20,
  total: 0,
})

// 单个商品报价历史
const itemHistoryDialogVisible = ref(false)
const itemHistoryLoading = ref(false)
const itemHistoryData = ref([])
const currentItemId = ref(null)

// 获取状态类型
function getStatusType(status) {
  const map = {
    1: 'success', // 生效中
    0: 'info',    // 未生效
    2: 'danger',  // 已失效
  }
  return map[status] || 'info'
}

// 获取状态文本
function getStatusText(status) {
  const map = {
    1: '生效中',
    0: '未生效',
    2: '已失效',
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

    if (searchForm.supplier_name) {
      params.supplier_name = searchForm.supplier_name
    }
    if (searchForm.school_name) {
      params.school_name = searchForm.school_name
    }
    if (searchForm.category_id) {
      params.category_id = searchForm.category_id
    }

    const { data } = await getBiddingDiscounts(params)
    tableData.value = data.list || []
    pagination.total = data.total || 0
  } catch (error) {
    console.error('获取供应商报价列表失败:', error)
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
    supplier_name: '',
    school_name: '',
    category_id: null,
  })
  handleSearch()
}

// 加载报价历史数据
async function loadHistoryData() {
  historyLoading.value = true
  try {
    const params = {
      page: historyPagination.page,
      page_size: historyPagination.pageSize,
    }

    if (historySearchForm.supplier_name) {
      params.supplier_name = historySearchForm.supplier_name
    }
    if (historySearchForm.goods_name) {
      params.goods_name = historySearchForm.goods_name
    }

    const { data } = await getBiddingDiscountHistory(params)
    historyData.value = data.list || []
    historyPagination.total = data.total || 0
  } catch (error) {
    console.error('获取报价历史失败:', error)
  } finally {
    historyLoading.value = false
  }
}

// 查看单个商品报价历史
async function handleViewHistory(row) {
  currentItemId.value = row.id
  itemHistoryDialogVisible.value = true
  itemHistoryLoading.value = true

  try {
    const { data } = await getBiddingDiscountHistory({
      discount_id: row.id,
      page: 1,
      page_size: 50,
    })
    itemHistoryData.value = data.list || []
  } catch (error) {
    ElMessage.error('获取报价历史失败')
  } finally {
    itemHistoryLoading.value = false
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

// 历史记录分页大小变化
function handleHistorySizeChange(size) {
  historyPagination.pageSize = size
  loadHistoryData()
}

// 历史记录页码变化
function handleHistoryPageChange(page) {
  historyPagination.page = page
  loadHistoryData()
}

// 初始化
onMounted(() => {
  fetchData()
})
</script>

<style lang="scss" scoped>
.bidding-discount {
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

  .price {
    color: #606266;
  }

  .discount {
    color: #e6a23c;
    font-weight: 600;
  }

  .final-price {
    color: #f56c6c;
    font-weight: 600;
  }

  .el-pagination {
    margin-top: 20px;
    justify-content: flex-end;
  }
}
</style>
