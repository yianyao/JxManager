<?php
/**
 * Created by PhpStorm.
 * User: yianyao
 * Date: 16-5-13
 * Time: 下午3:57
 */
    class flowController
    {
        /** 流程表
         * 流程表包括了系统注册的流程(流程名、步数)
         **/
        private $flow;
        /**
         * 流程步数
        **/
        private $count;
        public function __construct($flow)
        {
            parent::__construct();
            $this->flow = D($flow);
        }
        public function setCount()
        {
            $this->count =$this->flow->count();
        }
        public function getCount()
        {
            return $this->count;
        }
        /** 显示流程主界面
         **/
        public function index()
        {
            //获取所有流程列表供用户选择
            $flowList = $this->flow->getField("id","name");
        }
        /** 接受用户传入的流程名，创建流程
         **/
        public function createFlow()
        {
            //获取首节点明细项
            $res = $this->flow->getNotNull(1);
            //前端代码根据首节点生成订单第一步内容
            $this->assign("first",$res);
        }
        /** 显示前面的步骤  前端代码根据流程步数生成流程的tab页
         * @param int $id 当前步数
        **/
        public function setTab($id )
        {
            if (1 < $id < $this->count){
                $map['id'] = array("lt",$id);
                $res = $this->flow()->where($where)->select();
            }else{
                return false;
            }
        }
    }