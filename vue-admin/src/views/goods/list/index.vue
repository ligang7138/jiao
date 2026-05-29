<template>
  <div class="goods-list">
    <!-- 搜索区域 - 与旧系统完全一致的搜索条件 -->
    <el-card class="search-card">
      <el-form :model="searchForm" inline label-width="80px">
        <el-form-item label="商品编号">
          <el-input
            v-model="searchForm.goods_sn"
            placeholder="商品编号"
            clearable
            style="width: 120px"
            @keyup.enter="handleSearch"
          />
        </el-form-item>

        <el-form-item label="商品名称">
          <el-input
            v-model="searchForm.goods_name"
            placeholder="商品名称"
            clearable
            style="width: 200px"
            @keyup.enter="handleSearch"
          />
        </el-form-item>

        <el-form-item label="一级分类">
          <el-select
            v-model="searchForm.cate_id"
            placeholder="请选择"
            clearable
            style="width: 150px"
            @change="handleCategoryChange"
          >
            <el-option v-for="item in categoryList" :key="item.id" :label="item.name" :value="item.id" />
          </el-select>
        </el-form-item>

        <el-form-item label="二级分类">
          <el-select v-model="searchForm.scate_id" placeholder="请选择" clearable style="width: 150px">
            <el-option v-for="item in subCategoryList" :key="item.id" :label="item.name" :value="item.id" />
          </el-select>
        </el-form-item>

        <el-form-item label="商品属性">
          <el-select v-model="searchForm.attr" placeholder="请选择" clearable style="width: 120px">
            <el-option label="非标品" :value="1" />
            <el-option label="标品" :value="2" />
            <el-option label="特种品" :value="3" />
          </el-select>
        </el-form-item>

        <el-form-item label="等级">
          <el-select v-model="searchForm.level" placeholder="请选择" clearable style="width: 120px">
            <el-option label="普通" :value="1" />
            <el-option label="精品" :value="2" />
          </el-select>
        </el-form-item>

        <el-form-item label="价格异常">
          <el-select v-model="searchForm.limit_price" placeholder="请选择" clearable style="width: 120px">
            <el-option label="为0" :value="1" />
          </el-select>
        </el-form-item>

        <el-form-item label="上架状态">
          <el-select v-model="searchForm.status" placeholder="请选择" clearable style="width: 120px">
            <el-option label="上架" :value="1" />
            <el-option label="下架" :value="0" />
          </el-select>
        </el-form-item>

        <el-form-item label="教师专用">
          <el-select v-model="searchForm.goods_type" placeholder="请选择" clearable style="width: 120px">
            <el-option label="是" :value="1" />
            <el-option label="否" :value="0" />
          </el-select>
        </el-form-item>

        <el-form-item label="议价商品">
          <el-select v-model="searchForm.goods_channel" placeholder="请选择" clearable style="width: 120px">
            <el-option label="是" :value="1" />
            <el-option label="否" :value="0" />
          </el-select>
        </el-form-item>

        <el-form-item label="商品来源">
          <el-select v-model="searchForm.source" placeholder="请选择" clearable style="width: 120px">
            <el-option label="自有" :value="1" />
            <el-option label="多区联采" :value="2" />
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

    <!-- 操作栏 - 与旧系统完全一致 -->
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
              导出
            </el-button>
            <el-button @click="handleImport">
              <el-icon><Upload /></el-icon>
              导入商品
            </el-button>
            <el-button type="primary" @click="handleAdd">
              <el-icon><Plus /></el-icon>
              新增商品
            </el-button>
          </div>
          <span class="total-count">共 <b>{{ pagination.total }}</b> 条数据</span>
        </div>
      </template>

      <!-- 表格 - 列与旧系统完全一致 -->
      <el-table
        v-loading="loading"
        :data="tableData"
        border
        stripe
      >
        <el-table-column prop="goods_sn" label="商品编号" width="80" align="center" />
        <el-table-column prop="goods_name" label="商品名称" min-width="120" show-overflow-tooltip />
        <el-table-column label="商品图片" width="80" align="center">
          <template #default="{ row }">
            <el-image
              :src="getImageUrl(row.slogo)"
              :preview-src-list="[getImageUrl(row.slogo)]"
              style="width: 50px; height: 50px"
              fit="cover"
            >
              <template #error>
                <div class="image-placeholder">
                  <el-icon><Picture /></el-icon>
                </div>
              </template>
            </el-image>
          </template>
        </el-table-column>
        <el-table-column prop="spec" label="规格" min-width="120" show-overflow-tooltip />
        <el-table-column prop="unit" label="单位" width="60" align="center" />
        <el-table-column label="议价价格" width="80" align="center">
          <template #default="{ row }">
            {{ row.goods_channel === 1 ? (row.white_price || '-') : '-' }}
          </template>
        </el-table-column>
        <el-table-column label="指导价" width="80" align="center">
          <template #default="{ row }">
            {{ row.price > 0 ? row.price : 0 }}
          </template>
        </el-table-column>
        <el-table-column label="建议限高价" width="90" align="center">
          <template #default="{ row }">
            {{ row.limit_price || 0 }}
          </template>
        </el-table-column>
        <el-table-column prop="cate_name" label="一级分类" width="100" align="center" />
        <el-table-column prop="scate_name" label="二级分类" width="100" align="center" />
        <el-table-column label="等级" width="80" align="center">
          <template #default="{ row }">
            {{ row.level === 1 ? '普通' : '精品' }}
          </template>
        </el-table-column>
        <el-table-column label="商品属性" width="80" align="center">
          <template #default="{ row }">
            {{ getAttrText(row.attr) }}
          </template>
        </el-table-column>
        <el-table-column label="上架状态" width="100" align="center">
          <template #default="{ row }">
            <template v-if="row.status === 1">
              <template v-if="row.schedule_down_time > 0">
                <el-tag type="warning">待下架</el-tag>
                <div class="schedule-time">{{ formatTime(row.schedule_down_time, 'yy-MM-dd HH:mm') }}</div>
              </template>
              <template v-else>
                <el-tag type="success">上架</el-tag>
              </template>
            </template>
            <template v-else>
              <el-tag type="info">下架</el-tag>
            </template>
          </template>
        </el-table-column>
        <el-table-column label="教师专用" width="80" align="center">
          <template #default="{ row }">
            {{ row.goods_type === 1 ? '是' : '否' }}
          </template>
        </el-table-column>
        <el-table-column label="议价商品" width="80" align="center">
          <template #default="{ row }">
            {{ row.goods_channel === 1 ? '是' : '否' }}
          </template>
        </el-table-column>
        <el-table-column label="商品来源" width="100" align="center">
          <template #default="{ row }">
            {{ row.source === 1 ? '自有' : '多区联采' }}
          </template>
        </el-table-column>
        <el-table-column label="更新时间" width="150" align="center">
          <template #default="{ row }">
            {{ formatTime(row.update_time, 'yy-MM-dd HH:mm') }}
          </template>
        </el-table-column>
        <el-table-column label="操作" width="200" align="center" fixed="right">
          <template #default="{ row }">
            <el-dropdown trigger="click">
              <el-button type="primary" link>
                操作 <el-icon class="el-icon--right"><ArrowDown /></el-icon>
              </el-button>
              <template #dropdown>
                <el-dropdown-menu>
                  <el-dropdown-item @click="handleEdit(row)">编辑</el-dropdown-item>
                  <el-dropdown-item @click="handleHistoryPrice(row)">历史价格</el-dropdown-item>
                  <el-dropdown-item v-if="row.status === 0" @click="handleGoodsUp(row)">上架</el-dropdown-item>
                  <el-dropdown-item v-if="row.status === 1 && row.schedule_down_time === 0" @click="handleGoodsDown(row)">下架</el-dropdown-item>
                  <el-dropdown-item @click="handleStatusLog(row)">上下架记录</el-dropdown-item>
                </el-dropdown-menu>
              </template>
            </el-dropdown>
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

    <!-- 下架弹窗 - 与旧系统下架逻辑完全一致 -->
    <el-dialog v-model="downDialogVisible" title="商品下架" width="500px">
      <el-form :model="downForm" label-width="100px">
        <el-form-item label="下架类型">
          <el-radio-group v-model="downForm.down_type">
            <el-radio :value="1">立即下架</el-radio>
            <el-radio :value="2">预下架（7天后自动下架）</el-radio>
          </el-radio-group>
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="downDialogVisible = false">取消</el-button>
        <el-button type="primary" :loading="downLoading" @click="confirmGoodsDown">确定</el-button>
      </template>
    </el-dialog>

    <!-- 上下架记录弹窗 -->
    <el-dialog v-model="statusLogDialogVisible" title="上下架记录" width="80%">
      <el-table v-loading="statusLogLoading" :data="statusLogData" border stripe>
        <el-table-column label="操作类型" width="100" align="center">
          <template #default="{ row }">
            {{ row.operate_type === 1 ? '上架' : '下架' }}
          </template>
        </el-table-column>
        <el-table-column label="原状态" width="100" align="center">
          <template #default="{ row }">
            {{ row.old_status === 1 ? '上架' : '下架' }}
          </template>
        </el-table-column>
        <el-table-column label="新状态" width="100" align="center">
          <template #default="{ row }">
            {{ row.new_status === 1 ? '上架' : '下架' }}
          </template>
        </el-table-column>
        <el-table-column prop="reason" label="原因" min-width="200" />
        <el-table-column prop="operate_user" label="操作人" width="120" align="center" />
        <el-table-column label="操作时间" width="180" align="center">
          <template #default="{ row }">
            {{ formatTime(row.operate_time) }}
          </template>
        </el-table-column>
      </el-table>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { ElMessage, ElMessageBox } from 'element-plus'
import {
  Search,
  RefreshRight,
  Download,
  Upload,
  Plus,
  ArrowDown,
  Picture
} from '@element-plus/icons-vue'
import {
  getGoodsList,
  goodsUp,
  goodsDown,
  getGoodsStatusLog
} from '@/api/modules/goods'
import { getCategoryList, getSubCategories } from '@/api/modules/category'

const router = useRouter()

// 上传文件URL
const UPLOAD_URL = import.meta.env.VITE_UPLOAD_URL || '/upload/'

// 搜索表单 - 与旧系统完全一致
const searchForm = reactive({
  goods_sn: '',
  goods_name: '',
  cate_id: null,
  scate_id: null,
  attr: null,
  level: null,
  limit_price: null,
  status: null,
  goods_type: null,
  goods_channel: null,
  source: null
})

// 分类数据
const categoryList = ref([])
const subCategoryList = ref([])

// 表格数据
const loading = ref(false)
const tableData = ref([])

// 分页
const pagination = reactive({
  page: 1,
  pageSize: 20,
  total: 0
})

// 下架弹窗
const downDialogVisible = ref(false)
const downLoading = ref(false)
const currentDownGoods = ref(null)
const downForm = reactive({
  down_type: 1
})

// 上下架记录弹窗
const statusLogDialogVisible = ref(false)
const statusLogLoading = ref(false)
const statusLogData = ref([])

// 图片URL处理
function getImageUrl(path) {
  if (!path) return ''
  if (path.startsWith('http')) return path
  return UPLOAD_URL + path
}

// 商品属性文本
function getAttrText(attr) {
  const map = { 1: '非标品', 2: '标品', 3: '特种品' }
  return map[attr] || '-'
}

// 时间格式化
function formatTime(timestamp, format = 'yy-MM-dd HH:mm') {
  if (!timestamp) return ''
  const date = new Date(timestamp * 1000)
  const year = String(date.getFullYear()).slice(2)
  const month = String(date.getMonth() + 1).padStart(2, '0')
  const day = String(date.getDate()).padStart(2, '0')
  const hours = String(date.getHours()).padStart(2, '0')
  const minutes = String(date.getMinutes()).padStart(2, '0')

  return format
    .replace('yy', year)
    .replace('MM', month)
    .replace('dd', day)
    .replace('HH', hours)
    .replace('mm', minutes)
}

// 获取一级分类列表
async function fetchCategoryList() {
  try {
    const { data } = await getCategoryList({ pid: 0 })
    categoryList.value = data || []
  } catch (error) {
    console.error('获取分类失败:', error)
  }
}

// 一级分类变化 - 与旧系统联动逻辑一致
async function handleCategoryChange(cateId) {
  searchForm.scate_id = null
  if (cateId) {
    try {
      const { data } = await getSubCategories(cateId)
      subCategoryList.value = data || []
    } catch (error) {
      subCategoryList.value = []
    }
  } else {
    subCategoryList.value = []
  }
}

// 获取商品列表 - 与旧系统查询逻辑一致
async function fetchData() {
  loading.value = true
  try {
    const params = {}

    // 商品编号优先
    if (searchForm.goods_sn) {
      params.goods_sn = searchForm.goods_sn
    } else {
      // 其他搜索条件仅在无商品编号时生效
      if (searchForm.goods_name) params.goods_name = searchForm.goods_name
      if (searchForm.cate_id) params.cate_id = searchForm.cate_id
      if (searchForm.scate_id) params.scate_id = searchForm.scate_id
      if (searchForm.attr) params.attr = searchForm.attr
      if (searchForm.level) params.level = searchForm.level
      if (searchForm.limit_price) params.limit_price = searchForm.limit_price
      if (searchForm.status !== null) params.status = searchForm.status
      if (searchForm.goods_type !== null) params.goods_type = searchForm.goods_type
      if (searchForm.goods_channel !== null) params.goods_channel = searchForm.goods_channel
      if (searchForm.source) params.source = searchForm.source
    }

    params.page = pagination.page
    params.page_size = pagination.pageSize

    const { data } = await getGoodsList(params)
    tableData.value = data.list || []
    pagination.total = data.total || 0

    // 计算限高价 - 与旧系统逻辑一致
    tableData.value.forEach(item => {
      const floatRateCap = categoryList.value.find(c => c.id === item.cate_id)?.float_rate_cap || 0.13
      if (floatRateCap) {
        const basePrice = item.goods_channel === 1 ? item.white_price : item.price
        item.limit_price = Math.round(basePrice * floatRateCap + basePrice, 2)
      } else {
        item.limit_price = 0
      }
    })
  } catch (error) {
    console.error('获取商品列表失败:', error)
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

// 导出 - 与旧系统导出逻辑一致
function handleExport() {
  ElMessage.info('导出功能开发中')
}

// 导入
function handleImport() {
  router.push('/goods/import')
}

// 新增商品
function handleAdd() {
  router.push('/goods/add')
}

// 编辑商品
function handleEdit(row) {
  router.push(`/goods/edit/${row.id}`)
}

// 历史价格
function handleHistoryPrice(row) {
  router.push(`/goods/history-price/${row.id}`)
}

// 上架商品 - 与旧系统上架逻辑一致（需要满足上架条件）
async function handleGoodsUp(row) {
  try {
    // 上架条件检查：必须有指导价
    if (!row.price && !row.white_price) {
      ElMessage.warning('商品未匹配指导价，无法上架')
      return
    }

    await ElMessageBox.confirm('确认上架该商品？', '提示', { type: 'warning' })

    await goodsUp(row.id)
    ElMessage.success('上架成功')
    fetchData()
  } catch (error) {
    if (error !== 'cancel') {
      ElMessage.error(error.message || '上架失败')
    }
  }
}

// 下架商品 - 显示下架弹窗
function handleGoodsDown(row) {
  currentDownGoods.value = row
  downForm.down_type = 1
  downDialogVisible.value = true
}

// 确认下架 - 与旧系统下架逻辑一致
async function confirmGoodsDown() {
  if (!currentDownGoods.value) return

  downLoading.value = true
  try {
    await goodsDown(currentDownGoods.value.id, {
      down_type: downForm.down_type
    })
    ElMessage.success('下架成功')
    downDialogVisible.value = false
    fetchData()
  } catch (error) {
    ElMessage.error(error.message || '下架失败')
  } finally {
    downLoading.value = false
  }
}

// 查看上下架记录
async function handleStatusLog(row) {
  statusLogDialogVisible.value = true
  statusLogLoading.value = true
  try {
    const { data } = await getGoodsStatusLog(row.id)
    statusLogData.value = data || []
  } catch (error) {
    ElMessage.error('获取记录失败')
    statusLogData.value = []
  } finally {
    statusLogLoading.value = false
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
  fetchCategoryList()
  fetchData()
})
</script>

<style lang="scss" scoped>
.goods-list {
  .search-card {
    margin-bottom: 20px;
  }

  .table-card {
    .card-header {
      display: flex;
      justify-content: space-between;
      align-items: center;

      .header-actions {
        display: flex;
        gap: 10px;
      }

      .total-count {
        font-size: 14px;
        color: #666;
      }
    }
  }

  .image-placeholder {
    width: 50px;
    height: 50px;
    display: flex;
    justify-content: center;
    align-items: center;
    background: #f5f5f5;
    color: #ccc;
  }

  .schedule-time {
    font-size: 12px;
    color: #ffb800;
    margin-top: 4px;
  }

  .el-pagination {
    margin-top: 20px;
    justify-content: flex-end;
  }
}
</style>