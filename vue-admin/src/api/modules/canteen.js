import request from '@/api/request'

export function getActiveCanteens(params) {
  return request({
    url: '/admin/canteens/active',
    method: 'get',
    params,
  })
}

export function getCanteenList(params) {
  return request({
    url: '/admin/canteens',
    method: 'get',
    params,
  })
}
