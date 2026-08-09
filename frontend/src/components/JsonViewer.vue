<template>
  <div class="json-viewer">
    <template v-if="isObject">
      <div class="jv-bracket">{{ openBracket }}</div>
      <div v-for="(entry, i) in entries" :key="i" class="jv-line">
        <span class="jv-caret" @click="entry.collapsed = !entry.collapsed">
          <icon-down v-if="!entry.collapsed && isExpandable(entry.value)" />
          <icon-right v-else-if="isExpandable(entry.value)" />
          <span v-else class="jv-caret-space" />
        </span>
        <span class="jv-key">{{ entry.keyDisplay }}</span>
        <template v-if="isExpandable(entry.value)">
          <span v-if="entry.collapsed" class="jv-preview" @click="entry.collapsed = false">
            {{ previewOf(entry.value) }}
          </span>
          <JsonViewer v-else :value="entry.value" />
        </template>
        <span v-else class="jv-value" :class="typeClass(entry.value)">{{ scalarText(entry.value) }}</span>
        <span v-if="i < entries.length - 1" class="jv-comma">,</span>
      </div>
      <div class="jv-bracket">{{ closeBracket }}</div>
    </template>
    <span v-else class="jv-value" :class="typeClass(data)">{{ scalarText(data) }}</span>
  </div>
</template>

<script setup lang="ts">
import { computed, reactive, watch } from 'vue'

const props = defineProps<{ value: unknown }>()

interface Entry {
  key: string
  keyDisplay: string
  value: unknown
  collapsed: boolean
}

/** 字符串输入时尝试解析为 JSON */
const data = computed<unknown>(() => {
  if (typeof props.value === 'string') {
    try {
      return JSON.parse(props.value)
    } catch {
      return props.value
    }
  }
  return props.value
})

const isObject = computed(() => data.value !== null && typeof data.value === 'object')
const isArray = computed(() => Array.isArray(data.value))
const openBracket = computed(() => (isArray.value ? '[' : '{'))
const closeBracket = computed(() => (isArray.value ? ']' : '}'))

// 用响应式数组承载条目，collapsed 为普通布尔，展开/收起状态可正常更新
const entries = reactive<Entry[]>([])

// 浅引用跟踪：value 均以整体赋值/替换的方式更新，无需 deep 监听，避免递归 JSON 的深层遍历开销
watch(
  () => props.value,
  () => {
    entries.splice(0, entries.length)
    if (!(data.value !== null && typeof data.value === 'object')) return
    const d = data.value as Record<string, unknown> | unknown[]
    const isArr = Array.isArray(d)
    const keys = isArr
      ? (d as unknown[]).map((_, i) => String(i))
      : Object.keys(d as Record<string, unknown>)
    keys.forEach((k) => {
      entries.push({
        key: k,
        keyDisplay: isArr ? '' : `"${k}": `,
        value: isArr ? (d as unknown[])[Number(k)] : (d as Record<string, unknown>)[k],
        collapsed: false,
      })
    })
  },
  { immediate: true }
)

function isExpandable(v: unknown): boolean {
  return v !== null && typeof v === 'object'
}

function previewOf(v: unknown): string {
  if (Array.isArray(v)) return `[${v.length}项]`
  return `{${Object.keys(v as Record<string, unknown>).length}项}`
}

function scalarText(v: unknown): string {
  if (v === null) return 'null'
  if (typeof v === 'string') return `"${v}"`
  return String(v)
}

function typeClass(v: unknown): string {
  if (v === null) return 'jv-null'
  switch (typeof v) {
    case 'string':
      return 'jv-string'
    case 'number':
      return 'jv-number'
    case 'boolean':
      return 'jv-boolean'
    default:
      return 'jv-other'
  }
}
</script>

<style scoped>
.json-viewer {
  font-family: 'SFMono-Regular', Consolas, Menlo, 'Courier New', monospace;
  font-size: 13px;
  line-height: 1.7;
}
.json-viewer :deep(.json-viewer) {
  padding-left: 18px;
}
.jv-bracket {
  color: var(--color-text-3);
}
.jv-line {
  white-space: pre-wrap;
  word-break: break-all;
}
.jv-caret {
  display: inline-block;
  width: 16px;
  color: var(--color-text-3);
  cursor: pointer;
}
.jv-caret-space {
  display: inline-block;
  width: 16px;
}
.jv-key {
  color: rgb(var(--arcoblue-6));
}
.jv-string {
  color: rgb(var(--green-6));
}
.jv-number {
  color: rgb(var(--orangered-6));
}
.jv-boolean {
  color: rgb(var(--purple-6));
}
.jv-null,
.jv-other,
.jv-comma {
  color: var(--color-text-3);
}
.jv-preview {
  margin-left: 4px;
  color: var(--color-text-3);
  cursor: pointer;
}
</style>
