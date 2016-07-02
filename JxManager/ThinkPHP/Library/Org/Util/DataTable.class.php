<?php
/**
 * desc: jquery datatable服务端代码的thinkphp实现
 * Author: yianyao
 * Date: 16-5-26
 * ver: 1.0
 */
namespace Org\Util;
class DataTable
{
    /**
     * 数据组装，生成符合DT要求的数据
     * @param array $columns: 表格行信息
     * @param array $data:    从数据库获取的将要填充到表格的数据
     * @return array:         符合要求的数组
    **/
    static function dataOutput ( $columns, $data )
    {
        $out = array();

        for ( $i=0, $ien=count($data) ; $i<$ien ; $i++ ) {
            $row = array();

            for ( $j=0, $jen=count($columns) ; $j<$jen ; $j++ ) {
                $column = $columns[$j];

                // Is there a formatter?
                if ( isset( $column['formatter'] ) ) {
                    $row[ $column['dt'] ] = $column['formatter']( $data[$i][ $column['db'] ], $data[$i] );
                }
                else {
                    $row[ $column['dt'] ] = $data[$i][ $columns[$j]['db'] ];
                }
            }

            $out[] = $row;
        }

        return $out;
    }

    /**
     * 范围截取，根据传递的参数确定要取得的数据长度，即LIMIT
    **/
}