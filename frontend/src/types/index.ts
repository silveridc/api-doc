/** 站点配置 */
export interface SiteConfig {
  title: string
  version: string
  copyright: string
}

/** 目录树节点（与后端 /api/list 结构一致） */
export interface TreeNode {
  title: string
  name?: string
  description?: string
  actions?: TreeNode[]
  isParent?: boolean
  isText?: boolean
  open?: boolean
  icon?: string
  iconOpen?: string
  iconClose?: string
  [key: string]: unknown
}

export interface DocListResponse {
  firstId: string
  list: TreeNode[]
}

/** 请求头/参数条目 */
export interface HeaderItem {
  name: string
  require?: string | number
  default?: string
  desc?: string
}

export interface ParamItem {
  name: string
  type?: string
  require?: string | number
  default?: string
  other?: string
  desc?: string
}

/** 返回结构树节点（后端 formatReturnJson 输出） */
export interface ReturnNode {
  name: string
  type?: string
  desc?: string
  isArray?: boolean
  format?: 'colon' | 'standard'
  raw?: string
  children?: ReturnNode[]
}

export interface ReturnJson {
  base: Record<string, string>
  tree: ReturnNode[]
}

/** 接口详情 */
export interface DocInfo {
  name: string
  title?: string
  url?: string
  method?: string
  author?: string
  description?: string
  remark?: string
  header?: HeaderItem[]
  param?: ParamItem[]
  return?: string[]
  return_json?: ReturnJson
  curl_code?: string
}

/** 搜索结果条目 */
export interface SearchResult {
  name: string
  title?: string
  url?: string
  author?: string
  description?: string
}

export interface LoginResult {
  status: string
  message: string
}

export interface DebugResult {
  status: string
  message: string
  result: string
}
