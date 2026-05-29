<template>
  <div class="group-list">
    <el-card class="search-card">
      <el-form :model="searchForm" inline>
        <el-form-item label="分组名称">
          <el-select v-model="searchForm.pid" placeholder="全部分组" clearable filterable style="width: 220px">
            <el-option v-for="item in parentOptions" :key="item.id" :label="item.name" :value="item.id" />
          </el-select>
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="handleSearch">搜索</el-button>
          <el-button @click="handleReset">重置</el-button>
          <el-button v-if="canAdd" type="success" @click="handleAdd">新增食堂组</el-button>
        </el-form-item>
      </el-form>
    </el-card>

    <el-card class="table-card">
      <el-table v-loading="loading" :data="tableData" border stripe>
        <el-table-column prop="name" label="分组名称" min-width="160" />
        <el-table-column prop="canteen_count" label="食堂数" width="90" align="center" />
        <el-table-column prop="add_user" label="创建人" width="100" align="center" />
        <el-table-column prop="add_time" label="创建时间" width="160" align="center" />
        <el-table-column prop="update_user" label="修改人" width="100" align="center" />
        <el-table-column prop="update_time" label="修改时间" width="160" align="center" />
        <el-table-column label="操作" width="220" align="center" fixed="right">
          <template #default="{ row }">
            <el-button v-if="canEdit" type="primary" link @click="handleEdit(row)">编辑</el-button>
            <el-button v-if="canManageCanteen" type="info" link @click="handleCanteens(row)">食堂管理</el-button>
            <el-button v-if="canDelete" type="danger" link @click="handleDelete(row)">删除</el-button>
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

    <el-dialog v-model="formDialogVisible" :title="formType === 'add' ? '新增食堂组' : '编辑食堂组'" width="500px" :close-on-click-modal="false">
      <el-form ref="formRef" :model="groupForm" :rules="formRules" label-width="100px">
        <el-form-item label="分组名称" prop="name">
          <el-input v-model="groupForm.name" maxlength="50" />
        </el-form-item>
        <el-form-item label="父分组">
          <el-select v-model="groupForm.pid" placeholder="顶级分组" clearable style="width: 100%">
            <el-option label="顶级分组" :value="0" />
            <el-option
              v-for="item in parentOptions"
              :key="item.id"
              :label="item.name"
              :value="item.id"
              :disabled="item.id === groupForm.id"
            />
          </el-select>
        </el-form-item>
        <el-form-item label="分组编码">
          <el-input v-model="groupForm.code" maxlength="30" />
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="formDialogVisible = false">取消</el-button>
        <el-button type="primary" :loading="submitLoading" @click="handleSubmit">确定</el-button>
      </template>
    </el-dialog>

    <el-dialog v-model="canteenDialogVisible" title="食堂管理" width="900px" :close-on-click-modal="false">
      <div class="canteen-header">
        <span>分组：{{ currentGroup.name }}</span>
        <el-button v-if="canManageCanteen" type="primary" size="small" @click="handleAddCanteen">添加食堂</el-button>
      </div>

      <el-table :data="canteenList" border stripe style="margin-top: 15px">
        <el-table-column prop="name" label="食堂名称" min-width="140" />
        <el-table-column prop="school_name" label="学校" min-width="140" />
        <el-table-column prop="canteen_type_text" label="食堂类型" width="100" align="center" />
        <el-table-column prop="linkman" label="联系人" width="100" />
        <el-table-column prop="mobile" label="联系电话" width="120" />
        <el-table-column prop="is_audit_text" label="账号类型" width="100" align="center">
          <template #default="{ row }">
            <el-tag :type="row.is_audit === 1 ? 'success' : 'info'">{{ row.is_audit_text }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column label="操作" width="220" align="center">
          <template #default="{ row }">
            <el-button v-if="canManageCanteen && row.is_audit === 0" type="success" link @click="handleSetAudit(row)">设为主账号</el-button>
            <el-button v-if="canManageCanteen && row.is_audit === 1" type="warning" link @click="handleRemoveAudit(row)">取消主账号</el-button>
            <el-button v-if="canManageCanteen" type="danger" link @click="handleRemoveCanteen(row)">移除</el-button>
          </template>
        </el-table-column>
      </el-table>

      <el-dialog v-model="addCanteenDialogVisible" title="添加食堂" width="500px" append-to-body>
        <el-form label-width="100px">
          <el-form-item label="选择食堂">
            <el-select v-model="addCanteenForm.canteen_id" placeholder="请选择食堂" filterable style="width: 100%">
              <el-option
                v-for="item in availableCanteens"
                :key="item.id"
                :label="`${item.school_name} - ${item.name}`"
                :value="item.id"
              />
            </el-select>
          </el-form-item>
        </el-form>
        <template #footer>
          <el-button @click="addCanteenDialogVisible = false">取消</el-button>
          <el-button type="primary" :loading="addCanteenLoading" @click="handleAddCanteenSubmit">确定</el-button>
        </template>
      </el-dialog>
    </el-dialog>
  </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { useUserStore } from '@/stores/modules/user'
import {
  getGroupList,
  getGroupDetail,
  getGroupOptions,
  createGroup,
  updateGroup,
  deleteGroup,
  getGroupCanteens,
  addCanteenToGroup,
  removeCanteenFromGroup,
  setCanteenAudit,
  removeCanteenAudit,
} from '@/api/modules/group'
import { getActiveCanteens } from '@/api/modules/canteen'

const userStore = useUserStore()
const canAdd = computed(() => userStore.checkPermission('group.add'))
const canEdit = computed(() => userStore.checkPermission('group.edit'))
const canDelete = computed(() => userStore.checkPermission('group.delete'))
const canManageCanteen = computed(() => userStore.checkPermission('group.school'))

const searchForm = reactive({ pid: null })
const loading = ref(false)
const tableData = ref([])
const parentOptions = ref([])
const pagination = reactive({ page: 1, pageSize: 20, total: 0 })

const formDialogVisible = ref(false)
const formType = ref('add')
const formRef = ref(null)
const submitLoading = ref(false)
const groupForm = reactive({ id: null, name: '', pid: 0, code: '' })
const formRules = { name: [{ required: true, message: '请输入分组名称', trigger: 'blur' }] }

const canteenDialogVisible = ref(false)
const currentGroup = ref({})
const canteenList = ref([])
const availableCanteens = ref([])
const addCanteenDialogVisible = ref(false)
const addCanteenLoading = ref(false)
const addCanteenForm = reactive({ canteen_id: null })

async function loadParentOptions() {
  const { data } = await getGroupOptions({ pid: 0 })
  parentOptions.value = data || []
}

async function fetchData() {
  loading.value = true
  try {
    const params = { page: pagination.page, page_size: pagination.pageSize }
    if (searchForm.pid) {
      params.pid = searchForm.pid
    }
    const { data } = await getGroupList(params)
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
  searchForm.pid = null
  handleSearch()
}

function handleAdd() {
  formType.value = 'add'
  Object.assign(groupForm, { id: null, name: '', pid: 0, code: '' })
  formDialogVisible.value = true
}

async function handleEdit(row) {
  formType.value = 'edit'
  const { data } = await getGroupDetail(row.id)
  Object.assign(groupForm, { id: data.id, name: data.name, pid: data.pid || 0, code: data.code || '' })
  formDialogVisible.value = true
}

async function handleSubmit() {
  await formRef.value.validate()
  submitLoading.value = true
  try {
    if (formType.value === 'add') {
      await createGroup(groupForm)
      ElMessage.success('新增成功')
    } else {
      await updateGroup(groupForm.id, groupForm)
      ElMessage.success('编辑成功')
    }
    formDialogVisible.value = false
    await loadParentOptions()
    fetchData()
  } finally {
    submitLoading.value = false
  }
}

async function handleDelete(row) {
  await ElMessageBox.confirm('确定要删除该分组吗？', '提示', { type: 'warning' })
  await deleteGroup(row.id)
  ElMessage.success('删除成功')
  await loadParentOptions()
  fetchData()
}

async function handleCanteens(row) {
  currentGroup.value = row
  const { data } = await getGroupCanteens(row.id)
  canteenList.value = data || []
  canteenDialogVisible.value = true
}

async function handleAddCanteen() {
  const { data } = await getActiveCanteens({ exclude_grouped: 1 })
  availableCanteens.value = data || []
  addCanteenForm.canteen_id = null
  addCanteenDialogVisible.value = true
}

async function handleAddCanteenSubmit() {
  if (!addCanteenForm.canteen_id) {
    ElMessage.warning('请选择食堂')
    return
  }
  addCanteenLoading.value = true
  try {
    await addCanteenToGroup(currentGroup.value.id, { canteen_id: addCanteenForm.canteen_id })
    ElMessage.success('提交成功')
    addCanteenDialogVisible.value = false
    const { data } = await getGroupCanteens(currentGroup.value.id)
    canteenList.value = data || []
    fetchData()
  } finally {
    addCanteenLoading.value = false
  }
}

async function handleRemoveCanteen(row) {
  await ElMessageBox.confirm('确定要移除该食堂吗？', '提示', { type: 'warning' })
  await removeCanteenFromGroup(currentGroup.value.id, row.id)
  ElMessage.success('删除成功')
  const { data } = await getGroupCanteens(currentGroup.value.id)
  canteenList.value = data || []
  fetchData()
}

async function handleSetAudit(row) {
  await ElMessageBox.confirm('确定要设置为该分组的主账号吗？', '提示', { type: 'warning' })
  await setCanteenAudit(currentGroup.value.id, row.id)
  ElMessage.success('设置审核员成功')
  const { data } = await getGroupCanteens(currentGroup.value.id)
  canteenList.value = data || []
}

async function handleRemoveAudit(row) {
  await ElMessageBox.confirm('确定要取消主账号吗？', '提示', { type: 'warning' })
  await removeCanteenAudit(currentGroup.value.id, row.id)
  ElMessage.success('移除审核员成功')
  const { data } = await getGroupCanteens(currentGroup.value.id)
  canteenList.value = data || []
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
  await loadParentOptions()
  fetchData()
})
</script>

<style lang="scss" scoped>
.group-list {
  .search-card { margin-bottom: 20px; }
  .el-pagination { margin-top: 20px; justify-content: flex-end; }
  .canteen-header { display: flex; justify-content: space-between; align-items: center; }
}
</style>
