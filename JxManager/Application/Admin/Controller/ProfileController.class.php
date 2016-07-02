<?php
/**
 * Created by PhpStorm.
 * User: yianyao
 * Date: 16-5-17
 * Time: 上午10:09
 */
namespace Admin\Controller;
use Think\Controller;
class ProfileController extends AdminController
{
    public function index()
    {
        $mailcount = 0;
        $taskcount = 0;
        $title = "个人中心";
        $this->assign('MailCount',$mailcount);
        $this->assign('TaskCount',$taskcount);
        $this->assign('title',$title);
        $this->display();
    }




}