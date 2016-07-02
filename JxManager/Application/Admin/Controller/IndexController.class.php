<?php
namespace Admin\Controller;
use Think\Controller;
class IndexController extends AdminController
{
    /**
     * 继承自AdminController，Index被实例化时，会首先初始化控制器
     * 检查是否已定义UID常量，定义了则直接返回，否则定义
     * 如果UID为false，跳转到登录页面public/login
    **/
    public function index()
    {
        $this->display();
    }
}