/**
 * 兼容新旧响应结构：
 * - 新：{ code, msg, data }
 * - 旧：{ status, message|data }
 */
export function normalizeResponse(payload = {}) {
  const code = Number(payload.code ?? payload.status ?? 50000)
  const msg = payload.msg ?? payload.message ?? (typeof payload.data === 'string' ? payload.data : '请求失败')
  const data = payload.data ?? null

  return {
    code,
    msg,
    data,
    raw: payload,
  }
}

export function isSuccess(payload = {}) {
  return normalizeResponse(payload).code === 200
}
