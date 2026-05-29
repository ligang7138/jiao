import request from '../request'

/**
 * 获取用户列表
 */
export function getUserList(params) {
  return request({
    url: '/admin/users',
    method: 'get',
    params,
  })
}

/**
 * 获取用户详情
 */
export function getUserDetail(id) {
  return request({
    url: `/admin/users/${id}`,
    method: 'get',
  })
}

/**
 * 新增用户
 */
export function createUser(data) {
  return request({
    url: '/admin/users',
    method: 'post',
    data,
  })
}

/**
 * 编辑用户
 */
export function updateUser(id, data) {
  return request({
    url: `/admin/users/${id}`,
    method: 'put',
    data,
  })
}

/**
 * 删除用户
 */
export function deleteUser(id) {
  return request({
    url: `/admin/users/${id}`,
    method: 'delete',
  })
}

/**
 * 批量删除用户
 */
export function batchDeleteUsers(ids) {
  return request({
    url: '/admin/users/batch-delete',
    method: 'post',
    data: { ids },
  })
}

/**
 * 更新用户状态
 */
export function updateUserStatus(id, status) {
  return request({
    url: `/admin/users/${id}/status`,
    method: 'put',
    data: { status },
  })
}

/**
 * 重置用户密码
 */
export function resetUserPassword(id, data) {
  return request({
    url: `/admin/users/${id}/reset-password`,
    method: 'post',
    data,
  })
}

/**
 * 更新用户权限
 */
export function updateUserPrivilege(id, data) {
  return request({
    url: `/admin/users/${id}/privilege`,
    method: 'put',
    data,
  })
}

/**
 * 获取用户权限
 */
export function getUserPrivilege(id) {
  return request({
    url: `/admin/users/${id}/privilege`,
    method: 'get',
  })
}

/**
 * 获取用户选项列表（用于下拉选择）
 */
export function getUserOptions(params) {
  return request({
    url: '/admin/users/options',
    method: 'get',
    params,
  })
}
