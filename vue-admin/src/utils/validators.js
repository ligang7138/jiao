export const required = (message = '不能为空') => ({
  required: true,
  message,
  trigger: ['blur', 'change'],
})

export const mobile = (message = '手机号格式不正确') => ({
  pattern: /^1\d{10}$/,
  message,
  trigger: 'blur',
})

export const positiveNumber = (message = '请输入大于0的数字') => ({
  validator: (_rule, value, callback) => {
    if (value === '' || value === null || value === undefined || Number(value) <= 0) {
      callback(new Error(message))
      return
    }
    callback()
  },
  trigger: 'blur',
})
