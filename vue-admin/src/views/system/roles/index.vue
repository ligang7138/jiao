<template>
  <div class="system-page">
    <el-card shadow="never">
      <el-form :model="query" inline @keyup.enter="fetchData">
        <el-form-item label="部门名称">
          <el-select v-model="query.department_id" clearable placeholder="请选择" style="width: 150px">
            <el-option v-for="item in departments" :key="item.id" :label="item.name" :value="item.id" />
          </el-select>
        </el-form-item>
        <el-form-item label="岗位名称">
          <el-input v-model="query.name" clearable placeholder="岗位名称" style="width: 150px" />
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
          <el-button type="primary" @click="openForm()">新增岗位</el-button>
        </div>
        <span>共<b>{{ total }}</b>条数据</span>
      </div>

      <el-table v-loading="loading" :data="rows" border>
        <el-table-column prop="id" label="序号" width="80" />
        <el-table-column prop="name" label="岗位名称" min-width="150" />
        <el-table-column prop="department_name" label="所属部门" min-width="150" />
        <el-table-column label="状态" width="100">
          <template #default="{ row }">
            <el-link :type="row.status === 1 ? 'success' : 'info'" @click="toggleStatus(row)">
              {{ row.status === 1 ? '开启' : '停用' }}
            </el-link>
          </template>
        </el-table-column>
        <el-table-column prop="remark" label="备注" min-width="180" />
        <el-table-column label="权限管理" width="110">
          <template #default="{ row }">
            <el-button link type="primary" @click="openPrivilege(row)">编辑</el-button>
          </template>
        </el-table-column>
        <el-table-column label="操作" width="120" fixed="right">
          <template #default="{ row }">
            <el-button link type="primary" @click="openForm(row)">编辑岗位</el-button>
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

    <el-dialog v-model="formVisible" :title="form.id ? '编辑岗位' : '新增岗位'" width="460px" :close-on-click-modal="false" @closed="resetForm">
      <el-form ref="formRef" :model="form" :rules="rules" label-width="100px">
        <el-form-item label="岗位名称" prop="name">
          <el-input v-model.trim="form.name" placeholder="岗位名称" />
        </el-form-item>
        <el-form-item label="所属部门" prop="department_id">
          <el-select v-model="form.department_id" placeholder="请选择部门" style="width: 100%">
            <el-option v-for="item in departments" :key="item.id" :label="item.name" :value="item.id" />
          </el-select>
        </el-form-item>
        <el-form-item label="状态" prop="status">
          <el-select v-model="form.status" style="width: 100%">
            <el-option label="启用" :value="1" />
            <el-option label="停用" :value="0" />
          </el-select>
        </el-form-item>
        <el-form-item label="备注">
          <el-input v-model.trim="form.remark" placeholder="请输入备注" maxlength="100" />
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="formVisible = false">取消</el-button>
        <el-button type="primary" :loading="saving" @click="submitForm">提交</el-button>
      </template>
    </el-dialog>

    <el-dialog v-model="privilegeVisible" :title="`${currentRole?.name || ''}编辑权限`" width="960px" :close-on-click-modal="false">
      <div class="toolbar">
        <el-button type="primary" :loading="saving" @click="savePrivilege">保存</el-button>
        <span>已选 {{ checkedPermissionIds.length }} 项</span>
      </div>
      <el-tree
        ref="treeRef"
        v-loading="privilegeLoading"
        :data="permissionTree"
        show-checkbox
        node-key="id"
        default-expand-all
        :props="{ label: nodeLabel, children: 'children' }"
      />
    </el-dialog>
  </div>
</template>

<script setup>
import { computed, nextTick, onMounted, reactive, ref } from 'vue'
import { ElMessage } from 'element-plus'
import {
  createRole,
  getRoleDetail,
  getRoleList,
  getRolePrivilege,
  updateRole,
  updateRolePrivilege,
  updateRoleStatus,
} from '@/api/modules/role'

const loading = ref(false)
const saving = ref(false)
const rows = ref([])
const total = ref(0)
const departments = ref([])
const query = reactive({ page: 1, page_size: 20, department_id: '', name: '', status: '' })

const formRef = ref()
const formVisible = ref(false)
const form = reactive({ id: null, name: '', department_id: '', status: 1, remark: '' })
const rules = {
  name: [{ required: true, message: '请输入岗位名称', trigger: 'blur' }],
  department_id: [{ required: true, message: '请选择部门', trigger: 'change' }],
}

const treeRef = ref()
const privilegeVisible = ref(false)
const privilegeLoading = ref(false)
const currentRole = ref(null)
const permissionTree = ref([])
const checkedPermissionIds = computed(() => treeRef.value?.getCheckedKeys(true) || [])

async function fetchData() {
  loading.value = true
  try {
    const { data } = await getRoleList(query)
    rows.value = data.list || []
    total.value = data.total || 0
    departments.value = data.departments || departments.value
  } finally {
    loading.value = false
  }
}

function resetQuery() {
  Object.assign(query, { page: 1, page_size: 20, department_id: '', name: '', status: '' })
  fetchData()
}

async function openForm(row) {
  if (row?.id) {
    const { data } = await getRoleDetail(row.id)
    Object.assign(form, data)
  }
  formVisible.value = true
}

function resetForm() {
  formRef.value?.resetFields()
  Object.assign(form, { id: null, name: '', department_id: '', status: 1, remark: '' })
}

async function submitForm() {
  await formRef.value.validate()
  saving.value = true
  try {
    if (form.id) {
      await updateRole(form.id, form)
      ElMessage.success('编辑成功')
    } else {
      await createRole(form)
      ElMessage.success('新增成功')
    }
    formVisible.value = false
    fetchData()
  } finally {
    saving.value = false
  }
}

async function toggleStatus(row) {
  const nextStatus = row.status === 1 ? 0 : 1
  await updateRoleStatus(row.id, nextStatus)
  row.status = nextStatus
  ElMessage.success('设置成功')
}

async function openPrivilege(row) {
  currentRole.value = row
  privilegeVisible.value = true
  privilegeLoading.value = true
  try {
    const { data } = await getRolePrivilege(row.id)
    permissionTree.value = data.tree || []
    await nextTick()
    treeRef.value?.setCheckedKeys(data.checked_ids || [])
  } finally {
    privilegeLoading.value = false
  }
}

function nodeLabel(data) {
  return data.privilege || data.func || data.module || data.path
}

async function savePrivilege() {
  saving.value = true
  try {
    await updateRolePrivilege(currentRole.value.id, treeRef.value.getCheckedKeys(true))
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
