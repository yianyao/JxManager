<?php
/**
 * Created by PhpStorm.
 * User: yianyao
 * Date: 16-5-17
 * Time: 上午10:14
 */
namespace Admin\Controller;
use Think\Controller;
use Org\Util\Dtssp;
class ConfigController extends AdminController
{
    public function index()
    {
        $mailcount = 0;
        $taskcount = 0;
        $title = "配置管理";
        $this->assign('MailCount',$mailcount);
        $this->assign('TaskCount',$taskcount);
        $this->assign('title',$title);
        $this->display();
    }

    public function configList()
    {
        $columns = array(
            array('db' => 'id','dt' => 'id'),
            array('db' => 'name','dt' => 'name'),
            array('db' => 'title','dt' => 'title'),
            array('db' => 'type','dt' => 'type'),
            array('db' => 'value','dt' => 'value')
        );
        $m = D('Config');
        $r = $_REQUEST;
        $restrict = array(
            'period' => 'P50Y',
            'cfield' => 'create_time',
            'uid' => null
        );
        $this->ajaxReturn(Dtssp::simple($r,$m,$columns,$restrict));
    }

    public function add()
    {
        if(IS_POST){
            $Config = D('Config');
            $data = $Config->create();
            if ($data){
                if($Config->add()){
                    S('DB_CONFIG_DATA',null);
                    $this->ajaxReturn('添加成功！');
                }else{
                    $this->ajaxReturn('新增失败！');
                }
            }else{
                $this->ajaxReturn($Config->getError());
            }
        }else{
            $title = "新增";
            $this->assign('title',$title);
            $this->display('edit');
        }
    }

    public function edit($id=0)
    {
        if (IS_POST){
            $config = D('config');
            $data = $config->create();
            if ($data){
                if ($config->save()){
                    S('DB_CONFIG_DATA',null);
                    action_log('update_config','config',$data['id'],UID);
                    $this->ajaxReturn('更新成功！');
                }else{
                    $this->ajaxReturn('更新失败！');
                }
            }else{
                $this->ajaxReturn($Config->getError());
            }
        }else{
            $title = '编辑';
            $id = I('id');
            $info = array();
            $info = M('config')->field(true)->find($id);
            if (false === $info){
                $this->ajaxReturn('获取配置信息错误！');
            }
            $this->assign('info',$info);
            $this->assign('title',$title);
            $this->display();
        }
    }

    public function deleteRow()
    {
        $m = trim(key($_POST));
        $uModel = D($m);
        $data = I($m);
        $res = $uModel->delete($data);
        if ($res > 0){
            $this->ajaxReturn('删除成功！');
        }elseif($res === 0){
            $this->ajaxReturn('没有删除任何数据！');
        }else{
            $this->ajaxReturn('删除失败！');
        }
    }



}