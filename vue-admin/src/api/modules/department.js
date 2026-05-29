import request from '../request'

/**
 * 获取部门列表
 */
export function getDepartmentList(params) {
  return request({
    url: '/admin/departments',
    method: 'get',
    params,
  })
}

/**
 * 获取部门树形结构
 */
export function getDepartmentTree(params) {
  return request({
    url: '/admin/departments/tree',
    method: 'get',
    params,
  })
}

/**
 * 获取部门详情
 */
export function getDepartmentDetail(id) {
  return request({
    url: `/admin/departments/${id}`,
    method: 'get',
  })
}

/**
 * 新增部门
 */
export function createDepartment(data) {
  return request({
    url: '/admin/departments',
    method: 'post',
    data,
  })
}

/**
 * 编辑部门
 */
export function updateDepartment(id, data) {
  return request({
    url: `/admin/departments/${id}`,
    method: 'put',
    data,
  })
}

/**
 * 删除部门
 */
export function deleteDepartment(id) {
  return request({
    url: `/admin/departments/${id}`,
    method: 'delete',
  })
}

/**
 * 更新部门状态
 */
export function updateDepartmentStatus(id, status) {
  return request({
    url: `/admin/departments/${id}/status`,
    method: 'put',
    data: { status },
  })
}

/**
 * 获取部门选项列表（用于下拉选择）
 */
export function getDepartmentOptions(params) {
  return request({
    url: '/admin/departments/options',
    method: 'get',
    params,
  })
}
