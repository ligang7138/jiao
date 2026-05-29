import request from '../request'

/**
 * 登录
 */
export function login(data) {
  return request({
    url: '/auth/login',
    method: 'post',
    data,
  })
}

/**
 * 登出
 */
export function logout() {
  return request({
    url: '/auth/logout',
    method: 'post',
  })
}

/**
 * 获取用户信息
 */
export function getInfo() {
  return request({
    url: '/auth/me',
    method: 'get',
  })
}

/**
 * 刷新 Token
 */
export function refreshToken() {
  return request({
    url: '/auth/refresh',
    method: 'post',
  })
}

/**
 * 修改密码
 */
export function updatePassword(data) {
  return request({
    url: '/auth/password',
    method: 'put',
    data,
  })
}

/**
 * 获取验证码
 */
export function getCaptcha() {
  return request({
    url: '/auth/captcha',
    method: 'get',
  })
}
