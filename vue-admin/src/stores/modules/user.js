import { defineStore } from 'pinia'
import { login, logout, getInfo, refreshToken } from '@/api/modules/auth'
import { getToken, setToken, removeToken } from '@/utils/auth'
import { asyncRoutes, constantRoutes } from '@/router'
import { usePermissionStore } from '@/stores/modules/permission'
import { useMenuStore } from '@/stores/modules/menu'

export const useUserStore = defineStore('user', {
  state: () => ({
    token: getToken() || '',
    userInfo: {},
    roles: [],
    permissions: [],
    routes: [],
    addRoutes: [],
  }),

  getters: {
    userId: (state) => state.userInfo?.id,
    username: (state) => state.userInfo?.username,
    name: (state) => state.userInfo?.name,
    avatar: (state) => state.userInfo?.avatar || 'https://cube.elemecdn.com/3/7c/3ea6beec64369c2642b92c6726f1epng.png',
    isSuper: (state) => state.userInfo?.is_super,
  },

  actions: {
    // 登录
    async login(userInfo) {
      const { username, password } = userInfo
      const res = await login({ username: username.trim(), password })

      const { token } = res.data
      this.token = token
      setToken(token)

      return res
    },

    // 获取用户信息
    async getUserInfo() {
      const res = await getInfo()
      // 后端返回格式: data 包含用户信息和 permissions
      const data = res.data || {}
      const { permissions, ...userData } = data

      this.userInfo = userData
      this.permissions = permissions || []
      this.roles = userData.is_super ? ['admin'] : ['user']
      usePermissionStore().setPermissions(this.permissions)

      // 菜单加载失败不阻塞页面渲染
      try {
        await useMenuStore().fetchMenus(true)
      } catch (error) {
        console.warn('菜单加载失败:', error)
      }

      await this.generateRoutes()

      return res
    },

    // 登出
    async logout() {
      try {
        await logout()
      } finally {
        this.resetState()
      }
    },

    // 重置状态
    resetState() {
      this.token = ''
      this.userInfo = {}
      this.roles = []
      this.permissions = []
      this.routes = []
      this.addRoutes = []
      usePermissionStore().clearPermissions()
      useMenuStore().clearMenus()
      removeToken()
    },

    // 刷新 Token
    async refresh() {
      const res = await refreshToken()
      const { token } = res.data
      this.token = token
      setToken(token)
      return res
    },

    // 生成动态路由
    async generateRoutes() {
      // 超级管理员拥有所有路由
      if (this.isSuper) {
        this.addRoutes = asyncRoutes
        this.routes = constantRoutes.concat(asyncRoutes)
      } else {
        // 根据权限过滤路由
        this.addRoutes = this.filterAsyncRoutes(asyncRoutes, this.permissions)
        this.routes = constantRoutes.concat(this.addRoutes)
      }

      return this.addRoutes
    },

    // 过滤异步路由
    filterAsyncRoutes(routes, permissions) {
      const res = []

      routes.forEach((route) => {
        const tmp = { ...route }

        if (this.hasPermission(permissions, tmp)) {
          if (tmp.children) {
            tmp.children = this.filterAsyncRoutes(tmp.children, permissions)
          }
          res.push(tmp)
        }
      })

      return res
    },

    // 判断是否有权限
    hasPermission(permissions, route) {
      if (route.meta && route.meta.permission) {
        return permissions.some((permission) => {
          if (permission === '*') return true
          return route.meta.permission.includes(permission)
        })
      }
      return true
    },

    // 检查是否拥有某个权限
    checkPermission(permission) {
      if (this.isSuper) return true
      if (!this.permissions || this.permissions.length === 0) return false
      if (this.permissions.includes('*')) return true
      return this.permissions.includes(permission)
    },
  },
})
