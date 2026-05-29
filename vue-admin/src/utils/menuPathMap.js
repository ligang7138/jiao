/**
 * 旧 system_menu.path 到 Vue 路由的兜底映射（与后端 LegacyMenuPathMap 保持一致）
 */
const PATH_ROUTE_MAP = {
  'home.index': '/dashboard',
  'goods.index': '/goods/list',
  'goods.unit': '/goods/list',
  'goods.report': '/goods/list',
  'category.index': '/goods/category',
  'order.index': '/orders/list',
  'order.type': '/orders/list',
  'supplier.index': '/suppliers/list',
  'school.index': '/schools/list',
  'school_canteen.index': '/schools/canteens',
  'school_district.index': '/schools/list',
  'user.index': '/system/users',
  'post.index': '/system/roles',
  'department.index': '/system/users',
  'privilege.index': '/system/permissions',
  'bidding.index': '/bidding/histories',
  'bidding.discount': '/bidding/discounts',
  'backorder.index': '/backorder/list',
  'backorder.type': '/backorder/type',
  'jiagewang.index': '/jiagewang/list',
  'jiagewang.match': '/jiagewang/match',
  'jiagewang.import': '/jiagewang/import',
  'receivable.receipt': '/receivable/receipt',
  'receivable.account': '/receivable/account',
  'receivable.order': '/receivable/receipt',
  'receivable.accountNo': '/receivable/account/no-receipt',
  'receivable.import': '/receivable/receipt',
  'approve.comment': '/approve/comment',
  'approve.complaint': '/approve/complaint',
  'approve.bidding': '/approve/bidding',
  'complaint.index': '/complaint/list',
  'emergency.index': '/emergency/list',
  'emergency.type': '/emergency/list',
  'group.index': '/group/list',
  'comment.index': '/approve/comment',
  'stat.order': '/stat/order',
  'stat.goods': '/stat/order',
  'stat.bidding': '/stat/order',
  'stat.complaint': '/stat/order',
  'stat.backorder': '/stat/order',
  'stat.replenish': '/stat/order',
  'stat.ontime_rate': '/stat/order',
  'stat.backorder_rate': '/stat/order',
  'stat.replenish_rate': '/stat/order',
  'log.index': '/system/logs',
  'home.orders': '/dashboard',
  'home.supplier': '/dashboard',
  'data_analysis.supplier': '/dashboard',
  'data_analysis.goods': '/dashboard',
  'data_analysis.school_canteen': '/dashboard',
  'approve.supplier': '/approve/bidding',
  'approve.audit': '/approve/bidding',
}

const MODULE_FOLDER_MAP = {
  order: 'orders',
  supplier: 'suppliers',
  school: 'schools',
}

export function resolveMenuRoute(menuItem) {
  if (menuItem?.route) {
    return menuItem.route
  }

  const path = menuItem?.path
  if (!path) {
    return null
  }

  if (PATH_ROUTE_MAP[path]) {
    return PATH_ROUTE_MAP[path]
  }

  const indexMatch = path.match(/^([a-z0-9_]+)\.index$/)
  if (indexMatch) {
    const module = indexMatch[1]
    const folder = MODULE_FOLDER_MAP[module] || module
    return `/${folder}/list`
  }

  if (path.includes('.')) {
    const [module, action] = path.split('.', 2)
    return `/${module}/${action.replace(/_/g, '-')}`
  }

  return null
}
