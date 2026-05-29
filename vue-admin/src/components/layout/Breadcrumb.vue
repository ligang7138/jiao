<template>
  <el-breadcrumb class="breadcrumb" separator="/">
    <transition-group name="breadcrumb">
      <el-breadcrumb-item v-for="(item, index) in breadcrumbs" :key="item.path">
        <span v-if="index === breadcrumbs.length - 1 || !item.redirect" class="no-redirect">
          {{ item.meta?.title }}
        </span>
        <router-link v-else :to="item.redirect || item.path" class="redirect">
          {{ item.meta?.title }}
        </router-link>
      </el-breadcrumb-item>
    </transition-group>
  </el-breadcrumb>
</template>

<script setup>
import { ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'

const route = useRoute()
const router = useRouter()
const breadcrumbs = ref([])

function getBreadcrumbs() {
  const matched = route.matched.filter((item) => item.meta && item.meta.title)
  const first = matched[0]
  const isDashboard =
    route.path === '/dashboard' ||
    route.path === '/' ||
    route.name === 'Dashboard'

  if (first && first.path !== '/' && !isDashboard) {
    matched.unshift({ path: '/dashboard', meta: { title: '首页' } })
  }

  breadcrumbs.value = matched
}

watch(
  () => route.path,
  () => {
    getBreadcrumbs()
  },
  { immediate: true }
)
</script>

<style lang="scss" scoped>
.breadcrumb {
  font-size: 14px;

  .no-redirect {
    color: #97a8be;
    cursor: text;
  }

  .redirect {
    color: #333;

    &:hover {
      color: #409eff;
    }
  }
}
</style>
