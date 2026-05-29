<template>
  <div class="system-page">
    <el-card shadow="never">
      <el-form :model="query" inline @keyup.enter="fetchData">
        <el-form-item label="部门名称">
          <el-select v-model="query.department_id" clearable placeholder="请选择" style="width: 150px">
            <el-option v-for="item in departments" :key="item.id" :label="item.name" :value="item.id" />
          </el-select>
        </el-form-item>
        <el-form-item label="用户名称">
          <el-input v-model="query.name" clearable placeholder="用户名称" style="width: 150px" />
        </el-form-item>
        <el-form-item label="账号搜索">
          <el-input v-model="query.username" clearable placeholder="账号搜索" style="width: 150px" />
        </el-form-item>
        <el-form-item label="状态">
          <el-select v-model="query.status" clearable placeholder="请选择" style="width: 120px">
            <el-option label="启用" :value="1" />
            <el-option label="停用" :value="0" />
          </el-select>
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="fetchData">搜索</el-button>
          <el-button @click="resetQuery">重置</el-button>
        </el-form-item>
      </el-form>

      <div class="toolbar">
        <div>
          <el-button @click="fetchData">刷新</el-button>
          <el-button type="primary" @click="openForm()">新增用户</el-button>
        </div>
        <span>共<b>{{ total }}</b>条数据</span>
      </div>

      <el-table v-loading="loading" :data="rows" border>
        <el-table-column prop="id" label="序号" width="80" />
        <el-table-column prop="username" label="登录账号" min-width="130" />
        <el-table-column prop="name" label="用户名称" min-width="110" />
        <el-table-column prop="department_name" label="所属部门" min-width="130" />
        <el-table-column prop="mobile" label="联系电话" min-width="130" />
        <el-table-column prop="add_time" label="添加时间" width="160" />
        <el-table-column label="状态" width="100">
          <template #default="{ row }">
            <el-link :type="row.status === 1 ? 'success' : 'info'" @click="toggleStatus(row)">
              {{ row.status === 1 ? '启用' : '停用' }}
            </el-link>
          </template>
        </el-table-column>
        <el-table-column label="权限管理" width="110">
          <template #default="{ row }">
            <el-button link type="primary" @click="openPrivilege(row)">编辑</el-button>
          </template>
        </el-table-column>
        <el-table-column label="操作" width="110" fixed="right">
          <template #default="{ row }">
            <el-button link type="primary" @click="openForm(row)">编辑</el-button>
          </template>
        </el-table-column>
      </el-table>

      <el-pagination
        v-model:current-page="query.page"
        v-model:page-size="query.page_size"
        class="pagination"
        layout="total, sizes, prev, pager, next, jumper"
        :total="total"
        @size-change="fetchData"
        @current-change="fetchData"
      />
    </el-card>

    <el-dialog v-model="formVisible" :title="form.id ? '编辑用户' : '新增用户'" width="460px" :close-on-click-modal="false" @closed="resetForm">
      <el-form ref="formRef" :model="form" :rules="rules" label-width="100px">
        <el-form-item v-if="form.id" label="登录账号">
          <el-input v-model="form.username" disabled />
        </el-form-item>
        <el-form-item v-else label="登陆账号" prop="username">
          <el-input v-model.trim="form.username" placeholder="登陆账号" />
        </el-form-item>
        <el-form-item label="用户名称" prop="name">
          <el-input v-model.trim="form.name" placeholder="用户名称" maxlength="50" />
        </el-form-item>
        <el-form-item label="所属部门" prop="department_id">
          <el-select v-model="form.department_id" placeholder="请选择部门" style="width: 100%">
            <el-option v-for="item in departments" :key="item.id" :label="item.name" :value="item.id" />
          </el-select>
        </el-form-item>
        <el-form-item label="联系电话" prop="mobile">
          <el-input v-model.trim="form.mobile" placeholder="联系电话" />
        </el-form-item>
        <el-form-item v-if="!form.id" label="状态" prop="status">
          <el-select v-model="form.status" style="width: 100%">
            <el-option label="启用" :value="1" />
            <el-option label="停用" :value="0" />
          </el-select>
        </el-form-item>
        <el-form-item v-if="form.id" label="密码管理">
          <el-button @click="handleResetPassword">重置密码</el-button>
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="formVisible = false">取消</el-button>
        <el-button type="primary" :loading="saving" @click="submitForm">立即提交</el-button>
      </template>
    </el-dialog>

    <el-dialog v-model="privilegeVisible" title="编辑权限" width="900px" :close-on-click-modal="false">
      <div class="toolbar">
        <el-button type="primary" :loading="saving" @click="savePrivilege">保存</el-button>
        <span>已选 {{ checkedRoleIds.length }} 项</span>
      </div>
      <el-table v-loading="privilegeLoading" :data="roleRows" border max-height="520">
        <el-table-column width="60">
          <template #header>
            <el-checkbox :model-value="allChecked" @change="toggleAllRoles" />
          </template>
          <template #default="{ row }">
            <el-checkbox v-model="checkedRoleIds" :label="row.id">&nbsp;</el-checkbox>
          </template>
        </el-table-column>
        <el-table-column prop="name" label="岗位名称" />
        <el-table-column prop="department_name" label="所属部门" />
        <el-table-column label="状态" width="100">
          <template #default="{ row }">
            <el-tag :type="row.status === 1 ? 'success' : 'info'">{{ row.status === 1 ? '开启' : '停用' }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="remark" label="备注" />
      </el-table>
    </el-dialog>
  </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import {
  createUser,
  getUserDetail,
  getUserList,
  getUserPrivilege,
  resetUserPassword,
  updateUser,
  updateUserPrivilege,
  updateUserStatus,
} from '@/api/modules/user'

const loading = ref(false)
const saving = ref(false)
const rows = ref([])
const total = ref(0)
const departments = ref([])
const query = reactive({ page: 1, page_size: 20, department_id: '', name: '', username: '', status: '' })

const formRef = ref()
const formVisible = ref(false)
const form = reactive({ id: null, username: '', name: '', department_id: '', mobile: '', remark: '', status: 1 })
const rules = {
  username: [{ required: true, message: '请输入登陆账号', trigger: 'blur' }],
  name: [{ required: true, message: '请输入用户名称', trigger: 'blur' }],
  department_id: [{ required: true, message: '请选择部门', trigger: 'change' }],
  mobile: [
    { required: true, message: '请输入正确的手机号', trigger: 'blur' },
    { pattern: /^1\d{10}$/, message: '请输入正确的手机号', trigger: 'blur' },
  ],
}

const privilegeVisible = ref(false)
const privilegeLoading = ref(false)
const currentUserId = ref(null)
const roleRows = ref([])
const checkedRoleIds = ref([])
const allChecked = computed(() => roleRows.value.length > 0 && roleRows.value.every((item) => checkedRoleIds.value.includes(item.id)))

async function fetchData() {
  loading.value = true
  try {
    const { data } = await getUserList(query)
    rows.value = data.list || []
    total.value = data.total || 0
    departments.value = data.departments || departments.value
  } finally {
    loading.value = false
  }
}

function resetQuery() {
  Object.assign(query, { page: 1, page_size: 20, department_id: '', name: '', username: '', status: '' })
  fetchData()
}

async function openForm(row) {
  if (row?.id) {
    const { data } = await getUserDetail(row.id)
    Object.assign(form, data)
  }
  formVisible.value = true
}

function resetForm() {
  formRef.value?.resetFields()
  Object.assign(form, { id: null, username: '', name: '', department_id: '', mobile: '', remark: '', status: 1 })
}

async function submitForm() {
  await formRef.value.validate()
  saving.value = true
  try {
    if (form.id) {
      await updateUser(form.id, form)
      ElMessage.success('修改成功')
    } else {
      await createUser(form)
      ElMessage.success('添加成功')
    }
    formVisible.value = false
    fetchData()
  } finally {
    saving.value = false
  }
}

async function toggleStatus(row) {
  const nextStatus = row.status === 1 ? 0 : 1
  await updateUserStatus(row.id, nextStatus)
  row.status = nextStatus
  ElMessage.success('设置成功')
}

async function handleResetPassword() {
  await ElMessageBox.confirm('确定重置密码Dxdzcg888', '重置密码', { type: 'warning' })
  await resetUserPassword(form.id)
  ElMessage.success('设置成功')
  formVisible.value = false
  fetchData()
}

async function openPrivilege(row) {
  currentUserId.value = row.id
  privilegeVisible.value = true
  privilegeLoading.value = true
  try {
    const { data } = await getUserPrivilege(row.id)
    roleRows.value = data.roles || []
    checkedRoleIds.value = data.checked_ids || []
  } finally {
    privilegeLoading.value = false
  }
}

function toggleAllRoles(value) {
  checkedRoleIds.value = value ? roleRows.value.map((item) => item.id) : []
}

async function savePrivilege() {
  saving.value = true
  try {
    await updateUserPrivilege(currentUserId.value, { post_ids: checkedRoleIds.value })
    ElMessage.success('设置成功')
    privilegeVisible.value = false
    fetchData()
  } finally {
    saving.value = false
  }
}

onMounted(fetchData)
</script>

<style lang="scss" scoped>
.system-page {
  .toolbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin: 8px 0 14px;
  }

  .pagination {
    margin-top: 16px;
    justify-content: flex-end;
  }
}
</style>
