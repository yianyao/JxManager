<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2014 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用入口文件

// 检测PHP环境
if(version_compare(PHP_VERSION,'5.3.0','<'))  die('require PHP > 5.3.0 !');

// 开启调试模式 建议开发阶段开启 部署阶段注释或者设为false
define('APP_DEBUG',True);

//绑定模块
define('BIND_MODULE','Admin');

// 定义应用目录
define('APP_PATH','../../JxManager/Application/');

//检查安装
if (!is_file(APP_PATH . "/install.lock"))
{
    header('Location: ./install.php');
    exit;
}

// 引入ThinkPHP入口文件
require '../../JxManager/ThinkPHP/ThinkPHP.php';

?>