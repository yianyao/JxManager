<?php
/**
 * Created by PhpStorm.
 * User: yianyao
 * Date: 16-6-6
 * Time: 下午4:47
 */
namespace Admin\Controller;
use Think\Controller;
use Org\Util\Dtssp;
class MenuController extends AdminController
{
    public function index()
    {
        $mailcount = 0;
        $taskcount = 0;
        $title = "菜单管理";
        $this->assign('MailCount',$mailcount);
        $this->assign('TaskCount',$taskcount);
        $this->assign('title',$title);
        $this->display();
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
    public function menuList()
    {
        $columns = array(
            array('db' => 'id','dt' => 'id'),
            array('db' => 'title', 'dt' => 'title'),
            array('db' => 'pid', 'dt' => 'pid'),
            array('db' => 'group', 'dt' => 'group'),
            array('db' => 'sort', 'dt' => 'sort'),
            array('db' => 'url', 'dt' => 'url')
        );
        $m = D('Menu');
        $r = $_REQUEST;
        $restrict = array(
            'period' => 'P50Y',
            'cfield' => 'createTime',
            'uid' => null
        );
        $this->ajaxReturn(Dtssp::simple($r,$m,$columns,$restrict));
    }

    public function add()
    {
        if(IS_POST){
            $Menu = D('Menu');
            $data = $Menu->create();
            if($data){
                $id = $Menu->add();
                if($id){
                    session('ADMIN_MENU_LIST',null);
                    //记录行为
                    action_log('update_menu', 'Menu', $id, UID);
                    //$this->success('新增成功', Cookie('__forward__'));
                    $this->ajaxReturn("添加成功！");
                } else {
                    $this->ajaxReturn("添加失败！");
                }
            } else {
                $this->ajaxReturn($Menu->getError());
            }
        } else {
            $this->assign('info',array('pid'=>I('pid')));
            $menus = M('Menu')->field(true)->select();
            $menus = D('Common/Tree')->toFormatTree($menus);
            $menus = array_merge(array(0=>array('id'=>0,'title_show'=>'顶级菜单')), $menus);
            $this->assign('Menus', $menus);
            $title = '新增';
            $this->assign('title',$title);
            $this->display('edit');
        }
    }

    public function edit($id=0)
    {
        if(IS_POST){
            $Menu = D('Menu');
            $id = I('id');
            $map['id'] = $id;
            $data = $Menu->create();

            if($data){
                if($Menu->where($map)->save()!== false){
                    session('ADMIN_MENU_LIST',null);
                    //记录行为
                    action_log('update_menu', 'Menu', $id, UID);
                    $this->ajaxReturn('更新成功');
                } else {
                    $this->ajaxReturn('更新失败');
                }
            } else {
                $this->error($Menu->getError());
            }
        } else {
            $info = array();
            /* 获取数据 */
            $id = I('id');
            $info = M('Menu')->field(true)->find($id);
            $menus = M('Menu')->field(true)->select();
            $menus = D('Common/Tree')->toFormatTree($menus);

            $menus = array_merge(array(0=>array('id'=>0,'title_show'=>'顶级菜单')), $menus);
            $this->assign('Menus', $menus);
            if(false === $info){
                $this->error('获取后台菜单信息错误');
            }
            $this->assign('info', $info);
            $title = '编辑';
            $this->assign('title',$title);
            $this->display();
        }
    }

    /**
     * 删除纪录
     **/
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