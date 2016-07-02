<?php
/** 日程对象的控制层
 * Author: yianyao
 * Ver: v1.0
 */
namespace Admin\Controller;
use Think\Controller;
class CalendarController extends AdminController
{

    /** 日程对象模型
     *
    **/
    private $calendar;

    /** 解析后的用户输入，数组，CURD操作内容
     *
    **/
    private $data = array();
    
    public function __construct()
    {
        parent::__construct();
        $this->calendar = D('calendar');
    }

    public function showCalendar()
    {
        $user = "yianyao";
        $mailcount = 0;
        $taskcount = 0;
        $title = "日程";
        $this->assign('staff',$user);
        $this->assign('MailCount',$mailcount);
        $this->assign('TaskCount',$taskcount);
        $this->assign('title',$title);
        $this->display();
    }

    /** 取得指定用户的日程
     * @return json 指定用户的日程内容
    **/
    public function getJsonData()
    {
        $this->ajaxReturn($this->calendar->toJson(),"JSON");
    }

    /** 入口操作，解析用户输入并调用相关方法
     *
    **/
    public function operation()
    {
        $op = I("op",'');
        if ($op)
        {
            if ($op != 'delete')
            {
                $this->data['title'] = I('title','');
                $this->data['contents'] = I('contents','');
                $this->data['color'] = I('color','');
                $this->data['start'] = I('start',0);
                $this->data['end'] = I('end',0);
                //todo 暂时先默认日程为全天
                $this->data['allday'] = 1;
                if (($this->data['title'] === '') || ($this->data['start'] == 0) || ($this->data['end'] == 0)){
                    $this->ajaxReturn("标题和起始时间必须！");
                }
                if (strtotime($this->data['start']) > strtotime($this->data['end'])){
                    $this->ajaxReturn("结束时间不能早于开始时间！");
                }
            }

            if ($op != "add")
            {
                $id = I("id",0);
                if ($id == 0){
                    $this->ajaxReturn("指定的日程不存在，无法执行操作！");
                }
            }

            switch ($op)
            {
                case "add":
                    $this->add();
                    break;
                case  "edit":
                    $this->edit($id);
                    break;
                case "delete":
                    $this->delete($id);
                    break;
                case "resize":
                    $this->resize($id);
                    break;
                case "eventdrop":
                    $this->eventDrop($id);
                    break;
                default:
                    $this->ajaxReturn("操作错误");
            }
        }
        else
        {
            $this->ajaxReturn("参数错误！");
        }
    }

    /** 接受前端页面传递的数据，判断后确定是否调用对象模型方法添加到数据库并返回结果
     * @return mixed 成功或失败信息
     **/
    public function add()
    {
        $this->ajaxReturn($this->calendar->addCalendar());
    }

    /** 编辑日程内容
     * @param int $id
     * @param array $data (模型层方法)
     * @return mixed 成功或失败信息
     **/
    public function edit($id)
    {
        $this->ajaxReturn($this->calendar->editCalendar($id,$this->data));
    }

    /**  删除日程
     *  调用模型层相关方法
     *  @return mixed boolean|json object
    **/
    public function delete($id){
        $this->ajaxReturn($this->calendar->deleteCalendar($id));
    }

    /**  缩放日程 todo
     *  调用模型层相关方法
     *  @return mixed boolean|json object
     **/
    public function resize()
    {
        $this->ajaxReturn(true);
    }

    /** 拖动日程
     * 调用模型层相关方法
     *  @return mixed boolean|json object
    **/
    public function eventDrop($id)
    {
        $this->ajaxReturn($this->calendar->eventDrop($id,$this->data));
    }

    public function showbox()
    {
        $this->display();
    }
}