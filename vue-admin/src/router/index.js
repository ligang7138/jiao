import { createRouter, createWebHistory } from 'vue-router'
import NProgress from 'nprogress'
import { useUserStore } from '@/stores/modules/user'

// 静态路由
export const constantRoutes = [
  {
    path: '/login',
    name: 'Login',
    component: () => import('@/views/login/index.vue'),
    meta: { title: '登录', hidden: true },
  },
  {
    path: '/403',
    name: '403',
    component: () => import('@/views/error/403.vue'),
    meta: { title: '403', hidden: true },
  },
  {
    path: '/404',
    name: '404',
    component: () => import('@/views/error/404.vue'),
    meta: { title: '404', hidden: true },
  },
]

// 动态路由（根据权限动态加载）
export const asyncRoutes = [
  {
    path: '/',
    name: 'Layout',
    component: () => import('@/layouts/DefaultLayout.vue'),
    redirect: '/dashboard',
    children: [
      {
        path: '',
        redirect: '/dashboard',
      },
      {
        path: 'dashboard',
        name: 'Dashboard',
        component: () => import('@/views/dashboard/index.vue'),
        meta: { title: '首页', icon: 'HomeFilled', affix: true },
      },
    ],
  },
  {
    path: '/system',
    name: 'System',
    component: () => import('@/layouts/DefaultLayout.vue'),
    redirect: '/system/users',
    meta: { title: '系统管理', icon: 'Setting' },
    children: [
      {
        path: 'users',
        name: 'Users',
        component: () => import('@/views/system/users/index.vue'),
        meta: { title: '用户管理', icon: 'User', permission: ['user.index'] },
      },
      {
        path: 'roles',
        name: 'Roles',
        component: () => import('@/views/system/roles/index.vue'),
        meta: { title: '角色管理', icon: 'UserFilled', permission: ['post.index'] },
      },
      {
        path: 'permissions',
        name: 'Permissions',
        component: () => import('@/views/system/permissions/index.vue'),
        meta: { title: '权限管理', icon: 'Lock', permission: ['privilege.index'] },
      },
      {
        path: 'logs',
        name: 'SystemLogs',
        component: () => import('@/views/system/logs/index.vue'),
        meta: { title: '日志管理', icon: 'Document', permission: ['log.index'] },
      },
    ],
  },
  {
    path: '/goods',
    name: 'Goods',
    component: () => import('@/layouts/DefaultLayout.vue'),
    redirect: '/goods/list',
    meta: { title: '商品管理', icon: 'Goods' },
    children: [
      {
        path: 'list',
        name: 'GoodsList',
        component: () => import('@/views/goods/list/index.vue'),
        meta: { title: '商品列表', icon: 'Goods', permission: ['goods.index'] },
      },
      {
        path: 'add',
        name: 'GoodsAdd',
        component: () => import('@/views/goods/add/index.vue'),
        meta: { title: '新增商品', icon: 'Plus', hidden: true },
      },
      {
        path: 'edit/:id',
        name: 'GoodsEdit',
        component: () => import('@/views/goods/edit/index.vue'),
        meta: { title: '编辑商品', icon: 'Edit', hidden: true },
      },
      {
        path: 'import',
        name: 'GoodsImport',
        component: () => import('@/views/goods/import/index.vue'),
        meta: { title: '导入商品', icon: 'Upload', hidden: true },
      },
      {
        path: 'history-price/:id',
        name: 'GoodsHistoryPrice',
        component: () => import('@/views/goods/history-price/index.vue'),
        meta: { title: '历史价格', icon: 'Clock', hidden: true },
      },
      {
        path: 'category',
        name: 'Category',
        component: () => import('@/views/goods/category/index.vue'),
        meta: { title: '分类管理', icon: 'Grid', permission: ['category.index'] },
      },
    ],
  },
  {
    path: '/orders',
    name: 'Orders',
    component: () => import('@/layouts/DefaultLayout.vue'),
    redirect: '/orders/list',
    meta: { title: '订单管理', icon: 'Document' },
    children: [
      {
        path: 'list',
        name: 'OrderList',
        component: () => import('@/views/orders/list/index.vue'),
        meta: { title: '订单列表', icon: 'Document', permission: ['order.index'] },
      },
    ],
  },
  {
    path: '/suppliers',
    name: 'Suppliers',
    component: () => import('@/layouts/DefaultLayout.vue'),
    redirect: '/suppliers/list',
    meta: { title: '供应商管理', icon: 'Shop' },
    children: [
      {
        path: 'list',
        name: 'SupplierList',
        component: () => import('@/views/suppliers/list/index.vue'),
        meta: { title: '供应商列表', icon: 'Shop', permission: ['supplier.index'] },
      },
    ],
  },
  {
    path: '/schools',
    name: 'Schools',
    component: () => import('@/layouts/DefaultLayout.vue'),
    redirect: '/schools/list',
    meta: { title: '学校中心', icon: 'School' },
    children: [
      {
        path: 'list',
        name: 'SchoolList',
        component: () => import('@/views/schools/list/index.vue'),
        meta: { title: '学校列表', icon: 'School', permission: ['school.index'] },
      },
      {
        path: 'canteens',
        name: 'Canteens',
        component: () => import('@/views/canteen/list/index.vue'),
        meta: { title: '食堂管理', icon: 'Food', permission: ['school_canteen.index'] },
      },
    ],
  },
  {
    path: '/bidding',
    name: 'Bidding',
    component: () => import('@/layouts/DefaultLayout.vue'),
    redirect: '/bidding/histories',
    meta: { title: '招投标管理', icon: 'TrendCharts' },
    children: [
      {
        path: 'histories',
        name: 'BiddingHistories',
        component: () => import('@/views/bidding/index.vue'),
        meta: { title: '合作申请列表', icon: 'Document', permission: ['bidding.index'] },
      },
      {
        path: 'discounts',
        name: 'BiddingDiscounts',
        component: () => import('@/views/bidding/discount.vue'),
        meta: { title: '供应商报价', icon: 'PriceTag', permission: ['bidding.discount'] },
      },
    ],
  },
  {
    path: '/backorder',
    name: 'Backorder',
    component: () => import('@/layouts/DefaultLayout.vue'),
    redirect: '/backorder/list',
    meta: { title: '退单管理', icon: 'RefreshLeft' },
    children: [
      {
        path: 'list',
        name: 'BackorderList',
        component: () => import('@/views/backorder/index.vue'),
        meta: { title: '退货单列表', icon: 'Document' },
      },
      {
        path: 'type',
        name: 'BackorderType',
        component: () => import('@/views/backorder/type.vue'),
        meta: { title: '退货原因类型', icon: 'Grid' },
      },
      {
        path: 'view/:id',
        name: 'BackorderView',
        component: () => import('@/views/backorder/view.vue'),
        meta: { title: '退货单详情', icon: 'Document', hidden: true },
      },
    ],
  },
  {
    path: '/approve',
    name: 'Approve',
    component: () => import('@/layouts/DefaultLayout.vue'),
    redirect: '/approve/comment',
    meta: { title: '审批管理', icon: 'Stamp' },
    children: [
      {
        path: 'comment',
        name: 'ApproveComment',
        component: () => import('@/views/approve/comment.vue'),
        meta: { title: '评论审阅', icon: 'ChatDotSquare' },
      },
      {
        path: 'complaint',
        name: 'ApproveComplaint',
        component: () => import('@/views/approve/complaint.vue'),
        meta: { title: '投诉审阅', icon: 'Warning' },
      },
      {
        path: 'bidding',
        name: 'ApproveBidding',
        component: () => import('@/views/approve/bidding.vue'),
        meta: { title: '合作审阅', icon: 'Connection' },
      },
    ],
  },
  {
    path: '/jiagewang',
    name: 'Jiagewang',
    component: () => import('@/layouts/DefaultLayout.vue'),
    redirect: '/jiagewang/list',
    meta: { title: '价格网管理', icon: 'PriceTag' },
    children: [
      {
        path: 'list',
        name: 'JiagewangList',
        component: () => import('@/views/jiagewang/index.vue'),
        meta: { title: '指导价列表', icon: 'List' },
      },
      {
        path: 'import',
        name: 'JiagewangImport',
        component: () => import('@/views/jiagewang/import.vue'),
        meta: { title: '指导价导入', icon: 'Upload', hidden: true },
      },
      {
        path: 'history',
        name: 'JiagewangHistory',
        component: () => import('@/views/jiagewang/history.vue'),
        meta: { title: '历史记录', icon: 'Clock', hidden: true },
      },
      {
        path: 'match',
        name: 'JiagewangMatch',
        component: () => import('@/views/jiagewang/match.vue'),
        meta: { title: '商品匹配', icon: 'Connection', hidden: true },
      },
    ],
  },
  {
    path: '/receivable',
    name: 'Receivable',
    component: () => import('@/layouts/DefaultLayout.vue'),
    redirect: '/receivable/receipt',
    meta: { title: '应收账款', icon: 'Money' },
    children: [
      {
        path: 'receipt',
        name: 'ReceivableReceipt',
        component: () => import('@/views/receivable/receipt/index.vue'),
        meta: { title: '对账单管理', icon: 'Tickets' },
      },
      {
        path: 'receipt/view/:id',
        name: 'ReceivableReceiptView',
        component: () => import('@/views/receivable/receipt/view.vue'),
        meta: { title: '对账单详情', icon: 'Tickets', hidden: true },
      },
      {
        path: 'receipt/add',
        name: 'ReceivableReceiptAdd',
        component: () => import('@/views/receivable/receipt/edit.vue'),
        meta: { title: '新增对账单', icon: 'Tickets', hidden: true },
      },
      {
        path: 'receipt/edit/:id',
        name: 'ReceivableReceiptEdit',
        component: () => import('@/views/receivable/receipt/edit.vue'),
        meta: { title: '编辑对账单', icon: 'Tickets', hidden: true },
      },
      {
        path: 'account',
        name: 'ReceivableAccount',
        component: () => import('@/views/receivable/account/index.vue'),
        meta: { title: '账单明细', icon: 'List' },
      },
      {
        path: 'account/no-receipt',
        name: 'ReceivableAccountNoReceipt',
        component: () => import('@/views/receivable/account/no-receipt.vue'),
        meta: { title: '未入账账单', icon: 'Document' },
      },
    ],
  },
  {
    path: '/emergency',
    name: 'Emergency',
    component: () => import('@/layouts/DefaultLayout.vue'),
    redirect: '/emergency/list',
    meta: { title: '应急管理', icon: 'Warning' },
    children: [
      {
        path: 'list',
        name: 'EmergencyList',
        component: () => import('@/views/emergency/index.vue'),
        meta: { title: '应急事件列表', icon: 'Warning' },
      },
    ],
  },
  {
    path: '/complaint',
    name: 'Complaint',
    component: () => import('@/layouts/DefaultLayout.vue'),
    redirect: '/complaint/list',
    meta: { title: '投诉管理', icon: 'ChatLineSquare' },
    children: [
      {
        path: 'list',
        name: 'ComplaintList',
        component: () => import('@/views/complaint/index.vue'),
        meta: { title: '投诉列表', icon: 'ChatLineSquare' },
      },
    ],
  },
  {
    path: '/group',
    name: 'Group',
    component: () => import('@/layouts/DefaultLayout.vue'),
    redirect: '/group/list',
    meta: { title: '食堂组管理', icon: 'Folder' },
    children: [
      {
        path: 'list',
        name: 'GroupList',
        component: () => import('@/views/group/index.vue'),
        meta: { title: '食堂组列表', icon: 'Folder', permission: ['group.index'] },
      },
    ],
  },
  {
    path: '/stat',
    name: 'Stat',
    component: () => import('@/layouts/DefaultLayout.vue'),
    redirect: '/stat/order',
    meta: { title: '统计分析', icon: 'DataAnalysis' },
    children: [
      {
        path: 'order',
        name: 'StatOrder',
        component: () => import('@/views/stat/order.vue'),
        meta: { title: '订单统计', icon: 'Document' },
      },
    ],
  },
  {
    path: '/:pathMatch(.*)*',
    name: 'NotFound',
    redirect: '/404',
    meta: { hidden: true },
  },
]

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [...constantRoutes, ...asyncRoutes],
  scrollBehavior: () => ({ left: 0, top: 0 }),
})

const whiteList = ['/login', '/403', '/404']

function canAccessRoute(to, userStore) {
  if (userStore.isSuper) {
    return true
  }

  const permissions = userStore.permissions || []
  if (permissions.includes('*')) {
    return true
  }

  return to.matched.every((record) => {
    const required = record.meta?.permission
    if (!required || required.length === 0) {
      return true
    }
    return required.some((item) => permissions.includes(item))
  })
}

router.beforeEach(async (to, from, next) => {
  NProgress.start()

  const userStore = useUserStore()
  const hasToken = userStore.token

  if (hasToken) {
    if (to.path === '/login') {
      next({ path: '/dashboard', replace: true })
      NProgress.done()
      return
    }

    if (to.path === '/') {
      next({ path: '/dashboard', replace: true })
      NProgress.done()
      return
    }

    if (!userStore.roles?.length) {
      try {
        await userStore.getUserInfo()
      } catch {
        await userStore.logout()
        next(`/login?redirect=${to.path}`)
        NProgress.done()
        return
      }
    }

    if (!canAccessRoute(to, userStore)) {
      next('/403')
      NProgress.done()
      return
    }

    next()
    return
  }

  if (whiteList.includes(to.path)) {
    next()
    return
  }

  next(`/login?redirect=${to.path}`)
  NProgress.done()
})

router.afterEach(() => {
  NProgress.done()
})

export default router
