import request from '@/api/request'

/**
 * 统计分析 API
 */

/**
 * 订单统计
 * @param {Object} params - 查询参数
 * @param {string} params.start_date - 开始日期
 * @param {string} params.end_date - 结束日期
 * @param {number} params.school_id - 学校ID
 * @param {number} params.canteen_id - 食堂ID
 * @param {number} params.supplier_id - 供应商ID
 */
export function getOrderStat(params) {
  return request({
    url: '/admin/stat/order',
    method: 'get',
    params,
  })
}

/**
 * 商品统计
 * @param {Object} params - 查询参数
 * @param {string} params.start_date - 开始日期
 * @param {string} params.end_date - 结束日期
 * @param {number} params.category_id - 分类ID
 * @param {number} params.supplier_id - 供应商ID
 */
export function getGoodsStat(params) {
  return request({
    url: '/admin/stat/goods',
    method: 'get',
    params,
  })
}

/**
 * 退货统计
 * @param {Object} params - 查询参数
 * @param {string} params.start_date - 开始日期
 * @param {string} params.end_date - 结束日期
 * @param {number} params.school_id - 学校ID
 * @param {number} params.canteen_id - 食堂ID
 * @param {number} params.supplier_id - 供应商ID
 */
export function getBackorderStat(params) {
  return request({
    url: '/admin/stat/backorder',
    method: 'get',
    params,
  })
}

/**
 * 退货率统计
 * @param {Object} params - 查询参数
 * @param {string} params.start_date - 开始日期
 * @param {string} params.end_date - 结束日期
 * @param {number} params.school_id - 学校ID
 * @param {number} params.supplier_id - 供应商ID
 */
export function getBackorderRateStat(params) {
  return request({
    url: '/admin/stat/backorder-rate',
    method: 'get',
    params,
  })
}

/**
 * 准时率统计
 * @param {Object} params - 查询参数
 * @param {string} params.start_date - 开始日期
 * @param {string} params.end_date - 结束日期
 * @param {number} params.school_id - 学校ID
 * @param {number} params.supplier_id - 供应商ID
 */
export function getOntimeRateStat(params) {
  return request({
    url: '/admin/stat/ontime-rate',
    method: 'get',
    params,
  })
}

/**
 * 补货统计
 * @param {Object} params - 查询参数
 * @param {string} params.start_date - 开始日期
 * @param {string} params.end_date - 结束日期
 * @param {number} params.school_id - 学校ID
 * @param {number} params.canteen_id - 食堂ID
 * @param {number} params.supplier_id - 供应商ID
 */
export function getReplenishStat(params) {
  return request({
    url: '/admin/stat/replenish',
    method: 'get',
    params,
  })
}

/**
 * 补货率统计
 * @param {Object} params - 查询参数
 * @param {string} params.start_date - 开始日期
 * @param {string} params.end_date - 结束日期
 * @param {number} params.school_id - 学校ID
 * @param {number} params.supplier_id - 供应商ID
 */
export function getReplenishRateStat(params) {
  return request({
    url: '/admin/stat/replenish-rate',
    method: 'get',
    params,
  })
}

/**
 * 导出订单统计报表
 */
export function exportOrderStat(params) {
  return request({
    url: '/admin/stat/order/export',
    method: 'get',
    params,
    responseType: 'blob',
  })
}

/**
 * 导出商品统计报表
 */
export function exportGoodsStat(params) {
  return request({
    url: '/admin/stat/goods/export',
    method: 'get',
    params,
    responseType: 'blob',
  })
}

/**
 * 导出退货统计报表
 */
export function exportBackorderStat(params) {
  return request({
    url: '/admin/stat/backorder/export',
    method: 'get',
    params,
    responseType: 'blob',
  })
}

/**
 * 导出退货率统计报表
 */
export function exportBackorderRateStat(params) {
  return request({
    url: '/admin/stat/backorder-rate/export',
    method: 'get',
    params,
    responseType: 'blob',
  })
}

/**
 * 导出准时率统计报表
 */
export function exportOntimeRateStat(params) {
  return request({
    url: '/admin/stat/ontime-rate/export',
    method: 'get',
    params,
    responseType: 'blob',
  })
}

/**
 * 导出补货统计报表
 */
export function exportReplenishStat(params) {
  return request({
    url: '/admin/stat/replenish/export',
    method: 'get',
    params,
    responseType: 'blob',
  })
}

/**
 * 导出补货率统计报表
 */
export function exportReplenishRateStat(params) {
  return request({
    url: '/admin/stat/replenish-rate/export',
    method: 'get',
    params,
    responseType: 'blob',
  })
}
