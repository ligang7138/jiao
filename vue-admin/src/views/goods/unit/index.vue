<template>
  <div class="goods-unit">
    <el-card class="search-card">
      <el-form :model="searchForm" inline label-width="80px">
        <el-form-item label="单位名称">
          <el-input v-model="searchForm.name" placeholder="单位名称" clearable style="width: 150px" @keyup.enter="handleSearch" />
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
            <el-button type="primary" @click="handleAdd">新增单位</el-button>
          </div>
          <span class="total-count">共 <b>{{ pagination.total }}</b> 条数据</span>
        </div>
      </template>

      <el-table v-loading="loading" :data="tableData" border stripe>
        <el-table-column prop="name" label="单位" min-width="130" />
        <el-table-column prop="add_user" label="创建人" width="120" align="center" />
        <el-table-column label="创建时间" width="160" align="center">
          <template #default="{ row }">{{ formatTime(row.add_time) }}</template>
        </el-table-column>
        <el-table-column label="状态" width="100" align="center">
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
        <el-table-column label="操作" width="100" align="center" fixed="right">
          <template #default="{ row }">
            <el-button type="primary" link @click="handleEdit(row)">编辑</el-button>
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

    <el-dialog v-model="dialogVisible" :title="dialogTitle" width="400px" :close-on-click-modal="false" @closed="resetForm">
      <el-form ref="formRef" :model="formData" :rules="formRules" label-width="80px">
        <el-form-item label="单位名称" prop="name">
          <el-input v-model="formData.name" placeholder="请输入单位名称" maxlength="20" />
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="dialogVisible = false">取消</el-button>
        <el-button type="primary" :loading="submitLoading" @click="handleSubmit">确定</el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue'
import { ElMessage } from 'element-plus'
import {
  getGoodsUnitList,
  createGoodsUnit,
  updateGoodsUnit,
  setGoodsUnitStatus,
} from '@/api/modules/goodsUnit'

const loading = ref(false)
const tableData = ref([])

const searchForm = reactive({
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
})

const formRules = {
  name: [{ required: true, message: '请输入单位名称', trigger: 'blur' }],
}

const dialogTitle = computed(() => (editingId.value ? '编辑单位' : '新增单位'))

function formatTime(timestamp) {
  if (!timestamp) return ''
  const date = new Date(timestamp * 1000)
  const pad = (n) => String(n).padStart(2, '0')
  return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())} ${pad(date.getHours())}:${pad(date.getMinutes())}`
}

async function fetchData() {
  loading.value = true
  try {
    const params = {
      page: pagination.page,
      page_size: pagination.pageSize,
    }
    if (searchForm.name) params.name = searchForm.name
    if (searchForm.status !== null && searchForm.status !== '') params.status = searchForm.status

    const { data } = await getGoodsUnitList(params)
    tableData.value = data?.list || []
    pagination.total = data?.total || 0
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
  formData.name = ''
  dialogVisible.value = true
}

function handleEdit(row) {
  editingId.value = row.id
  formData.name = row.name
  dialogVisible.value = true
}

async function handleStatusChange(row, enabled) {
  try {
    await setGoodsUnitStatus(row.id, enabled ? 1 : 0)
    ElMessage.success('设置成功')
    fetchData()
  } catch (error) {
    ElMessage.error(error.message || '设置失败')
  }
}

async function handleSubmit() {
  await formRef.value.validate()
  submitLoading.value = true
  try {
    if (editingId.value) {
      await updateGoodsUnit(editingId.value, { name: formData.name })
      ElMessage.success('编辑成功')
    } else {
      await createGoodsUnit({ name: formData.name })
      ElMessage.success('新增成功')
    }
    dialogVisible.value = false
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
  fetchData()
})
</script>

<style lang="scss" scoped>
.goods-unit {
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
