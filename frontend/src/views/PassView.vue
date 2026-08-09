<template>
  <div class="pass-page">
    <div class="pass-card">
      <h2 class="pass-title">{{ store.config.title || 'API接口文档' }}</h2>
      <div class="pass-version">{{ store.config.version }}</div>
      <a-input-password
        v-model="pass"
        placeholder="请输入访问密码..."
        allow-clear
        @press-enter="submit"
      />
      <a-button type="primary" long :loading="loading" @click="submit">进入</a-button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { Message } from '@arco-design/web-vue'
import { useDocStore } from '@/stores/doc'
import { loginApi } from '@/api'

const router = useRouter()
const store = useDocStore()

const pass = ref('')
const loading = ref(false)

onMounted(() => {
  store.init()
})

async function submit() {
  if (!pass.value) {
    Message.warning('请输入密码')
    return
  }
  loading.value = true
  try {
    const res = await loginApi(pass.value)
    if (res.status === '200') {
      router.push('/')
    } else {
      Message.error(res.message)
    }
  } catch {
    Message.error('登录失败，请重试')
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.pass-page {
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(to bottom, #71ba51 0%, #00b16a 100%);
}
.pass-card {
  width: 360px;
  padding: 32px;
  border-radius: 8px;
  background: #fff;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
  display: flex;
  flex-direction: column;
  gap: 12px;
}
.pass-title {
  text-align: center;
  margin: 0;
}
.pass-version {
  text-align: center;
  color: var(--color-text-3);
  margin-bottom: 12px;
}
</style>
