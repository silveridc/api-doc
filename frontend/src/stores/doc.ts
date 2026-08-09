import { defineStore } from 'pinia'
import { ref } from 'vue'
import { getConfig, getList, getInfo } from '@/api'
import type { SiteConfig, TreeNode, DocInfo } from '@/types'

export interface DocTab {
  key: string
  name: string
  title: string
  loading?: boolean
  info?: DocInfo | null
}

export const useDocStore = defineStore('doc', () => {
  const config = ref<SiteConfig>({ title: '', version: '', copyright: '' })
  const treeList = ref<TreeNode[]>([])
  const tabs = ref<DocTab[]>([])
  const activeKey = ref('')

  /** 拉取站点配置与目录树（仅一次） */
  async function init() {
    if (!config.value.title) {
      try {
        config.value = await getConfig()
        document.title = config.value.title || 'API接口文档'
      } catch {
        /* 忽略，接口列表会再次触发 401 跳转 */
      }
    }
    if (!treeList.value.length) {
      try {
        const res = await getList()
        treeList.value = res.list
      } catch {
        /* 401 由拦截器统一处理 */
      }
    }
  }

  /** 打开（或激活）一个接口 tab */
  async function openDoc(name: string, title: string) {
    const exist = tabs.value.find((t) => t.key === name)
    if (exist) {
      activeKey.value = exist.key
      return
    }
    const tab: DocTab = { key: name, name, title, loading: true }
    tabs.value.push(tab)
    activeKey.value = name
    try {
      const info = await getInfo(name)
      const idx = tabs.value.findIndex((t) => t.key === name)
      if (idx >= 0) {
        tabs.value[idx].info = info
        tabs.value[idx].loading = false
      }
    } catch {
      const idx = tabs.value.findIndex((t) => t.key === name)
      if (idx >= 0) {
        tabs.value[idx].loading = false
      }
    }
  }

  function closeTab(key: string) {
    const idx = tabs.value.findIndex((t) => t.key === key)
    if (idx < 0) return
    tabs.value.splice(idx, 1)
    if (activeKey.value === key) {
      activeKey.value = tabs.value.length ? tabs.value[tabs.value.length - 1].key : ''
    }
  }

  return { config, treeList, tabs, activeKey, init, openDoc, closeTab }
})
