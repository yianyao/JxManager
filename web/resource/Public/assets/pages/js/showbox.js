function showResponse(responseText, statusText, xhr, $form) {
    if (statusText == "success") {
        if (responseText === true) {
            $.fancybox.close();//关闭弹出层
            $('#calendar').fullCalendar('refetchEvents'); //重新获取所有事件数据
        } else {
            alert(responseText);
        }
    } else {
        alert(statusText);
    }
}
$.validator.addMethod("comp", function(value,element,param) {
    var st = $("start").val();
    return this.optional(element) || moment(value).isAfter(st);
}, '结束日期不能小于开始日期！');
var FormValidation = function () {
    var r = function () {
        var e = $("#addCalendar"), r = $(".alert-danger", e), i = $(".alert-success", e);
        e.validate({
            errorElement: "span",
            errorClass: "help-block help-block-error",
            focusInvalid: !1,
            ignore: "",
            rules: {
                title: {required: !0},
                start: {required: !0},
                end:"comp"
            },
            invalidHandler: function (e, t) {
                i.hide(), r.show(), App.scrollTo(r, -200)
            },
            errorPlacement: function (e, r) {
                var i = $(r).parent(".input-icon").children("i");
                i.removeClass("fa-check").addClass("fa-warning"), i.attr("data-original-title", e.text()).tooltip({container: "body"})
            },
            highlight: function (e) {
                $(e).closest(".form-group").removeClass("has-success").addClass("has-error")
            },
            unhighlight: function (e) {
            },
            success: function (e, r) {
                var i = $(r).parent(".input-icon").children("i");
                $(r).closest(".form-group").removeClass("has-error").addClass("has-success"), i.removeClass("fa-warning").addClass("fa-check")
            },
            submitHandler: function (form) {
                jQuery(form).ajaxSubmit({
                    dataType:"json",
                    type:"post",
                    url:"operaC",
                    success:showResponse
                })
            }
        }), $(".date-picker").datepicker({
            rtl: App.isRTL(),
            autoclose: !0
        }), $(".date-picker .form-control").change(function () {
            e.validate().element($(this))
        })
    };
    return {
        init: function () {
            r()
        }
    }
}();
jQuery(document).ready(function () {
    FormValidation.init();

});