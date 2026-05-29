import { defineConfig, loadEnv } from 'vite'
import vue from '@vitejs/plugin-vue'
import path from 'path'
import AutoImport from 'unplugin-auto-import/vite'
import Components from 'unplugin-vue-components/vite'
import { ElementPlusResolver } from 'unplugin-vue-components/resolvers'
import { createSvgIconsPlugin } from 'vite-plugin-svg-icons'
import viteCompression from 'vite-plugin-compression'

function apiHealthCheckPlugin(apiTarget) {
  return {
    name: 'api-health-check',
    configureServer() {
      const healthUrl = `${apiTarget.replace(/\/$/, '')}/api/v1/health`
      setTimeout(async () => {
        try {
          const res = await fetch(healthUrl)
          if (res.ok) {
            console.log(`\n  ➜  API: ${apiTarget} ✓\n`)
            return
          }
          console.warn(`\n  ⚠ API 响应异常: ${healthUrl} (HTTP ${res.status})\n`)
        } catch {
          console.warn('\n  ⚠ 无法连接 Laravel API:', apiTarget)
          console.warn('     请先启动后端: cd laravel-api && docker compose up -d')
          console.warn('     或一键启动: npm run dev:all\n')
        }
      }, 800)
    },
  }
}

export default defineConfig(({ mode }) => {
  const env = loadEnv(mode, process.cwd(), '')
  const apiProxyTarget = env.VITE_API_PROXY_TARGET || 'http://127.0.0.1:8001'

  return {
    plugins: [
      vue(),
      apiHealthCheckPlugin(apiProxyTarget),
      AutoImport({
        imports: ['vue', 'vue-router', 'pinia'],
        resolvers: [ElementPlusResolver()],
        dts: 'src/auto-imports.d.ts',
      }),
      Components({
        resolvers: [ElementPlusResolver()],
        dts: 'src/components.d.ts',
      }),
      createSvgIconsPlugin({
        iconDirs: [path.resolve(process.cwd(), 'src/assets/icons')],
        symbolId: 'icon-[dir]-[name]',
      }),
      viteCompression({
        algorithm: 'gzip',
        ext: '.gz',
      }),
    ],
    resolve: {
      alias: {
        '@': path.resolve(__dirname, 'src'),
      },
    },
    server: {
      port: 3000,
      strictPort: true,
      host: true,
      open: '/login',
      proxy: {
        '/api': {
          target: apiProxyTarget,
          changeOrigin: true,
          secure: false,
        },
      },
    },
    build: {
      outDir: 'dist',
      sourcemap: false,
      chunkSizeWarningLimit: 1500,
      rollupOptions: {
        output: {
          manualChunks: {
            vue: ['vue', 'vue-router', 'pinia'],
            elementPlus: ['element-plus', '@element-plus/icons-vue'],
            echarts: ['echarts'],
          },
        },
      },
    },
  }
})
