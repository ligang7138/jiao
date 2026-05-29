<template>
  <div class="complaint-list">
    <!-- 搜索区域 -->
    <el-card class="search-card">
      <el-form :model="searchForm" inline>
        <el-form-item label="日期范围">
          <el-date-picker
            v-model="dateRange"
            type="daterange"
            range-separator="-"
            start-placeholder="开始日期"
            end-placeholder="结束日期"
            value-format="YYYY-MM-DD"
            style="width: 240px"
          />
        </el-form-item>
        <el-form-item label="食堂名称">
          <el-input
            v-model="searchForm.canteen_name"
            placeholder="请输入食堂名称"
            clearable
            @keyup.enter="handleSearch"
          />
        </el-form-item>
        <el-form-item label="处理状态">
          <el-select
            v-model="searchForm.process_status"
            placeholder="请选择状态"
            clearable
            style="width: 120px"
          >
            <el-option label="未处理" :value="0" />
            <el-option label="已处理" :value="1" />
          </el-select>
        </el-form-item>
        <el-form-item label="审阅状态">
          <el-select
            v-model="searchForm.review_status"
            placeholder="请选择状态"
            clearable
            style="width: 120px"
          >
            <el-option label="未审阅" :value="0" />
            <el-option label="已审阅" :value="1" />
          </el-select>
        </el-form-item>
        <el-form-item label="投诉类型">
          <el-select
            v-model="searchForm.type_id"
            placeholder="请选择类型"
            clearable
            style="width: 150px"
          >
            <el-option
              v-for="item in typeOptions"
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

    <!-- 表格区域 -->
    <el-card class="table-card">
      <template #header>
        <div class="card-header">
          <span>投诉列表</span>
        </div>
      </template>

      <el-table v-loading="loading" :data="tableData" border stripe>
        <el-table-column prop="id" label="ID" width="80" align="center" />
        <el-table-column prop="order_sn" label="订单编号" width="180" />
        <el-table-column prop="canteen_name" label="食堂名称" min-width="150" show-overflow-tooltip />
        <el-table-column prop="supp_name" label="供应商" min-width="150" show-overflow-tooltip />
        <el-table-column prop="type_name" label="投诉类型" width="120" align="center" />
        <el-table-column prop="content" label="投诉内容" min-width="200" show-overflow-tooltip />
        <el-table-column prop="process_status" label="处理状态" width="100" align="center">
          <template #default="{ row }">
            <el-tag :type="row.process_status === 1 ? 'success' : 'warning'">
              {{ row.process_status_text }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="review_status" label="审阅状态" width="100" align="center">
          <template #default="{ row }">
            <el-tag :type="row.review_status === 1 ? 'success' : 'info'">
              {{ row.review_status_text }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="add_time" label="投诉时间" width="180" align="center" />
        <el-table-column label="操作" width="150" align="center" fixed="right">
          <template #default="{ row }">
            <el-button type="primary" link @click="handleView(row)">详情</el-button>
            <el-button
              v-if="row.process_status === 0"
              type="success"
              link
              @click="handleProcess(row)"
            >
              处理
            </el-button>
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

    <!-- 详情对话框 -->
    <el-dialog
      v-model="detailDialogVisible"
      title="投诉详情"
      width="700px"
      :close-on-click-modal="false"
    >
      <el-descriptions :column="2" border>
        <el-descriptions-item label="订单编号">{{ detail.order_sn }}</el-descriptions-item>
        <el-descriptions-item label="送货日期">{{ detail.send_date }}</el-descriptions-item>
        <el-descriptions-item label="食堂名称">{{ detail.canteen_name }}</el-descriptions-item>
        <el-descriptions-item label="供应商">{{ detail.supp_name }}</el-descriptions-item>
        <el-descriptions-item label="投诉类型">{{ detail.type_name }}</el-descriptions-item>
        <el-descriptions-item label="投诉时间">{{ detail.add_time }}</el-descriptions-item>
        <el-descriptions-item label="处理状态">
          <el-tag :type="detail.process_status === 1 ? 'success' : 'warning'">
            {{ detail.process_status_text }}
          </el-tag>
        </el-descriptions-item>
        <el-descriptions-item label="审阅状态">
          <el-tag :type="detail.review_status === 1 ? 'success' : 'info'">
            {{ detail.review_status_text }}
          </el-tag>
        </el-descriptions-item>
      </el-descriptions>

      <el-divider content-position="left">投诉内容</el-divider>
      <el-input
        type="textarea"
        :rows="3"
        :model-value="detail.content"
        readonly
      />

      <el-divider content-position="left">投诉照片</el-divider>
      <el-image
        v-for="(img, index) in detail.logo"
        :key="index"
        :src="img"
        :preview-src-list="detail.logo"
        style="width: 100px; height: 100px; margin-right: 10px"
        fit="cover"
      />

      <div v-if="detail.process_status === 1" style="margin-top: 20px">
        <el-divider content-position="left">处理信息</el-divider>
        <el-descriptions :column="2" border>
          <el-descriptions-item label="处理人">{{ detail.process_user }}</el-descriptions-item>
          <el-descriptions-item label="处理时间">{{ detail.process_time }}</el-descriptions-item>
          <el-descriptions-item label="处理备注" :span="2">{{ detail.process_remark || '-' }}</el-descriptions-item>
        </el-descriptions>
      </div>

      <template #footer>
        <el-button @click="detailDialogVisible = false">关闭</el-button>
      </template>
    </el-dialog>

    <!-- 处理对话框 -->
    <el-dialog
      v-model="processDialogVisible"
      title="处理投诉"
      width="500px"
      :close-on-click-modal="false"
    >
      <el-form :model="processForm" label-width="100px">
        <el-form-item label="处理备注">
          <el-input
            v-model="processForm.remark"
            type="textarea"
            :rows="3"
            placeholder="请输入处理备注"
            maxlength="500"
          />
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="processDialogVisible = false">取消</el-button>
        <el-button type="primary" :loading="processLoading" @click="handleProcessSubmit">
          确认处理
        </el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { Search, Refresh } from '@element-plus/icons-vue'
import {
  getComplaintList,
  getComplaintDetail,
  processComplaint,
  getComplaintTypeList,
} from '@/api/modules/complaint'

const searchForm = reactive({
  canteen_name: '',
  process_status: null,
  review_status: null,
  type_id: null,
})
const dateRange = ref([])
const typeOptions = ref([])
const loading = ref(false)
const tableData = ref([])
const pagination = reactive({
  page: 1,
  pageSize: 20,
  total: 0,
})

const detailDialogVisible = ref(false)
const detail = ref({})
const processDialogVisible = ref(false)
const processLoading = ref(false)
const processForm = reactive({
  remark: '',
})

async function fetchTypeOptions() {
  try {
    const { data } = await getComplaintTypeList({ status: 1, page_size: 100 })
    typeOptions.value = data.list || []
  } catch (error) {
    console.error('获取投诉类型失败:', error)
  }
}

async function fetchData() {
  loading.value = true
  try {
    const params = {
      ...searchForm,
      page: pagination.page,
      page_size: pagination.pageSize,
    }
    if (dateRange.value && dateRange.value.length === 2) {
      params.start_date = dateRange.value[0]
      params.end_date = dateRange.value[1]
    }
    const { data } = await getComplaintList(params)
    tableData.value = data.list || []
    pagination.total = data.total || 0
  } catch (error) {
    console.error('获取投诉列表失败:', error)
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
    canteen_name: '',
    process_status: null,
    review_status: null,
    type_id: null,
  })
  dateRange.value = []
  handleSearch()
}

async function handleView(row) {
  try {
    const { data } = await getComplaintDetail(row.id)
    detail.value = data
    detailDialogVisible.value = true
  } catch (error) {
    ElMessage.error('获取投诉详情失败')
  }
}

function handleProcess(row) {
  detail.value = { id: row.id }
  processForm.remark = ''
  processDialogVisible.value = true
}

async function handleProcessSubmit() {
  try {
    await ElMessageBox.confirm('确定处理该投诉吗？', '提示', { type: 'warning' })
    processLoading.value = true
    await processComplaint(detail.value.id, { remark: processForm.remark })
    ElMessage.success('处理成功')
    processDialogVisible.value = false
    fetchData()
  } catch (error) {
    if (error !== 'cancel') {
      ElMessage.error(error.message || '处理失败')
    }
  } finally {
    processLoading.value = false
  }
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
  fetchTypeOptions()
  fetchData()
})
</script>

<style lang="scss" scoped>
.complaint-list {
  .search-card {
    margin-bottom: 20px;
  }
  .el-pagination {
    margin-top: 20px;
    justify-content: flex-end;
  }
}
</style>