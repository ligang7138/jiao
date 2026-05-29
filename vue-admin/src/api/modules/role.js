import request from '../request'

export function getRoleList(params) {
  return request({
    url: '/admin/roles',
    method: 'get',
    params,
  })
}

export function getRoleOptions(params) {
  return request({
    url: '/admin/roles/options',
    method: 'get',
    params,
  })
}

export function getRoleDetail(id) {
  return request({
    url: `/admin/roles/${id}`,
    method: 'get',
  })
}

export function createRole(data) {
  return request({
    url: '/admin/roles',
    method: 'post',
    data,
  })
}

export function updateRole(id, data) {
  return request({
    url: `/admin/roles/${id}`,
    method: 'put',
    data,
  })
}

export function updateRoleStatus(id, status) {
  return request({
    url: `/admin/roles/${id}/status`,
    method: 'put',
    data: { status },
  })
}

export function getRolePrivilege(id) {
  return request({
    url: `/admin/roles/${id}/privilege`,
    method: 'get',
  })
}

export function updateRolePrivilege(id, permissionIds) {
  return request({
    url: `/admin/roles/${id}/privilege`,
    method: 'put',
    data: { permission_ids: permissionIds },
  })
}
