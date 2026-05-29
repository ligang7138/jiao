<template>
  <div class="default-layout">
    <!-- 侧边栏 -->
    <Sidebar class="sidebar-container" />

    <!-- 主内容区 -->
    <div class="main-container" :class="{ 'sidebar-collapsed': isCollapsed }">
      <!-- 头部 -->
      <Header />

      <!-- 标签页 -->
      <TagsView />

      <!-- 内容区 -->
      <AppMain />
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useAppStore } from '@/stores/modules/app'
import Sidebar from '@/components/layout/Sidebar.vue'
import Header from '@/components/layout/Header.vue'
import TagsView from '@/components/layout/TagsView.vue'
import AppMain from '@/components/layout/AppMain.vue'

const appStore = useAppStore()

const isCollapsed = computed(() => !appStore.sidebarOpened)
</script>

<style lang="scss" scoped>
// 主题色 - 与旧系统一致
$theme-color: #dc3251;
$sidebar-bg: #222d32;

.default-layout {
  display: flex;
  height: 100vh;
  width: 100%;
  overflow: hidden;
}

.sidebar-container {
  width: var(--sidebar-width);
  height: 100%;
  position: fixed;
  left: 0;
  top: 0;
  z-index: 1001;
  transition: width 0.3s ease;
  background-color: $sidebar-bg;
  overflow: hidden;
}

.main-container {
  margin-left: var(--sidebar-width);
  flex: 1;
  display: flex;
  flex-direction: column;
  min-height: 100%;
  transition: margin-left 0.3s ease;
  position: relative;
  background-color: rgba(247, 249, 252, 1);
  overflow: hidden;

  &.sidebar-collapsed {
    margin-left: var(--sidebar-collapsed-width);
  }
}
</style>
