import request from '@/api/request'

/**
 * 订单管理 API
 */

// 获取订单列表
export function getOrderList(params) {
  return request({
    url: '/admin/orders',
    method: 'get',
    params,
  })
}

// 获取订单详情
export function getOrderDetail(id) {
  return request({
    url: `/admin/orders/${id}`,
    method: 'get',
  })
}

// 创建订单
export function createOrder(data) {
  return request({
    url: '/admin/orders',
    method: 'post',
    data,
  })
}

// 更新订单
export function updateOrder(id, data) {
  return request({
    url: `/admin/orders/${id}`,
    method: 'put',
    data,
  })
}

// 删除订单
export function deleteOrder(id) {
  return request({
    url: `/admin/orders/${id}`,
    method: 'delete',
  })
}

// 更改订单状态
export function changeOrderStatus(id, status) {
  return request({
    url: `/admin/orders/${id}/status`,
    method: 'put',
    data: { status },
  })
}

// 提交审核
export function submitOrder(id) {
  return request({
    url: `/admin/orders/${id}/submit`,
    method: 'post',
  })
}

// 取消订单
export function cancelOrder(id) {
  return request({
    url: `/admin/orders/${id}/cancel`,
    method: 'post',
  })
}

// 订单导出
export function exportOrder(params) {
  return request({
    url: '/admin/orders/export',
    method: 'get',
    params,
    responseType: 'blob',
  })
}

// 订单明细导出
export function exportOrderDetail(params) {
  return request({
    url: '/admin/orders/export-detail',
    method: 'get',
    params,
    responseType: 'blob',
  })
}

// 订单统计
export function getOrderStatistics(params) {
  return request({
    url: '/admin/orders/statistics',
    method: 'get',
    params,
  })
}

// 订单溯源
export function getOrderTraceSource(id) {
  return request({
    url: `/admin/orders/${id}/trace-source`,
    method: 'get',
  })
}