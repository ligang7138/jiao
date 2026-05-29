import request from '@/api/request'

/**
 * 价格网管理 API
 */

// 获取指导价列表
export function getJiagewangList(params) {
  return request({
    url: '/admin/jiagewang',
    method: 'get',
    params,
  })
}

// 获取指导价详情
export function getJiagewangDetail(id) {
  return request({
    url: `/admin/jiagewang/${id}`,
    method: 'get',
  })
}

// 编辑指导价
export function updateJiagewang(id, data) {
  return request({
    url: `/admin/jiagewang/${id}`,
    method: 'put',
    data,
  })
}

// 删除指导价
export function deleteJiagewang(id) {
  return request({
    url: `/admin/jiagewang/${id}`,
    method: 'delete',
  })
}

// 批量删除指导价
export function batchDeleteJiagewang(ids) {
  return request({
    url: '/admin/jiagewang/batch-delete',
    method: 'post',
    data: { ids },
  })
}

// 导入指导价
export function importJiagewang(data) {
  return request({
    url: '/admin/jiagewang/import',
    method: 'post',
    data,
    headers: {
      'Content-Type': 'multipart/form-data',
    },
  })
}

// 获取导入模板
export function getImportTemplate() {
  return request({
    url: '/admin/jiagewang/template',
    method: 'get',
    responseType: 'blob',
  })
}

// 获取历史记录
export function getJiagewangHistory(params) {
  return request({
    url: '/admin/jiagewang/history',
    method: 'get',
    params,
  })
}

// 获取商品匹配列表
export function getJiagewangMatch(params) {
  return request({
    url: '/admin/jiagewang/match',
    method: 'get',
    params,
  })
}

// 获取未匹配商品列表
export function getJiagewangNoMatch(params) {
  return request({
    url: '/admin/jiagewang/no-match',
    method: 'get',
    params,
  })
}

// 手动匹配商品
export function matchGoods(data) {
  return request({
    url: '/admin/jiagewang/match',
    method: 'post',
    data,
  })
}

// 取消匹配
export function unmatchGoods(id) {
  return request({
    url: `/admin/jiagewang/match/${id}`,
    method: 'delete',
  })
}

// 导出指导价
export function exportJiagewang(params) {
  return request({
    url: '/admin/jiagewang/export',
    method: 'get',
    params,
    responseType: 'blob',
  })
}
