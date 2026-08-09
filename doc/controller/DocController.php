<?php
namespace app\doc\controller;

use think\facade\Config;
use app\BaseController;
use app\doc\logic\DocLogic as Doc;
use think\Response;
use think\facade\View;
class DocController extends BaseController
{
    /**
     * @var \think\Request Request实例
     */
    protected $request;

    /**
     * @var Doc
     */
    protected $doc;

    /**
     * @var array 资源类型
     */
    protected $mimeType = [
        'xml'  => 'application/xml,text/xml,application/x-xml',
        'json' => 'application/json,text/x-json,application/jsonrequest,text/json',
        'js'   => 'text/javascript,application/javascript,application/x-javascript',
        'css'  => 'text/css',
        'rss'  => 'application/rss+xml',
        'yaml' => 'application/x-yaml,text/yaml',
        'atom' => 'application/atom+xml',
        'pdf'  => 'application/pdf',
        'text' => 'text/plain',
        'png'  => 'image/png',
        'jpg'  => 'image/jpg,image/jpeg,image/pjpeg',
        'gif'  => 'image/gif',
        'csv'  => 'text/csv',
        'html' => 'text/html,application/xhtml+xml,*/*',
    ];

    public $static_path = '/doc-static/';

    public function initialize(){
        parent::initialize();
        $this->doc = new Doc((array)Config::get('doc'));
        if(Config::get("doc.static_path", '')){
            $this->static_path = Config::get("doc.static_path");
        }
    }

    /**
     * 文档入口：返回前端 SPA 挂载页（构建产物 public/doc/index.html）
     * @return Response
     */
    public function index()
    {
        $path = public_path().'doc-f/index.html';
        return View::fetch($path);
    }

    /**
     * 站点配置：标题/版本/版权
     */
    public function apiConfig()
    {
        return json([
            'title'     => Config::get('doc.title'),
            'version'   => Config::get('doc.version'),
            'copyright' => Config::get('doc.copyright'),
        ]);
    }

    /**
     * 接口列表（目录树数据）
     */
    public function apiList()
    {
        if(!$this->checkLogin()){
            return json(['status' => 401, 'message' => '未登录'], 401);
        }
        return $this->getList();
    }

    /**
     * 接口搜索
     */
    public function apiSearch()
    {
        if(!$this->checkLogin()){
            return json(['status' => 401, 'message' => '未登录'], 401);
        }
        $data = $this->doc->searchList($this->request->get('query'));
        return json($data);
    }

    /**
     * 接口详情（JSON 版）
     */
    public function apiInfo()
    {
        if(!$this->checkLogin()){
            return json(['status' => 401, 'message' => '未登录'], 401);
        }
        $name = $this->request->get('name');
        if(!is_string($name) || !str_contains($name, '::')){
            return json(['status' => 400, 'message' => '参数错误'], 400);
        }
        list($class, $action) = explode("::", $name, 2);
        if(!$class || !$action){
            return json(['status' => 400, 'message' => '参数错误'], 400);
        }
        $action_doc = $this->doc->getInfo($class, $action);
        if($action_doc)
        {
            $action_doc['header'] = isset($action_doc['header']) ? array_merge($this->doc->__get('public_header') ?: [], $action_doc['header']) : [];
            $action_doc['param'] = isset($action_doc['param']) ? array_merge($this->doc->__get('public_param') ?: [], $action_doc['param']) : [];
            $action_doc['return_json'] = $this->doc->formatReturnJson($action_doc);
            $action_doc['curl_code'] = $this->buildCurlCode($action_doc);
            return json($action_doc);
        }
        return json(['status' => 404, 'message' => '接口不存在'], 404);
    }

    /**
     * 登录（SPA 版）
     */
    public function apiLogin()
    {
        return $this->login();
    }

    /**
     * 接口调试（SPA 版）
     */
    public function apiDebug()
    {
        if(!$this->checkLogin()){
            return json(['status' => 401, 'message' => '未登录'], 401);
        }
        return $this->debug();
    }

    /**
     * 设置目录树及图标
     * @param $actions
     * @param int $num
     * @return mixed
     */
    protected function setIcon($actions, int $num = 1)
    {
        foreach ($actions as $key=>$moudel){
            if(isset($moudel['actions'])){
                $actions[$key]['iconClose'] = $this->static_path."/js/zTree_v3/img/zt-folder.png";
                $actions[$key]['iconOpen'] = $this->static_path."/js/zTree_v3/img/zt-folder-o.png";
                $actions[$key]['open'] = true;
                $actions[$key]['isParent'] = true;
                $actions[$key]['actions'] = $this->setIcon($moudel['actions']);
            }else{
                $actions[$key]['icon'] = $this->static_path."/js/zTree_v3/img/zt-file.png";
                $actions[$key]['isParent'] = false;
                $actions[$key]['isText'] = true;
            }
        }
        return $actions;
    }

    /**
     * 接口列表
     */
    public function getList()
    {
        $list = $this->doc->getList();
        $list = $this->setIcon($list);
        return json(['firstId'=>'', 'list'=>$list]);
    }

    /**
     * 生成 curl 命令（供前端复制）
     * @param array $action_doc
     * @return string
     */
    protected function buildCurlCode(array $action_doc)
    {
        $curl_code = 'curl --location --request '.($action_doc['method'] ?? 'GET');
        $params = [];
        foreach ($action_doc['param'] as $param){
            $params[$param['name']] = $param['default'] ?? '';
        }
        $curl_code .= ' \''.$this->request->root().($action_doc["url"] ?? '').(count($params) > 0 ? '?'.http_build_query($params) : '').'\' ';
        foreach ($action_doc['header'] as $header){
            $curl_code .= '--header \''.$header['name'].':\'';
        }
        return $curl_code;
    }

    /**
     * 验证密码
     * @return bool
     */
    protected function checkLogin()
    {
        $pass = $this->doc->__get("password");
        if($pass){
            if(cache('apidoc-pass') === sha1($pass)){
                return true;
            }else{
                return false;
            }
        }else{
            return true;
        }
    }

    /**
     * 登录
     * @return string
     */
    public function login()
    {
        $pass = $this->doc->__get("password");
        if($pass && $this->request->param('pass') === $pass){
            cache('apidoc-pass', sha1($pass));
            $data = ['status' => '200', 'message' => '登录成功'];
        }else if(!$pass){
            $data = ['status' => '200', 'message' => '登录成功'];
        }else{
            $data = ['status' => '401', 'message' => '密码错误'];
        }
        return json($data);
    }

    /**
     * 接口访问测试
     * @return \think\Response
     */
    public function debug()
    {
        $data = $this->request->all();
        $api_url = $this->request->input('url');
        $res['status'] = '404';
        $res['message'] = '接口地址无法访问！';
        $res['message'] = '接口地址无法访问！';
        $method =  $this->request->input('method_type', 'GET');
        $cookie = $this->request->input('cookie');
        $headers = $this->request->input('header', array());
        unset($data['method_type']);
        unset($data['url']);
        unset($data['cookie']);
        unset($data['header']);
        $res['result'] = $this->http_request($api_url, $cookie, $data, $method, $headers);
        if($res['result']){
            $res['status'] = '200';
            $res['message'] = 'success';
            $res['message'] = 'success';
        }
        return json($res);
    }

    /**
     * curl模拟请求方法
     * @param string $url
     * @param string $cookie
     * @param array $data
     * @param string $method
     * @param array $headers
     * @return mixed
     */
    private function http_request(string $url, string $cookie = '', array $data = array(), string $method = 'GET', array $headers = array()){
        $curl = curl_init();
        if(count($data) && $method == "GET"){
            $data = array_filter($data);
            $url .= "?".http_build_query($data);
            $url = str_replace(array('%5B0%5D'), array('[]'), $url);
        }
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        if (count($headers)){
            $head = array();
            foreach ($headers as $name=>$value){
                $head[] = $name.":".$value;
            }
            curl_setopt($curl, CURLOPT_HTTPHEADER, $head);
        }
        $method = strtoupper($method);
        switch($method) {
            case 'GET':
                break;
            case 'POST':
                curl_setopt($curl, CURLOPT_POST, true);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                break;
            case 'PUT':
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PUT');
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                break;
            case 'DELETE':
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'DELETE');
                break;
        }
        if (!empty($cookie)){
            curl_setopt($curl, CURLOPT_COOKIE, $cookie);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($curl);
        curl_close($curl);
        return $output;
    }

}
