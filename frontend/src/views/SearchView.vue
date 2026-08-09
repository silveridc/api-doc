<template>
  <div class="search-page">
    <div class="search-head">
      <h1>{{ store.config.title }}</h1>
      <div class="search-box">
        <a-auto-complete
          v-model="keyword"
          :data="suggestions"
          placeholder="接口名称/接口信息/作者/接口地址"
          style="width: 460px"
          @search="onSuggest"
          @select="onSuggestSelect"
          @press-enter="doSearch"
        />
        <a-button type="primary" @click="doSearch">搜索</a-button>
      </div>
    </div>

    <div class="search-result">
      <a-empty v-if="done && results.length === 0" description="未找到相关接口" />
      <a-list v-else :data="results">
        <template #item="{ item }">
          <a-list-item class="result-item" @click="openResult(item)">
            <a-list-item-meta
              :title="item.title"
              :description="item.url || '—'"
            >
              <template #avatar>
                <a-tag color="arcoblue">{{ item.author || '未知' }}</a-tag>
              </template>
            </a-list-item-meta>
          </a-list-item>
        </template>
      </a-list>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useDocStore } from '@/stores/doc'
import { searchApi } from '@/api'
import type { SearchResult } from '@/types'

const store = useDocStore()
const route = useRoute()
const router = useRouter()

const keyword = ref('')
const suggestions = ref<string[]>([])
const results = ref<SearchResult[]>([])
const done = ref(false)

onMounted(async () => {
  store.init()
  const q = (route.query.q as string) || ''
  if (q) {
    keyword.value = q
    await doSearch()
  }
})

// 联想输入防抖，避免每次键入都发起请求
let suggestTimer: number | undefined

function onSuggest(query: string) {
  if (suggestTimer) window.clearTimeout(suggestTimer)
  if (!query.trim()) {
    suggestions.value = []
    return
  }
  suggestTimer = window.setTimeout(async () => {
    try {
      const list = await searchApi(query.trim())
      suggestions.value = list.map((i) => i.title || '').filter(Boolean)
    } catch {
      suggestions.value = []
    }
  }, 300)
}

onUnmounted(() => {
  if (suggestTimer) window.clearTimeout(suggestTimer)
})

function onSuggestSelect(value: string) {
  keyword.value = value
}

async function doSearch() {
  const q = keyword.value.trim()
  if (!q) return
  try {
    results.value = await searchApi(q)
  } catch {
    results.value = []
  }
  done.value = true
}

function openResult(item: SearchResult) {
  if (item.name) {
    store.openDoc(item.name, item.title || item.name)
    router.push('/')
  }
}
</script>

<style scoped>
.search-page {
  max-width: 860px;
  margin: 0 auto;
  padding: 40px 20px;
}
.search-head {
  text-align: center;
  margin-bottom: 32px;
}
.search-box {
  display: flex;
  justify-content: center;
  gap: 12px;
  margin-top: 24px;
}
.search-result {
  margin-top: 24px;
}
.result-item {
  cursor: pointer;
}
.result-item:hover {
  background: var(--color-fill-2);
}
</style>
