import request from '../request'

/**
 * 获取岗位列表
 */
export function getPostList(params) {
  return request({
    url: '/admin/posts',
    method: 'get',
    params,
  })
}

/**
 * 获取岗位详情
 */
export function getPostDetail(id) {
  return request({
    url: `/admin/posts/${id}`,
    method: 'get',
  })
}

/**
 * 新增岗位
 */
export function createPost(data) {
  return request({
    url: '/admin/posts',
    method: 'post',
    data,
  })
}

/**
 * 编辑岗位
 */
export function updatePost(id, data) {
  return request({
    url: `/admin/posts/${id}`,
    method: 'put',
    data,
  })
}

/**
 * 删除岗位
 */
export function deletePost(id) {
  return request({
    url: `/admin/posts/${id}`,
    method: 'delete',
  })
}

/**
 * 批量删除岗位
 */
export function batchDeletePosts(ids) {
  return request({
    url: '/admin/posts/batch-delete',
    method: 'post',
    data: { ids },
  })
}

/**
 * 更新岗位状态
 */
export function updatePostStatus(id, status) {
  return request({
    url: `/admin/posts/${id}/status`,
    method: 'put',
    data: { status },
  })
}

/**
 * 更新岗位权限
 */
export function updatePostPrivilege(id, data) {
  return request({
    url: `/admin/posts/${id}/privilege`,
    method: 'put',
    data,
  })
}

/**
 * 获取岗位权限
 */
export function getPostPrivilege(id) {
  return request({
    url: `/admin/posts/${id}/privilege`,
    method: 'get',
  })
}

/**
 * 获取岗位选项列表（用于下拉选择）
 */
export function getPostOptions(params) {
  return request({
    url: '/admin/posts/options',
    method: 'get',
    params,
  })
}
