<template>
  <div class="sidebar" :class="{ 'is-collapsed': isCollapsed }">
    <div class="sidebar-logo">
      <router-link to="/dashboard" class="logo-link">
        <el-icon :size="24" color="#fff">
          <Shop />
        </el-icon>
        <span class="logo-title" v-if="!isCollapsed">大兴教委管理系统</span>
      </router-link>
    </div>

    <div class="sidebar-home">
      <div
        class="menu-item home-item"
        :class="{ active: isHomeActive }"
        @click="goHome"
      >
        <el-icon :size="18"><HomeFilled /></el-icon>
        <span class="menu-text" v-if="!isCollapsed">首页</span>
      </div>
    </div>

    <el-scrollbar class="sidebar-menu-wrapper">
      <div class="sidebar-menu">
        <div
          v-for="menu in menuList"
          :key="menu.id"
          class="menu-group"
        >
          <div
            class="menu-title"
            :class="{ active: expandedMenu === menu.id }"
            @click="toggleMenu(menu)"
          >
            <el-icon :size="16"><List /></el-icon>
            <span class="title-text" v-if="!isCollapsed">{{ menu.module }}</span>
            <el-icon v-if="!isCollapsed" class="arrow-icon" :class="{ expanded: expandedMenu === menu.id }">
              <ArrowRight />
            </el-icon>
          </div>

          <transition name="slide">
            <div
              v-show="expandedMenu === menu.id && !isCollapsed"
              class="menu-children"
            >
              <div
                v-for="child in menu.children"
                :key="child.id"
                class="menu-item child-item"
                :class="{ active: isChildActive(child) }"
                @click="goToPage(child)"
              >
                <el-icon :size="14"><Right /></el-icon>
                <span class="menu-text">{{ child.func }}</span>
              </div>
            </div>
          </transition>
        </div>
      </div>
    </el-scrollbar>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { ElMessage } from 'element-plus'
import { Shop, HomeFilled, List, ArrowRight, Right } from '@element-plus/icons-vue'
import { useAppStore } from '@/stores/modules/app'
import { useMenuStore } from '@/stores/modules/menu'
import { resolveMenuRoute } from '@/utils/menuPathMap'

const route = useRoute()
const router = useRouter()
const appStore = useAppStore()
const menuStore = useMenuStore()

const expandedMenu = ref(null)

const isCollapsed = computed(() => !appStore.sidebarOpened)
const isHomeActive = computed(() => route.path === '/dashboard' || route.path === '/')

// 与旧系统 main.php 一致：一级 module + 二级 func（来自 system_menu）
const menuList = computed(() => menuStore.menuTree)

function toggleMenu(menu) {
  if (isCollapsed.value) return
  expandedMenu.value = expandedMenu.value === menu.id ? null : menu.id
}

function goHome() {
  router.push('/dashboard')
}

function isChildActive(child) {
  const target = resolveMenuRoute(child)
  if (!target) return false
  return route.path === target || route.path.startsWith(`${target}/`)
}

function goToPage(item) {
  const target = resolveMenuRoute(item)
  if (!target) {
    ElMessage.warning('该菜单页面尚未迁移，请联系管理员')
    return
  }

  router.push(target)
}

function syncMenuByRoute(path) {
  for (const menu of menuList.value) {
    const found = menu.children?.some((child) => {
      const childRoute = resolveMenuRoute(child)
      return childRoute === path || (childRoute && path.startsWith(childRoute + '/'))
    })

    if (found) {
      expandedMenu.value = menu.id
      return
    }
  }
}

watch(
  () => route.path,
  (path) => {
    syncMenuByRoute(path)
  },
  { immediate: true }
)

watch(
  () => menuList.value,
  (menus) => {
    if (menus.length > 0 && expandedMenu.value === null) {
      syncMenuByRoute(route.path)
      if (expandedMenu.value === null) {
        expandedMenu.value = menus[0].id
      }
    }
  },
  { immediate: true }
)
</script>

<style lang="scss" scoped>
$sidebar-bg: #222d32;
$menu-text-color: #b8c7ce;
$menu-active-text-color: #fff;
$menu-hover-bg: #2c3b41;
$theme-color: #dc3251;

.sidebar {
  height: 100%;
  width: 170px;
  background-color: $sidebar-bg;
  display: flex;
  flex-direction: column;
  transition: width 0.3s ease;
  overflow: hidden;

  &.is-collapsed {
    width: 54px;

    .logo-title,
    .menu-text,
    .title-text,
    .arrow-icon {
      display: none;
    }

    .sidebar-logo {
      padding: 0;
      justify-content: center;
    }

    .menu-title {
      justify-content: center;
      padding: 12px 0;
    }

    .home-item {
      justify-content: center;
      padding-left: 0;
    }

    .menu-children {
      display: none;
    }
  }
}

.sidebar-logo {
  height: 50px;
  display: flex;
  align-items: center;
  padding: 0 15px;
  background-color: #1a2226;
  border-bottom: 1px solid #1a2226;

  .logo-link {
    display: flex;
    align-items: center;
    text-decoration: none;
    width: 100%;
  }

  .logo-img {
    width: 32px;
    height: 32px;
    object-fit: contain;
  }

  .logo-title {
    color: $menu-active-text-color;
    font-size: 15px;
    font-weight: 600;
    margin-left: 10px;
    white-space: nowrap;
    overflow: hidden;
  }
}

.sidebar-home {
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
  padding: 8px 0;
}

.menu-item {
  display: flex;
  align-items: center;
  padding: 10px 15px;
  cursor: pointer;
  color: $menu-text-color;
  transition: all 0.2s ease;

  &:hover {
    background-color: $menu-hover-bg;
    color: $menu-active-text-color;
  }

  &.active {
    color: $menu-active-text-color;
    background-color: $theme-color;
  }

  .menu-text {
    margin-left: 10px;
    font-size: 14px;
    white-space: nowrap;
  }
}

.home-item {
  padding-left: 18px;

  &.active {
    background-color: $theme-color;
  }
}

.sidebar-menu-wrapper {
  flex: 1;
  overflow: hidden;

  :deep(.el-scrollbar__wrap) {
    overflow-x: hidden;
  }
}

.sidebar-menu {
  padding: 8px 0;
}

.menu-group {
  margin-bottom: 2px;
}

.menu-title {
  display: flex;
  align-items: center;
  padding: 12px 15px;
  cursor: pointer;
  color: $menu-text-color;
  transition: all 0.2s ease;
  user-select: none;

  &:hover {
    background-color: $menu-hover-bg;
  }

  &.active {
    color: $menu-active-text-color;
  }

  .title-text {
    margin-left: 10px;
    font-size: 14px;
    font-weight: 500;
    flex: 1;
  }

  .arrow-icon {
    transition: transform 0.3s ease;

    &.expanded {
      transform: rotate(90deg);
    }
  }
}

.menu-children {
  background-color: rgba(0, 0, 0, 0.1);
  overflow: hidden;
}

.child-item {
  padding-left: 35px;

  .menu-text {
    margin-left: 8px;
  }
}

.slide-enter-active,
.slide-leave-active {
  transition: all 0.3s ease;
  max-height: 500px;
}

.slide-enter-from,
.slide-leave-to {
  max-height: 0;
  opacity: 0;
}

:deep(::-webkit-scrollbar) {
  width: 5px;
  height: 5px;
}

:deep(::-webkit-scrollbar-track) {
  display: none;
}

:deep(::-webkit-scrollbar-thumb) {
  background: $theme-color;
  border-radius: 4px;
}
</style>
