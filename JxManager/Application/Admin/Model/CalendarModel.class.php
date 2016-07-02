<?php
    /**
     * Created by PhpStorm.
     * User: yianyao
     * Date: 16-4-22
     * Time: 下午10:40
     */
    namespace Admin\Model;
    use Think\Model;
    class CalendarModel extends Model
    {
        /** 错误信息
         * 
        **/
        private $_error = array(
            "create" => "数据创建失败",
            "add" => "数据写入失败",
            "update" => "数据更新失败",
            "delete" => "删除失败",
            "del0" => "没有删除数据",
            "eventdrop" => "无法拖动日程"
        );
        /** 字段自动验证
         *
        **/
        protected $_validate = array(
            array('title','require','标题必须！'),
            array('start','require','开始日期必须！'),
            array('end','require','结束日期必须！')
        );
        /** 字段自动完成
         *
        **/
        protected $_auto = array(
            array("status",'1'),
            array('sharekey','1'),
            array('uid','1')
        );
        /** 将取到的数据集转换为符合Calendar控件的json
         *
        **/
        public function toJson()
        {
            $condition['uid'] = session('uid') ? session('uid') : 1;
            $data = $this->where($condition)->select();
            foreach($data as &$v)
            {
                $v['title'] = $v['title']. ' ' . $v['contents'];
            }
            return $data;
        }

        /** 添加数据
         * @param array $data 需写入的数组
         * @return mixed 返回错误信息
        **/
        public function addCalendar()
        {
            if ($this->create())
            {
                if ($this->add())
                {
                    return true;
                }else
                {
                    return $this->_error['add'];
                }
            }else
            {
                return $this->_error['create'];
            }
        }

        /** 更新数据
         * @param array $data 需更新的数据
         * @param int $id 更新项
         * @return mixed 返回错误信息
        **/
        public function editCalendar($id,$data)
        {
            $condition['id'] = $id;
            if ($this->where($condition)->setField($data))
            {
                return true;
            }else
            {
                return $this->_error['update'];
            }
        }
        /** 删除日程
         * @param int $id 日程id
         * @return mix 返回布尔值或错误信息
        **/
        public function deleteCalendar($id)
        {
            $condition['id'] = $id;
            $res = $this->where($condition)->delete();
            if ($res > 0)
            {
                return true;
            }elseif ($res === 0)
            {
                return $this->_error['del0'];
            }else
            {
                return $this->_error['delete'];
            }
        }
        /** 拖动日程
         * @param int $id 日程id
         * @return mix 返回布尔值或错误信息
        **/
        public function eventDrop($id,$data)
        {
            $condition['id'] = $id;
            if ($this->where($condition)->setField($data))
            {
                return true;
            }else
            {
                return $this->_error['eventdrop'];
            }
        }
    }