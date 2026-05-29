<template>
  <div class="school-list">
    <el-card class="search-card">
      <el-form :model="searchForm" inline>
        <el-form-item label="学校名称">
          <el-input v-model="searchForm.school_name" placeholder="学校名称" clearable @keyup.enter="handleSearch" />
        </el-form-item>
        <el-form-item label="学校编码">
          <el-input v-model="searchForm.school_sn" placeholder="学校编码" clearable @keyup.enter="handleSearch" />
        </el-form-item>
        <el-form-item label="状态">
          <el-select v-model="searchForm.status" placeholder="全部" clearable style="width: 120px">
            <el-option label="启用" :value="1" />
            <el-option label="停用" :value="2" />
          </el-select>
        </el-form-item>
        <el-form-item label="是否绑定货商">
          <el-select v-model="searchForm.bidding_status" placeholder="全部" clearable style="width: 120px">
            <el-option label="是" :value="1" />
            <el-option label="否" :value="0" />
          </el-select>
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="handleSearch">搜索</el-button>
          <el-button @click="handleReset">重置</el-button>
        </el-form-item>
      </el-form>
    </el-card>

    <el-card class="table-card">
      <template #header>
        <div class="card-header">
          <span>学校列表</span>
          <el-button v-if="canAdd" type="primary" @click="handleAdd">新增学校</el-button>
        </div>
      </template>

      <el-table v-loading="loading" :data="tableData" border stripe>
        <el-table-column prop="school_sn" label="学校编码" width="120" align="center" />
        <el-table-column prop="school_name" label="学校名称" min-width="180" show-overflow-tooltip />
        <el-table-column prop="school_district" label="学区" width="120" align="center" />
        <el-table-column prop="school_period" label="学段" width="100" align="center" />
        <el-table-column prop="status" label="状态" width="100" align="center">
          <template #default="{ row }">
            <el-tag
              v-if="canEdit"
              :type="row.status === 1 ? 'success' : 'info'"
              class="status-tag"
              @click="handleToggleStatus(row)"
            >
              {{ row.status_text }}
            </el-tag>
            <el-tag v-else :type="row.status === 1 ? 'success' : 'info'">{{ row.status_text }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="supplier_num" label="绑定货商数量" width="120" align="center" />
        <el-table-column label="操作" width="120" align="center" fixed="right">
          <template #default="{ row }">
            <el-button v-if="canEdit" type="primary" link @click="handleEdit(row)">编辑</el-button>
          </template>
        </el-table-column>
      </el-table>

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

    <el-dialog v-model="dialogVisible" :title="dialogTitle" width="520px" :close-on-click-modal="false" @closed="handleDialogClosed">
      <el-form ref="formRef" :model="formData" :rules="formRules" label-width="100px">
        <el-form-item v-if="formData.id" label="学校编码">
          <span>{{ formData.school_sn }}</span>
        </el-form-item>
        <el-form-item label="学校名称" prop="school_name">
          <el-input v-model="formData.school_name" placeholder="请输入学校名称" maxlength="100" />
        </el-form-item>
        <el-form-item label="学区" prop="school_district">
          <el-select v-model="formData.school_district" placeholder="请选择学区" style="width: 100%">
            <el-option v-for="item in districtOptions" :key="item" :label="item" :value="item" />
          </el-select>
        </el-form-item>
        <el-form-item label="学段" prop="school_period">
          <el-select v-model="formData.school_period" placeholder="请选择学段" style="width: 100%">
            <el-option v-for="item in periodOptions" :key="item" :label="item" :value="item" />
          </el-select>
        </el-form-item>
        <el-form-item label="状态" prop="status">
          <el-radio-group v-model="formData.status">
            <el-radio :value="1">启用</el-radio>
            <el-radio :value="2">停用</el-radio>
          </el-radio-group>
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
import { computed, onMounted, reactive, ref } from 'vue'
import { ElMessage } from 'element-plus'
import { useUserStore } from '@/stores/modules/user'
import {
  getSchoolOptions,
  getSchoolList,
  getSchoolDetail,
  createSchool,
  updateSchool,
  changeSchoolStatus,
} from '@/api/modules/school'

const userStore = useUserStore()
const canAdd = computed(() => userStore.checkPermission('school.add'))
const canEdit = computed(() => userStore.checkPermission('school.edit'))

const searchForm = reactive({
  school_name: '',
  school_sn: '',
  status: null,
  bidding_status: null,
})
const loading = ref(false)
const tableData = ref([])
const districtOptions = ref([])
const periodOptions = ref([])
const pagination = reactive({ page: 1, pageSize: 20, total: 0 })

const dialogVisible = ref(false)
const dialogTitle = computed(() => (formData.id ? '编辑学校' : '新增学校'))
const formRef = ref()
const submitLoading = ref(false)

const formData = reactive({
  id: null,
  school_sn: '',
  school_name: '',
  school_district: '',
  school_period: '',
  status: 1,
})

const formRules = {
  school_name: [{ required: true, message: '请输入学校名称', trigger: 'blur' }],
  school_district: [{ required: true, message: '请选择学区', trigger: 'change' }],
  school_period: [{ required: true, message: '请选择学段', trigger: 'change' }],
}

async function loadOptions() {
  try {
    const { data } = await getSchoolOptions()
    districtOptions.value = data?.districts || []
    periodOptions.value = data?.school_periods || []
  } catch (error) {
    console.error(error)
  }
}

async function fetchData() {
  loading.value = true
  try {
    const { data } = await getSchoolList({
      ...searchForm,
      page: pagination.page,
      page_size: pagination.pageSize,
    })
    tableData.value = data.list || []
    pagination.total = data.total || 0
  } finally {
    loading.value = false
  }
}

function handleSearch() {
  pagination.page = 1
  fetchData()
}

function handleReset() {
  Object.assign(searchForm, { school_name: '', school_sn: '', status: null, bidding_status: null })
  handleSearch()
}

function handleAdd() {
  Object.assign(formData, { id: null, school_sn: '', school_name: '', school_district: '', school_period: '', status: 1 })
  dialogVisible.value = true
}

async function handleEdit(row) {
  const { data } = await getSchoolDetail(row.id)
  Object.assign(formData, data)
  dialogVisible.value = true
}

async function handleToggleStatus(row) {
  const nextStatus = row.status === 1 ? 2 : 1
  await changeSchoolStatus(row.id, nextStatus)
  ElMessage.success('设置成功')
  fetchData()
}

async function handleSubmit() {
  await formRef.value.validate()
  submitLoading.value = true
  try {
    const payload = {
      school_name: formData.school_name,
      school_district: formData.school_district,
      school_period: formData.school_period,
      status: formData.status,
    }
    if (formData.id) {
      await updateSchool(formData.id, payload)
      ElMessage.success('修改成功')
    } else {
      await createSchool(payload)
      ElMessage.success('添加成功')
    }
    dialogVisible.value = false
    fetchData()
  } finally {
    submitLoading.value = false
  }
}

function handleDialogClosed() {
  formRef.value?.resetFields()
}

function handleSizeChange(size) {
  pagination.pageSize = size
  fetchData()
}

function handlePageChange(page) {
  pagination.page = page
  fetchData()
}

onMounted(() => {
  loadOptions()
  fetchData()
})
</script>

<style lang="scss" scoped>
.school-list {
  .search-card { margin-bottom: 20px; }
  .table-card .card-header { display: flex; justify-content: space-between; align-items: center; }
  .el-pagination { margin-top: 20px; justify-content: flex-end; }
  .status-tag { cursor: pointer; }
}
</style>
