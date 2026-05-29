import request from '@/api/request'

/**
 * 投诉管理 API
 */

// 获取投诉列表
export function getComplaintList(params) {
  return request({
    url: '/admin/complaint',
    method: 'get',
    params,
  })
}

// 获取投诉详情
export function getComplaintDetail(id) {
  return request({
    url: `/admin/complaint/${id}`,
    method: 'get',
  })
}

// 处理投诉
export function processComplaint(id, data) {
  return request({
    url: `/admin/complaint/${id}/process`,
    method: 'post',
    data,
  })
}

// 获取投诉类型列表
export function getComplaintTypeList(params) {
  return request({
    url: '/admin/complaint/type',
    method: 'get',
    params,
  })
}

// 新增投诉类型
export function createComplaintType(data) {
  return request({
    url: '/admin/complaint/type',
    method: 'post',
    data,
  })
}

// 编辑投诉类型
export function updateComplaintType(id, data) {
  return request({
    url: `/admin/complaint/type/${id}`,
    method: 'put',
    data,
  })
}

// 删除投诉类型
export function deleteComplaintType(id) {
  return request({
    url: `/admin/complaint/type/${id}`,
    method: 'delete',
  })
}

// 获取启用的投诉类型（下拉选择用）
export function getActiveComplaintTypes() {
  return request({
    url: '/admin/complaint/type',
    method: 'get',
    params: { status: 1, page_size: 100 },
  })
}
