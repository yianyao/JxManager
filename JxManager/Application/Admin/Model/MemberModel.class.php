<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------
namespace Admin\Model;
use Think\Model;

/**
 * 用户模型
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */

class MemberModel extends Model {
    /* 字段映射 */
    protected $_map = array(
        'radio1' => 'status'
    );
    /* 用户模型自动验证 */
    protected $_validate = array(
        /* 验证用户名 */
        array('username','6,30',-1,self::EXISTS_VALIDATE,'length'),
        array('username','checkDenyMember',-2,self::EXISTS_VALIDATE,'callback'),
        array('username','',-3,self::EXISTS_VALIDATE,'unique'),
        /* 验证密码 */
        array('password','6,30',-4,self::EXISTS_VALIDATE,'length'),
        array('repassword','password',-12,0,'confirm'),
        /* 验证邮箱 */
        array('email', 'email', -5, self::EXISTS_VALIDATE), //邮箱格式不正确
        array('email', '1,32', -6, self::EXISTS_VALIDATE, 'length'), //邮箱长度不合法
        array('email', 'checkDenyEmail', -7, self::EXISTS_VALIDATE, 'callback'), //邮箱禁止注册
        array('email', '', -8, self::EXISTS_VALIDATE, 'unique',self::MODEL_INSERT), //邮箱被占用
        /* 验证手机号码 */
        array('mobile', '//', -9, self::EXISTS_VALIDATE), //手机格式不正确 TODO:
        array('mobile', 'checkDenyMobile', -10, self::EXISTS_VALIDATE, 'callback'), //手机禁止注册
        array('mobile', '', -11, self::EXISTS_VALIDATE, 'unique',self::MODEL_INSERT), //手机号被占用
        array('mobile','11',-17,self::EXISTS_VALIDATE,'length'),
        /* 验证部门和状态 */
        array('dept','require',-16),
        array('status','require',-16),
    );

    /* 用户模型自动完成 */
    protected $_auto = array(
        array('password','md5',self::MODEL_BOTH,'function'),
        array('reg_time', 'getDate', self::MODEL_INSERT,'callback'),
        array('reg_ip', 'get_client_ip', self::MODEL_INSERT, 'function', 1),
        array('update_time', 'getDate',self::MODEL_UPDATE,'callback'),
        array('status', 'getStatus', self::MODEL_BOTH, 'callback')
    );
	/**
     * 检测用户名是不是被禁止注册
     * @param  string $username 用户名
     * @return boolean          ture - 未禁用，false - 禁止注册
     */
	protected function checkDenyMember($username){
        return true; //TODO: 暂不限制，下一个版本完善
    }

	/**
     * 检测邮箱是不是被禁止注册
     * @param  string $email 邮箱
     * @return boolean       ture - 未禁用，false - 禁止注册
     */
	protected function checkDenyEmail($email){
        return true; //TODO: 暂不限制，下一个版本完善
    }

	/**
     * 检测手机是不是被禁止注册
     * @param  string $mobile 手机
     * @return boolean        ture - 未禁用，false - 禁止注册
     */
	protected function checkDenyMobile($mobile){
        return true; //TODO: 暂不限制，下一个版本完善
    }

	/**
     * 根据配置指定用户状态
     * @return integer 用户状态
     */
	protected function getStatus(){
        return true; //TODO: 暂不限制，下一个版本完善
    }

    /* 获取注册日期 */
    protected function getDate()
    {
        return ((new \DateTime(date('Y-m-d')))->format('Y-m-d'));
    }

    /* 添加用户 */
    public function addUser()
    {
        if ($this->create()){
            $uid = $this->add();
            return $uid ? $uid : 0;
        }else{
            return $this->getError();
        }
    }

    /* * 用户登录认证
     * @param  string  $username 用户名
     * @param  string  $password 用户密码
     * @param  integer $type     用户名类型 （1-用户名，2-邮箱，3-手机，4-UID，5-微信）
     * @return integer           登录成功-用户ID，登录失败-错误编号
     */
	public function login($username, $password, $type = 1){
        $map = array();
        switch ($type) {
            case 1:
                $map['username'] = $username;
                break;
            case 2:
                $map['email'] = $username;
                break;
            case 3:
                $map['mobile'] = $username;
                break;
            case 4:
                $map['uid'] = $username;
                break;
            case 5:
                $map['wx'] = $username;
                break;
            default:
                return 0; //参数错误
        }

        /* 获取用户数据 */
        $user = $this->where($map)->find();
        if(is_array($user) && $user['status']){
            /* 验证用户密码 */
            if(md5($password) === $user['password']){
                $this->updateLogin($user); //更新用户登录信息
                return $user['uid']; //登录成功，返回用户ID
            } else {
                return -2; //密码错误
            }
        } else {
            return -1; //用户不存在或被禁用
        }
    }
	/**
     * 获取用户信息
     * @param  string  $uid         用户ID或用户名
     * @param  boolean $is_username 是否使用用户名查询
     * @return array                用户信息
     */
	public function info($uid, $is_username = false){
        $map = array();
        if($is_username){ //通过用户名获取
            $map['username'] = $uid;
        } else {
            $map['uid'] = $uid;
        }

        $user = $this->where($map)->field('uid,username,email,mobile,status')->find();
        if(is_array($user) && $user['status'] = 1){
            return array($user['uid'], $user['username'], $user['email'], $user['mobile']);
        } else {
            return -1; //用户不存在或被禁用
        }
    }

	/**
     * 检测用户信息
     * @param  string  $field  用户名
     * @param  integer $type   用户名类型 1-用户名，2-用户邮箱，3-用户电话
     * @return integer         错误编号
     */
	public function checkField($field, $type = 1){
        $data = array();
        switch ($type) {
            case 1:
                $data['username'] = $field;
                break;
            case 2:
                $data['email'] = $field;
                break;
            case 3:
                $data['mobile'] = $field;
                break;
            default:
                return 0; //参数错误
        }

        return $this->create($data) ? 1 : $this->getError();
    }

	/**
     * 更新用户登录信息
     * @param  integer $uid 用户ID
     */
	protected function updateLogin($user){
        $data = array(
            'uid'              =>$user['uid'],
            'last_login_time' => NOW_TIME,
            'last_login_ip'   => get_client_ip(1),
        );
        $this->save($data);
    ;
        /* 记录登录SESSION和COOKIES */
        $auth = array(
            'uid'             => $user['uid'],
            'username'        => $user['username'],
            'last_login_time' => $user['last_login_time'],
        );
        session('user_auth', $auth);
        session('user_auth_sign', data_auth_sign($auth));
        //print_r($_SESSION);
    }

    /**
     * 注销当前用户
     * @return void
     */
    public function logout(){
        session('user_auth', null);
        session('user_auth_sign', null);
    }

	/**
     * 更新用户信息
     * @param int $uid 用户id
     * @param array $data 修改的字段数组
     * @return true 修改成功，false 修改失败
     * @author huajie <banhuajie@163.com>
     */
	public function updateUserFields($uid,$data){
        if(empty($uid) || empty($data)){
            $this->error = '参数错误！';
            return false;
        }

        /**
        if(!$this->verifyUser($uid, $password)){
            $this->error = '验证出错：密码不正确！';
            return false;
        }
        **/
        //更新用户信息
        //$data = $this->create($data);
        if($data){
            return $this->where(array('uid'=>$uid))->save($data);
        }
        return false;
    }

	/**
     * 验证用户密码
     * @param int $uid 用户id
     * @param string $password_in 密码
     * @return true 验证成功，false 验证失败
     * @author huajie <banhuajie@163.com>
     */
	protected function verifyUser($uid, $password_in){
        $password = $this->getFieldByUid($uid, 'password');
        if(md5($password_in) === $password){
            return true;
        }
        return false;
    }



}
