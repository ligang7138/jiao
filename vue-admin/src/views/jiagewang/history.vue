<template>
  <div class="jiagewang-history-container">
    <el-card shadow="never">
      <template #header>
        <div class="card-header">
          <span>历史记录</span>
          <el-button @click="handleBack">
            <el-icon><Back /></el-icon>
            返回列表
          </el-button>
        </div>
      </template>

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
        <el-form-item label="操作时间">
          <el-date-picker
            v-model="queryParams.date_range"
            type="daterange"
            range-separator="至"
            start-placeholder="开始日期"
            end-placeholder="结束日期"
            value-format="YYYY-MM-DD"
            style="width: 260px"
          />
        </el-form-item>
        <el-form-item label="操作类型">
          <el-select
            v-model="queryParams.action"
            placeholder="请选择操作类型"
            clearable
            style="width: 150px"
          >
            <el-option label="新增" value="create" />
            <el-option label="更新" value="update" />
            <el-option label="删除" value="delete" />
            <el-option label="导入" value="import" />
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

      <!-- 表格 -->
      <el-table v-loading="loading" :data="tableData" border stripe>
        <el-table-column prop="id" label="ID" width="80" align="center" />
        <el-table-column prop="goods_sn" label="商品编码" width="150" />
        <el-table-column prop="goods_name" label="商品名称" min-width="200" show-overflow-tooltip />
        <el-table-column prop="action" label="操作类型" width="100" align="center">
          <template #default="{ row }">
            <el-tag :type="getActionTagType(row.action)">
              {{ getActionLabel(row.action) }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="old_price" label="原价格" width="100" align="right">
          <template #default="{ row }">
            <span v-if="row.old_price">{{ Number(row.old_price).toFixed(2) }}</span>
            <span v-else>-</span>
          </template>
        </el-table-column>
        <el-table-column prop="new_price" label="新价格" width="100" align="right">
          <template #default="{ row }">
            <span v-if="row.new_price">{{ Number(row.new_price).toFixed(2) }}</span>
            <span v-else>-</span>
          </template>
        </el-table-column>
        <el-table-column prop="operator" label="操作人" width="120" />
        <el-table-column prop="created_at" label="操作时间" width="160" align="center" />
        <el-table-column prop="remark" label="备注" min-width="150" show-overflow-tooltip />
      </el-table>

      <!-- 分页 -->
      <el-pagination
        v-model:current-page="queryParams.page"
        v-model:page-size="queryParams.page_size"
        :total="total"
        :page-sizes="[10, 20, 50, 100]"
        layout="total, sizes, prev, pager, next, jumper"
        class="pagination"
        @size-change="handleSizeChange"
        @current-change="handleCurrentChange"
      />
    </el-card>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { Back, Search, Refresh } from '@element-plus/icons-vue'
import { getJiagewangHistory } from '@/api/modules/jiagewang'

const router = useRouter()

// 加载状态
const loading = ref(false)

// 查询参数
const queryParams = reactive({
  page: 1,
  page_size: 20,
  goods_sn: '',
  goods_name: '',
  date_range: [],
  action: '',
})

// 表格数据
const tableData = ref([])
const total = ref(0)

// 获取列表数据
async function fetchData() {
  loading.value = true
  try {
    const params = { ...queryParams }
    if (params.date_range && params.date_range.length === 2) {
      params.start_date = params.date_range[0]
      params.end_date = params.date_range[1]
    }
    delete params.date_range

    const res = await getJiagewangHistory(params)
    tableData.value = res.data.list || []
    total.value = res.data.total || 0
  } catch (error) {
    console.error('获取历史记录失败:', error)
  } finally {
    loading.value = false
  }
}

// 搜索
function handleSearch() {
  queryParams.page = 1
  fetchData()
}

// 重置
function handleReset() {
  queryParams.page = 1
  queryParams.page_size = 20
  queryParams.goods_sn = ''
  queryParams.goods_name = ''
  queryParams.date_range = []
  queryParams.action = ''
  fetchData()
}

// 分页
function handleSizeChange(size) {
  queryParams.page_size = size
  queryParams.page = 1
  fetchData()
}

function handleCurrentChange(page) {
  queryParams.page = page
  fetchData()
}

// 返回列表
function handleBack() {
  router.push('/jiagewang')
}

// 获取操作类型标签
function getActionTagType(action) {
  const typeMap = {
    create: 'success',
    update: 'warning',
    delete: 'danger',
    import: 'primary',
  }
  return typeMap[action] || 'info'
}

// 获取操作类型文本
function getActionLabel(action) {
  const labelMap = {
    create: '新增',
    update: '更新',
    delete: '删除',
    import: '导入',
  }
  return labelMap[action] || action
}

onMounted(() => {
  fetchData()
})
</script>

<style lang="scss" scoped>
.jiagewang-history-container {
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

.pagination {
  margin-top: 20px;
  justify-content: flex-end;
}
</style>
