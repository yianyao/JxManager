<?php
/*
 * 修改DataTable的SSP类满足ThinkPHP实现
 * @license MIT - http://datatables.net/license_mit
 */
namespace Org\Util;
class Dtssp {
    /**
     * Create the data output array for the DataTables rows
     *
     *  @param  array $columns Column information array
     *  @param  array $data    Data from the SQL get
     *  @return array          Formatted data in a row based format
     */
    static function data_output ( $columns, $data )
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
     * Paging
     *
     * Construct the LIMIT clause for server-side processing SQL query
     *
     *  @param  array $request Data sent to server by DataTables
     *  @param  array $columns Column information array
     *  @return string SQL limit clause
     */
    static function limit ( $request, $columns )
    {
        $limit = '';

        if ( isset($request['start']) && $request['length'] != -1 ) {
            $limit = intval($request['start']).", ".intval($request['length']);
        }

        return $limit;
    }


    /**
     * Ordering
     *  1、定义表格列字段数组，如{0,1,2,3,4}
     *  2、循环读取REQUEST二维数组中的order元素
     *      2.1、找到参加排序的列序
     *      2.2、根据该列序找到其在REQUEST二维数组中的元素A
     *      2.3、在表格列字段数组中，找出与该元素data值对应的列字段数组键名
     *      2.4、在表格定义数组中，根据该列字段键名找到对应的列
     *      2.5、如果元素A允许排序，确定其是升序或降序
     *      2.6、把相关的排序sql（`id` asc）赋予变量$orderBy
     *  3、将所有需要排序的字段组装成sql（ORDER BY ·id` asc `name` desc）并返回
     *  @param  array $request Data sent to server by DataTables
     *  @param  array $columns Column information array
     *  @return string SQL order by clause
     */
    static function order ( $request, $columns )
    {
        $order = '';

        if ( isset($request['order']) && count($request['order']) ) {
            $orderBy = array();
            $dtColumns = self::pluck( $columns, 'dt' );

            for ( $i=0, $ien=count($request['order']) ; $i<$ien ; $i++ ) {
                // Convert the column index into the column data property
                $columnIdx = intval($request['order'][$i]['column']);
                $requestColumn = $request['columns'][$columnIdx];

                $columnIdx = array_search( $requestColumn['data'], $dtColumns );
                $column = $columns[ $columnIdx ];

                if ( $requestColumn['orderable'] == 'true' ) {
                    $dir = $request['order'][$i]['dir'] === 'asc' ?
                        'ASC' :
                        'DESC';
                    //格式如 `id` asc
                    $orderBy[] = '`'.$column['db'].'` '.$dir;
                }
            }
            /**  $orderBy = array("`id` asc","`name` desc" )
             *   $order = "`id` asc,`name` desc";
             **/
           $order = implode(', ', $orderBy);
        }
        return $order;
    }


    /**
     * Searching / Filtering
     *  1、如果有设置搜索且搜索值非空，将搜索值赋予$str
     *  2、循环每列，定义全局搜索
     *      2.1、如果允许搜索，
     *      2.2、组装其sql（`id` LIKE :binding_0）
     *      2.3、最后会得到一个数组如：$globalSearch = array(`id` LIKE :binding_0, `name` LIKKE :binding_1)
     *      2.4、组装成sql为：`id` LIKE :binding_0 OR `name` LIKE :binding_1
     *  3、再次循环每列
     *      3.1、如果该列允许搜索且其搜索值非空，同样组装其sql
     *      3.2、最后会得到一个数组，如$columnSearch = array(`age` LIKE :binding_2, `sex` LIKE :binding_3)
     *      3.3、组装成sql为：`age` LIKE :binding_2 AND `sex` LIKE :binding_3
     *  4、此时的$bindings数组如下：
     *      $bindings = array(
     *          array("key"=>':binding_0,"val"=>"%5%","type"=>PDO::PARAM_STR),
     *          array("key"=>':binding_1,"val"=>"%A%","type"=>PDO::PARAM_STR),
     *          array("key"=>':binding_2,"val"=>"%1%","type"=>PDO::PARAM_STR),
     *          array("key"=>':binding_3,"val"=>"%F%","type"=>PDO::PARAM_STR),
     *      )
     *  5、将$globalSearch和$columnSearch数组转换为字符串，最后会组成sql如下（注意：全局的搜索是OR，多列的搜索是AND）
     *       WHERE `id` LIKE :binding_0 OR `name` LIKE :binding_1 AND `age` LIKE :binding_2 AND `sex` LIKE :binding_3
     *  @param  array $request Data sent to server by DataTables
     *  @param  array $columns Column information array
     *  @param  array $bindings Array of values for PDO bindings, used in the
     *    sql_exec() function
     *  @return string SQL where clause
     */
    static function filter ( $request, $columns,$restrict)
    {
        $globalSearch = array();
        $columnSearch = array();
        $dtColumns = self::pluck( $columns, 'dt' );
        if ( isset($request['search']) && $request['search']['value'] != '' ) {
            $str = "'%" . $request['search']['value'] . "%'";

            for ( $i=0, $ien=count($request['columns']) ; $i<$ien ; $i++ ) {
                $requestColumn = $request['columns'][$i];
                $columnIdx = array_search( $requestColumn['data'], $dtColumns );
                $column = $columns[ $columnIdx ];

                if ( $requestColumn['searchable'] == 'true' ) {
                    $globalSearch[] = "`" . $column['db'] . "` LIKE " . $str;
                }
            }
        }
        // Individual column filtering
        if ( isset( $request['columns'] ) ) {
            for ( $i=0, $ien=count($request['columns']) ; $i<$ien ; $i++ ) {
                $requestColumn = $request['columns'][$i];
                $columnIdx = array_search( $requestColumn['data'], $dtColumns );
                $column = $columns[ $columnIdx ];

                $str =  $requestColumn['search']['value'];

                if ( $requestColumn['searchable'] == 'true' &&  $str != '' ) {
                    $str = "'%" . $requestColumn['search']['value'] . "%'";
                    $columnSearch[] = "`".$column['db']."` LIKE " .$str;
                }
            }
        }

        /**  根据入参$restrict限制日期和用户
         * 日期限制，为避免数据量太大影响速度，应限定数据的时间范围，例如，只能获取三年内的数据
         *  默认为查询当前日期三年内的数据，可通过配置参数修改时间范围
         *  因为要通过数据创建时间来确定期限，所以，要求每个数据表必须记录数据创建时间的字段，类型为datetime
         *  同时，字段名定义为createTime
         */
        if (is_array($restrict)){
            //分解约束条件
            $period = $restrict['period'];
            $cfield = $restrict['cfield'];
            $uid = $restrict['uid'];

            $last = (new \DateTime(date('Y-m-d')))->sub(new \DateInterval($period))->format('Y-m-d');
            //加一天，以包含当天
            $now = (new \DateTime(date('Y-m-d')))->add(new \DateInterval('P1D'))->format('Y-m-d');
            $dateLimit = "(`" . $cfield ."` between '" . $last . "' AND  '" . $now . "')" ;

            /**
             * 用户限制
             * 默认只能查询数据库中当前用户的数据，以确保数据不被泄漏，即其他非授权用户无法查看他人数据
             * 如果指定了授权用户，则查询授权用户的数据
             * 超级管理员（uid=1）具有所有权限
             * TODO  因暂时未实现权限系统，所以默认为超管权限，即无需限制数据。在完成权限系统后需要修改代码为：
             * $user =  isset($uid) ? $uid : isset($_SESSION['uid']) ? $_SESSION['uid'] : -1;
             **/
            $user =  isset($uid) ? $uid : isset($_SESSION['uid']) ? $_SESSION['uid'] : 1;
            if ($user > 1){//查找指定用户
                $userLimit = " AND  `uid` = " . $user;
            }elseif($user = 1){ //等于1，超管
                $userLimit = '';
            }else{  //不存在的UID
                $userLimit = " AND `uid` < 0";
            }

        }



        // Combine the filters into a single string
        $where = '';
        if ( count( $globalSearch ) > 0 ) {
            $where = '(' .implode(' OR  ',$globalSearch) . ')';
        }

        if (count($columnSearch)){
            $where = $where === '' ? implode(' AND ',$columnSearch) : $where . ' AND ' . implode(' AND ' ,$columnSearch);
        }
        if (!empty($dateLimit)){
            $where = $where === '' ? $dateLimit . $userLimit : $where . ' AND ' . $dateLimit . $userLimit;
        }

        if ( $where !== '') {
            return $where;
        }else{
            return false;
        }
    }

    /**
     * Perform the SQL queries needed for an server-side processing requested,
     * utilising the helper functions of this class, limit(), order() and
     * filter() among others. The returned array is ready to be encoded as JSON
     * in response to an SSP request, or can be modified if needed before
     * sending back to the client.
     *
     *  @param  array $request Data sent to server by DataTables
     *  @param  object $DtModel 表格对应的模型实例
     *  @param  array $columns Column information array
     * @param array $restrict 约束条件，限制查询范围，分别是period=>日期范围，cfield=>日期字段名，uid=>用户
     *  @return array          Server-side processing response array
     */
    static function simple ( $request,$DtModel, $columns,$restrict )
    {
        // Build the SQL query string from the request
        $limit = self::limit( $request, $columns );//返回 limit 0,10
        $order = self::order( $request, $columns );//返回需排序字段的sql（ORDER BY `id` asc `name` desc）
        $where = self::filter( $request, $columns, $restrict );//返回如`age` LIKE :binding_2 AND `sex` LIKE :binding_3的sql，同时$binginds被更新
        $field = self::getFields($columns);
        /**
         * Main query to actually get the data $dtModel->filed($field)->where($where)->order($order)->limit($limit)->select();
         * 注意：调用field与否的区别，只在于取得数据源的范围。用不用这些数据源来构成表格，由datatables决定
        **/
        $data = $DtModel->where($where)->order($order)->limit($limit)->select();
        // Data set length after filtering
        $recordsFiltered =$DtModel->where($where)->count();

        // Total data set length

        $recordsTotal = $DtModel->count($DtModel->getPk());

        /*
         * Output
         */
        return array(
            "draw"            => isset ( $request['draw'] ) ?
                    intval( $request['draw'] ) :
                    0,
            "recordsTotal"    => intval( $recordsTotal ),
            "recordsFiltered" => intval( $recordsFiltered ),
            "data"            => self::data_output( $columns, $data )
        );
        //echo $DtModel->_sql();
    }

    /**
     * 根据客户端代码传递的查询请求、数据数组、过滤条目及表格信息返回DT数据模型
     * @param array $request:datatables传递过来的REQUEST请求
     * @param array $dt:     客户端代码传递的要输出到表格的数据
     * @param number $recordfilter: 执行了过滤操作后的表格数据的条数
     * @param array $column: 表格列信息
     * @return array
    **/
    static function dtoutput($request,$dt,$recordfilter,$columns)
    {
        $recordstTotal = count($dt);
        return array(
            "draw"            => isset ( $request['draw'] ) ?
                    intval( $request['draw'] ) :
                    0,
            "recordsTotal"    => intval( $recordsTotal ),
            "recordsFiltered" => intval( $recordsFiltered ),
            "data"            => self::data_output( $columns, $dt )
        );
    }


    /**
     * Pull a particular property from each assoc. array in a numeric array,
     * returning and array of the property values from each item.
     *
     *  @param  array  $a    Array to get data from
     *  @param  string $prop Property to read
     *  @return array        Array of property values
     */
    static function pluck ( $a, $prop )
    {
        $out = array();

        for ( $i=0, $len=count($a) ; $i<$len ; $i++ ) {
            $out[] = $a[$i][$prop];
        }

        return $out;
    }

    /**
     * 定义要从数据库中获取的字段
     * @param array $columns : 客户端代码传递来的表格列定义（二维数组）
     *  $columns = array(
     *       array( 'db' => 'nickname', 'dt' => 0 ),
     *       array( 'db' => 'username',  'dt' => 1 ),
     *       array( 'db' => 'dept',      'dt' => 2 )
     *       array( 'db' => 'email',     'dt' => 3 ),
     *       array( 'db' => 'mobile',    'dt' =>4),
     *       array( 'db' => 'status',     'dt' =>5)
     *       );
     * @return string : 返回解析后的字符串
     **/
    static function getFields($columns)
    {
        $fields = self::pluck($columns,'db');
        return implode(',',$fields);
    }

}

