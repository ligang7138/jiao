import request from '@/api/request'

export function getDictionaries(keys = []) {
  return request({
    url: '/admin/dictionaries',
    method: 'get',
    params: {
      keys: keys.join(','),
    },
  })
}
