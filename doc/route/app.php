<?php
use think\facade\Route;

// ===== 前端 SPA 入口（路由保持不变）=====
//Route::get('/', 'DocController/index');
//Route::get('doc', 'DocController/index');

// ===== 文档 JSON API =====
Route::get('api/config', 'DocController/apiConfig');
Route::get('api/list', 'DocController/apiList');
Route::get('api/search', 'DocController/apiSearch');
Route::get('api/info', 'DocController/apiInfo');
Route::post('api/login', 'DocController/apiLogin');
Route::post('api/debug', 'DocController/apiDebug');

Route::miss('DocController');
