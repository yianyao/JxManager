<?php
/**
 * Created by PhpStorm.
 * User: yianyao
 * Date: 16-5-13
 * Time: 下午3:57
 */
class flowModel extends Model
{
    /** 获取某行中所有非空字段
     * @param int $id 行id
     * @return mixed 字段内容数组或布尔值
    **/
    public function getNotNull($id)
    {
        //根据传入的流程名实例化相关流程明细表，取当前节点的明细项
        $res = $this->find($id);
        //遍历明细项，返回非空非0非null的项
        if (count($res) > 0){
            foreach ($res as $k)
            {
                if (($k===null) || ($k === false)  || ($k === '')){
                    unset($res[$k]);
                }
            }
            return $res;
        }else{
            return false;
        }
    }
}