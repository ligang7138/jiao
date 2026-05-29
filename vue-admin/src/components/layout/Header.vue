<template>
  <div class="header">
    <!-- Logo 区域 - 与旧系统一致 -->
    <div class="header-logo">
      <span class="logo-text">大兴教委管理系统</span>
    </div>

    <!-- 右侧操作区 -->
    <div class="header-right">
      <!-- 折叠按钮 -->
      <div class="header-item hamburger" @click="toggleSidebar">
        <el-icon :size="18">
          <Fold v-if="sidebarOpened" />
          <Expand v-else />
        </el-icon>
      </div>

      <!-- 全屏 -->
      <div class="header-item" @click="toggleFullscreen">
        <el-icon :size="16">
          <FullScreen />
        </el-icon>
      </div>

      <!-- 用户名 -->
      <span class="username">{{ name }}</span>

      <!-- 修改密码 -->
      <span class="header-link" @click="changePassword">修改密码</span>

      <!-- 退出 -->
      <span class="header-link logout" @click="handleLogout">退出</span>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import { ElMessageBox } from 'element-plus'
import { Fold, Expand, FullScreen } from '@element-plus/icons-vue'
import { useAppStore } from '@/stores/modules/app'
import { useUserStore } from '@/stores/modules/user'

const router = useRouter()
const appStore = useAppStore()
const userStore = useUserStore()

const sidebarOpened = computed(() => appStore.sidebarOpened)
const name = computed(() => userStore.name)

function toggleSidebar() {
  appStore.toggleSidebar()
}

function toggleFullscreen() {
  if (!document.fullscreenElement) {
    document.documentElement.requestFullscreen()
  } else {
    document.exitFullscreen()
  }
}

function changePassword() {
  router.push('/password')
}

function handleLogout() {
  ElMessageBox.confirm('确定要退出登录吗？', '提示', {
    confirmButtonText: '确定',
    cancelButtonText: '取消',
    type: 'warning',
  }).then(async () => {
    await userStore.logout()
    router.push('/login')
  })
}
</script>

<style lang="scss" scoped>
// 主题色 - 与旧系统一致
$theme-color: #dc3251;
$header-bg: linear-gradient(to right, #e74c3c, #c0392b);

.header {
  height: var(--header-height);
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0;
  background: $header-bg;
  color: #fff;
}

// Logo 区域
.header-logo {
  display: flex;
  align-items: center;
  padding: 0 15px;
  height: 100%;

  .logo-text {
    font-size: 16px;
    font-weight: 600;
    white-space: nowrap;
  }
}

// 右侧操作区
.header-right {
  display: flex;
  align-items: center;
  height: 100%;
  padding-right: 15px;

  .header-item {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 100%;
    cursor: pointer;
    transition: background-color 0.2s ease;

    &:hover {
      background-color: rgba(255, 255, 255, 0.1);
    }
  }

  .hamburger {
    font-size: 18px;
  }

  .username {
    padding: 0 15px;
    font-size: 14px;
  }

  .header-link {
    padding: 0 15px;
    font-size: 14px;
    cursor: pointer;
    transition: opacity 0.2s ease;

    &:hover {
      opacity: 0.8;
    }
  }

  .logout {
    border-left: 1px solid rgba(255, 255, 255, 0.3);
    padding-left: 15px;
  }
}
</style>
