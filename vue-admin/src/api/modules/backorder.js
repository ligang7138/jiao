import request from '@/api/request'

/**
 * 退单管理 API
 */

// 获取退货单列表
export function getBackorderList(params) {
  return request({
    url: '/admin/backorders',
    method: 'get',
    params,
  })
}

// 获取退货单详情
export function getBackorderDetail(id) {
  return request({
    url: `/admin/backorders/${id}`,
    method: 'get',
  })
}

// 审核通过
export function auditBackorder(id, data) {
  return request({
    url: `/admin/backorders/${id}/audit`,
    method: 'post',
    data,
  })
}

// 审核拒绝
export function rejectBackorder(id, data) {
  return request({
    url: `/admin/backorders/${id}/reject`,
    method: 'post',
    data,
  })
}

// 取消退货单
export function cancelBackorder(id) {
  return request({
    url: `/admin/backorders/${id}/cancel`,
    method: 'post',
  })
}

// 设置解决方案
export function setBackorderSolution(id, data) {
  return request({
    url: `/admin/backorders/${id}/solution`,
    method: 'put',
    data,
  })
}

// 获取退货原因类型列表
export function getBackorderTypeList(params) {
  return request({
    url: '/admin/backorder-types',
    method: 'get',
    params,
  })
}

// 新增退货原因类型
export function createBackorderType(data) {
  return request({
    url: '/admin/backorder-types',
    method: 'post',
    data,
  })
}

// 编辑退货原因类型
export function updateBackorderType(id, data) {
  return request({
    url: `/admin/backorder-types/${id}`,
    method: 'put',
    data,
  })
}

// 删除退货原因类型
export function deleteBackorderType(id) {
  return request({
    url: `/admin/backorder-types/${id}`,
    method: 'delete',
  })
}

// 获取启用的退货原因类型（下拉选择用）
export function getActiveBackorderTypes() {
  return request({
    url: '/admin/backorder-types',
    method: 'get',
    params: { status: 1, page_size: 100 },
  })
}
