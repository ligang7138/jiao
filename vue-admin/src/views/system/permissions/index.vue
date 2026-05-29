<template>
  <div class="system-page">
    <el-card shadow="never">
      <el-form :model="query" inline @keyup.enter="fetchData">
        <el-form-item label="模块名称">
          <el-select v-model="query.module" clearable placeholder="请选择" style="width: 150px">
            <el-option v-for="item in modules" :key="item.id" :label="item.module" :value="item.module" />
          </el-select>
        </el-form-item>
        <el-form-item label="功能名称">
          <el-input v-model="query.func" clearable placeholder="功能名称" style="width: 150px" />
        </el-form-item>
        <el-form-item label="权限名称">
          <el-input v-model="query.privilege" clearable placeholder="权限名称" style="width: 150px" />
        </el-form-item>
        <el-form-item label="级别">
          <el-select v-model="query.level" clearable placeholder="请选择" style="width: 120px">
            <el-option label="顶级" :value="1" />
            <el-option label="菜单" :value="2" />
            <el-option label="模块" :value="3" />
          </el-select>
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
          <el-button type="primary" @click="openForm()">新增权限</el-button>
        </div>
        <span>共<b>{{ total }}</b>条数据</span>
      </div>

      <el-table v-loading="loading" :data="rows" border>
        <el-table-column type="index" label="序号" width="80" />
        <el-table-column prop="module" label="模块名称" min-width="130" />
        <el-table-column prop="func" label="功能名称" min-width="130" />
        <el-table-column prop="privilege" label="权限名称" min-width="140" />
        <el-table-column prop="path" label="权限路径" min-width="160" />
        <el-table-column label="状态" width="90">
          <template #default="{ row }">
            <el-link :disabled="row.level === 1" :type="row.status === 1 ? 'success' : 'info'" @click="row.level !== 1 && toggleStatus(row)">
              {{ row.status === 1 ? '是' : '否' }}
            </el-link>
          </template>
        </el-table-column>
        <el-table-column label="操作" width="100" fixed="right">
          <template #default="{ row }">
            <el-button v-if="row.level !== 1" link type="primary" @click="openForm(row)">编辑</el-button>
            <el-button v-else disabled>编辑</el-button>
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

    <el-dialog v-model="formVisible" :title="form.id ? '修改权限' : '新增权限'" width="480px" :close-on-click-modal="false" @closed="resetForm">
      <el-form ref="formRef" :model="form" :rules="rules" label-width="100px">
        <template v-if="!form.id">
          <el-form-item label="所属模块">
            <el-select v-model="form.module" clearable placeholder="不选则新增模块" style="width: 100%" @change="loadControls">
              <el-option v-for="item in modules" :key="item.id" :label="item.module" :value="item.id" />
            </el-select>
          </el-form-item>
          <el-form-item label="所属功能">
            <el-select v-model="form.func" clearable placeholder="不选则新增功能" style="width: 100%">
              <el-option v-for="item in controls" :key="item.id" :label="item.func" :value="item.id" />
            </el-select>
          </el-form-item>
        </template>
        <el-form-item label="权限名称" prop="privilege">
          <el-input v-model.trim="form.privilege" placeholder="模块名称|功能名称|权限名称" />
        </el-form-item>
        <el-form-item label="权限路径" prop="path">
          <el-input v-model.trim="form.path" placeholder="权限路径" />
        </el-form-item>
        <el-form-item label="状态" prop="status">
          <el-select v-model="form.status" style="width: 100%">
            <el-option label="启用" :value="1" />
            <el-option label="停用" :value="0" />
          </el-select>
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="formVisible = false">取消</el-button>
        <el-button type="primary" :loading="saving" @click="submitForm">提交</el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue'
import { ElMessage } from 'element-plus'
import {
  createPermission,
  getPermissionControls,
  getPermissionDetail,
  getPermissionList,
  updatePermission,
  updatePermissionStatus,
} from '@/api/modules/permission'

const loading = ref(false)
const saving = ref(false)
const rows = ref([])
const total = ref(0)
const modules = ref([])
const controls = ref([])
const query = reactive({ page: 1, page_size: 20, module: '', func: '', privilege: '', level: '', status: '' })

const formRef = ref()
const formVisible = ref(false)
const form = reactive({ id: null, module: '', func: '', privilege: '', path: '', status: 1 })
const rules = {
  privilege: [{ required: true, message: '请输入功能名称', trigger: 'blur' }],
  path: [{ validator: validatePath, trigger: 'blur' }],
}

function validatePath(rule, value, callback) {
  if ((form.module || form.func || form.id) && !value) {
    callback(new Error('请输入权限路径'))
    return
  }
  callback()
}

async function fetchData() {
  loading.value = true
  try {
    const { data } = await getPermissionList(query)
    rows.value = data.list || []
    total.value = data.total || 0
    modules.value = data.modules || modules.value
  } finally {
    loading.value = false
  }
}

function resetQuery() {
  Object.assign(query, { page: 1, page_size: 20, module: '', func: '', privilege: '', level: '', status: '' })
  fetchData()
}

async function openForm(row) {
  if (row?.id) {
    const { data } = await getPermissionDetail(row.id)
    Object.assign(form, data)
  }
  formVisible.value = true
}

function resetForm() {
  formRef.value?.resetFields()
  controls.value = []
  Object.assign(form, { id: null, module: '', func: '', privilege: '', path: '', status: 1 })
}

async function loadControls(moduleId) {
  form.func = ''
  controls.value = []
  if (!moduleId) return
  const { data } = await getPermissionControls(moduleId)
  controls.value = data || []
}

async function submitForm() {
  await formRef.value.validate()
  saving.value = true
  try {
    if (form.id) {
      await updatePermission(form.id, form)
      ElMessage.success('编辑成功')
    } else {
      await createPermission(form)
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
  await updatePermissionStatus(row.id, nextStatus)
  row.status = nextStatus
  ElMessage.success('设置成功')
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
