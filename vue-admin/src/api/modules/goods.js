import request from '@/api/request'

/**
 * 商品管理 API
 */

// 获取商品列表
export function getGoodsList(params) {
  return request({
    url: '/admin/goods',
    method: 'get',
    params,
  })
}

// 获取商品详情
export function getGoodsDetail(id) {
  return request({
    url: `/admin/goods/${id}`,
    method: 'get',
  })
}

// 创建商品
export function createGoods(data) {
  return request({
    url: '/admin/goods',
    method: 'post',
    data,
  })
}

// 更新商品
export function updateGoods(id, data) {
  return request({
    url: `/admin/goods/${id}`,
    method: 'put',
    data,
  })
}

// 删除商品
export function deleteGoods(id) {
  return request({
    url: `/admin/goods/${id}`,
    method: 'delete',
  })
}

// 商品上架
export function goodsUp(id) {
  return request({
    url: `/admin/goods/${id}/publish`,
    method: 'post',
  })
}

// 商品下架
export function goodsDown(id, data) {
  return request({
    url: `/admin/goods/${id}/unpublish`,
    method: 'post',
    data,
  })
}

// 批量上架
export function batchGoodsUp(ids) {
  return request({
    url: '/admin/goods/batch-publish',
    method: 'post',
    data: { ids },
  })
}

// 批量下架
export function batchGoodsDown(ids) {
  return request({
    url: '/admin/goods/batch-unpublish',
    method: 'post',
    data: { ids },
  })
}

// 获取商品上下架记录
export function getGoodsStatusLog(id) {
  return request({
    url: `/admin/goods/${id}/status-log`,
    method: 'get',
  })
}

// 获取商品历史价格
export function getGoodsHistoryPrice(id, params) {
  return request({
    url: `/admin/goods/${id}/history-price`,
    method: 'get',
    params,
  })
}

// 导入商品
export function importGoods(data) {
  return request({
    url: '/admin/goods/import',
    method: 'post',
    data,
    headers: {
      'Content-Type': 'multipart/form-data',
    },
  })
}

// 导出商品
export function exportGoods(params) {
  return request({
    url: '/admin/goods/export',
    method: 'get',
    params,
    responseType: 'blob',
  })
}

// 获取商品单位列表
export function getGoodsUnits() {
  return request({
    url: '/admin/goods/units',
    method: 'get',
  })
}

// 获取供应商商品列表
export function getSupplierGoods(supplierId) {
  return request({
    url: `/admin/suppliers/${supplierId}/goods`,
    method: 'get',
  })
}
