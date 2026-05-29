import request from '@/api/request'

/**
 * 应急管理 API
 */

// 获取应急事件列表
export function getEmergencyList(params) {
  return request({
    url: '/admin/emergency',
    method: 'get',
    params,
  })
}

// 获取应急事件详情
export function getEmergencyDetail(id) {
  return request({
    url: `/admin/emergency/${id}`,
    method: 'get',
  })
}

// 处理应急事件
export function processEmergency(id, data) {
  return request({
    url: `/admin/emergency/${id}/process`,
    method: 'post',
    data,
  })
}

// 获取应急类型列表
export function getEmergencyTypeList(params) {
  return request({
    url: '/admin/emergency/type',
    method: 'get',
    params,
  })
}

// 新增应急类型
export function createEmergencyType(data) {
  return request({
    url: '/admin/emergency/type',
    method: 'post',
    data,
  })
}

// 编辑应急类型
export function updateEmergencyType(id, data) {
  return request({
    url: `/admin/emergency/type/${id}`,
    method: 'put',
    data,
  })
}

// 删除应急类型
export function deleteEmergencyType(id) {
  return request({
    url: `/admin/emergency/type/${id}`,
    method: 'delete',
  })
}

// 获取启用的应急类型（下拉选择用）
export function getActiveEmergencyTypes() {
  return request({
    url: '/admin/emergency/type',
    method: 'get',
    params: { status: 1, page_size: 100 },
  })
}
