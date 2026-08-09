<template>
  <div class="doc-tabs">
    <a-tabs
      v-if="store.tabs.length > 0"
      v-model:active-key="store.activeKey"
      type="card-gutter"
      @delete="onDelete"
    >
      <a-tab-pane
        v-for="tab in store.tabs"
        :key="tab.key"
        :title="tab.title"
        :closable="true"
      >
        <div class="tab-body">
          <template v-if="tab.loading">
            <a-spin style="margin-top: 80px" />
          </template>
          <template v-else-if="tab.info">
            <a-tabs default-active-key="info">
              <a-tab-pane key="info" title="接口信息">
                <InfoPanel :doc="tab.info" />
              </a-tab-pane>
              <a-tab-pane key="test" title="在线测试">
                <DebugPanel :doc="tab.info" />
              </a-tab-pane>
            </a-tabs>
          </template>
          <a-empty v-else description="加载失败" />
        </div>
      </a-tab-pane>
    </a-tabs>
    <a-empty v-else description="从左侧目录选择接口" style="margin-top: 80px" />
  </div>
</template>

<script setup lang="ts">
import { useDocStore } from '@/stores/doc'
import InfoPanel from './InfoPanel.vue'
import DebugPanel from './DebugPanel.vue'

const store = useDocStore()

function onDelete(key: string | number) {
  store.closeTab(String(key))
}
</script>

<style scoped>
.doc-tabs {
  height: 100%;
  padding: 8px 16px;
}
.tab-body {
  padding: 16px 8px;
  overflow: auto;
  max-height: calc(100vh - 130px);
}
</style>
