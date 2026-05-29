<template>
  <div class="category-list">
    <el-card class="search-card">
      <el-form :model="searchForm" inline label-width="80px">
        <el-form-item label="一级分类">
          <el-select v-model="searchForm.pid" placeholder="请选择" clearable style="width: 150px">
            <el-option v-for="item in topCategories" :key="item.id" :label="item.name" :value="item.id" />
          </el-select>
        </el-form-item>
        <el-form-item label="分类名称">
          <el-input v-model="searchForm.name" placeholder="分类名称" clearable style="width: 150px" @keyup.enter="handleSearch" />
        </el-form-item>
        <el-form-item label="状态">
          <el-select v-model="searchForm.status" placeholder="请选择" clearable style="width: 120px">
            <el-option label="启用" :value="1" />
            <el-option label="停用" :value="0" />
          </el-select>
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="handleSearch">搜索</el-button>
        </el-form-item>
      </el-form>
    </el-card>

    <el-card class="table-card">
      <template #header>
        <div class="card-header">
          <div class="header-actions">
            <el-button @click="handleRefresh">刷新</el-button>
            <el-button type="primary" @click="handleAdd">新增分类</el-button>
          </div>
          <span class="total-count">共 <b>{{ pagination.total }}</b> 条数据</span>
        </div>
      </template>

      <el-table v-loading="loading" :data="tableData" border stripe>
        <el-table-column label="一级分类" min-width="130">
          <template #default="{ row }">
            {{ row.pid === 0 ? row.name : row.pname }}
          </template>
        </el-table-column>
        <el-table-column label="分类名称" min-width="130">
          <template #default="{ row }">
            {{ row.pid > 0 ? row.name : '' }}
          </template>
        </el-table-column>
        <el-table-column prop="sort" label="排序号" width="80" align="center" />
        <el-table-column label="状态" width="90" align="center">
          <template #default="{ row }">
            <el-switch
              :model-value="row.status === 1"
              inline-prompt
              active-text="启用"
              inactive-text="停用"
              @change="(val) => handleStatusChange(row, val)"
            />
          </template>
        </el-table-column>
        <el-table-column prop="add_user" label="创建人" width="100" align="center" />
        <el-table-column label="创建时间" width="150" align="center">
          <template #default="{ row }">{{ formatTime(row.add_time) }}</template>
        </el-table-column>
        <el-table-column prop="update_user" label="更新人" width="100" align="center" />
        <el-table-column label="更新时间" width="150" align="center">
          <template #default="{ row }">{{ formatTime(row.update_time) }}</template>
        </el-table-column>
        <el-table-column label="操作" width="260" align="center" fixed="right">
          <template #default="{ row }">
            <el-button type="primary" link @click="handleEdit(row)">编辑</el-button>
            <el-button v-if="row.pid === 0" type="primary" link @click="handleFloatRate(row)">浮动率上限</el-button>
          </template>
        </el-table-column>
      </el-table>

      <el-pagination
        v-model:current-page="pagination.page"
        v-model:page-size="pagination.pageSize"
        :total="pagination.total"
        :page-sizes="[10, 20, 50, 100]"
        layout="total, sizes, prev, pager, next, jumper"
        @size-change="fetchData"
        @current-change="fetchData"
      />
    </el-card>

    <el-dialog v-model="dialogVisible" :title="dialogTitle" width="500px" :close-on-click-modal="false" @closed="resetForm">
      <el-form ref="formRef" :model="formData" :rules="formRules" label-width="100px">
        <el-form-item label="分类名称" prop="name">
          <el-input v-model="formData.name" placeholder="请输入分类名称" maxlength="50" />
        </el-form-item>
        <el-form-item label="一级分类" prop="pid">
          <el-select v-model="formData.pid" placeholder="不选则为顶级分类" clearable style="width: 100%">
            <el-option v-for="item in topCategories" :key="item.id" :label="item.name" :value="item.id" />
          </el-select>
        </el-form-item>
        <el-form-item label="排序" prop="sort">
          <el-input-number v-model="formData.sort" :min="0" :max="9999" style="width: 100%" />
        </el-form-item>
        <el-form-item label="状态" prop="status">
          <el-radio-group v-model="formData.status">
            <el-radio :value="1">启用</el-radio>
            <el-radio :value="0">停用</el-radio>
          </el-radio-group>
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="dialogVisible = false">取消</el-button>
        <el-button type="primary" :loading="submitLoading" @click="handleSubmit">确定</el-button>
      </template>
    </el-dialog>

    <el-dialog v-model="floatDialogVisible" title="设置浮动率上限" width="400px">
      <el-form label-width="120px">
        <el-form-item label="浮动率上限(%)">
          <el-input-number v-model="floatRateCap" :min="0" :max="100" :precision="2" style="width: 100%" />
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="floatDialogVisible = false">取消</el-button>
        <el-button type="primary" :loading="floatLoading" @click="confirmFloatRate">确定</el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue'
import { ElMessage } from 'element-plus'
import {
  getCategoryList,
  getTopCategories,
  getCategoryDetail,
  createCategory,
  updateCategory,
  setCategoryStatus,
  setFloatRateCap,
} from '@/api/modules/category'

const loading = ref(false)
const tableData = ref([])
const topCategories = ref([])

const searchForm = reactive({
  pid: null,
  name: '',
  status: null,
})

const pagination = reactive({
  page: 1,
  pageSize: 20,
  total: 0,
})

const dialogVisible = ref(false)
const formRef = ref()
const submitLoading = ref(false)
const editingId = ref(null)

const formData = reactive({
  name: '',
  pid: null,
  sort: 0,
  status: 1,
})

const formRules = {
  name: [{ required: true, message: '请输入分类名称', trigger: 'blur' }],
}

const dialogTitle = computed(() => (editingId.value ? '编辑分类' : '新增分类'))

const floatDialogVisible = ref(false)
const floatLoading = ref(false)
const floatRateCap = ref(13)
const currentFloatCategory = ref(null)

function formatTime(timestamp) {
  if (!timestamp) return ''
  const date = new Date(timestamp * 1000)
  const pad = (n) => String(n).padStart(2, '0')
  return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())} ${pad(date.getHours())}:${pad(date.getMinutes())}`
}

async function fetchTopCategories() {
  const { data } = await getTopCategories()
  topCategories.value = data || []
}

async function fetchData() {
  loading.value = true
  try {
    const params = {
      page: pagination.page,
      page_size: pagination.pageSize,
    }
    if (searchForm.pid) params.pid = searchForm.pid
    if (searchForm.name) params.name = searchForm.name
    if (searchForm.status !== null && searchForm.status !== '') params.status = searchForm.status

    const { data } = await getCategoryList(params)
    tableData.value = data?.list || data || []
    pagination.total = data?.total || tableData.value.length
  } finally {
    loading.value = false
  }
}

function handleSearch() {
  pagination.page = 1
  fetchData()
}

function handleRefresh() {
  fetchData()
}

function handleAdd() {
  editingId.value = null
  Object.assign(formData, { name: '', pid: null, sort: 0, status: 1 })
  dialogVisible.value = true
}

async function handleEdit(row) {
  const { data } = await getCategoryDetail(row.id)
  editingId.value = row.id
  Object.assign(formData, {
    name: data.name,
    pid: data.pid || null,
    sort: data.sort,
    status: data.status,
  })
  dialogVisible.value = true
}

async function handleStatusChange(row, enabled) {
  try {
    await setCategoryStatus(row.id, enabled ? 1 : 0)
    ElMessage.success('设置成功')
    fetchData()
  } catch (error) {
    ElMessage.error(error.message || '设置失败')
  }
}

function handleFloatRate(row) {
  currentFloatCategory.value = row
  floatRateCap.value = row.float_rate_cap ? Number((row.float_rate_cap * 100).toFixed(2)) : 13
  floatDialogVisible.value = true
}

async function confirmFloatRate() {
  if (!currentFloatCategory.value) return
  floatLoading.value = true
  try {
    await setFloatRateCap(currentFloatCategory.value.id, floatRateCap.value)
    ElMessage.success('设置成功')
    floatDialogVisible.value = false
    fetchData()
  } catch (error) {
    ElMessage.error(error.message || '设置失败')
  } finally {
    floatLoading.value = false
  }
}

async function handleSubmit() {
  await formRef.value.validate()
  submitLoading.value = true
  try {
    const payload = {
      name: formData.name,
      pid: formData.pid || 0,
      sort: formData.sort,
      status: formData.status,
    }
    if (editingId.value) {
      await updateCategory(editingId.value, payload)
      ElMessage.success('编辑成功')
    } else {
      await createCategory(payload)
      ElMessage.success('新增成功')
    }
    dialogVisible.value = false
    fetchTopCategories()
    fetchData()
  } finally {
    submitLoading.value = false
  }
}

function resetForm() {
  formRef.value?.resetFields()
  editingId.value = null
}

onMounted(() => {
  fetchTopCategories()
  fetchData()
})
</script>

<style lang="scss" scoped>
.category-list {
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
    }
  }

  .el-pagination {
    margin-top: 20px;
    justify-content: flex-end;
  }
}
</style>
