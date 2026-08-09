<template>
  <div class="info-panel">
    <a-descriptions
      bordered
      size="small"
      :column="2"
      :title="doc.title || '未设置 title 注释'"
    >
      <a-descriptions-item label="接口地址">
        <span class="info-url">{{ doc.url || '请设置 url 注释' }}</span>
        <a-tag color="arcoblue" class="method-tag">{{ (doc.method || 'GET').toUpperCase() }}</a-tag>
      </a-descriptions-item>
      <a-descriptions-item label="作者">{{ doc.author || '请设置 author 注释' }}</a-descriptions-item>
      <a-descriptions-item label="描述" :span="2">{{ doc.description || '—' }}</a-descriptions-item>
    </a-descriptions>

    <a-typography-paragraph class="curl-block">
      <code>{{ doc.curl_code || '暂无 curl 命令' }}</code>
      <template v-if="doc.curl_code">
        <a-button type="text" size="small" @click="copyCurl">
          <template #icon><icon-copy /></template>
          复制
        </a-button>
      </template>
    </a-typography-paragraph>

    <template v-if="doc.header && doc.header.length">
      <h3 class="section-title">请求 Headers</h3>
      <a-table :columns="headerColumns" :data="doc.header" :pagination="false" size="small" />
    </template>

    <template v-if="doc.param && doc.param.length">
      <h3 class="section-title">接口参数</h3>
      <a-table :columns="paramColumns" :data="doc.param" :pagination="false" size="small" />
    </template>

    <template v-if="doc.remark">
      <h3 class="section-title">备注说明</h3>
      <a-alert>{{ doc.remark }}</a-alert>
    </template>

    <h3 class="section-title">返回结果</h3>
    <div class="return-box">
      <JsonViewer v-if="returnDisplay" :value="returnDisplay" />
      <a-empty v-else description="无返回结构" />
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { Message, type TableColumnData } from '@arco-design/web-vue'
import type { DocInfo, ReturnNode } from '@/types'
import JsonViewer from './JsonViewer.vue'

const props = defineProps<{ doc: DocInfo }>()

const headerColumns: TableColumnData[] = [
  { title: '名称', dataIndex: 'name' },
  {
    title: '是否必须',
    dataIndex: 'require',
    render: ({ record }) =>
      String(record.require) === '1' ? '必填' : '非必填',
  },
  { title: '默认值', dataIndex: 'default' },
  { title: '说明', dataIndex: 'desc' },
]

const paramColumns: TableColumnData[] = [
  { title: '参数名字', dataIndex: 'name' },
  { title: '类型', dataIndex: 'type' },
  {
    title: '是否必须',
    dataIndex: 'require',
    render: ({ record }) =>
      String(record.require) === '1' ? '必填' : '非必填',
  },
  { title: '默认值', dataIndex: 'default' },
  { title: '其他', dataIndex: 'other' },
  { title: '说明', dataIndex: 'desc' },
]

/** 将后端的 return 树节点转成普通 JSON 结构供 JsonViewer 渲染 */
function treeToJson(nodes: ReturnNode[] = []): Record<string, unknown> {
  const obj: Record<string, unknown> = {}
  for (const n of nodes) {
    if (n.format === 'colon' && n.raw) {
      const idx = n.raw.indexOf(':')
      if (idx > 0) obj[n.raw.slice(0, idx).trim()] = n.raw.slice(idx + 1).trim()
      continue
    }
    if (n.children && n.children.length) {
      const child = treeToJson(n.children)
      obj[n.name] = n.isArray ? [child] : child
    } else {
      obj[n.name] = n.desc || ''
    }
  }
  return obj
}

const returnDisplay = computed<Record<string, unknown> | null>(() => {
  const rj = props.doc.return_json
  if (!rj) return null
  return { ...rj.base, ...treeToJson(rj.tree) }
})

async function copyCurl() {
  if (!props.doc.curl_code) return
  try {
    await navigator.clipboard.writeText(props.doc.curl_code)
    Message.success('已复制')
  } catch {
    Message.error('复制失败，请手动复制')
  }
}
</script>

<style scoped>
.info-panel {
  max-width: 960px;
}
.method-tag {
  margin-left: 8px;
}
.curl-block {
  margin-top: 16px;
  padding: 8px 12px;
  background: var(--color-fill-2);
  border-radius: 4px;
  word-break: break-all;
}
.section-title {
  margin: 20px 0 8px;
  font-size: 16px;
}
.return-box {
  padding: 12px;
  background: var(--color-fill-2);
  border-radius: 4px;
  overflow: auto;
}
</style>
