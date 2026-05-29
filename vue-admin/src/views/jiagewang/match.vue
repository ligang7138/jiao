<template>
  <div class="jiagewang-match-container">
    <el-card shadow="never">
      <template #header>
        <div class="card-header">
          <span>商品匹配</span>
          <el-button @click="handleBack">
            <el-icon><Back /></el-icon>
            返回列表
          </el-button>
        </div>
      </template>

      <!-- Tab 切换 -->
      <el-tabs v-model="activeTab" @tab-change="handleTabChange">
        <el-tab-pane label="已匹配商品" name="matched">
          <template #label>
            <span>已匹配商品 ({{ matchedCount }})</span>
          </template>
        </el-tab-pane>
        <el-tab-pane label="未匹配商品" name="unmatched">
          <template #label>
            <span>未匹配商品 ({{ unmatchedCount }})</span>
          </template>
        </el-tab-pane>
      </el-tabs>

      <!-- 搜索区域 -->
      <el-form :model="queryParams" inline class="search-form">
        <el-form-item label="商品编码">
          <el-input
            v-model="queryParams.goods_sn"
            placeholder="请输入商品编码"
            clearable
            style="width: 200px"
            @keyup.enter="handleSearch"
          />
        </el-form-item>
        <el-form-item label="商品名称">
          <el-input
            v-model="queryParams.goods_name"
            placeholder="请输入商品名称"
            clearable
            style="width: 200px"
            @keyup.enter="handleSearch"
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

      <!-- 已匹配商品表格 -->
      <el-table
        v-if="activeTab === 'matched'"
        v-loading="loading"
        :data="tableData"
        border
        stripe
      >
        <el-table-column prop="id" label="ID" width="80" align="center" />
        <el-table-column prop="goods_sn" label="指导价商品编码" width="150" />
        <el-table-column prop="goods_name" label="指导价商品名称" min-width="180" show-overflow-tooltip />
        <el-table-column prop="matched_goods_sn" label="系统商品编码" width="150" />
        <el-table-column prop="matched_goods_name" label="系统商品名称" min-width="180" show-overflow-tooltip />
        <el-table-column prop="price" label="指导价" width="100" align="right">
          <template #default="{ row }">
            <span class="price">{{ Number(row.price).toFixed(2) }}</span>
          </template>
        </el-table-column>
        <el-table-column prop="matched_at" label="匹配时间" width="160" align="center" />
        <el-table-column label="操作" width="120" align="center" fixed="right">
          <template #default="{ row }">
            <el-button type="danger" link @click="handleUnmatch(row)">
              取消匹配
            </el-button>
          </template>
        </el-table-column>
      </el-table>

      <!-- 未匹配商品表格 -->
      <el-table
        v-if="activeTab === 'unmatched'"
        v-loading="loading"
        :data="tableData"
        border
        stripe
        @selection-change="handleSelectionChange"
      >
        <el-table-column type="selection" width="55" align="center" />
        <el-table-column prop="id" label="ID" width="80" align="center" />
        <el-table-column prop="goods_sn" label="商品编码" width="150" />
        <el-table-column prop="goods_name" label="商品名称" min-width="200" show-overflow-tooltip />
        <el-table-column prop="cate_name" label="一级分类" width="120" />
        <el-table-column prop="scate_name" label="二级分类" width="120" />
        <el-table-column prop="unit" label="单位" width="80" align="center" />
        <el-table-column prop="price" label="指导价" width="100" align="right">
          <template #default="{ row }">
            <span class="price">{{ Number(row.price).toFixed(2) }}</span>
          </template>
        </el-table-column>
        <el-table-column label="操作" width="150" align="center" fixed="right">
          <template #default="{ row }">
            <el-button type="primary" link @click="handleMatchSingle(row)">
              手动匹配
            </el-button>
          </template>
        </el-table-column>
      </el-table>

      <!-- 批量操作 -->
      <div v-if="activeTab === 'unmatched' && selectedIds.length > 0" class="batch-actions">
        <el-button type="primary" @click="handleBatchMatch">
          批量匹配 ({{ selectedIds.length }})
        </el-button>
      </div>

      <!-- 分页 -->
      <el-pagination
        v-model:current-page="queryParams.page"
        v-model:page-size="queryParams.page_size"
        :total="activeTab === 'matched' ? matchedCount : unmatchedCount"
        :page-sizes="[10, 20, 50, 100]"
        layout="total, sizes, prev, pager, next, jumper"
        class="pagination"
        @size-change="handleSizeChange"
        @current-change="handleCurrentChange"
      />
    </el-card>

    <!-- 手动匹配弹窗 -->
    <el-dialog
      v-model="matchDialogVisible"
      title="手动匹配商品"
      width="800px"
      :close-on-click-modal="false"
    >
      <div class="match-dialog-content">
        <el-form :model="matchForm" inline class="match-search-form">
          <el-form-item label="商品编码">
            <el-input
              v-model="matchForm.goods_sn"
              placeholder="请输入商品编码"
              clearable
              style="width: 180px"
            />
          </el-form-item>
          <el-form-item label="商品名称">
            <el-input
              v-model="matchForm.goods_name"
              placeholder="请输入商品名称"
              clearable
              style="width: 180px"
            />
          </el-form-item>
          <el-form-item>
            <el-button type="primary" @click="handleSearchGoods">搜索</el-button>
          </el-form-item>
        </el-form>

        <el-table
          v-loading="goodsLoading"
          :data="goodsList"
          border
          stripe
          highlight-current-row
          @current-change="handleGoodsSelect"
        >
          <el-table-column prop="id" label="ID" width="80" align="center" />
          <el-table-column prop="goods_sn" label="商品编码" width="150" />
          <el-table-column prop="goods_name" label="商品名称" min-width="200" show-overflow-tooltip />
          <el-table-column prop="cate_name" label="分类" width="120" />
          <el-table-column prop="unit" label="单位" width="80" align="center" />
        </el-table>

        <el-pagination
          v-model:current-page="matchForm.page"
          v-model:page-size="matchForm.page_size"
          :total="goodsTotal"
          :page-sizes="[10, 20, 50]"
          layout="total, prev, pager, next"
          class="goods-pagination"
          @size-change="handleGoodsSizeChange"
          @current-change="handleGoodsPageChange"
        />
      </div>
      <template #footer>
        <el-button @click="matchDialogVisible = false">取消</el-button>
        <el-button
          type="primary"
          :loading="matchLoading"
          :disabled="!selectedGoods"
          @click="handleConfirmMatch"
        >
          确定匹配
        </el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { ElMessage, ElMessageBox } from 'element-plus'
import { Back, Search, Refresh } from '@element-plus/icons-vue'
import {
  getJiagewangMatch,
  getJiagewangNoMatch,
  matchGoods,
  unmatchGoods,
} from '@/api/modules/jiagewang'
import { getGoodsList } from '@/api/modules/goods'

const router = useRouter()

// 加载状态
const loading = ref(false)
const goodsLoading = ref(false)
const matchLoading = ref(false)

// Tab 状态
const activeTab = ref('matched')
const matchedCount = ref(0)
const unmatchedCount = ref(0)

// 查询参数
const queryParams = reactive({
  page: 1,
  page_size: 20,
  goods_sn: '',
  goods_name: '',
})

// 表格数据
const tableData = ref([])
const selectedIds = ref([])

// 匹配弹窗
const matchDialogVisible = ref(false)
const currentJiagewang = ref(null)
const matchForm = reactive({
  goods_sn: '',
  goods_name: '',
  page: 1,
  page_size: 10,
})
const goodsList = ref([])
const goodsTotal = ref(0)
const selectedGoods = ref(null)

// 获取已匹配列表
async function fetchMatchedList() {
  loading.value = true
  try {
    const res = await getJiagewangMatch(queryParams)
    tableData.value = res.data.list || []
    matchedCount.value = res.data.total || 0
  } catch (error) {
    console.error('获取已匹配列表失败:', error)
  } finally {
    loading.value = false
  }
}

// 获取未匹配列表
async function fetchUnmatchedList() {
  loading.value = true
  try {
    const res = await getJiagewangNoMatch(queryParams)
    tableData.value = res.data.list || []
    unmatchedCount.value = res.data.total || 0
  } catch (error) {
    console.error('获取未匹配列表失败:', error)
  } finally {
    loading.value = false
  }
}

// Tab 切换
function handleTabChange(tab) {
  queryParams.page = 1
  if (tab === 'matched') {
    fetchMatchedList()
  } else {
    fetchUnmatchedList()
  }
}

// 搜索
function handleSearch() {
  queryParams.page = 1
  if (activeTab.value === 'matched') {
    fetchMatchedList()
  } else {
    fetchUnmatchedList()
  }
}

// 重置
function handleReset() {
  queryParams.page = 1
  queryParams.page_size = 20
  queryParams.goods_sn = ''
  queryParams.goods_name = ''
  handleSearch()
}

// 选择变化
function handleSelectionChange(selection) {
  selectedIds.value = selection.map(item => item.id)
}

// 分页
function handleSizeChange(size) {
  queryParams.page_size = size
  queryParams.page = 1
  handleSearch()
}

function handleCurrentChange(page) {
  queryParams.page = page
  handleSearch()
}

// 返回列表
function handleBack() {
  router.push('/jiagewang')
}

// 取消匹配
async function handleUnmatch(row) {
  try {
    await ElMessageBox.confirm('确定要取消该商品的匹配关系吗？', '提示', {
      type: 'warning',
    })
    await unmatchGoods(row.id)
    ElMessage.success('取消匹配成功')
    fetchMatchedList()
  } catch (error) {
    if (error !== 'cancel') {
      console.error('取消匹配失败:', error)
    }
  }
}

// 单个匹配
function handleMatchSingle(row) {
  currentJiagewang.value = row
  selectedGoods.value = null
  matchForm.goods_sn = ''
  matchForm.goods_name = ''
  matchForm.page = 1
  matchDialogVisible.value = true
  handleSearchGoods()
}

// 批量匹配
function handleBatchMatch() {
  ElMessage.info('批量匹配功能开发中')
}

// 搜索商品
async function handleSearchGoods() {
  goodsLoading.value = true
  try {
    const res = await getGoodsList({
      page: matchForm.page,
      page_size: matchForm.page_size,
      goods_sn: matchForm.goods_sn,
      goods_name: matchForm.goods_name,
    })
    goodsList.value = res.data.list || []
    goodsTotal.value = res.data.total || 0
  } catch (error) {
    console.error('搜索商品失败:', error)
  } finally {
    goodsLoading.value = false
  }
}

// 商品分页
function handleGoodsSizeChange(size) {
  matchForm.page_size = size
  matchForm.page = 1
  handleSearchGoods()
}

function handleGoodsPageChange(page) {
  matchForm.page = page
  handleSearchGoods()
}

// 选择商品
function handleGoodsSelect(row) {
  selectedGoods.value = row
}

// 确认匹配
async function handleConfirmMatch() {
  if (!selectedGoods.value) {
    ElMessage.warning('请选择要匹配的商品')
    return
  }

  matchLoading.value = true
  try {
    await matchGoods({
      jiagewang_id: currentJiagewang.value.id,
      goods_id: selectedGoods.value.id,
    })
    ElMessage.success('匹配成功')
    matchDialogVisible.value = false
    fetchUnmatchedList()
  } catch (error) {
    console.error('匹配失败:', error)
  } finally {
    matchLoading.value = false
  }
}

onMounted(() => {
  fetchMatchedList()
})
</script>

<style lang="scss" scoped>
.jiagewang-match-container {
  padding: 20px;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.search-form {
  margin-bottom: 20px;
}

.batch-actions {
  margin-top: 20px;
  padding: 15px;
  background-color: #f5f7fa;
  border-radius: 4px;
}

.pagination {
  margin-top: 20px;
  justify-content: flex-end;
}

.price {
  color: #f56c6c;
  font-weight: 600;
}

.match-dialog-content {
  .match-search-form {
    margin-bottom: 15px;
  }

  .goods-pagination {
    margin-top: 15px;
    justify-content: flex-end;
  }
}
</style>
