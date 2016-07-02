var FormValidationMd = function () {
    var r = function () {
        var e = $("#formvalid2"), r = $(".alert-danger", e), i = $(".alert-success", e);
        e.validate({
            errorElement: "span",
            errorClass: "help-block help-block-error",
            focusInvalid: !1,
            ignore: "",
            messages: {
                "checkboxes2[]": {
                    required: "Please check some options",
                    minlength: jQuery.validator.format("At least {0} items must be selected")
                }
            },
            rules: {
                name: {minlength: 6, required: !0},
                pwd:{required:!0,minlength:6},
                cpwd:{required:!0,minlength:6,equalTo:"#pwd"},
                email: {required: !0, email: !0},
                "checkboxes2[]": {required: !0, minlength: 1},
                radio1: {required: !0},
                radio2: {required: !0}
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
                    url: "test_save.php?time=" + (new Date()).getTime()
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