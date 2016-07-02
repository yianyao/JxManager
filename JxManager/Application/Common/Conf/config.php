<?php
return array(
    /* 模块相关配置 */
    //'AUTOLOAD_NAMESPACE' => array('Addons' => ONETHINK_ADDON_PATH), //扩展模块列表
    'DEFAULT_MODULE'     => 'Admin',
    'MODULE_DENY_LIST'   => array('Common','User','Install'),
    //'MODULE_ALLOW_LIST'  => array('Home','Admin'),

    /* 系统数据加密设置 */
    'DATA_AUTH_KEY' => '7Nj].<iX+5K$nm^wI}Z@e8)z,lGD(#b{UdR~_MW|', //默认数据加密KEY

    /* 用户相关设置 */
    'USER_MAX_CACHE'     => 1000, //最大缓存用户数
    'USER_ADMINISTRATOR' => 1, //管理员用户ID

    /* Action参数xdpb */
    'URL_PARAMS_BIND' =>true,

    /* URL配置 */
    'URL_CASE_INSENSITIVE' => true, //默认false 表示URL区分大小写 true则表示不区分大小写
    'URL_MODEL' => 2,
    /* 全局过滤配置 */
    'DEFAULT_FILTER' => '', //全局过滤函数

    //数据库配置
    'DB_TYPE' => 'mysql',
    'DB_HOST' => 'localhost',
    'DB_NAME' => 'jxmanager',
    'DB_PORT' => '3306',
    'DB_USER' => 'root',
    'DB_PWD' => '',
    'DB_PREFIX' => 'jx_',
    'DB_CHARSET' => 'utf8',


    //模板配置
    'SHOW_PAGE_TRACE' =>true,
    'TMPL_L_DELIM' => '<<{',
    'TMPL_R_DELIM' => '}>>'


);