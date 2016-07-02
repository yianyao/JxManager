<?php
return array(
    /* 数据缓存设置 */
    'DATA_CACHE_PREFIX'    => 'jx_', // 缓存前缀
    'DATA_CACHE_TYPE'      => 'File', // 数据缓存类型

    /* SESSION 和 COOKIE 配置 */
    'SESSION_PREFIX' => 'jx_admin', //session前缀
    'COOKIE_PREFIX'  => 'jx_admin_', // Cookie前缀 避免冲突
    'VAR_SESSION_ID' => 'session_id',	//修复uploadify插件无法传递session_id的bug
);