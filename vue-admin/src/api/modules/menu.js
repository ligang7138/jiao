import request from '@/api/request'

export function getMenus() {
  return request({
    url: '/admin/menus',
    method: 'get',
  })
}
