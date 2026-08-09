<template>
  <a-layout class="app-layout">
    <a-layout-header class="app-header">
      <div class="header-left">
        <span class="app-title">{{ store.config.title }}</span>
        <span class="app-version">{{ store.config.version }}</span>
      </div>
      <div class="header-right">
        <a-input-search
          v-model="keyword"
          placeholder="接口名称/接口信息/作者/接口地址"
          style="width: 340px"
          @search="goSearch"
        />
      </div>
    </a-layout-header>
    <a-layout>
      <a-layout-sider class="app-sider" :width="300" collapsible>
        <DocTree />
      </a-layout-sider>
      <a-layout-content class="app-content">
        <DocTabs />
      </a-layout-content>
    </a-layout>
  </a-layout>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useDocStore } from '@/stores/doc'
import DocTree from '@/components/DocTree.vue'
import DocTabs from '@/components/DocTabs.vue'

const store = useDocStore()
const router = useRouter()
const keyword = ref('')

onMounted(() => {
  store.init()
})

function goSearch(value: string) {
  const q = (value || keyword.value).trim()
  router.push({ path: '/search', query: q ? { q } : {} })
}
</script>

<style scoped>
.app-layout {
  height: 100%;
}
.app-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  background: #fff;
  border-bottom: 1px solid var(--color-border-2);
  padding: 0 20px;
  height: 56px;
  line-height: 56px;
}
.header-left {
  display: flex;
  align-items: baseline;
  gap: 10px;
}
.app-title {
  font-size: 18px;
  font-weight: 600;
}
.app-version {
  font-size: 12px;
  color: var(--color-text-3);
}
.app-sider {
  border-right: 1px solid var(--color-border-2);
}
.app-content {
  overflow: hidden;
}
</style>
