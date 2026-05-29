<template>
  <div class="dashboard-container">
    <el-row :gutter="20">
      <!-- 统计卡片 -->
      <el-col :span="6" v-for="item in statistics" :key="item.key">
        <el-card class="stat-card" shadow="hover">
          <div class="stat-content">
            <div class="stat-icon" :style="{ backgroundColor: item.color }">
              <el-icon :size="32">
                <component :is="item.icon" />
              </el-icon>
            </div>
            <div class="stat-info">
              <div class="stat-value">{{ item.value }}</div>
              <div class="stat-label">{{ item.label }}</div>
            </div>
          </div>
        </el-card>
      </el-col>
    </el-row>

    <!-- 图表区域 -->
    <el-row :gutter="20" class="chart-row">
      <el-col :span="12">
        <el-card>
          <template #header>
            <span>采购趋势</span>
          </template>
          <div ref="lineChartRef" class="chart"></div>
        </el-card>
      </el-col>
      <el-col :span="12">
        <el-card>
          <template #header>
            <span>供应商排名</span>
          </template>
          <div ref="barChartRef" class="chart"></div>
        </el-card>
      </el-col>
    </el-row>

    <!-- 快捷操作 -->
    <el-card class="quick-actions">
      <template #header>
        <span>快捷操作</span>
      </template>
      <el-row :gutter="20">
        <el-col :span="4" v-for="action in quickActions" :key="action.path">
          <div class="action-item" @click="router.push(action.path)">
            <el-icon :size="28" :color="action.color">
              <component :is="action.icon" />
            </el-icon>
            <span>{{ action.title }}</span>
          </div>
        </el-col>
      </el-row>
    </el-card>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import * as echarts from 'echarts'
import { Goods, Document, Shop, School, User, Setting } from '@element-plus/icons-vue'

const router = useRouter()

const lineChartRef = ref()
const barChartRef = ref()

const statistics = ref([
  { key: 'orders', label: '今日订单', value: '128', icon: 'Document', color: '#409EFF' },
  { key: 'goods', label: '商品总数', value: '1,256', icon: 'Goods', color: '#67C23A' },
  { key: 'suppliers', label: '供应商数', value: '45', icon: 'Shop', color: '#E6A23C' },
  { key: 'schools', label: '学校数量', value: '89', icon: 'School', color: '#F56C6C' },
])

const quickActions = [
  { title: '商品管理', path: '/goods/list', icon: 'Goods', color: '#409EFF' },
  { title: '订单管理', path: '/orders/list', icon: 'Document', color: '#67C23A' },
  { title: '供应商管理', path: '/suppliers/list', icon: 'Shop', color: '#E6A23C' },
  { title: '学校管理', path: '/schools/list', icon: 'School', color: '#F56C6C' },
  { title: '用户管理', path: '/system/users', icon: 'User', color: '#909399' },
  { title: '系统设置', path: '/system/permissions', icon: 'Setting', color: '#606266' },
]

onMounted(() => {
  initLineChart()
  initBarChart()
})

function initLineChart() {
  if (!lineChartRef.value) return
  const chart = echarts.init(lineChartRef.value)
  const option = {
    tooltip: {
      trigger: 'axis',
    },
    xAxis: {
      type: 'category',
      data: ['周一', '周二', '周三', '周四', '周五', '周六', '周日'],
    },
    yAxis: {
      type: 'value',
    },
    series: [
      {
        name: '采购金额',
        type: 'line',
        smooth: true,
        data: [820, 932, 901, 934, 1290, 1330, 1320],
        areaStyle: {
          color: 'rgba(64, 158, 255, 0.3)',
        },
        lineStyle: {
          color: '#409EFF',
        },
        itemStyle: {
          color: '#409EFF',
        },
      },
    ],
  }
  chart.setOption(option)
  window.addEventListener('resize', () => chart.resize())
}

function initBarChart() {
  if (!barChartRef.value) return
  const chart = echarts.init(barChartRef.value)
  const option = {
    tooltip: {
      trigger: 'axis',
      axisPointer: {
        type: 'shadow',
      },
    },
    xAxis: {
      type: 'value',
    },
    yAxis: {
      type: 'category',
      data: ['供应商A', '供应商B', '供应商C', '供应商D', '供应商E'],
    },
    series: [
      {
        name: '合作学校数',
        type: 'bar',
        data: [15, 12, 10, 8, 6],
        itemStyle: {
          color: '#67C23A',
        },
      },
    ],
  }
  chart.setOption(option)
  window.addEventListener('resize', () => chart.resize())
}
</script>

<style lang="scss" scoped>
.dashboard-container {
  padding: 20px;
}

.stat-card {
  margin-bottom: 20px;

  .stat-content {
    display: flex;
    align-items: center;
    padding: 10px;
  }

  .stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    margin-right: 15px;
  }

  .stat-info {
    flex: 1;
  }

  .stat-value {
    font-size: 28px;
    font-weight: 600;
    color: #303133;
  }

  .stat-label {
    font-size: 14px;
    color: #909399;
    margin-top: 5px;
  }
}

.chart-row {
  margin-bottom: 20px;
}

.chart {
  height: 300px;
}

.quick-actions {
  .action-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 20px;
    cursor: pointer;
    border-radius: 8px;
    transition: all 0.3s;

    &:hover {
      background-color: #f5f7fa;
    }

    span {
      margin-top: 10px;
      font-size: 14px;
      color: #606266;
    }
  }
}
</style>
