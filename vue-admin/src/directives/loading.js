import { createApp } from 'vue'
import LoadingComponent from './LoadingComponent.vue'

/**
 * Loading 指令
 * 用法: v-loading="loading"
 */
const loadingDirective = {
  mounted(el, binding) {
    const app = createApp(LoadingComponent)
    const instance = app.mount(document.createElement('div'))
    el.instance = instance

    if (binding.value) {
      appendEl(el)
    }
  },

  updated(el, binding) {
    if (binding.value !== binding.oldValue) {
      binding.value ? appendEl(el) : removeEl(el)
    }
  },
}

function appendEl(el) {
  el.style.position = 'relative'
  el.appendChild(el.instance.$el)
}

function removeEl(el) {
  el.style.position = ''
  const loadingEl = el.instance.$el
  if (loadingEl && loadingEl.parentNode) {
    loadingEl.parentNode.removeChild(loadingEl)
  }
}

export default loadingDirective
