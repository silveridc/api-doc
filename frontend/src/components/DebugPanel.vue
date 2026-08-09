<template>
  <div class="debug-panel">
    <a-form layout="vertical" :model="form" style="max-width: 720px">
      <a-form-item label="接口地址">
        <a-input v-model="form.url" placeholder="接口地址" />
      </a-form-item>

      <a-form-item label="请求 Headers">
        <div v-for="(row, i) in headerRows" :key="'h' + i" class="row-line">
          <a-input v-model="row.name" placeholder="Header 名" style="width: 200px" />
          <a-input v-model="row.value" placeholder="值" style="width: 260px" />
          <a-button type="text" status="danger" @click="headerRows.splice(i, 1)">
            <template #icon><icon-delete /></template>
          </a-button>
        </div>
        <a-button type="dashed" size="small" @click="addHeader">
          <template #icon><icon-plus /></template>
          增加 Header
        </a-button>
      </a-form-item>

      <a-form-item label="提交方式">
        <a-select v-model="form.method" style="width: 200px">
          <a-option value="GET">GET</a-option>
          <a-option value="POST">POST</a-option>
          <a-option value="PUT">PUT</a-option>
          <a-option value="DELETE">DELETE</a-option>
        </a-select>
      </a-form-item>

      <a-form-item label="Cookie">
        <a-textarea v-model="form.cookie" :auto-size="{ minRows: 2, maxRows: 4 }" />
      </a-form-item>

      <a-form-item label="接口参数">
        <div v-for="(row, i) in paramRows" :key="'p' + i" class="row-line">
          <a-input v-model="row.name" placeholder="参数名" style="width: 200px" />
          <a-input v-model="row.value" placeholder="值" style="width: 260px" />
          <a-button type="text" status="danger" @click="paramRows.splice(i, 1)">
            <template #icon><icon-delete /></template>
          </a-button>
        </div>
        <a-button type="dashed" size="small" @click="addParam">
          <template #icon><icon-plus /></template>
          增加参数
        </a-button>
      </a-form-item>

      <a-form-item>
        <a-button type="primary" :loading="sending" @click="send">发送测试</a-button>
      </a-form-item>
    </a-form>

    <a-divider />

    <div class="debug-result">
      <div class="debug-result-title">返回结果</div>
      <div v-if="result !== null" class="debug-result-body">
        <a-tag :color="resultStatus === '200' ? 'green' : 'red'" class="status-tag">
          {{ resultStatus }}
        </a-tag>
        <JsonViewer :value="result" />
      </div>
      <a-empty v-else description="尚未发送请求" />
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue'
import { Message } from '@arco-design/web-vue'
import { debugApi } from '@/api'
import type { DocInfo } from '@/types'
import JsonViewer from './JsonViewer.vue'

const props = defineProps<{ doc: DocInfo }>()

// @url 为完整 URL 时直接使用，相对路径才拼接站点 origin
function buildDefaultUrl(raw: string): string {
  const u = raw || ''
  if (/^https?:\/\//i.test(u)) return u
  return window.location.origin + u
}

const form = reactive({
  url: buildDefaultUrl(props.doc.url || ''),
  method: (props.doc.method || 'GET').toUpperCase(),
  cookie: document.cookie,
})

const headerRows = ref(
  (props.doc.header || []).map((h) => ({ name: h.name, value: '' }))
)
const paramRows = ref(
  (props.doc.param || []).map((p) => ({ name: p.name, value: '' }))
)

const sending = ref(false)
const result = ref<unknown>(null)
const resultStatus = ref('')

function addHeader() {
  headerRows.value.push({ name: '', value: '' })
}
function addParam() {
  paramRows.value.push({ name: '', value: '' })
}

async function send() {
  sending.value = true
  result.value = null
  try {
    const payload: Record<string, unknown> = {
      url: form.url,
      method_type: form.method,
      cookie: form.cookie,
      header: headerRows.value.reduce<Record<string, string>>((acc, r) => {
        if (r.name) acc[r.name] = r.value
        return acc
      }, {}),
    }
    paramRows.value.forEach((r) => {
      if (r.name) payload[r.name] = r.value
    })
    const res = await debugApi(payload)
    resultStatus.value = res.status
    result.value = res.result
    if (res.status !== '200') {
      Message.error(res.message)
    }
  } catch {
    Message.error('请求失败')
  } finally {
    sending.value = false
  }
}
</script>

<style scoped>
.row-line {
  display: flex;
  gap: 8px;
  margin-bottom: 8px;
}
.debug-result {
  max-width: 720px;
}
.debug-result-title {
  font-size: 16px;
  font-weight: 500;
  margin-bottom: 8px;
}
.debug-result-body {
  padding: 12px;
  background: var(--color-fill-2);
  border-radius: 4px;
  overflow: auto;
}
.status-tag {
  margin-bottom: 8px;
}
</style>
