var FormValidationMd = function () {
    var r = function () {
        var e = $("#formvalid2"), r = $(".alert-danger", e), i = $(".alert-success", e);
        e.validate({
            errorElement: "span",
            errorClass: "help-block help-block-error",
            focusInvalid: !1,
            ignore: "",
            rules: {
                title: {required:!0}
            },
            invalidHandler: function (e, t) {
                i.hide(), r.show(), App.scrollTo(r, -200)
            },
            errorPlacement: function (e, r) {
                r.is(":checkbox") ? e.insertAfter(r.closest(".md-checkbox-list, .md-checkbox-inline, .checkbox-list, .checkbox-inline")) : r.is(":radio") ? e.insertAfter(r.closest(".md-radio-list, .md-radio-inline, .radio-list,.radio-inline")) : e.insertAfter(r)
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
            submitHandler:function(form) {
                $(form).ajaxSubmit({
                    type: "post",
                    url: "",
                    success:function(t){
                        layer.msg(t);
                    }
                });
            }
        })
    };
    return {
        init: function () {
            r()
        }
    }
}();
jQuery(document).ready(function () {
    FormValidationMd.init()
});