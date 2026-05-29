import request from '@/api/request'

export function useExport() {
  async function download(url, params = {}, filename = 'export.xlsx') {
    const response = await request({
      url,
      method: 'get',
      params,
      responseType: 'blob',
    })
    const blob = response instanceof Blob ? response : response.data
    const objectUrl = window.URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = objectUrl
    link.download = filename
    link.click()
    window.URL.revokeObjectURL(objectUrl)
  }

  return { download }
}
