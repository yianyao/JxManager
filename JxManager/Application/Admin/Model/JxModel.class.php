<?php
/**
 * Created by PhpStorm.
 * User: yianyao
 * Date: 16-6-8
 * Time: 上午11:40
 */
namespace Admin\Model;
use Think\Model;
class JxModel extends Model
{
    public function getDate()
    {
        return ((new \DateTime(date('Y-m-d')))->format('Y-m-d'));
    }
}