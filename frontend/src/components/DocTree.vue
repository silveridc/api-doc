<template>
  <div class="doc-tree">
    <a-input-search
      v-model="keyword"
      placeholder="过滤接口"
      allow-clear
      style="margin-bottom: 8px"
    />
    <div class="doc-tree-body">
      <a-tree
        :data="filteredData"
        :default-expand-all="true"
        block-node
        @select="onSelect"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue'
import { useDocStore } from '@/stores/doc'
import type { TreeNode } from '@/types'

const store = useDocStore()
const keyword = ref('')

interface TreeDataItem {
  key: string
  title: string
  isLeaf: boolean
  children?: TreeDataItem[]
  raw: TreeNode
}

function toTreeData(nodes: TreeNode[], prefix = ''): TreeDataItem[] {
  return (nodes || []).map((n, idx) => {
    const key = `${prefix}${idx}:${n.name || n.title}`
    return {
      key,
      title: n.title,
      isLeaf: !n.actions || n.actions.length === 0,
      children: n.actions && n.actions.length ? toTreeData(n.actions, `${key}/`) : undefined,
      raw: n,
    }
  })
}

const filteredData = computed<TreeDataItem[]>(() => {
  const kw = keyword.value.trim().toLowerCase()
  if (!kw) return toTreeData(store.treeList)
  const walk = (nodes: TreeNode[]): TreeNode[] =>
    nodes
      .map((n) => {
        const children = n.actions ? walk(n.actions) : []
        if (children.length) return { ...n, actions: children }
        if (n.title.toLowerCase().includes(kw)) return n
        return null
      })
      .filter(Boolean) as TreeNode[]
  return toTreeData(walk(store.treeList))
})

// Arco Tree select 事件签名：(selectedKeys, { selected, node, selectedNodes, e })
function onSelect(_keys: (string | number)[], data: unknown) {
  const node = (data as { node?: TreeDataItem }).node
  if (node?.isLeaf && node.raw.name) {
    store.openDoc(node.raw.name, node.raw.title)
  }
}
</script>

<style scoped>
.doc-tree {
  display: flex;
  flex-direction: column;
  height: 100%;
  padding: 12px;
}
.doc-tree-body {
  flex: 1;
  overflow: auto;
}
</style>
