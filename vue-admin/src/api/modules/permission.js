import request from '../request'

export function getPermissionList(params) {
  return request({
    url: '/admin/permissions',
    method: 'get',
    params,
  })
}

export function getPermissionTree(params) {
  return request({
    url: '/admin/permissions/tree',
    method: 'get',
    params,
  })
}

export function getPermissionModules() {
  return request({
    url: '/admin/permissions/modules',
    method: 'get',
  })
}

export function getPermissionControls(id) {
  return request({
    url: '/admin/permissions/controls',
    method: 'get',
    params: { id },
  })
}

export function getPermissionDetail(id) {
  return request({
    url: `/admin/permissions/${id}`,
    method: 'get',
  })
}

export function createPermission(data) {
  return request({
    url: '/admin/permissions',
    method: 'post',
    data,
  })
}

export function updatePermission(id, data) {
  return request({
    url: `/admin/permissions/${id}`,
    method: 'put',
    data,
  })
}

export function updatePermissionStatus(id, status) {
  return request({
    url: `/admin/permissions/${id}/status`,
    method: 'put',
    data: { status },
  })
}
