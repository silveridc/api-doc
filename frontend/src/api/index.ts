import request from './request'
import type {
  SiteConfig,
  DocListResponse,
  DocInfo,
  SearchResult,
  LoginResult,
  DebugResult,
} from '@/types'

export const getConfig = () =>
  request.get<SiteConfig>('/doc/api/config').then((r) => r.data)

export const getList = () =>
  request.get<DocListResponse>('/doc/api/list').then((r) => r.data)

export const searchApi = (query: string) =>
  request.get<SearchResult[]>('/doc/api/search', { params: { query } }).then((r) => r.data)

export const getInfo = (name: string) =>
  request.get<DocInfo>('/doc/api/info', { params: { name } }).then((r) => r.data)

export const loginApi = (pass: string) =>
  request.post<LoginResult>('/doc/api/login', { pass }).then((r) => r.data)

export const debugApi = (payload: Record<string, unknown>) =>
  request.post<DebugResult>('/doc/api/debug', payload).then((r) => r.data)
