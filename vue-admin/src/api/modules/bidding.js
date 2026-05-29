import request from '@/api/request'

/**
 * 招投标管理 API
 */

// ==================== 合作申请相关 ====================

/**
 * 获取合作申请列表
 */
export function getBiddingHistories(params) {
  return request({
    url: '/admin/bidding/histories',
    method: 'get',
    params,
  })
}

/**
 * 获取合作申请详情
 */
export function getBiddingHistoryDetail(id) {
  return request({
    url: `/admin/bidding/histories/${id}`,
    method: 'get',
  })
}

/**
 * 审核合作申请
 */
export function auditBiddingHistory(id, data) {
  return request({
    url: `/admin/bidding/histories/${id}/audit`,
    method: 'post',
    data,
  })
}

// ==================== 合作关系相关 ====================

/**
 * 获取合作关系列表
 */
export function getBiddingLogs(params) {
  return request({
    url: '/admin/bidding/logs',
    method: 'get',
    params,
  })
}

// ==================== 供应商报价相关 ====================

/**
 * 获取供应商报价列表
 */
export function getBiddingDiscounts(params) {
  return request({
    url: '/admin/bidding/discounts',
    method: 'get',
    params,
  })
}

/**
 * 获取报价历史记录
 */
export function getBiddingDiscountHistory(params) {
  return request({
    url: '/admin/bidding/discounts/history',
    method: 'get',
    params,
  })
}
