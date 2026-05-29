import request from '../request'

export function getSystemLogList(params) {
  return request({
    url: '/admin/logs',
    method: 'get',
    params,
  })
}

export function getSystemLogDetail(id) {
  return request({
    url: `/admin/logs/${id}`,
    method: 'get',
  })
}

export function exportSystemLogs(params) {
  return request({
    url: '/admin/logs/export',
    method: 'get',
    params,
    responseType: 'blob',
  })
}
