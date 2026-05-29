<template>
  <div class="stat-order">
    <!-- 搜索区域 -->
    <el-card class="search-card">
      <el-form :model="searchForm" inline>
        <el-form-item label="日期类型">
          <el-select v-model="searchForm.date_type" style="width: 120px">
            <el-option label="送货日期" :value="1" />
            <el-option label="下单时间" :value="2" />
          </el-select>
        </el-form-item>
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
        <el-form-item label="学校">
          <el-select
            v-model="searchForm.school_id"
            placeholder="请选择学校"
            clearable
            filterable
            style="width: 150px"
          >
            <el-option
              v-for="item in schoolOptions"
              :key="item.id"
              :label="item.school_name"
              :value="item.id"
            />
          </el-select>
        </el-form-item>
        <el-form-item label="食堂类型">
          <el-select
            v-model="searchForm.canteen_type"
            placeholder="请选择"
            clearable
            style="width: 120px"
          >
            <el-option label="教师食堂" :value="1" />
            <el-option label="学生食堂" :value="2" />
          </el-select>
        </el-form-item>
        <el-form-item label="供应商">
          <el-select
            v-model="searchForm.supplier_id"
            placeholder="请选择供应商"
            clearable
            filterable
            style="width: 150px"
          >
            <el-option
              v-for="item in supplierOptions"
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

    <!-- 汇总统计 -->
    <el-card class="summary-card">
      <template #header>
        <span>订单汇总统计</span>
      </template>
      <el-row :gutter="20">
        <el-col :span="4">
          <div class="stat-item">
            <div class="stat-value">{{ summary.order_count || 0 }}</div>
            <div class="stat-label">订单数量</div>
          </div>
        </el-col>
        <el-col :span="4">
          <div class="stat-item">
            <div class="stat-value amount">¥{{ formatAmount(summary.total_amount) }}</div>
            <div class="stat-label">订单总额</div>
          </div>
        </el-col>
        <el-col :span="4">
          <div class="stat-item">
            <div class="stat-value amount">¥{{ formatAmount(summary.send_amount) }}</div>
            <div class="stat-label">配送金额</div>
          </div>
        </el-col>
        <el-col :span="4">
          <div class="stat-item">
            <div class="stat-value amount success">¥{{ formatAmount(summary.receive_amount) }}</div>
            <div class="stat-label">实收金额</div>
          </div>
        </el-col>
        <el-col :span="4">
          <div class="stat-item">
            <div class="stat-value amount danger">¥{{ formatAmount(summary.back_amount) }}</div>
            <div class="stat-label">退货金额</div>
          </div>
        </el-col>
      </el-row>
    </el-card>

    <!-- 食堂排名 -->
    <el-card class="table-card">
      <template #header>
        <span>食堂订单排名（TOP20）</span>
      </template>
      <el-table :data="byCanteen" border stripe>
        <el-table-column prop="school_name" label="学校" min-width="150" />
        <el-table-column prop="canteen_name" label="食堂名称" min-width="150" />
        <el-table-column prop="canteen_type" label="食堂类型" width="100" align="center">
          <template #default="{ row }">
            <el-tag :type="row.canteen_type === 1 ? 'warning' : 'success'">
              {{ row.canteen_type === 1 ? '教师食堂' : '学生食堂' }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="order_count" label="订单数量" width="100" align="center" />
        <el-table-column prop="total_amount" label="订单金额" width="120" align="right">
          <template #default="{ row }">
            <span class="amount">¥{{ formatAmount(row.total_amount) }}</span>
          </template>
        </el-table-column>
        <el-table-column prop="receive_amount" label="实收金额" width="120" align="right">
          <template #default="{ row }">
            <span class="amount success">¥{{ formatAmount(row.receive_amount) }}</span>
          </template>
        </el-table-column>
      </el-table>
    </el-card>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { Search, Refresh } from '@element-plus/icons-vue'
import { getOrderStat } from '@/api/modules/stat'
import { getActiveSchools } from '@/api/modules/school'
import { getActiveSuppliers } from '@/api/modules/supplier'

const searchForm = reactive({
  date_type: 1,
  school_id: null,
  canteen_type: null,
  supplier_id: null,
})
const dateRange = ref([])
const schoolOptions = ref([])
const supplierOptions = ref([])
const loading = ref(false)
const summary = ref({})
const byCanteen = ref([])

function formatAmount(amount) {
  if (!amount) return '0.00'
  return Number(amount).toFixed(2)
}

async function fetchOptions() {
  try {
    const [schoolRes, supplierRes] = await Promise.all([
      getActiveSchools(),
      getActiveSuppliers(),
    ])
    schoolOptions.value = schoolRes.data || []
    supplierOptions.value = supplierRes.data || []
  } catch (error) {
    console.error('获取选项失败:', error)
  }
}

async function fetchData() {
  loading.value = true
  try {
    const params = { ...searchForm }
    if (dateRange.value && dateRange.value.length === 2) {
      params.start_date = dateRange.value[0]
      params.end_date = dateRange.value[1]
    }
    const { data } = await getOrderStat(params)
    summary.value = data.summary || {}
    byCanteen.value = data.by_canteen || []
  } catch (error) {
    console.error('获取订单统计失败:', error)
  } finally {
    loading.value = false
  }
}

function handleSearch() {
  fetchData()
}

function handleReset() {
  Object.assign(searchForm, {
    date_type: 1,
    school_id: null,
    canteen_type: null,
    supplier_id: null,
  })
  dateRange.value = []
  fetchData()
}

onMounted(() => {
  fetchOptions()
  fetchData()
})
</script>

<style lang="scss" scoped>
.stat-order {
  .search-card {
    margin-bottom: 20px;
  }
  .summary-card {
    margin-bottom: 20px;
    .stat-item {
      text-align: center;
      .stat-value {
        font-size: 24px;
        font-weight: 600;
        &.amount {
          color: #409eff;
          &.success { color: #67c23a; }
          &.danger { color: #f56c6c; }
        }
      }
      .stat-label {
        color: #909399;
        margin-top: 8px;
      }
    }
  }
  .amount {
    color: #409399;
    font-weight: 600;
    &.success { color: #67c23a; }
  }
}
</style>