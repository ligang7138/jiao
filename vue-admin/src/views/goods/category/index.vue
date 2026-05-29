<template>
  <div class="category-list">
    <!-- 操作栏 -->
    <el-card class="table-card">
      <template #header>
        <div class="card-header">
          <span>分类管理</span>
          <el-button type="primary" @click="handleAdd(0)">
            <el-icon><Plus /></el-icon>
            新增顶级分类
          </el-button>
        </div>
      </template>

      <!-- 树形表格 -->
      <el-table
        v-loading="loading"
        :data="tableData"
        row-key="id"
        border
        default-expand-all
        :tree-props="{ children: 'children', hasChildren: 'hasChildren' }"
      >
        <el-table-column prop="name" label="分类名称" min-width="200" />
        <el-table-column prop="sort" label="排序" width="100" align="center" />
        <el-table-column prop="is_active" label="状态" width="100" align="center">
          <template #default="{ row }">
            <el-tag :type="row.is_active ? 'success' : 'info'">
              {{ row.is_active ? '启用' : '禁用' }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="created_at" label="创建时间" width="180" align="center" />
        <el-table-column label="操作" width="280" align="center" fixed="right">
          <template #default="{ row }">
            <el-button type="primary" link @click="handleAdd(row.id)">添加子分类</el-button>
            <el-button type="primary" link @click="handleEdit(row)">编辑</el-button>
            <el-button type="danger" link @click="handleDelete(row)">删除</el-button>
          </template>
        </el-table-column>
      </el-table>
    </el-card>

    <!-- 新增/编辑对话框 -->
    <el-dialog
      v-model="dialogVisible"
      :title="dialogTitle"
      width="500px"
      :close-on-click-modal="false"
      @closed="handleDialogClosed"
    >
      <el-form ref="formRef" :model="formData" :rules="formRules" label-width="100px">
        <el-form-item label="分类名称" prop="name">
          <el-input v-model="formData.name" placeholder="请输入分类名称" maxlength="100" />
        </el-form-item>
        <el-form-item label="父级分类" prop="parent_id">
          <el-cascader
            v-model="formData.parent_id"
            :options="parentCategoryOptions"
            :props="{ value: 'id', label: 'name', checkStrictly: true, emitPath: false }"
            placeholder="请选择父级分类（不选则为顶级分类）"
            clearable
            style="width: 100%"
          />
        </el-form-item>
        <el-form-item label="排序" prop="sort">
          <el-input-number v-model="formData.sort" :min="0" :max="9999" style="width: 100%" />
        </el-form-item>
        <el-form-item label="状态" prop="is_active">
          <el-switch v-model="formData.is_active" active-text="启用" inactive-text="禁用" />
        </el-form-item>
        <el-form-item label="分类描述" prop="description">
          <el-input v-model="formData.description" type="textarea" :rows="3" placeholder="请输入分类描述" maxlength="500" />
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
import { ElMessage, ElMessageBox } from 'element-plus'
import { Plus } from '@element-plus/icons-vue'
import { getCategoryTree, getCategoryDetail, createCategory, updateCategory, deleteCategory } from '@/api/modules/category'

// 表格数据
const loading = ref(false)
const tableData = ref([])

// 对话框
const dialogVisible = ref(false)
const dialogTitle = computed(() => (formData.id ? '编辑分类' : '新增分类'))
const formRef = ref()
const submitLoading = ref(false)

// 表单数据
const formData = reactive({
  id: null,
  name: '',
  parent_id: null,
  sort: 0,
  is_active: true,
  description: '',
})

// 表单验证规则
const formRules = {
  name: [{ required: true, message: '请输入分类名称', trigger: 'blur' }],
}

// 父级分类选项
const parentCategoryOptions = computed(() => {
  return tableData.value
})

// 获取分类树
async function fetchData() {
  loading.value = true
  try {
    const { data } = await getCategoryTree()
    tableData.value = data || []
  } catch (error) {
    console.error('获取分类树失败:', error)
  } finally {
    loading.value = false
  }
}

// 新增
function handleAdd(parentId) {
  formData.id = null
  formData.parent_id = parentId || null
  dialogVisible.value = true
}

// 编辑
async function handleEdit(row) {
  try {
    const { data } = await getCategoryDetail(row.id)
    Object.assign(formData, {
      id: data.id,
      name: data.name,
      parent_id: data.parent_id || null,
      sort: data.sort,
      is_active: data.is_active,
      description: data.description,
    })
    dialogVisible.value = true
  } catch (error) {
    ElMessage.error('获取分类详情失败')
  }
}

// 删除
async function handleDelete(row) {
  // 检查是否有子分类
  if (row.children && row.children.length > 0) {
    ElMessage.warning('该分类下存在子分类，无法删除')
    return
  }

  try {
    await ElMessageBox.confirm('确定要删除该分类吗？', '提示', {
      type: 'warning',
    })
    await deleteCategory(row.id)
    ElMessage.success('删除成功')
    fetchData()
  } catch (error) {
    if (error !== 'cancel') {
      ElMessage.error(error.message || '删除失败')
    }
  }
}

// 提交表单
async function handleSubmit() {
  try {
    await formRef.value.validate()
    submitLoading.value = true

    const data = {
      name: formData.name,
      parent_id: formData.parent_id || 0,
      sort: formData.sort,
      is_active: formData.is_active,
      description: formData.description,
    }

    if (formData.id) {
      await updateCategory(formData.id, data)
      ElMessage.success('更新成功')
    } else {
      await createCategory(data)
      ElMessage.success('创建成功')
    }
    dialogVisible.value = false
    fetchData()
  } catch (error) {
    console.error('提交失败:', error)
  } finally {
    submitLoading.value = false
  }
}

// 对话框关闭
function handleDialogClosed() {
  formRef.value?.resetFields()
  Object.assign(formData, {
    id: null,
    name: '',
    parent_id: null,
    sort: 0,
    is_active: true,
    description: '',
  })
}

// 初始化
onMounted(() => {
  fetchData()
})
</script>

<style lang="scss" scoped>
.category-list {
  .table-card {
    .card-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
  }
}
</style>
