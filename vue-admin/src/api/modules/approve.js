import request from '@/api/request'

/**
 * 审批管理 API
 */

/**
 * 评论审阅
 */

// 获取评论列表
export function getCommentList(params) {
  return request({
    url: '/admin/approve/comments',
    method: 'get',
    params,
  })
}

// 审阅评论
export function reviewComment(id, data) {
  return request({
    url: `/admin/approve/comments/${id}/review`,
    method: 'post',
    data,
  })
}

/**
 * 投诉审阅
 */

// 获取投诉列表
export function getComplaintList(params) {
  return request({
    url: '/admin/approve/complaints',
    method: 'get',
    params,
  })
}

// 审阅投诉
export function reviewComplaint(id, data) {
  return request({
    url: `/admin/approve/complaints/${id}/review`,
    method: 'post',
    data,
  })
}

/**
 * 合作审阅
 */

// 获取合作申请列表
export function getBiddingList(params) {
  return request({
    url: '/admin/approve/biddings',
    method: 'get',
    params,
  })
}

// 审阅合作申请
export function reviewBidding(id, data) {
  return request({
    url: `/admin/approve/biddings/${id}/review`,
    method: 'post',
    data,
  })
}
