import request from '../request'

/**
 * 获取权限列表
 */
export function getPrivilegeList(params) {
  return request({
    url: '/admin/privileges',
    method: 'get',
    params,
  })
}

/**
 * 获取权限树形结构
 */
export function getPrivilegeTree(params) {
  return request({
    url: '/admin/privileges/tree',
    method: 'get',
    params,
  })
}

/**
 * 获取权限详情
 */
export function getPrivilegeDetail(id) {
  return request({
    url: `/admin/privileges/${id}`,
    method: 'get',
  })
}

/**
 * 新增权限
 */
export function createPrivilege(data) {
  return request({
    url: '/admin/privileges',
    method: 'post',
    data,
  })
}

/**
 * 编辑权限
 */
export function updatePrivilege(id, data) {
  return request({
    url: `/admin/privileges/${id}`,
    method: 'put',
    data,
  })
}

/**
 * 删除权限
 */
export function deletePrivilege(id) {
  return request({
    url: `/admin/privileges/${id}`,
    method: 'delete',
  })
}

/**
 * 批量删除权限
 */
export function batchDeletePrivileges(ids) {
  return request({
    url: '/admin/privileges/batch-delete',
    method: 'post',
    data: { ids },
  })
}

/**
 * 更新权限状态
 */
export function updatePrivilegeStatus(id, status) {
  return request({
    url: `/admin/privileges/${id}/status`,
    method: 'put',
    data: { status },
  })
}

/**
 * 获取权限选项列表（用于下拉选择）
 */
export function getPrivilegeOptions(params) {
  return request({
    url: '/admin/privileges/options',
    method: 'get',
    params,
  })
}
