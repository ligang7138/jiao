<template>
  <div class="tags-view">
    <el-scrollbar class="tags-view-wrapper">
      <router-link
        v-for="tag in visitedTags"
        :key="tag.path"
        :to="{ path: tag.path, query: tag.query }"
        class="tags-view-item"
        :class="{ active: isActive(tag) }"
        @contextmenu.prevent="openMenu(tag, $event)"
      >
        {{ tag.meta?.title }}
        <el-icon v-if="!isAffix(tag)" class="close-icon" @click.prevent.stop="closeTag(tag)">
          <Close />
        </el-icon>
      </router-link>
    </el-scrollbar>
  </div>
</template>

<script setup>
import { computed, watch, ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { Close } from '@element-plus/icons-vue'
import { useUserStore } from '@/stores/modules/user'

const route = useRoute()
const router = useRouter()
const userStore = useUserStore()

const visitedTags = ref([])

const routes = computed(() => userStore.routes)

function isActive(tag) {
  return tag.path === route.path
}

function isAffix(tag) {
  return tag.meta && tag.meta.affix
}

function resolveRoutePath(basePath, routePath) {
  if (routePath.startsWith('/')) {
    return routePath
  }
  const base = basePath.endsWith('/') ? basePath.slice(0, -1) : basePath
  return `${base}/${routePath}`.replace(/\/+/g, '/')
}

function addTags() {
  if (!route.name) {
    return
  }

  const exists = visitedTags.value.some(
    (v) => v.path === route.path || v.name === route.name
  )
  if (exists) {
    return
  }

  visitedTags.value.push({
    path: route.path,
    name: route.name,
    query: route.query,
    meta: { ...route.meta },
  })
}

function closeTag(tag) {
  visitedTags.value = visitedTags.value.filter((v) => v.path !== tag.path)
  if (isActive(tag)) {
    toLastTag()
  }
}

function toLastTag() {
  const lastTag = visitedTags.value[visitedTags.value.length - 1]
  if (lastTag) {
    router.push(lastTag.path)
  } else {
    router.push('/')
  }
}

function initTags() {
  function filterAffixTags(routes, basePath = '') {
    let tags = []
    routes.forEach((route) => {
      if (route.meta && route.meta.affix) {
        const path = resolveRoutePath(basePath, route.path)
        tags.push({
          path,
          name: route.name,
          meta: { ...route.meta },
        })
      }
      if (route.children) {
        const childBase = route.path === '/' ? '' : resolveRoutePath(basePath, route.path)
        tags = [...tags, ...filterAffixTags(route.children, childBase)]
      }
    })
    return tags
  }

  const affixTags = filterAffixTags(routes.value)
  visitedTags.value = affixTags
}

watch(
  () => route.path,
  () => {
    addTags()
  }
)

onMounted(() => {
  initTags()
  addTags()
})
</script>

<style lang="scss" scoped>
// 主题色 - 与旧系统一致
$theme-color: #dc3251;

.tags-view {
  height: var(--tagsview-height);
  background: #fff;
  border-bottom: 1px solid #e3e3e3;
  padding-left: 10px;
}

.tags-view-wrapper {
  .tags-view-item {
    display: inline-flex;
    align-items: center;
    height: 26px;
    line-height: 26px;
    padding: 0 12px;
    margin: 4px 5px 0 0;
    font-size: 13px;
    color: #666666;
    background: #fff;
    border-radius: 2px;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.2s ease;

    &:hover:not(.active) {
      color: $theme-color;
    }

    &.active {
      color: $theme-color;
      font-weight: bold;

      // 底部红色指示线 - 与旧系统一致
      &::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 2px;
        background-color: $theme-color;
      }
    }

    .close-icon {
      margin-left: 8px;
      font-size: 14px;
      color: #aaaaaa;
      transition: all 0.2s ease;

      &:hover {
        color: red;
        font-weight: bold;
      }
    }
  }
}
</style>
