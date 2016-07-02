var TableDatatablesAjax = function () {
    //该方法会实例化DataTable，然后初始化DataTable，通过ajax.reload重载表格，根据url获取数据。在点击搜索或提交组操作时，传递参数给服务器并返回结果
    var handleRecords = function () {

        function restoreRow(oTable, nRow) {
            var aData = oTable.fnGetData(nRow);
            var jqTds = $('>td', nRow);

            for (var i = 0, iLen = jqTds.length; i < iLen; i++) {
                oTable.fnUpdate(aData[i], nRow, i, false);
            }

            oTable.fnDraw();
        }

        function editRow(oTable, nRow) {
            var aData = oTable.fnGetData(nRow);
            var jqTds = $('>td', nRow);
            jqTds[0].innerHTML = '<input type="text" class="form-control input-small" value="' + aData[0] + '">';
            jqTds[1].innerHTML = '<input type="text" class="form-control input-small" value="' + aData[1] + '">';
            jqTds[2].innerHTML = '<input type="text" class="form-control input-small" value="' + aData[2] + '">';
            jqTds[3].innerHTML = '<input type="text" class="form-control input-small" value="' + aData[3] + '">';
            jqTds[4].innerHTML = '<a class="edit" href="">Save</a>';
            jqTds[5].innerHTML = '<a class="cancel" href="">Cancel</a>';
        }

        function saveRow(oTable, nRow) {
            var jqInputs = $('input', nRow);
            oTable.fnUpdate(jqInputs[0].value, nRow, 0, false);
            oTable.fnUpdate(jqInputs[1].value, nRow, 1, false);
            oTable.fnUpdate(jqInputs[2].value, nRow, 2, false);
            oTable.fnUpdate(jqInputs[3].value, nRow, 3, false);
            oTable.fnUpdate('<a class="edit" href="">Edit</a>', nRow, 4, false);
            oTable.fnUpdate('<a class="delete" href="">Delete</a>', nRow, 5, false);
            oTable.fnDraw();
        }

        function cancelEditRow(oTable, nRow) {
            var jqInputs = $('input', nRow);
            oTable.fnUpdate(jqInputs[0].value, nRow, 0, false);
            oTable.fnUpdate(jqInputs[1].value, nRow, 1, false);
            oTable.fnUpdate(jqInputs[2].value, nRow, 2, false);
            oTable.fnUpdate(jqInputs[3].value, nRow, 3, false);
            oTable.fnUpdate('<a class="edit" href="">Edit</a>', nRow, 4, false);
            oTable.fnDraw();
        }

        //调用datatable.js的Datatable方法实例化Datatable
        var grid = new Datatable();
        grid.init({
            src: $("#datatable_ajax"),
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
            loadingMessage: '加载中...',
            dataTable: {
                'ajax': function (data, callback, settings) {
                    $.ajax({
                        url: 'test',
                        dataType: 'json',
                        //成功返回后需要调用datatables的callback方法进行渲染以生成表格
                        success: function (res) {
                            callback(res);
                        },
                        error: function (XMLHttpRequest, textStatus, errorThrown) {
                            layer.alert("查询失败，请联系管理员！");
                        }
                    });
                }
            }
        });
        grid.getDataTable().ajax.reload();
        grid.clearAjaxParams();

        //添加工具栏，注意datatable.js文件中dom的定义
        $("div.toolbar").html('<span><button id="addRow"  class="btn green"><i class="fa fa-plus "></i>添加新记录</button></span><span><button id="removeRow"  class="btn red"><i class="fa fa-remove "></i>删除所选记录</button></span>');
        $("#addRow").on('click',function(){
            grid.getDataTable().row.add(100000,'12/06/2015','','','54545$',8,'<span class="label label-sm label-success">Pending</span>','<a href="javascript:;" class="btn btn-sm btn-outline grey-salsa"><i class="fa fa-search"></i> View</a>').draw();
        });
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