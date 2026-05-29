import request from '@/api/request'

export function getGroupList(params) {
  return request({
    url: '/admin/group',
    method: 'get',
    params,
  })
}

export function getGroupOptions(params) {
  return request({
    url: '/admin/group/options',
    method: 'get',
    params,
  })
}

export function getGroupDetail(id) {
  return request({
    url: `/admin/group/${id}`,
    method: 'get',
  })
}

export function createGroup(data) {
  return request({
    url: '/admin/group',
    method: 'post',
    data,
  })
}

export function updateGroup(id, data) {
  return request({
    url: `/admin/group/${id}`,
    method: 'put',
    data,
  })
}

export function deleteGroup(id) {
  return request({
    url: `/admin/group/${id}`,
    method: 'delete',
  })
}

export function getGroupCanteens(groupId) {
  return request({
    url: `/admin/group/${groupId}/canteens`,
    method: 'get',
  })
}

export function addCanteenToGroup(groupId, data) {
  return request({
    url: `/admin/group/${groupId}/canteens`,
    method: 'post',
    data,
  })
}

export function removeCanteenFromGroup(groupId, canteenId) {
  return request({
    url: `/admin/group/${groupId}/canteens/${canteenId}`,
    method: 'delete',
  })
}

export function setCanteenAudit(groupId, canteenId) {
  return request({
    url: `/admin/group/${groupId}/canteens/${canteenId}/set-audit`,
    method: 'post',
  })
}

export function removeCanteenAudit(groupId, canteenId) {
  return request({
    url: `/admin/group/${groupId}/canteens/${canteenId}/remove-audit`,
    method: 'post',
  })
}
