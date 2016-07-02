var ComponentsDateTimePickers = function () {
    var t = function () {
        jQuery().datepicker && $(".date-picker").datepicker({rtl: App.isRTL(), orientation: "left", autoclose: !0})
    },m = function () {
        jQuery().clockface && ($("#st").clockface({
            format: "HH:mm",
            trigger: "manual"
        }), $("#clockface_1").click(function (t) {
            t.stopPropagation(), $("#st").clockface("toggle")
        }),$("#et").clockface({
                format: "HH:mm",
                trigger: "manual"
            }), $("#clockface_2").click(function (t) {
                t.stopPropagation(), $("#et").clockface("toggle")
            })
       )
    };
    return {
        init: function () {
            t(),m()
        }
    }
}();
var ComponentsColorPickers = function () {
    var t = function () {
        jQuery().colorpicker && ($(".colorpicker-default").colorpicker({format: "hex"}))
    };
    return {
        init: function () {
            t()
        }
    }
}();
function showResponse(responseText, statusText, xhr, $form) {
    if (statusText == "success") {
        if (responseText === true) {
            //$.fancybox.close();//关闭弹出层
            $('#calendar').fullCalendar('refetchEvents'); //重新获取所有事件数据
        } else {
            alert(responseText);
        }
    } else {
        alert(statusText);
    }
}
var SubOptions = {
    success:showResponse,
    url:"opearC",
    type:"post",
    dataType:"json"
}
App.isAngularJsApp() === !1 && jQuery(document).ready(function () {
    ComponentsDateTimePickers.init();
    ComponentsColorPickers.init();
    jQuery("newcalendar").validate();
    jQuery("newcalendar").ajaxForm(SubOptions);
});