var Datatable = function() {

    var tableOptions; // main options
    var dataTable; // datatable object
    var table; // actual table jquery object
    var tableContainer; // actual table container object
    var tableInitialized = false;
    var ajaxParams = {}; // set filter mode
    var the;
    return {

        //main function to initiate the module
        init: function(options) {

            if (!$().dataTable) {//没有实例化对象则退出？
                return;
            }

            the = this;

            // default settings
            options = $.extend(true, {//递归合并
                src: "", // actual table  
                filterApplyAction: "filter",
                filterCancelAction: "filter_cancel",
                resetGroupActionInputOnSuccess: true,
                loadingMessage: '加载中...',
                DeferRender:true,//开启延迟渲染，Ajax加载时提升速度
                stripeClasses: ["odd", "even"],//为奇偶行加上样式，兼容不支持CSS伪类的场合
                serverSide: true,	//启用服务器端分页
                searching: true,
                dataTable: {
                    "dom": '<"toolbar">ifrt<"bottom"lp><"clear">', // datatable layout
                    "pageLength": 10, // default records per page
                    "pagingType": "bootstrap_full_number", // pagination type(bootstrap, bootstrap_full_number or bootstrap_extended)
                    "autoWidth": false, // disable fixed width and enable fluid table
                    "processing": false, // enable/disable display message box on record load
                    "serverSide": true, // enable/disable server side ajax loading
                    language: {
                        "sProcessing":   "处理中...",
                        "sLengthMenu":   "每页 _MENU_ 项",
                        "sZeroRecords":  "没有匹配结果",
                        "sInfo":         "当前显示第 _START_ 至 _END_ 项，共 _TOTAL_ 项。",
                        "sInfoEmpty":    "当前显示第 0 至 0 项，共 0 项",
                        "sInfoFiltered": "(由 _MAX_ 项结果过滤)",
                        "sInfoPostFix":  "",
                        "sSearch":       "搜索:",
                        "sUrl":          "",
                        "sEmptyTable":     "表中数据为空",
                        "sLoadingRecords": "载入中...",
                        "sInfoThousands":  ",",
                        "oPaginate": {
                            "sFirst":    "首页",
                            "sPrevious": "上页",
                            "sNext":     "下页",
                            "sLast":     "末页",
                            "sJump":     "跳转"
                        },
                        "oAria": {
                            "sSortAscending":  ": 以升序排列此列",
                            "sSortDescending": ": 以降序排列此列"
                        }
                    },
                    "orderCellsTop": true,
                    "order": [
                        [1, "asc"]
                    ],
                    "columnDefs":[
                        {
                            data:null,
                            "targets":0,
                            "createdCell":function(td,cellData,rowData,row,col){
                                $(td).html("<input type='checkbox' value='" + rowData.id + "'/>");
                            },
                            className: "td-checkbox",
                        },
                        {
                            "targets":-1,
                            "createdCell":function(td,cellData,rowData,row,col){
                                $(td).html(('<button type="button" class="btn btn-small btn-primary btn-edit">编辑</button>&nbsp;&nbsp;')).append('<button type="button" class="btn btn-small btn-danger btn-del">删除</button>');
                            },
                            data:null
                        },
                        {
                            "targets":[-1,0],
                            orderable: false,
                            searchable:false,
                        }

                    ],
                    "ajax": { // define ajax settings
                        "url": "", // ajax URL
                        "type": "POST", // request type
                        "timeout": 20000,
                        "data": function(data) { // add request parameters before submit
                            $.each(ajaxParams, function(key, value) {
                                data[key] = value;
                            });
                        }
                    },
                    "createdRow":function(row,data,index){
                        $("tbody",table).on("click","tr",function(event) {
                            $(this).addClass("active").siblings().removeClass("active");
                            //获取该行对应的数据
                            var item = dataTable.row($(this).closest('tr')).data();
                        });
                    },
                    "initComplete":function(settings,response){
                        //添加工具栏，注意datatable.js文件中dom的定义
                        $("div.toolbar").html('<button id="addRow"  class="btn green"><i class="fa fa-plus "></i>添加新纪录</button>&nbsp;' +
                            '&nbsp;<button id="removeRow"  class="btn red"><i class="fa fa-remove "></i>删除所选纪录</button>');
                    },
                    "drawCallback": function(oSettings) { // run some code on table redraw
                        if (tableInitialized === false) { // check if table has been initialized
                            tableInitialized = true; // set table initialized
                            table.show(); // display table
                        }

                       //清空全选状态
                        $('.group-checkable',table).prop('checked', false);

                        // callback for ajax data load
                        if (tableOptions.onDataLoad) {
                            tableOptions.onDataLoad.call(undefined, the);
                        }
                    }
                }
            }, options);

            tableOptions = options;

            // create table's jquery object
            table = $(options.src);
            tableContainer = table.parents(".table-container");


            // initialize a datatable
            dataTable = table.DataTable(options.dataTable);

            //handle group checkboxes check/uncheck
            $('.group-checkable',table).change(function(){
                var set = table.find('tbody > tr > td:nth-child(1) input[type="checkbox"]');
                var checked = $(this).prop("checked");
                $(set).each(function(){
                    $(this).prop("checked",checked);
                });
                $.uniform.update(set);
            });

            //点击单元格即点击复选框
            table.on("click", ".td-checkbox",function (event) {
                !$(event.target).is(":checkbox") && $(":checkbox", this).trigger("click");
            }).on("click",".btn-edit",function(e){  //点击编辑按钮
                e.preventDefault();
                var item = dataTable.row($(this).closest('tr')).data();
                $(this).closest('tr').addClass("active").siblings().removeClass("active");
                the.editRow(item);
                dataTable.ajax.reload();
            }).on("click",".btn-del",function(e) {
                //点击删除按钮
                e.preventDefault();
                ItemId = [];
                var item = dataTable.row($(this).closest('tr')).data();
                ItemId.push(item.id);
                $(this).closest('tr').addClass("active").siblings().removeClass("active");
                the.deleteRow(ItemId);
                dataTable.ajax.reload();
            });

            //新建
            tableContainer.on("click","#addRow",function(e){
                e.preventDefault();

                layer.open({
                    type: 1,
                    title: '用户注册',
                    move:'.layui-layer-title',
                    shift:5,
                    closeBtn: 1,
                    btn:['提交','取消'],
                    area:['80%','100%'],
                    skin: 'layui-layer-lan',
                    shadeClose: true,
                    content: $('#modal'),
                    yes:function(index,layero){
                        $.ajax({
                            url:"regedit",
                            data:$("#formvalid4").serialize(),
                            type:"post",
                            //dataType:'json',
                            beforeSend:function(x){
                                $("#formvalid4").validate();
                            },
                            success:function(msg){
                                if (msg == 1000){
                                    layer.close(index);
                                    dataTable.ajax.reload();
                                }else{
                                    layer.msg(msg);
                                }
                            },
                        })
                    }
                });
            })

            //批量删除
            tableContainer.on("click","#removeRow",function(e){
                e.preventDefault();
                var ItemId = [];
                $("tbody :checkbox:checked",table).each(function(i) {
                    var item = dataTable.row($(this).closest('tr')).data();
                    ItemId.push(item.id);
                });
                //alert(ItemId.length);
                the.deleteRow(ItemId);
            })

            // handle filter submit button click  点击搜索按钮时，将搜索参数数组ajaxParams发送到服务器
            table.on('click', '.filter-submit', function(e) {
                e.preventDefault();
                the.submitFilter();
            });

            // handle filter cancel button click
            table.on('click', '.filter-cancel', function(e) {
                e.preventDefault();
                the.resetFilter();
            });

        },

        submitFilter: function() {
            the.setAjaxParam("action", tableOptions.filterApplyAction);//调用setAjaxParam()返回ajaxParams['action']=filter

            // get all typeable inputs 返回所有CSS类为form-filter的过滤项名值对，格式为ajaxParams['name']=value，如ajaxParam['order_order_status']='Pending'
            $('textarea.form-filter, select.form-filter, input.form-filter:not([type="radio"],[type="checkbox"])', table).each(function() {
                the.setAjaxParam($(this).attr("name"), $(this).val());
            });

            // get all checkboxes 返回表格中所有CSS类为form-filter的已选中复选框的名值对
            $('input.form-filter[type="checkbox"]:checked', table).each(function() {
                the.addAjaxParam($(this).attr("name"), $(this).val());
            });

            // get all radio buttons 返回表格中所有CSS类为form-filter的已选中的单选框的名值对
            $('input.form-filter[type="radio"]:checked', table).each(function() {
                the.setAjaxParam($(this).attr("name"), $(this).val());
            });
            //重新加载表格，其实就是发送过滤请求到服务器中
            dataTable.ajax.reload();
        },

        resetFilter: function() {
            $('textarea.form-filter, select.form-filter, input.form-filter', table).each(function() {
                $(this).val("");
            });
            $('input.form-filter[type="checkbox"]', table).each(function() {
                $(this).attr("checked", false);
            });
            the.clearAjaxParams();
            the.addAjaxParam("action", tableOptions.filterCancelAction);
            dataTable.ajax.reload();
        },


        setAjaxParam: function(name, value) {
            ajaxParams[name] = value;
        },

        addAjaxParam: function(name, value) {
            if (!ajaxParams[name]) {
                ajaxParams[name] = [];
            }

            skip = false;
            for (var i = 0; i < (ajaxParams[name]).length; i++) { // check for duplicates
                if (ajaxParams[name][i] === value) {
                    skip = true;
                }
            }

            if (skip === false) {
                ajaxParams[name].push(value);
            }
        },

        clearAjaxParams: function(name, value) {
            ajaxParams = {};
        },

        getDataTable: function() {
            return dataTable;
        },

        gettableContainer: function() {
            return tableContainer;
        },

        getTable: function() {
            return table;
        },

        deleteRow : function(selectedItems) {
            var message;
            if (selectedItems&&selectedItems.length) {
                if (selectedItems.length == 1) {
                    message = "确定要删除第" + selectedItems + "项记录吗?";

                }else{
                    message = "确定要删除选中的"+selectedItems.length+"项记录吗?";
                }
                layer.confirm(message, function(){
                    $.ajax({
                        url:"deleteRow",
                        data:{Datatables:selectedItems.toString()},
                        type:"post",
                        success:function(res){
                            if (res){
                                layer.msg(res);
                                dataTable.ajax.reload();
                            }else{
                                layer.msg('删除失败！');
                            }
                        },
                        error:function(e){
                            layer.msg('无法执行删除操作！');
                        }
                    })
                });
            }else{
                layer.msg('请先选中要操作的行');
            }
        },

        editRow:function(rowInfo){
            $('#username-edit').val(rowInfo.account);
            $('#nickname-edit').val(rowInfo.nname);
            $('#email-edit').val(rowInfo.email);
            $('#mobile-edit').val(rowInfo.mobile);
            $('#dept-edit').val(rowInfo.dept);
            $('#uid').val(rowInfo.id);
            parseInt(rowInfo.status) ? $('#checkbox1_8').prop("checked","checked") : $('#checkbox1_9').prop("checked","checked");
            layer.open({
                type: 1,
                title: '用户信息修改',
                move:'.layui-layer-title',
                shift:5,
                closeBtn: 1,
                btn:['提交','取消'],
                area:['80%','100%'],
                skin: 'layui-layer-lan',
                shadeClose: true,
                content: $('#edit'),
                yes:function(index,layero){
                    $.ajax({
                        url:"update",
                        data:$("#formvalid").serialize(),
                        type:"post",
                        //dataType:'json',
                        success:function(msg){
                            if (msg > 0 && msg != false){
                                layer.close(index);
                                dataTable.ajax.reload();
                            }else{
                                msg = "更新失败！";
                                layer.msg(msg);
                            }
                        },
                    })
                }
            })
        }

    };

};