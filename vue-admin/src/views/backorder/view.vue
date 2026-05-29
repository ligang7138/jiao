<template>
  <div class="backorder-view">
    <el-card v-loading="loading" class="info-card">
      <template #header>
        <div class="card-header">
          <span>退货单详情</span>
          <div class="header-actions">
            <el-button @click="handleBack">
              <el-icon><Back /></el-icon>
              返回列表
            </el-button>
            <el-button
              v-if="detail.status === 3"
              type="warning"
              @click="handleCancel"
            >
              取消退货单
            </el-button>
            <el-button
              v-if="detail.status === 3"
              type="danger"
              @click="showRejectDialog = true"
            >
              审核拒绝
            </el-button>
            <el-button
              v-if="detail.status === 3"
              type="success"
              @click="showAuditDialog = true"
            >
              审核通过
            </el-button>
          </div>
        </div>
      </template>

      <!-- 基本信息 -->
      <el-descriptions title="基本信息" :column="3" border>
        <el-descriptions-item label="退货单号">{{ detail.backorder_sn }}</el-descriptions-item>
        <el-descriptions-item label="订单编号">{{ detail.order_sn }}</el-descriptions-item>
        <el-descriptions-item label="状态">
          <el-tag :type="getStatusType(detail.status)">{{ detail.status_text }}</el-tag>
        </el-descriptions-item>
        <el-descriptions-item label="食堂名称">{{ detail.canteen_name }}</el-descriptions-item>
        <el-descriptions-item label="学校">{{ detail.school_name }}</el-descriptions-item>
        <el-descriptions-item label="供应商">{{ detail.supplier_name }}</el-descriptions-item>
        <el-descriptions-item label="退货类型">{{ detail.type_text }}</el-descriptions-item>
        <el-descriptions-item label="退货金额">
          <span class="amount">¥{{ detail.total_amount }}</span>
        </el-descriptions-item>
        <el-descriptions-item label="商品数量">{{ detail.goods_count || 0 }} 种</el-descriptions-item>
        <el-descriptions-item label="申请人">{{ detail.apply_user_name || '-' }}</el-descriptions-item>
        <el-descriptions-item label="申请时间">{{ detail.created_at }}</el-descriptions-item>
        <el-descriptions-item label="处理时间">{{ detail.processed_at || '-' }}</el-descriptions-item>
      </el-descriptions>

      <!-- 退货原因 -->
      <el-descriptions title="退货信息" :column="1" border style="margin-top: 20px">
        <el-descriptions-item label="退货原因">{{ detail.reason }}</el-descriptions-item>
        <el-descriptions-item v-if="detail.solution" label="解决方案">{{ detail.solution }}</el-descriptions-item>
        <el-descriptions-item v-if="detail.remark" label="备注">{{ detail.remark }}</el-descriptions-item>
      </el-descriptions>

      <!-- 退货商品明细 -->
      <div class="goods-section">
        <h4>退货商品明细</h4>
        <el-table :data="detail.goods" border stripe>
          <el-table-column prop="goods_name" label="商品名称" min-width="150" />
          <el-table-column prop="category_name" label="分类" width="100" />
          <el-table-column prop="unit" label="单位" width="80" align="center" />
          <el-table-column prop="spec" label="规格" width="100" />
          <el-table-column prop="price" label="单价" width="100" align="right">
            <template #default="{ row }">¥{{ row.price }}</template>
          </el-table-column>
          <el-table-column prop="order_quantity" label="订购数量" width="100" align="center" />
          <el-table-column prop="quantity" label="退货数量" width="100" align="center">
            <template #default="{ row }">
              <span class="quantity">{{ row.quantity }}</span>
            </template>
          </el-table-column>
          <el-table-column prop="amount" label="退货金额" width="100" align="right">
            <template #default="{ row }">
              <span class="amount">¥{{ row.amount }}</span>
            </template>
          </el-table-column>
        </el-table>
        <div class="total-info">
          <span>退货商品数量：{{ detail.goods?.length || 0 }} 种</span>
          <span>退货总金额：<span class="amount">¥{{ detail.total_amount }}</span></span>
        </div>
      </div>

      <!-- 处理记录 -->
      <div v-if="detail.logs && detail.logs.length > 0" class="logs-section">
        <h4>处理记录</h4>
        <el-timeline>
          <el-timeline-item
            v-for="log in detail.logs"
            :key="log.id"
            :timestamp="log.created_at"
            placement="top"
          >
            <el-card>
              <h4>{{ log.action_text }}</h4>
              <p>操作人：{{ log.operator_name }}</p>
              <p v-if="log.remark">备注：{{ log.remark }}</p>
            </el-card>
          </el-timeline-item>
        </el-timeline>
      </div>
    </el-card>

    <!-- 审核通过对话框 -->
    <el-dialog
      v-model="showAuditDialog"
      title="审核通过"
      width="500px"
      :close-on-click-modal="false"
    >
      <el-form :model="auditForm" label-width="100px">
        <el-form-item label="解决方案" required>
          <el-radio-group v-model="auditForm.solution_type">
            <el-radio :value="1">退款</el-radio>
            <el-radio :value="2">换货</el-radio>
            <el-radio :value="3">补发</el-radio>
          </el-radio-group>
        </el-form-item>
        <el-form-item label="处理说明">
          <el-input
            v-model="auditForm.remark"
            type="textarea"
            :rows="4"
            placeholder="请输入处理说明（选填）"
            maxlength="500"
            show-word-limit
          />
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="showAuditDialog = false">取消</el-button>
        <el-button type="primary" :loading="submitLoading" @click="handleAudit">
          确定通过
        </el-button>
      </template>
    </el-dialog>

    <!-- 审核拒绝对话框 -->
    <el-dialog
      v-model="showRejectDialog"
      title="审核拒绝"
      width="500px"
      :close-on-click-modal="false"
    >
      <el-form :model="rejectForm" label-width="100px">
        <el-form-item label="拒绝原因" required>
          <el-input
            v-model="rejectForm.remark"
            type="textarea"
            :rows="4"
            placeholder="请输入拒绝原因"
            maxlength="500"
            show-word-limit
          />
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="showRejectDialog = false">取消</el-button>
        <el-button type="danger" :loading="submitLoading" @click="handleReject">
          确定拒绝
        </el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { ElMessage, ElMessageBox } from 'element-plus'
import { Back } from '@element-plus/icons-vue'
import {
  getBackorderDetail,
  auditBackorder,
  rejectBackorder,
  cancelBackorder,
} from '@/api/modules/backorder'

const router = useRouter()
const route = useRoute()

const loading = ref(false)
const detail = ref({})

const showAuditDialog = ref(false)
const showRejectDialog = ref(false)
const submitLoading = ref(false)

const auditForm = reactive({
  solution_type: 1,
  remark: '',
})

const rejectForm = reactive({
  remark: '',
})

// 获取状态类型
function getStatusType(status) {
  const map = {
    1: 'info',      // 取消
    2: 'danger',    // 审核拒绝
    3: 'warning',   // 待审核
    4: 'success',   // 通过
  }
  return map[status] || 'info'
}

// 获取详情
async function fetchDetail() {
  const id = route.params.id
  if (!id) {
    ElMessage.error('退货单ID不存在')
    router.back()
    return
  }

  loading.value = true
  try {
    const { data } = await getBackorderDetail(id)
    detail.value = data
  } catch (error) {
    ElMessage.error('获取退货单详情失败')
    router.back()
  } finally {
    loading.value = false
  }
}

// 返回列表
function handleBack() {
  router.push('/backorder')
}

// 取消退货单
async function handleCancel() {
  try {
    await ElMessageBox.confirm('确定要取消该退货单吗？', '提示', {
      type: 'warning',
    })
    await cancelBackorder(detail.value.id)
    ElMessage.success('退货单已取消')
    fetchDetail()
  } catch (error) {
    if (error !== 'cancel') {
      ElMessage.error(error.message || '取消失败')
    }
  }
}

// 审核通过
async function handleAudit() {
  try {
    await ElMessageBox.confirm('确定审核通过该退货单吗？', '提示', {
      type: 'warning',
    })
    submitLoading.value = true
    await auditBackorder(detail.value.id, {
      solution_type: auditForm.solution_type,
      remark: auditForm.remark,
    })
    ElMessage.success('审核通过')
    showAuditDialog.value = false
    fetchDetail()
  } catch (error) {
    if (error !== 'cancel') {
      ElMessage.error(error.message || '审核失败')
    }
  } finally {
    submitLoading.value = false
  }
}

// 审核拒绝
async function handleReject() {
  if (!rejectForm.remark) {
    ElMessage.warning('请输入拒绝原因')
    return
  }
  try {
    await ElMessageBox.confirm('确定拒绝该退货单吗？', '提示', {
      type: 'warning',
    })
    submitLoading.value = true
    await rejectBackorder(detail.value.id, {
      remark: rejectForm.remark,
    })
    ElMessage.success('已拒绝')
    showRejectDialog.value = false
    fetchDetail()
  } catch (error) {
    if (error !== 'cancel') {
      ElMessage.error(error.message || '操作失败')
    }
  } finally {
    submitLoading.value = false
  }
}

// 初始化
onMounted(() => {
  fetchDetail()
})
</script>

<style lang="scss" scoped>
.backorder-view {
  .info-card {
    .card-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
  }

  .amount {
    color: #f56c6c;
    font-weight: 600;
  }

  .quantity {
    color: #e6a23c;
    font-weight: 600;
  }

  .goods-section {
    margin-top: 20px;

    h4 {
      margin-bottom: 15px;
      padding-left: 10px;
      border-left: 3px solid #409eff;
    }

    .total-info {
      margin-top: 15px;
      padding: 10px 20px;
      background: #f5f7fa;
      border-radius: 4px;
      display: flex;
      justify-content: space-between;
      font-size: 14px;
    }
  }

  .logs-section {
    margin-top: 20px;

    h4 {
      margin-bottom: 15px;
      padding-left: 10px;
      border-left: 3px solid #409eff;
    }
  }
}
</style>
