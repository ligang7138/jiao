import request from '@/api/request'

/**
 * 应收账款管理 API
 */

/**
 * 对账单管理
 */

// 获取对账单列表
export function getReceiptList(params) {
  return request({
    url: '/admin/receivable/receipts',
    method: 'get',
    params,
  })
}

// 获取对账单详情
export function getReceiptDetail(id) {
  return request({
    url: `/admin/receivable/receipts/${id}`,
    method: 'get',
  })
}

// 创建对账单
export function createReceipt(data) {
  return request({
    url: '/admin/receivable/receipts',
    method: 'post',
    data,
  })
}

// 调整对账单
export function updateReceipt(id, data) {
  return request({
    url: `/admin/receivable/receipts/${id}`,
    method: 'put',
    data,
  })
}

// 删除对账单
export function deleteReceipt(id) {
  return request({
    url: `/admin/receivable/receipts/${id}`,
    method: 'delete',
  })
}

// 开票
export function invoiceReceipt(id, data) {
  return request({
    url: `/admin/receivable/receipts/${id}/invoice`,
    method: 'post',
    data,
  })
}

// 收款
export function billReceipt(id, data) {
  return request({
    url: `/admin/receivable/receipts/${id}/bill`,
    method: 'post',
    data,
  })
}

// 学校确认
export function confirmReceipt(id) {
  return request({
    url: `/admin/receivable/receipts/${id}/confirm`,
    method: 'post',
  })
}

// 导出对账单
export function exportReceipt(id) {
  return request({
    url: `/admin/receivable/receipts/${id}/export`,
    method: 'get',
    responseType: 'blob',
  })
}

/**
 * 账单明细管理
 */

// 获取账单明细列表
export function getAccountList(params) {
  return request({
    url: '/admin/receivable/accounts',
    method: 'get',
    params,
  })
}

// 获取账单明细详情
export function getAccountDetail(id) {
  return request({
    url: `/admin/receivable/accounts/${id}`,
    method: 'get',
  })
}

// 新增账单明细
export function createAccount(data) {
  return request({
    url: '/admin/receivable/accounts',
    method: 'post',
    data,
  })
}

// 编辑账单明细
export function updateAccount(id, data) {
  return request({
    url: `/admin/receivable/accounts/${id}`,
    method: 'put',
    data,
  })
}

// 删除账单明细
export function deleteAccount(id) {
  return request({
    url: `/admin/receivable/accounts/${id}`,
    method: 'delete',
  })
}

// 获取未入账账单列表
export function getNoReceiptAccounts(params) {
  return request({
    url: '/admin/receivable/accounts/no-receipt',
    method: 'get',
    params,
  })
}

// 批量入账
export function batchReceipt(data) {
  return request({
    url: '/admin/receivable/accounts/batch-receipt',
    method: 'post',
    data,
  })
}

/**
 * 统计相关
 */

// 获取应收账款统计
export function getReceivableStats(params) {
  return request({
    url: '/admin/receivable/stats',
    method: 'get',
    params,
  })
}

// 获取学校应收汇总
export function getSchoolReceivableSummary(params) {
  return request({
    url: '/admin/receivable/school-summary',
    method: 'get',
    params,
  })
}

// 获取供应商应收汇总
export function getSupplierReceivableSummary(params) {
  return request({
    url: '/admin/receivable/supplier-summary',
    method: 'get',
    params,
  })
}
