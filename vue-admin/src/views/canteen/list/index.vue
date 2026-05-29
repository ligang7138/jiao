<template>
  <div class="canteen-list">
    <el-card class="search-card">
      <el-form :model="searchForm" inline>
        <el-form-item label="食堂名称">
          <el-input v-model="searchForm.name" placeholder="食堂名称" clearable @keyup.enter="handleSearch" />
        </el-form-item>
        <el-form-item label="食堂编码">
          <el-input v-model="searchForm.canteen_sn" placeholder="食堂编码" clearable @keyup.enter="handleSearch" />
        </el-form-item>
        <el-form-item label="食堂状态">
          <el-select v-model="searchForm.status" placeholder="全部" clearable style="width: 120px">
            <el-option label="启用" :value="1" />
            <el-option label="停用" :value="2" />
          </el-select>
        </el-form-item>
        <el-form-item label="食堂类型">
          <el-select v-model="searchForm.canteen_type" placeholder="全部" clearable style="width: 120px">
            <el-option label="教师食堂" :value="1" />
            <el-option label="学生食堂" :value="2" />
          </el-select>
        </el-form-item>
        <el-form-item label="所属学校">
          <el-input v-model="searchForm.school_name" placeholder="所属学校" clearable @keyup.enter="handleSearch" />
        </el-form-item>
        <el-form-item label="学区">
          <el-select v-model="searchForm.school_district" placeholder="全部" clearable style="width: 140px">
            <el-option v-for="item in districtOptions" :key="item" :label="item" :value="item" />
          </el-select>
        </el-form-item>
        <el-form-item label="紧急联系人">
          <el-input v-model="searchForm.emergency_linkman" placeholder="紧急联系人" clearable />
        </el-form-item>
        <el-form-item label="紧急联系电话">
          <el-input v-model="searchForm.emergency_mobile" placeholder="紧急联系电话" clearable />
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
          <span>食堂列表</span>
          <el-button v-if="canAdd" type="primary" @click="handleAdd">新增食堂</el-button>
        </div>
      </template>

      <el-table v-loading="loading" :data="tableData" border stripe>
        <el-table-column prop="canteen_sn" label="食堂编码" width="100" align="center" />
        <el-table-column prop="name" label="食堂名称" min-width="140" show-overflow-tooltip />
        <el-table-column prop="school_name" label="所属学校" min-width="160" show-overflow-tooltip />
        <el-table-column prop="school_district" label="所属学区" width="120" align="center" />
        <el-table-column prop="emergency_linkman" label="紧急联系人" width="110" align="center" />
        <el-table-column prop="emergency_mobile" label="紧急联系电话" width="130" align="center" />
        <el-table-column prop="address" label="地址" min-width="160" show-overflow-tooltip />
        <el-table-column prop="status" label="食堂状态" width="100" align="center">
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
        <el-table-column prop="canteen_type_text" label="食堂类型" width="100" align="center" />
        <el-table-column prop="purchase_percentage" label="月计划采购额" width="120" align="center">
          <template #default="{ row }">
            <span :class="percentageClass(row.purchase_percentage)">{{ row.purchase_percentage }}%</span>
          </template>
        </el-table-column>
        <el-table-column prop="add_time" label="注册时间" width="120" align="center" />
        <el-table-column label="操作" width="100" align="center" fixed="right">
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

    <el-dialog v-model="dialogVisible" :title="dialogTitle" width="760px" :close-on-click-modal="false" @closed="handleDialogClosed">
      <el-form ref="formRef" :model="formData" :rules="formRules" label-width="130px">
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="食堂名称" prop="name">
              <el-input v-model="formData.name" placeholder="请输入食堂名称" maxlength="100" />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="所属学校" prop="school_id">
              <el-select v-model="formData.school_id" placeholder="请选择学校" filterable style="width: 100%">
                <el-option v-for="item in schoolList" :key="item.id" :label="item.school_name" :value="item.id" />
              </el-select>
            </el-form-item>
          </el-col>
        </el-row>
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="食堂类型" prop="canteen_type">
              <el-select v-model="formData.canteen_type" style="width: 100%">
                <el-option label="教师食堂" :value="1" />
                <el-option label="学生食堂" :value="2" />
              </el-select>
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="月计划(万元)" prop="monthly_purchase_amount">
              <el-input-number v-model="formData.monthly_purchase_amount" :min="0.01" :max="99999999.99" :precision="2" style="width: 100%" />
            </el-form-item>
          </el-col>
        </el-row>
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="联系人">
              <el-input v-model="formData.linkman" maxlength="50" />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="联系电话">
              <el-input v-model="formData.mobile" maxlength="20" />
            </el-form-item>
          </el-col>
        </el-row>
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="紧急联系人">
              <el-input v-model="formData.emergency_linkman" maxlength="50" />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="紧急联系电话">
              <el-input v-model="formData.emergency_mobile" maxlength="20" />
            </el-form-item>
          </el-col>
        </el-row>
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="收货联系人">
              <el-input v-model="formData.receive_linkman" maxlength="50" />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="收货联系电话">
              <el-input v-model="formData.receive_mobile" maxlength="20" />
            </el-form-item>
          </el-col>
        </el-row>
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="收货开始时间">
              <el-input v-model="formData.receive_start_time" placeholder="06:00" />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="收货结束时间">
              <el-input v-model="formData.receive_end_time" placeholder="08:00" />
            </el-form-item>
          </el-col>
        </el-row>
        <el-form-item label="地址">
          <el-input v-model="formData.address" maxlength="200" />
        </el-form-item>
        <el-form-item label="备注">
          <el-input v-model="formData.remark" type="textarea" :rows="2" maxlength="500" />
        </el-form-item>
        <el-form-item label="状态">
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
import { useRoute } from 'vue-router'
import { ElMessage } from 'element-plus'
import { useUserStore } from '@/stores/modules/user'
import {
  getSchoolOptions,
  getActiveSchools,
  getCanteenList,
  getCanteenDetail,
  createCanteen,
  updateCanteen,
  changeCanteenStatus,
} from '@/api/modules/school'

const route = useRoute()
const userStore = useUserStore()
const canAdd = computed(() => userStore.checkPermission('school_canteen.add'))
const canEdit = computed(() => userStore.checkPermission('school_canteen.edit'))

const searchForm = reactive({
  name: '',
  canteen_sn: '',
  school_name: '',
  school_district: '',
  emergency_linkman: '',
  emergency_mobile: '',
  canteen_type: null,
  status: null,
  school_id: null,
})
const loading = ref(false)
const tableData = ref([])
const schoolList = ref([])
const districtOptions = ref([])
const pagination = reactive({ page: 1, pageSize: 20, total: 0 })

const dialogVisible = ref(false)
const dialogTitle = computed(() => (formData.id ? '编辑食堂' : '新增食堂'))
const formRef = ref()
const submitLoading = ref(false)

const formData = reactive({
  id: null,
  school_id: null,
  name: '',
  canteen_type: 1,
  monthly_purchase_amount: 1,
  linkman: '',
  mobile: '',
  emergency_linkman: '',
  emergency_mobile: '',
  receive_linkman: '',
  receive_mobile: '',
  receive_start_time: '06:00',
  receive_end_time: '08:00',
  address: '',
  remark: '',
  status: 1,
})

const formRules = {
  name: [{ required: true, message: '请输入食堂名称', trigger: 'blur' }],
  school_id: [{ required: true, message: '请选择所属学校', trigger: 'change' }],
  canteen_type: [{ required: true, message: '请选择食堂类型', trigger: 'change' }],
  monthly_purchase_amount: [{ required: true, message: '请输入月计划（万元）', trigger: 'blur' }],
}

function percentageClass(value) {
  if (value > 100) return 'danger'
  if (value > 90) return 'warning'
  return ''
}

async function loadOptions() {
  const [schoolRes, optionRes] = await Promise.all([getActiveSchools(), getSchoolOptions()])
  schoolList.value = schoolRes.data || []
  districtOptions.value = optionRes.data?.districts || []
}

async function fetchData() {
  loading.value = true
  try {
    const { data } = await getCanteenList({
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
  Object.assign(searchForm, {
    name: '',
    canteen_sn: '',
    school_name: '',
    school_district: '',
    emergency_linkman: '',
    emergency_mobile: '',
    canteen_type: null,
    status: null,
    school_id: route.query.school_id ? Number(route.query.school_id) : null,
  })
  handleSearch()
}

function handleAdd() {
  Object.assign(formData, {
    id: null,
    school_id: searchForm.school_id || null,
    name: '',
    canteen_type: 1,
    monthly_purchase_amount: 1,
    linkman: '',
    mobile: '',
    emergency_linkman: '',
    emergency_mobile: '',
    receive_linkman: '',
    receive_mobile: '',
    receive_start_time: '06:00',
    receive_end_time: '08:00',
    address: '',
    remark: '',
    status: 1,
  })
  dialogVisible.value = true
}

async function handleEdit(row) {
  const { data } = await getCanteenDetail(row.id)
  Object.assign(formData, data)
  dialogVisible.value = true
}

async function handleToggleStatus(row) {
  const nextStatus = row.status === 1 ? 2 : 1
  await changeCanteenStatus(row.id, nextStatus)
  ElMessage.success('设置成功')
  fetchData()
}

async function handleSubmit() {
  await formRef.value.validate()
  submitLoading.value = true
  try {
    const payload = { ...formData }
    delete payload.id
    if (formData.id) {
      await updateCanteen(formData.id, payload)
      ElMessage.success('修改成功')
    } else {
      await createCanteen(payload)
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

onMounted(async () => {
  if (route.query.school_id) {
    searchForm.school_id = Number(route.query.school_id)
  }
  await loadOptions()
  fetchData()
})
</script>

<style lang="scss" scoped>
.canteen-list {
  .search-card { margin-bottom: 20px; }
  .table-card .card-header { display: flex; justify-content: space-between; align-items: center; }
  .el-pagination { margin-top: 20px; justify-content: flex-end; }
  .status-tag { cursor: pointer; }
  .danger { color: #f56c6c; }
  .warning { color: #e6a23c; }
}
</style>
