import { legacyRouteMap } from '@/router/legacyRouteMap'

export function findByLegacyDo(legacyDo) {
  return legacyRouteMap.find((item) => item.legacyDo === legacyDo)
}

export function findByPermission(permission) {
  return legacyRouteMap.filter((item) => item.permission === permission)
}

export function findByRoute(route) {
  return legacyRouteMap.find((item) => item.route === route)
}
