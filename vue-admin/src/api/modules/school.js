import request from '@/api/request'

export function getSchoolOptions() {
  return request({
    url: '/admin/schools/options',
    method: 'get',
  })
}

export function getSchoolList(params) {
  return request({
    url: '/admin/schools',
    method: 'get',
    params,
  })
}

export function getSchoolDetail(id) {
  return request({
    url: `/admin/schools/${id}`,
    method: 'get',
  })
}

export function createSchool(data) {
  return request({
    url: '/admin/schools',
    method: 'post',
    data,
  })
}

export function updateSchool(id, data) {
  return request({
    url: `/admin/schools/${id}`,
    method: 'put',
    data,
  })
}

export function changeSchoolStatus(id, status) {
  return request({
    url: `/admin/schools/${id}/status`,
    method: 'put',
    data: { status },
  })
}

export function getActiveSchools() {
  return request({
    url: '/admin/schools/active',
    method: 'get',
  })
}

export function getCanteenList(params) {
  return request({
    url: '/admin/canteens',
    method: 'get',
    params,
  })
}

export function getCanteenDetail(id) {
  return request({
    url: `/admin/canteens/${id}`,
    method: 'get',
  })
}

export function createCanteen(data) {
  return request({
    url: '/admin/canteens',
    method: 'post',
    data,
  })
}

export function updateCanteen(id, data) {
  return request({
    url: `/admin/canteens/${id}`,
    method: 'put',
    data,
  })
}

export function changeCanteenStatus(id, status) {
  return request({
    url: `/admin/canteens/${id}/status`,
    method: 'put',
    data: { status },
  })
}

export function getActiveCanteens(params) {
  return request({
    url: '/admin/canteens/active',
    method: 'get',
    params,
  })
}
