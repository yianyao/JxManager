<?php
/**
 * Created by PhpStorm.
 * User: yianyao
 * Date: 16-6-3
 * Time: 下午5:52
 */
namespace Admin\Controller;
use Think\Controller;
use Org\Util\Dtssp;
class UserController extends AdminController
{
    public function index()
    {
        $mailCount = 0;
        $taskCount = 0;
        $title = '用户管理';
        $this->assign('MailCount',$mailCount);
        $this->assign('TaskCount',$taskCount);
        $this->assign('title',$title);
        $this->display();
    }

    /** 用户注册 添加纪录
     * @return string 返回注册信息
     **/
    public function regedit()
    {
        $uid = (D('Member')->addUser());
        if ($uid > 0){
            echo 1000;
        }else{
            $this->showError($uid);
        }
    }


    /**
     * 删除纪录
     **/
    public function deleteRow()
    {
        $m = trim(key($_POST));
        $uModel = D('Member');
        $data = I($m);
        $res = $uModel->delete($data);
        if ($res > 0){
            $this->showError(1000);
        }elseif($res === 0){
            $this->showError(-14);
        }else{
            $this->showError(-15);
        }
    }

    /**
     * 编辑纪录
     **/
    public function update()
    {
        $uid = I('uid');
        $data = array();
        //$data['username'] = I('username-edit');
        $data['nickname'] = I('nickname-edit');
        $data['email'] = I('email-edit');
        $data['wx'] = I('wx-edit');
        $data['mobile'] = I('mobile-edit');
        $data['sex'] = I('sex-edit');
        $data['status'] = I('status-edit');
        $data['dept'] = I('dept-edit');
        //$this->ajaxReturn($data);
        $res = D('Member')->updateUserFields($uid,$data);
        echo $res;
    }

    /**
     * $columns描述数据表格的数据源，db对应数据表字段，dt对应datatables中的column.data，即数据源
     * 的名字。严格来说，可以根据需求返回更多的字段，而在datatables中可根据需求选择相应的数据源
     * 为方便对datatables的增删查改操作，最好指定数据表主键及对应的column data用于数据返回，如
     * 当前方法中的uid与id
     * $restrict数组描述约束条件，period指定时间约束，P10Y表示当前日期往前10年内，cfield指定字段
     * 约束，reg_time表示时间约束以reg_time字段为准。uid指定用户约束，null表示不指定用户。
     * 当前方法示例为：where reg_time between '十年前今日','今日'
     * 核心代码Dtssp.class.php直接处理客户提交的$_REQUEST数据，并根据传递的时间和字段约束生成日期
     * 范围，再结合用户约束限定默认搜索条件
     **/
    public function processing()
    {
        $columns = array(
            array( 'db' => 'nickname', 'dt' => 'nname' ),
            array( 'db' => 'username',  'dt' => 'account' ),
            array( 'db' => 'dept',      'dt' => 'dept' ),
            array( 'db' => 'email',     'dt' => 'email' ),
            array( 'db' => 'mobile',    'dt' => 'mobile'),
            array('db' => 'status',     'dt' => 'status'),
            array('db' => 'uid', 'dt' => 'id')
        );
        $m = D('Member');
        $r = $_REQUEST;
        $restrict = array(
            'period' => 'P10Y',
            'cfield' => 'reg_time',
            'uid' => null
        );
        $this->ajaxReturn(Dtssp::simple($r,$m,$columns,$restrict));
    }

    /**
     * 数据表格数据源
     **/
    public function test()
    {
        $columns = array(
            array('db' => 'id','dt'=>'id'),
            array('db' => 'first_name','dt'=>'fname'),
            array('db' => 'position','dt'=>'position'),
            array('db' => 'email','dt'=>'email'),
            array('db' => 'office','dt'=>'office'),
            array('db' => 'start_date','dt'=>'start_date'),
            array('db' => 'age','dt'=>'age'),
            array('db' => 'salary','dt'=>'salary'),
            array('db' => 'seq','dt'=>'seq'),
            array('db' => 'extn','dt'=>'extn')
        );
        $restrict = array(
            'period' => 'P5Y',
            'cfield' => 'start_date',
            'uid' => null
        );
        $m = D('datatables');
        $r = $_REQUEST;
        $this->ajaxReturn(Dtssp::simple($r,$m,$columns,$restrict));
    }

    /**
     * 获取错误信息
     * @param  integer $code 错误编码
     * @return string        错误信息
     */
    private function showError($code = 0){
        switch ($code) {
            case 1000: $error = '操作成功!';break;
            case -1:  $error = '用户名长度必须在6到16个字符以内！'; break;
            case -2:  $error = '用户名被禁止注册！'; break;
            case -3:  $error = '用户名被占用！'; break;
            case -4:  $error = '密码长度必须在6-30个字符之间！'; break;
            case -5:  $error = '邮箱格式不正确！'; break;
            case -6:  $error = '邮箱长度必须在1-32个字符之间！'; break;
            case -7:  $error = '邮箱被禁止注册！'; break;
            case -8:  $error = '邮箱被占用！'; break;
            case -9:  $error = '手机格式不正确！'; break;
            case -10: $error = '手机被禁止注册！'; break;
            case -11: $error = '手机号被占用！'; break;
            case -12; $error = '密码不一致!';break;
            case -13: $error = '参数非法！';break;
            case -14: $error = '没有删除数据！';break;
            case -15: $error = '操作失败！';break;
            case -16: $error = '字段必需！';break;
            case -17: $error = '手机号必须为11位数字！';break;
            case -18: $error ='没有更新任何记录！'; break;
            default:  $error = '未知错误';
        }
        echo $error;
    }

    public function temp()
    {
        $this->display();
    }

}