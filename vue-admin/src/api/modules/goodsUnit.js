import request from '@/api/request'

export function getGoodsUnitList(params) {
  return request({
    url: '/admin/goods-units',
    method: 'get',
    params,
  })
}

export function createGoodsUnit(data) {
  return request({
    url: '/admin/goods-units',
    method: 'post',
    data,
  })
}

export function updateGoodsUnit(id, data) {
  return request({
    url: `/admin/goods-units/${id}`,
    method: 'put',
    data,
  })
}

export function setGoodsUnitStatus(id, status) {
  return request({
    url: `/admin/goods-units/${id}/status`,
    method: 'put',
    data: { status },
  })
}
