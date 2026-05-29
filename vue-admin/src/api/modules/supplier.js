import request from '@/api/request'

/**
 * 供应商管理 API
 */

// 获取供应商列表
export function getSupplierList(params) {
  return request({
    url: '/admin/suppliers',
    method: 'get',
    params,
  })
}

// 获取供应商详情
export function getSupplierDetail(id) {
  return request({
    url: `/admin/suppliers/${id}`,
    method: 'get',
  })
}

// 创建供应商
export function createSupplier(data) {
  return request({
    url: '/admin/suppliers',
    method: 'post',
    data,
  })
}

// 更新供应商
export function updateSupplier(id, data) {
  return request({
    url: `/admin/suppliers/${id}`,
    method: 'put',
    data,
  })
}

// 删除供应商
export function deleteSupplier(id) {
  return request({
    url: `/admin/suppliers/${id}`,
    method: 'delete',
  })
}

// 更改供应商状态
export function changeSupplierStatus(id, status) {
  return request({
    url: `/admin/suppliers/${id}/status`,
    method: 'put',
    data: { status },
  })
}

// 获取所有启用的供应商
export function getActiveSuppliers() {
  return request({
    url: '/admin/suppliers/active',
    method: 'get',
  })
}

// 获取折扣变更记录
export function getDiscountLogs(id) {
  return request({
    url: `/admin/suppliers/${id}/discount-logs`,
    method: 'get',
  })
}