<?php
/**
 * Created by PhpStorm.
 * User: yianyao
 * Date: 16-6-12
 * Time: 下午3:04
 */
namespace Admin\Controller;
use Admin\Model\AuthRuleModel;
use Admin\Model\AuthGroupModel;
use Org\Util\Dtssp;
class AuthController extends AdminController
{
    /**
     * 动态更新授权规则，对menu表的操作节点增删改后
     * 当执行授权操作时动态更新auth_rules表规则
     * 修改自OT源码同名方法
    **/
    public function updateRules()
    {
        //返回menu表中经过整理的节点数据
        $nodes = $this->returnNodes(false);
        $AuthRule = M('AuthRule');
        $map = array('module'=>'admin','type'=>array('in','1,2'));
        //返回权限表中已有的权限规则
        $rules = $AuthRule->where($map)->order('name')->select();

        //因为menu表和rules表字段不同，必须经过转换
        $data = array();
        foreach($nodes as $value){
            $temp['name'] = $value['url'];
            $temp['title'] = $value['title'];
            $temp['module'] = 'admin';
            if ($value['pid'] > 0){
                $temp['type'] = AuthRuleModel::RULE_URL;
            } else {
                $temp['type'] = AuthRuleModel::RULE_MAIN;
            }
            $temp['status'] = 1;

            /**
             * 因为目的是对$data（即转换键值后的$nodes)和$rules元素比较以找出有同一模块下同一控制器操作的异同
             * 但不同模块下可能会有同名的操作，所以只是比较name和title字段是不够的，仅比较module字段那更不成
             * 比较modu + name呢？同一模块的同一操作，还是有两个类型的，所以还是不行
             * 但可以保证：在这两个表中，同一模块下某个类型的操作只能有一个，即是唯一的
             * 所以，$data的每一项记录都使用name+module+type作为键名以保证其在$data中唯一
             * 同样的，如果该记录在$rules即rules表中存在，那也是唯一的
             * 而两个表中的这两条记录肯定是匹配的，即都是同一模块同一类型的相同操作
             * 通过对这两条记录的各元素进行比较，就可以发现是否进行了修改
             * 而如果该记录在$data中有而在$rules中没有，显然是新增的；反之，则是已被删除或禁用的
            **/
            $data[strtolower($temp['name'].$temp['module'].$temp['type'])] = $temp;
        }
            $diff = array();
            $ids = array();
            /**
             *  以下循环及条件判断语句的逻辑：
             *  1、取出$rules中的数组元素并生成$key
             *  2、判断isset($data[$key])是否成立
             *      a、如果满足，表示该项同时存在于rules和menu表中，执行语句101-108，然后退出判断，跳到1，进行下一循环
             *      b、如果不满足，表示该项只在rules中存在，继续执行下一个条件判断
             *   3、在上一条件为假的前提下，判断$rulep['status']==1是否成立
             *      a、如果满足，执行语句94，然后结束判断，跳到1，进入下一循环
             *      b、如果不满足，结束判断，跳到1进入下一循环
             *  条件判断语句从上往下，如果当前条件满足，执行{}中的语句并退出判断；如果不满足，继续下一个条件，直到有某个条件
             *  满足或条件判断完毕
             **/
            foreach ($rules as $index=>$rule){
                $key = strtolower($rule['name'].$rule['module'].$rule['type']);
                /**
                 * 如果isset($data[$key])为真，表示该项同时存在于rules和menu表中，又因为menu表对应的$data（即修改后的$nodes）不
                 * 包含对应的id和condition字段。所以直接保留rules中的原值即可，只需比较对应的status和title字段
                 * 也就是说，当isset($data[$key])为真时，nodes中的操作节点在规则表AuthRules中有对应的记录，我们只需比较同一条记录
                 * 的status及title字段是否和data中的status及title字段一致即可，不一致需要更新，将rules中的值换为nodes的，一致，则无需更新
                 *
                 *因为data数组中的status在此前置1，所以，只需判断rule的status即可
                 **/
                if (isset($data[$key])){
                    //判断status和title是否一致，只要有一个不同，都修改$rule中对应元素的值
                    if ($rule['status'] != 1 || $rule['title'] != $data[$key]['title']){
                        $newrule['status'] = 1;
                        $newrule['title'] = $data[$key]['title'];
                        $diff[$rule['id']] = $newrule;
                    }
                    //如果一致的话，无需更新，销毁$data和$rules数组的当前元素
                    unset($data[$key]);
                    unset($rules[$index]);
                    //如果isset($data[$key])条件不满足，表示该节点只存在于$rules中。换言之，该操作已在menu表中被删除了，失效了
                }elseif ($rule['status'] == 1){
                    $ids[] = $rule['id'];
                }
            }
        /**
         * 上面的foreach循环之后，如果$data数组还存在并且长度大于0，表示该数组中的元素都是rules表中没有的，也就是
         * 新增的操作，需要将其添加到rules表中
         * 如果$diff数组存在且长度大于0，表示这是在nodes中修改了title或者在rules中被禁用而在nodes中启用的，也就是
         * 更新的操作，需要进行更新
         * 如果$ids存在且长度大于0，表示rules中有而nodes中没有的，也就是无效的规则，需要将之删除或禁用
         **/
        //修改更新
        if (count($diff)){
            foreach($diff as $k=>$v){
                $id = 'id=' .$k;
                $AuthRule->where($id)->setField($v);
            }
        }
        //禁用无效规则
        if (count($ids)){
            $AuthRule->where(array('id'=>array('IN',implode(',',$ids))))->save(array('status'=>-1));
        }
        //新增
        if (count($data)){
            $AuthRule->addAll(array_values($data));
        }

        if($AuthRule->getDbError()){
            trace('['._METHOD_.']:' . $AuthRule->getDbError());
            return false;
        }else{
            return true;
        }
    }

    public function index()
    {
        $mailcount = 0;
        $taskcount = 0;
        $title = "角色管理";
        $this->assign('MailCount',$mailcount);
        $this->assign('TaskCount',$taskcount);
        $this->assign('title',$title);
        $this->display();
    }

    /**
     * 角色编辑列表
    **/
    public function AuthList()
    {
        $columns = array(
            array('db' => 'id','dt' => 'id'),
            array('db' => 'title','dt' => 'title'),
            array('db' => 'description','dt' => 'description'),
            array('db' => 'status','dt' => 'status')
        );
        $m = D('Auth_group');
        $r = $_REQUEST;
        $restrict = null;
        $this->ajaxReturn(Dtssp::simple($r,$m,$columns,$restrict));
    }

    /**
     * 添加角色或显示添加页面
    **/
    public function addGroup()
    {
        if(IS_POST){
            $Auth = D('Auth_group');
            $data = $Auth->create();
            if ($data){
                if($Auth->add()){
                    S('DB_AUTH_GROUP_DATA',null);
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

    /**
     * 角色编辑
    **/
    public function editGroup($id=0)
    {
        if (IS_POST){
            $Auth = D('Auth_group');
            $data = $Auth->create();
            if ($data){
                if ($Auth->save()){
                    //$Auth->_sql();
                    S('DB_AUTH_GROUP_DATA',null);
                    action_log('update_auth_group','auth_group',$data['id'],UID);
                    $this->ajaxReturn('更新成功！');
                }else{
                    $this->ajaxReturn('更新失败！');
                }
            }else{
                $this->ajaxReturn($Auth->getError());
            }
        }else{
            $title = '编辑';
            $id = I('id');
            $info = array();
            $info = M('Auth_group')->field(true)->find($id);
            if (false === $info){
                $this->ajaxReturn('获取配置信息错误！');
            }
            $this->assign('info',$info);
            $this->assign('title',$title);
            $this->display('edit');
        }
    }

    /**
     * 角色删除
    **/
    public function deleteGroup()
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

    /**
     * 访问授权页
    **/
    public function access()
    {
        $this->updateRules();
        $auth_group = M('AuthGroup')->where( array('status'=>array('egt','0'),'module'=>'admin','type'=>AuthGroupModel::TYPE_ADMIN) )
                      ->getfield('id,id,title,rules');
        $node_list   = $this->returnNodes();
        $map         = array('module'=>'admin','type'=>AuthRuleModel::RULE_MAIN,'status'=>1);
        $main_rules  = M('AuthRule')->where($map)->getField('name,id');
        $map         = array('module'=>'admin','type'=>AuthRuleModel::RULE_URL,'status'=>1);
        $child_rules = M('AuthRule')->where($map)->getField('name,id');

        $mailcount = 0;
        $taskcount = 0;
        $title = "访问授权";

        $this->assign('main_rules', $main_rules);
        $this->assign('auth_rules', $child_rules);
        $this->assign('node_list',  $node_list);
        $this->assign('auth_group', $auth_group);
        $this->assign('this_group', $auth_group[(int)$_GET['group_id']]);
        $this->assign('MailCount',$mailcount);
        $this->assign('TaskCount',$taskcount);
        $this->assign('title',$title);
        $this->display();
    }

    /**
     * 用户角色授权页
    **/
    public function user()
    {
        $mailcount = 0;
        $taskcount = 0;
        $title = "成员授权";
        //获取用户和角色
        $users = D("Member")->where("uid >1 and status = 1")->getfield('uid,uid,nickname');
        $roles = D('AuthGroup')->where("status = 1")->getfield('id,id,title');
        $this->assign('users',$users);
        $this->assign('roles',$roles);
        $this->assign('title',$title);
        $this->assign('MailCount',$mailcount);
        $this->assign('TaskCount',$taskcount);
        $this->display();
    }

    /**
     * 角色与用户对照表
    **/
    public function roleList()
    {
        $columns = array(
            array('db' => 'uid', 'dt' => 'uid'),
            array( 'db' => 'username', 'dt' => 'username' ),
            array( 'db' => 'nickname',  'dt' => 'nname' ),
            array( 'db' => 'dept',      'dt' => 'dept' ),
            array('db' => 'group_id_text',     'dt' => 'role')
        );
        $data = M()
            ->field('jx_member.uid,username,nickname,dept,group_id')
            ->table('jx_member')
            ->join('jx_auth_group_access on jx_member.uid=jx_auth_group_access.uid')
            ->select();
        $filter = count($dt);
        $r = $_REQUEST;
        $gid = M(AuthGroupModel::AUTH_GROUP)->getField('id,title');
        $dt = int_to_string($data,$map=array("group_id"=>$gid));
        //print_r($dt);
        $this->ajaxReturn(Dtssp::dtoutput($r,$dt,$filter,$columns));
    }

    /**
     * 用户角色授权及授权编辑
     * 授权：接受传入的uid和gid，检查这两者是否存在；再检查对应的授权是否存在，有则提示无则添加
     * 编辑授权：逻辑和授权完全一样。所以，两者共用同一操作
    **/
    public function AddRole()
    {
        /**
         * isset($_POST['uid']) ? I('uid') : isset($_POST['userid']) ? I('userid') : 0;
         * 设想：判断是否设置了$_POST['uid']，有则返回其值I('uid')，否则再判断是否设置了$_POST['userid']，有则返回
         * 其值I('userid')，否则返回0。即要么返回uid，要么返回userid，两者都没有返回0
         * 但三元运算符从左至右执行，如果不加括号，会首先进行如下判断：
         * 1、isset($_POST['uid']) ? I('uid') : isset($_POST['userid'])
         *    如果isset($_POST['uid'])返回I('uid')，否则返回isset($_POST['userid'])
         *    1.1、返回I('uid')，则继续进行判断：I('uid') ? I('userid') : 0，结果是根据是否有I('userid')来返回I('userid')或0
         *    1.2、返回isset($_POST['userid'])则继续判断：isset($_POST['userid']) ? I('userid') : 0，结果是根据是否设置了
         *          $_POST['userid']来确定是返回其值I('userid')或0
         * 2、从上述可知，最后的结果是，要么返回userid，要么返回0。也就是说，虽然对$_POST['uid']进行了判断，但那只是中间过程，
         *    而不是作为二选一的最终判断，即I('uid')只是判断的依据，永远不可能是最终的值。其逻辑是：
         *    要么得到根据I('uid')判断返回的I('userid')或0，要么得到根据isset($_POST['userid'])判断返回的I('userid')或0
         * 3、isset($_POST['uid']) ? I('uid') : (isset($_POST['userid']) ? I('userid') : 0);
         *    加上括号之后，可以看到，会先判断isset($_POST['userid'])得到相应的值；然后，再判断isset($_POST['uid'])
         *    也就是说，要么得到isset($_POST['uid'])判断返回的值，要么得到isset($_POST['userid'])判断返回的值
         *    而这正是想要的
        **/
        $uid = isset($_POST['uid']) ? I('uid') : (isset($_POST['userid']) ? I('userid') : 0);
        $gid = I('group_id');
        //print_r($_POST);
        if( empty($uid) || empty($gid) ){
            $this->ajaxReturn($uid);
        }
        $AuthGroup = D('AuthGroup');
        if(is_numeric($uid)){
            if ( is_administrator($uid) ) {
                $this->ajaxReturn('该用户为超级管理员');
            }
            if( !M('Member')->where(array('uid'=>$uid))->find() ){
                $this->ajaxReturn('用户不存在');
            }
        }

        if( $gid && !$AuthGroup->checkGroupId($gid)){
            $this->ajaxReturn("角色不存在！");
        }
        $r = $AuthGroup->addToGroup($uid,$gid);
        if ( $r===true ){
            $this->ajaxReturn(1000);
        }else{
            $this->ajaxReturn($r);
        }
    }

    /**
     * 移除用户对某个角色的授权
    **/
    public function deleteRole()
    {
        $deleteItem = $_POST['it'];
        $model = D("AuthGroup");
        $res = $model->deleteRole($deleteItem);
        echo $res;
    }


    public function category()
    {
        $user = "yianyao";
        $mailcount = 0;
        $taskcount = 0;
        $title = "授权管理";
        $this->assign('staff',$user);
        $this->assign('MailCount',$mailcount);
        $this->assign('TaskCount',$taskcount);
        $this->assign('title',$title);
        $this->display('managergroup');
    }








}