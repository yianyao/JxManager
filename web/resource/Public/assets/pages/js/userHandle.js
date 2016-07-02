/**
 * Created by yiany on 2016/5/29.
 */
var TableDatatablesAjax = function () {
    //该方法实例化DataTable，然后初始化DataTable，通过ajax.reload重载表格，根据url获取数据。在点击搜索或提交组操作时，传递参数给服务器并返回结果
    var handleRecords = function () {
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
            dataTable: { // here you can define a typical datatable settings from http://datatables.net/usage/options

            },
            dataTable:{
                ajax:{
                    url:'test'
                },
                columns:[
                    {},
                    {data:'fname'},
                    {data:'start_date'},
                    {data:'position'},
                    {
                        data:'email',
                        className : "ellipsis",	//文字过长时用省略号显示，CSS实现
                        render:function (data, type, row, meta) {
                            data = data||"";
                            return '<span title="' + data + '">' + data + '</span>';
                        }
                    },
                    {data:'office'},
                    {data:'age'},
                    {data:'salary'},
                    {}
                ],
            }
        });
        grid.getDataTable().ajax.reload();
        grid.clearAjaxParams();

    }

    var userTable = function(){
        var ugrid = new Datatable();
        ugrid.init({
            src:$("#userTable"),
            onSuccess:function(ugrid,response){

            },
            onError:function(ugrid){

            },
            onDataLoad:function(ugrid){

            },
            dataTable:{
                ajax: {
                    url: 'processing'
                },
                columns: [
                    //决定要使用哪些数据源来显示表格，因为服务器返回的数据源可能很多，但并不要求全用上。对于如复选框列和操作列，应明确设置其不可排序和搜索
                    {},
                    {data: 'nname'},
                    {data: 'account'},
                    {
                        data: 'dept'
                    },
                    {
                        data: 'email',
                        className : "ellipsis",	//文字过长时用省略号显示，CSS实现
                        render:function (data, type, row, meta) {
                            data = data||"";
                            return '<span title="' + data + '">' + data + '</span>';
                        }
                    },
                    {data: 'mobile'},
                    {
                        data: 'status',
                        render: function (data, type, row, meta) {
                            return '<i class="fa fa-male"></i>' + (parseInt(data) ? "在职" : "离职");
                        }
                    },
                    {}
                ],
                "createdRow": function ( row, data, index ) {
                    //行渲染回调,在这里可以对该行dom元素进行任何操作，以下为第六列添加样式
                    $('td', row).eq(6).addClass(parseInt(data.status)?"text-success":"text-error");
                }
            }
        });

    }

    return {

        //main function to initiate the module
        init: function () {
            handleRecords();
            userTable();
        }

    };

}();

var FormValidationMd = function () {
    var e = function () {
        var e = $("#formvalid4"), r = $(".alert-danger", e), i = $(".alert-success", e);
        e.validate({
            errorElement: "span",
            errorClass: "help-block help-block-error",
            focusInvalid: !1,
            ignore: "",
            messages: {
                "dept": {
                    minlength: jQuery.validator.format("至少要选择一项")
                }
            },
            rules: {
                username: {minlength: 6, required: !0},
                password:{required:!0,minlength:6},
                repassword:{required:!0,minlength:6,equalTo:"#password"},
                email: {required: !0, email: !0},
                dept: {required: !0, minlength: 1},
                mobile: {required: !0, digits: !0, minlength: 11},
                status: {required: !0}
            },
            invalidHandler: function (e, t) {
                i.hide(), r.show(), App.scrollTo(r, -100)
            },
            highlight: function (e) {
                $(e).closest(".form-group").addClass("has-error")
            },
            unhighlight: function (e) {
                $(e).closest(".form-group").removeClass("has-error")
            },
            success: function (e) {
                e.closest(".form-group").removeClass("has-error")
            },
            submitHandler: function (e) {
                i.show(), r.hide()
            }
        })
    };
    return {
        init: function () {
            e()
        }
    }
}();
jQuery(document).ready(function() {
    TableDatatablesAjax.init();
    FormValidationMd.init();
});