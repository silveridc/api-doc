import { createRouter, createWebHistory } from 'vue-router'
import IndexView from '@/views/IndexView.vue'
import PassView from '@/views/PassView.vue'
import SearchView from '@/views/SearchView.vue'

const router = createRouter({
  // 部署在 /doc 路径下
  history: createWebHistory('/doc/'),
  routes: [
    { path: '/', name: 'index', component: IndexView },
    { path: '/pass', name: 'pass', component: PassView },
    { path: '/search', name: 'search', component: SearchView },
  ],
})

export default router
