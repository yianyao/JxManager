$(function (){
    var tableContainer = $('.table-container');
    var table = $('#Config');

    var _table = table.dataTable({
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
        "columns":[
            {
                data:null,
                "createdCell":function(td,cellData,rowData,row,col){
                    $(td).html("<input type='checkbox' value='" + rowData.id + "'/>");
                },
                className: "td-checkbox",
                orderable: false,
                searchable:false
            },
            {data:'id'},
            {data:'name'},
            {data:'title'},
            {data:'type'},
            {data:'value'},
            {
                "createdCell":function(td,cellData,rowData,row,col){
                    var ahref = '<a href = "edit/id/' + rowData.id + '" ';
                    var cls = 'class="btn btn-small btn-primary btn-edit">编辑\<\/a\>&nbsp;&nbsp;';
                    $(td).html(ahref + cls).append('<button type="button" class="btn btn-small btn-danger btn-del">删除</button>');
                },
                data:null,
                orderable: false,
                searchable:false
            },
        ],
        "ajax": { // define ajax settings
            "url": "configList", // ajax URL
            "type": "POST", // request type
        },
        "createdRow":function(row,data,index){
            $("tbody",table).on("click","tr",function(event) {
                $(this).addClass("active").siblings().removeClass("active");
            });
        },
        "initComplete":function(settings,response){
            //添加工具栏，注意datatable.js文件中dom的定义
            $("div.toolbar").html('<a href="add"  class="btn green"><i class="fa fa-plus "></i>添加新纪录</a>&nbsp;' +
                '&nbsp;<button id="removeRow"  class="btn red"><i class="fa fa-remove "></i>删除所选纪录</button>');
        },
        "drawCallback": function(oSettings) { // run some code on table redraw
            $('.group-checkable',table).prop('checked', false);
        }
    }).api();//此处需调用api()方法,否则返回的是JQuery对象而不是DataTables的API对象

    //handle group checkboxes check/uncheck
    $('.group-checkable',table).change(function(){
        var set = table.find('tbody > tr > td:nth-child(1) input[type="checkbox"]');
        var checked = $(this).prop("checked");
        $(set).each(function(){
            $(this).prop("checked",checked);
        });
        $.uniform.update(set);
    });


    $("#btn-add").click(function(){
        userManage.addItemInit();
    });

    $("#btn-del").click(function(){
        var arrItemId = [];
        $("tbody :checkbox:checked",$table).each(function(i) {
            var item = _table.row($(this).closest('tr')).data();
            arrItemId.push(item);
        });
        userManage.deleteRow(arrItemId);
    });

    //行点击事件
    $("tbody",table).on("click","tr",function(event) {
        $(this).addClass("active").siblings().removeClass("active");
        //获取该行对应的数据
        var item = _table.row($(this).closest('tr')).data();
        userManage.currentItem = item;
        userManage.showItemDetail(item);
    });

    table.on("click",".td-checkbox",function(event) {
        //点击单元格即点击复选框
        !$(event.target).is(":checkbox") && $(":checkbox",this).trigger("click");
    }).on("click",".btn-del",function(e) {
        //点击删除按钮
        e.preventDefault();
        var ItemId = [];
        var item = _table.row($(this).closest('tr')).data();
        ItemId.push(item.id);
        $(this).closest('tr').addClass("active").siblings().removeClass("active");
        userManage.deleteRow([ItemId]);
        _table.ajax.reload();
    });

    //批量删除
    tableContainer.on("click","#removeRow",function(e){
        e.preventDefault();
        var ItemId = [];
        $("tbody :checkbox:checked",table).each(function(i) {
            var item = _table.row($(this).closest('tr')).data();
            ItemId.push(item.id);
        });
        //alert(ItemId.length);
        userManage.deleteRow(ItemId);
        _table.ajax.reload();
    })

});

var userManage = {
    showItemDetail : function(item) {
        $("#user-view").show().siblings(".info-block").hide();
        if (!item) {
            $("#user-view .prop-value").text("");
            return;
        }
        $("#name-view").text(item.name);
        $("#position-view").text(item.position);
        $("#salary-view").text(item.salary);
        $("#start-date-view").text(item.start_date);
        $("#office-view").text(item.office);
        $("#extn-view").text(item.extn);
        $("#role-view").text(item.role?"管理员":"操作员");
        $("#status-view").text(item.status?"在线":"离线");
    },
    addItemInit : function() {
        $("#form-add")[0].reset();

        $("#user-add").show().siblings(".info-block").hide();
    },
    editItemInit : function(item) {
        if (!item) {
            return;
        }
        $("#form-edit")[0].reset();
        $("#title-edit").text(item.name);
        $("#name-edit").val(item.name);
        $("#position-edit").val(item.position);
        $("#salary-edit").val(item.salary);
        $("#start-date-edit").val(item.start_date);
        $("#office-edit").val(item.office);
        $("#extn-edit").val(item.extn);
        $("#role-edit").val(item.role);
        $("#user-edit").show().siblings(".info-block").hide();
    },
    addItemSubmit : function() {
        $.dialog.tips('保存当前添加用户');
    },
    editItemSubmit : function() {
        $.dialog.tips('保存当前编辑用户');
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
                    data:{Config:selectedItems.toString()},
                    type:"post",
                    success:function(res){
                        if (res){
                            layer.msg(res);
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
    }


};



