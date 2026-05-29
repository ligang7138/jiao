<template>
  <div class="jiagewang-container">
    <!-- 搜索区域 -->
    <el-card class="search-card" shadow="never">
      <el-form :model="queryParams" inline>
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
        <el-form-item label="一级分类">
          <el-select
            v-model="queryParams.cate_id"
            placeholder="请选择一级分类"
            clearable
            style="width: 180px"
            @change="handleCateChange"
          >
            <el-option
              v-for="item in categoryList"
              :key="item.id"
              :label="item.name"
              :value="item.id"
            />
          </el-select>
        </el-form-item>
        <el-form-item label="二级分类">
          <el-select
            v-model="queryParams.scate_id"
            placeholder="请选择二级分类"
            clearable
            style="width: 180px"
          >
            <el-option
              v-for="item in subCategoryList"
              :key="item.id"
              :label="item.name"
              :value="item.id"
            />
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

    <!-- 操作区域 -->
    <el-card class="table-card" shadow="never">
      <template #header>
        <div class="card-header">
          <span>指导价列表</span>
          <div class="header-actions">
            <el-button type="primary" @click="handleImport">
              <el-icon><Upload /></el-icon>
              导入
            </el-button>
            <el-button type="success" @click="handleExport">
              <el-icon><Download /></el-icon>
              导出
            </el-button>
            <el-button type="warning" @click="handleMatch">
              <el-icon><Connection /></el-icon>
              商品匹配
            </el-button>
            <el-button @click="handleHistory">
              <el-icon><Clock /></el-icon>
              历史记录
            </el-button>
            <el-button
              type="danger"
              :disabled="selectedIds.length === 0"
              @click="handleBatchDelete"
            >
              <el-icon><Delete /></el-icon>
              批量删除
            </el-button>
          </div>
        </div>
      </template>

      <!-- 表格 -->
      <el-table
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
        <el-table-column prop="matched_goods_name" label="匹配商品" width="150" show-overflow-tooltip>
          <template #default="{ row }">
            <span v-if="row.matched_goods_name" class="matched">
              {{ row.matched_goods_name }}
            </span>
            <span v-else class="unmatched">未匹配</span>
          </template>
        </el-table-column>
        <el-table-column prop="updated_at" label="更新时间" width="160" align="center" />
        <el-table-column label="操作" width="150" align="center" fixed="right">
          <template #default="{ row }">
            <el-button type="primary" link @click="handleEdit(row)">
              编辑
            </el-button>
            <el-button type="danger" link @click="handleDelete(row)">
              删除
            </el-button>
          </template>
        </el-table-column>
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

    <!-- 编辑弹窗 -->
    <el-dialog
      v-model="editDialogVisible"
      title="编辑指导价"
      width="500px"
      :close-on-click-modal="false"
    >
      <el-form
        ref="editFormRef"
        :model="editForm"
        :rules="editRules"
        label-width="100px"
      >
        <el-form-item label="商品编码">
          <el-input v-model="editForm.goods_sn" disabled />
        </el-form-item>
        <el-form-item label="商品名称">
          <el-input v-model="editForm.goods_name" disabled />
        </el-form-item>
        <el-form-item label="单位">
          <el-input v-model="editForm.unit" disabled />
        </el-form-item>
        <el-form-item label="指导价" prop="price">
          <el-input-number
            v-model="editForm.price"
            :precision="2"
            :min="0"
            :step="0.1"
            style="width: 100%"
          />
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="editDialogVisible = false">取消</el-button>
        <el-button type="primary" :loading="submitLoading" @click="handleSubmit">
          确定
        </el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import { ElMessage, ElMessageBox } from 'element-plus'
import { Search, Refresh, Upload, Download, Connection, Clock, Delete } from '@element-plus/icons-vue'
import {
  getJiagewangList,
  updateJiagewang,
  deleteJiagewang,
  batchDeleteJiagewang,
  exportJiagewang,
} from '@/api/modules/jiagewang'
import { getCategoryList } from '@/api/modules/category'

const router = useRouter()

// 加载状态
const loading = ref(false)
const submitLoading = ref(false)

// 查询参数
const queryParams = reactive({
  page: 1,
  page_size: 20,
  goods_sn: '',
  goods_name: '',
  cate_id: '',
  scate_id: '',
})

// 表格数据
const tableData = ref([])
const total = ref(0)
const selectedIds = ref([])

// 分类数据
const categoryList = ref([])
const allSubCategories = ref([])

// 计算二级分类
const subCategoryList = computed(() => {
  if (!queryParams.cate_id) {
    return []
  }
  return allSubCategories.value.filter(item => item.parent_id === queryParams.cate_id)
})

// 编辑弹窗
const editDialogVisible = ref(false)
const editFormRef = ref()
const editForm = reactive({
  id: '',
  goods_sn: '',
  goods_name: '',
  unit: '',
  price: 0,
})

const editRules = {
  price: [
    { required: true, message: '请输入指导价', trigger: 'blur' },
    { type: 'number', min: 0, message: '指导价不能小于0', trigger: 'blur' },
  ],
}

// 获取分类列表
async function fetchCategories() {
  try {
    const res = await getCategoryList({ page_size: 1000 })
    const list = res.data.list || []
    categoryList.value = list.filter(item => item.parent_id === 0)
    allSubCategories.value = list.filter(item => item.parent_id !== 0)
  } catch (error) {
    console.error('获取分类失败:', error)
  }
}

// 获取列表数据
async function fetchData() {
  loading.value = true
  try {
    const res = await getJiagewangList(queryParams)
    tableData.value = res.data.list || []
    total.value = res.data.total || 0
  } catch (error) {
    console.error('获取列表失败:', error)
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
  queryParams.cate_id = ''
  queryParams.scate_id = ''
  fetchData()
}

// 一级分类变化
function handleCateChange() {
  queryParams.scate_id = ''
}

// 选择变化
function handleSelectionChange(selection) {
  selectedIds.value = selection.map(item => item.id)
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

// 导入
function handleImport() {
  router.push('/jiagewang/import')
}

// 导出
async function handleExport() {
  try {
    const res = await exportJiagewang(queryParams)
    const blob = new Blob([res], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' })
    const url = window.URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = url
    link.download = `指导价导出_${new Date().toISOString().slice(0, 10)}.xlsx`
    link.click()
    window.URL.revokeObjectURL(url)
    ElMessage.success('导出成功')
  } catch (error) {
    console.error('导出失败:', error)
  }
}

// 商品匹配
function handleMatch() {
  router.push('/jiagewang/match')
}

// 历史记录
function handleHistory() {
  router.push('/jiagewang/history')
}

// 编辑
function handleEdit(row) {
  editForm.id = row.id
  editForm.goods_sn = row.goods_sn
  editForm.goods_name = row.goods_name
  editForm.unit = row.unit
  editForm.price = Number(row.price)
  editDialogVisible.value = true
}

// 提交编辑
async function handleSubmit() {
  try {
    await editFormRef.value.validate()
    submitLoading.value = true
    await updateJiagewang(editForm.id, { price: editForm.price })
    ElMessage.success('编辑成功')
    editDialogVisible.value = false
    fetchData()
  } catch (error) {
    console.error('编辑失败:', error)
  } finally {
    submitLoading.value = false
  }
}

// 删除
async function handleDelete(row) {
  try {
    await ElMessageBox.confirm('确定要删除该指导价吗？', '提示', {
      type: 'warning',
    })
    await deleteJiagewang(row.id)
    ElMessage.success('删除成功')
    fetchData()
  } catch (error) {
    if (error !== 'cancel') {
      console.error('删除失败:', error)
    }
  }
}

// 批量删除
async function handleBatchDelete() {
  try {
    await ElMessageBox.confirm(`确定要删除选中的 ${selectedIds.value.length} 条记录吗？`, '提示', {
      type: 'warning',
    })
    await batchDeleteJiagewang(selectedIds.value)
    ElMessage.success('删除成功')
    fetchData()
  } catch (error) {
    if (error !== 'cancel') {
      console.error('批量删除失败:', error)
    }
  }
}

onMounted(() => {
  fetchCategories()
  fetchData()
})
</script>

<style lang="scss" scoped>
.jiagewang-container {
  padding: 20px;
}

.search-card {
  margin-bottom: 20px;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;

  .header-actions {
    display: flex;
    gap: 10px;
  }
}

.price {
  color: #f56c6c;
  font-weight: 600;
}

.matched {
  color: #67c23a;
}

.unmatched {
  color: #909399;
}

.pagination {
  margin-top: 20px;
  justify-content: flex-end;
}
</style>
