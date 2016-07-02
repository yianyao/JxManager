<?php
/**
 * Created by PhpStorm.
 * User: yiany
 * Date: 2016/5/14
 * Time: 19:16
 */
namespace Admin\Controller;
use Think\Controller;
use User\Api\UserApi;

class PublicController extends Controller
{
    /** 
     *  后台用户登录
    **/
    public function login()
    {
        if (IS_POST){
            $username = I("username",'');
            $password = I("password",'');
            /* 验证账号密码是否为空 */
            if ($username ==  '' || $password == ''){
                $this->showLogError(0);
            }else{
                $User = D('Member');
                $uid = $User->login($username,$password);
                /* 返回uid大于0（即用户uid）则登录成功，否则抛出错误 */
                if ($uid > 0){
                    echo $uid;
                }else{
                    $this->showLogError($uid);
                }
            }
        /* 在登录页面，如果无POST数据，需判断是否已设置缓存 */
        }else{
            if (is_login()){
                $this->redirect("Admin.php/Index/index");
            }else{
                /* 读取数据库中的配置 */
                $config = S('DB_CONFIG_DATA');
                if (!$config){
                    $config = D("Config")->lists();
                    S('DB_CONFIG_DATA',$config);
                }
                //添加配置
                C($config); 
                $this->display();
            }
        }
    }
    
    /* 退出登录 */
    public function logout()
    {
        if(is_login()){
            D("member")->logout();
            session('[destroy');
            $this->redirect('login');
        }else{
            $this->redirect('login');
        }
    }

    /* 锁屏 */
    public function lock()
    {
        $this->display();
    }

    /* 登录信息 */
    private function showLogError($code)
    {
        switch($code){
            case 0: $error = "账号或密码为空！";  break;
            case -1: $error = "用户不存在或被禁用！"; break;
            case -2: $error = "密码错误！"; break;
            default: $error = '未知错误';
        }
        echo $error;
    }

}