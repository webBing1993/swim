<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
// $Id$

return [
    'url_route_on' => true,
//    'log'          => [
//        'type' => 'trace', // 支持 socket trace file
//    ],

    /* 默认模块和控制器 */
    'default_module' => 'home',

    /* URL配置 */
    'base_url'=>'',
    'parse_str'=>[
        '__ROOT__' => '/',
        '__STATIC__' => '/static',
        '__ADMIN__' => '/admin',
        '__HOME__' => '/home',
    ],

    /* UC用户中心配置 */
    'uc_auth_key' => '(.t!)=JTb_OPCkrD:-i"QEz6KLGq5glnf^[{p;je',

    /* 分页自定义 */
    'paginate' => [
        'type'     => '\org\Pager',
        'var_page' => 'page',
        'list_rows'=> 12
    ],
    
    //最新动态
    'work' => array(
        'login' => 'http://swim.0571ztnet.com/home/index/login',
//        'token' => 'RMRUYhgJh7C',
//        'encodingaeskey' => 'XO7KtGSIpsnGPR24x3UmfTnLXSmEfogGhmRqUkoefNj',
        'appid' => 'wwc2778c4e14ba4809',
        'appsecret' => 'Kv-8VAYDn5tj8q4Rl0PEVog6Qtn3269SqV5s3WKIscg',
        'agentid' => 1000005
    ),

];
