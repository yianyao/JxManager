/**
 * Created by yiany on 2016/5/29.
 */
var TableDatatablesAjax = function () {
    //该方法实例化DataTable，然后初始化DataTable，通过ajax.reload重载表格，根据url获取数据。在点击搜索或提交组操作时，传递参数给服务器并返回结果
    var roleTable = function(){
        var ugrid = new Datatable();
        ugrid.init({
            src:$("#roleTable"),
            onSuccess:function(ugrid,response){

            },
            onError:function(ugrid){

            },
            onDataLoad:function(ugrid){

            },
            dataTable:{
                ajax: {
                    url: 'roleList'
                },
                columns: [
                    //决定要使用哪些数据源来显示表格，因为服务器返回的数据源可能很多，但并不要求全用上。对于如复选框列和操作列，应明确设置其不可排序和搜索
                    {},
                    {data: 'uid'},
                    {data: 'username'},
                    {data: 'nname'},
                    {
                        data: 'dept',
                        className : "ellipsis",	//文字过长时用省略号显示，CSS实现
                        render:function (data, type, row, meta) {
                            data = data||"";
                            return '<span title="' + data + '">' + data + '</span>';
                        }
                    },
                    {data: 'role'},
                    {}
                ],
            }
        });

    }

    return {

        //main function to initiate the module
        init: function () {
            roleTable();
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