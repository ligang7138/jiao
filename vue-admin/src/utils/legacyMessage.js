import { ElMessage, ElMessageBox } from 'element-plus'

export function successMessage(message) {
  ElMessage({
    message,
    type: 'success',
    duration: 2000,
  })
}

export function errorMessage(message) {
  ElMessage({
    message,
    type: 'error',
    duration: 3000,
  })
}

export function confirmMessage(content, title = '提示') {
  return ElMessageBox.confirm(content, title, {
    confirmButtonText: '是',
    cancelButtonText: '否',
    type: 'warning',
  })
}
