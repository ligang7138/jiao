<template>
  <div class="goods-history-price">
    <el-card>
      <template #header>
        <div class="card-header">
          <span>历史价格 - {{ goodsName }}</span>
          <el-button @click="goBack">返回</el-button>
        </div>
      </template>

      <el-empty v-if="!loading && list.length === 0" description="暂无历史价格数据" />

      <el-table v-else :data="list" v-loading="loading" border>
        <el-table-column prop="price" label="价格" />
        <el-table-column prop="created_at" label="更新时间" />
      </el-table>
    </el-card>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { getGoodsHistoryPrice } from '@/api/modules/goods'

const route = useRoute()
const router = useRouter()

const goodsName = ref('')
const list = ref([])
const loading = ref(false)

const goBack = () => {
  router.push('/goods/list')
}

const fetchData = async () => {
  const id = route.params.id
  if (!id) return

  loading.value = true
  try {
    const res = await getGoodsHistoryPrice(id)
    list.value = res.data?.list || []
  } catch (error) {
    console.error('获取历史价格失败:', error)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchData()
})
</script>

<style scoped>
.goods-history-price {
  padding: 20px;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}
</style>
