import request from '@/api/request'

/**
 * 分类管理 API
 */

// 获取分类树
export function getCategoryTree() {
  return request({
    url: '/admin/categories/tree',
    method: 'get',
  })
}

// 获取分类列表
export function getCategoryList(params) {
  return request({
    url: '/admin/categories',
    method: 'get',
    params,
  })
}

// 获取分类详情
export function getCategoryDetail(id) {
  return request({
    url: `/admin/categories/${id}`,
    method: 'get',
  })
}

// 创建分类
export function createCategory(data) {
  return request({
    url: '/admin/categories',
    method: 'post',
    data,
  })
}

// 更新分类
export function updateCategory(id, data) {
  return request({
    url: `/admin/categories/${id}`,
    method: 'put',
    data,
  })
}

// 删除分类
export function deleteCategory(id) {
  return request({
    url: `/admin/categories/${id}`,
    method: 'delete',
  })
}

// 获取顶级分类
export function getTopCategories() {
  return request({
    url: '/admin/categories/top',
    method: 'get',
  })
}

// 获取子分类
export function getChildrenCategories(parentId) {
  return request({
    url: `/admin/categories/${parentId}/children`,
    method: 'get',
  })
}

// 获取子分类列表（别名）
export function getSubCategories(parentId) {
  return request({
    url: `/admin/categories/${parentId}/children`,
    method: 'get',
  })
}

// 设置分类状态
export function setCategoryStatus(id, status) {
  return request({
    url: `/admin/categories/${id}/status`,
    method: 'put',
    data: { status },
  })
}

// 设置浮动率上限
export function setFloatRateCap(id, floatRateCap) {
  return request({
    url: `/admin/categories/${id}/float-rate-cap`,
    method: 'put',
    data: { float_rate_cap: floatRateCap },
  })
}
