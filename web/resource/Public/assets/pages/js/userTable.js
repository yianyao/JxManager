var TableDatatablesAjax = function () {
    //该方法会实例化DataTable，然后初始化DataTable，通过ajax.reload重载表格，根据url获取数据。在点击搜索或提交组操作时，传递参数给服务器并返回结果
    var handleRecords = function () {
        //调用datatable.js的Datatable方法实例化Datatable
        var grid = new Datatable();
        grid.init({
            src: $("#menu"),
            onSuccess: function (grid, response) {
                // grid:        grid object
                // response:    json object of server side ajax response
                // execute some code after table records loaded
            },
            onError: function (grid) {
                // execute some code on network or other general error
            },
            onDataLoad: function(grid) {
                // execute some code on ajax data load
            },
            dataTable:{
                ajax: {
                    url: 'menuList'
                },
                columns: [
                    //决定要使用哪些数据源来显示表格，因为服务器返回的数据源可能很多，但并不要求全用上。对于如复选框列和操作列，应明确设置其不可排序和搜索
                    {},
                    {data: 'id'},
                    {data: 'title'},
                    {data: 'pid'},
                    {
                        data: 'url',
                        className : "ellipsis",	//文字过长时用省略号显示，CSS实现
                        render:function (data, type, row, meta) {
                            data = data||"";
                            return '<span title="' + data + '">' + data + '</span>';
                        }
                    },
                    {data: 'group'},
                    {data: 'sort'},
                    {}
                ],
                "initComplete":function(settings,response){
                    //添加工具栏，注意datatable.js文件中dom的定义
                    $("div.toolbar").html('<a href="add"  class="btn green"><i class="fa fa-plus "></i>添加新纪录</a>&nbsp;' +
                        '&nbsp;<button id="removeRow"  class="btn red"><i class="fa fa-remove "></i>删除所选纪录</button>');
                },
            }
        });
        grid.getDataTable().ajax.reload();
        grid.clearAjaxParams();

    }

    return {

        //main function to initiate the module
        init: function () {
            handleRecords();
        }

    };

}();

jQuery(document).ready(function() {
    TableDatatablesAjax.init();
});

