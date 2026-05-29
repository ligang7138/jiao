// 统一导出指令
import permission from './permission'
import loading from './loading'

export function setupDirectives(app) {
  app.directive('permission', permission)
  app.directive('loading', loading)
}

export { permission, loading }
