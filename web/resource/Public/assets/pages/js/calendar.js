var AppCalendar = function () {
    return {
        init: function () {
            this.initCalendar()
        }, initCalendar: function () {
            if (jQuery().fullCalendar) {
                /* 创建日期对象e，获取其日、月、年并赋予t、a、n，并创建空对象r */
                var e = new Date, t = e.getDate(), a = e.getMonth(), n = e.getFullYear(), r = {};
                /*
                 *  连续的三元运算符，先判断日程表元素#calender父元素集中应用了portlet样式的父元素宽度是否小于720，以调整日程表元素顶端内容位置；
                 *  再根据父级元素宽度判断日程表元素宽度是否需要应用mobile样式；最后，判断是否使用RTL模式，选择合适的表现风格
                 *  把这些内容赋予r
                 */
                App.isRTL() ? $("#calendar").parents(".portlet").width() <= 720 ? ($("#calendar").addClass("mobile"), r = {
                    right: "title, prev, next",
                    center: "",
                    left: "agendaDay, agendaWeek, month, today"
                }) : ($("#calendar").removeClass("mobile"), r = {
                    right: "title",
                    center: "",
                    left: "agendaDay, agendaWeek, month, today, prev,next"
                }) : $("#calendar").parents(".portlet").width() <= 720 ? ($("#calendar").addClass("mobile"), r = {
                    left: "title, prev, next",
                    center: "",
                    right: "today,month,agendaWeek,agendaDay"
                }) : ($("#calendar").removeClass("mobile"), r = {
                    left: "title",
                    center: "",
                    right: "prev,next,today,month,agendaWeek,agendaDay"
                });
                var l = function (e) {
                    //去掉e元素的文本内容首尾空格
                    var t = {title: $.trim(e.text())};
                    //设置eventObject的值为t
                    e.data("eventObject", t), e.draggable({zIndex: 999, revert: !0, revertDuration: 0})
                }, o = function (e) {
                    e = 0 === e.length ? "Untitled Event" : e;
                    var t = $('<div class="external-event label label-default">' + e + "</div>");
                    //新建内容为e或untitled Event的日程标签并附加到#event_box元素后
                    jQuery("#event_box").append(t), l(t)
                };
                /*
                 遍历#external-events下样式为external-event的div，执行l()方法
                 其目的是去除div包含文本的首尾空格，并将其作为键值对中的值，把整个键值对赋予对象t，再设置eventObject的值为t
                 */
                $("#external-events div.external-event").each(function () {
                    l($(this))
                }), $("#event_add").unbind("click").click(function () {
                    /*
                     在执行单击操作前先解除绑定在该元素上的单击事件
                     获取#event_title元素（即新鲜输入的日程标签）的值并赋予e
                     执行o()方法，即把该日程标签附加到#event_box元素后
                     简言之，这几行代码用来创建带有文本的日程标签
                     */
                    var e = $("#event_title").val();
                    o(e)
                }), $("#event_box").html(""),$("#calendar").fullCalendar("destroy"),
                    /*
                     * 主程序
                     * */
                    $("#calendar").fullCalendar({
                        //将前面r对象作为日程表顶部内容，包括其他选项
                        header: r,
                        defaultView: "month",
                        slotMinutes: 15,
                        editable: !0,
                        droppable: !0,
                        weekMode: 'liquid',
                        events: "getJsonData",
                        //拖动
                        drop: function (e, t,ui) {
                            var a = $(this).data("eventObject"), n = $.extend({}, a);
                            n.start = e,n.allDay = t, n.className = $(this).attr("data-class"), $("#calendar").fullCalendar("renderEvent", n, !0), $("#drop-remove").is(":checked") && $(this).remove();
                            var st = moment(e).format('YYYY-MM-DD');
                            var et = moment(e).add(1,'d').format("YYYY-MM-DD");
                            var tt = a.title;
                            //console.warn(a);
                            $.post("operation",{op:"add",start:st,end:et,title:tt},function(r){
                                if (r === true){
                                    $("#calendar").fullCalendar("refetchEvent");
                                }else{
                                    layer.msg(r);
                                }
                            });
                        },
                        dayClick:function(date,allDay,jsEvent,view){
                            var selDate = date.format("YYYY-MM-DD HH:mm");
                            $('#start').val(selDate);
                            $('#end').val(moment(selDate).add(1,'d').format("YYYY-MM-DD HH:mm"));
                            layer.open({
                                type: 1,
                                title: '日程规划',
                                move:'.layui-layer-title',
                                shift:5,
                                closeBtn: 1,
                                btn:['提交','取消'],
                                area:['80%','100%'],
                                skin: 'layui-layer-lan',
                                shadeClose: true,
                                content: $('#modal'),
                                yes:function(index,layero){
                                    $("#newCalendar").validate();
                                    $.post("operation",{op:"add",title:$("#title").val(),start:$("#start").val(),end:$("#end").val(),color:$("#color").val()},function(r){
                                        if (r === true){
                                            $("#calendar").fullCalendar("refetchEvents");
                                            layer.close(index);
                                        }else{
                                            layer.msg(r);
                                        }
                                    },"json")
                                }
                            });
                        },
                        eventResize:function(evt,delta,revertFunc){
                          var evtData = {
                              id:evt.id,
                              start:evt.start.format(),
                              end:evt.end.format()
                          };
                          $.ajax({
                              type:"post",
                              url:"resize",
                              data:evtData,
                              success:function(r,s,x){
                                  if (s === "success"){
                                      if (r === true){
                                          $("#Calendar").fullCalendar("refetchEvents");
                                      }else{
                                          layer.msg(r);
                                      }
                                  }else{
                                      revertFunc();
                                      layer.alert(s);
                                  }
                              }
                          })
                        },
                        eventDrop:function(evt,delta,revertFunc,js,ui){
                            $.post("operation",{op:"eventdrop",id:evt.id,start:moment(evt.start).format("YYYY-MM-DD"),end:moment(evt.end).format("YYYY-MM-DD"),title:evt.title,color:evt.color},function(r){
                                if (r !== true){
                                    revertFunc();
                                }else{
                                    //$("#calendar").fullCalendar("refetchEvents");
                                }
                            });
                        },
                        eventClick:function(event,jsEvent,view){
                            var t = event.title;
                            var s = moment(event.start).format("YYYY-MM-DD");
                            var e = moment(event.end).format("YYYY-MM-DD");
                            $("#title").val(t);
                            $("#start").val(s);
                            $("#end").val(e);
                            layer.open({
                                type: 1,
                                title: '日程编辑',
                                move:'.layui-layer-title',
                                shift:5,
                                closeBtn: 1,
                                btn:['提交','取消','删除'],
                                area:['80%','100%'],
                                skin: 'layui-layer-lan',
                                shadeClose: true,
                                content: $('#modal'),
                                yes:function(index,layero){
                                    $("#newCalendar").validate();
                                    $.post("operation",{op:"edit",id:event.id,title:$("#title").val(),start:$("#start").val(),end:$("#end").val(),color:$("#color").val()},function(r){
                                        if (r === true){
                                            $("#calendar").fullCalendar("refetchEvents");
                                            layer.close(index);
                                        }else{
                                            layer.msg(r);
                                        }
                                    },"json")
                                },
                                btn3:function(index){
                                    layer.alert("你确定要删除该日程么？",{icon:3,title:"提示"},function(){
                                            $.post("operation",{op:"delete",id:event.id},function(r){
                                                if (r === true){
                                                    $("#calendar").fullCalendar("refetchEvents");
                                                    layer.close(index);
                                                }else{
                                                    layer.msg(r);
                                                }
                                            },"json");
                                        }
                                    )
                                }
                            });
                        }
                    })
            }
        }
    }
}();
jQuery(document).ready(function () {
    AppCalendar.init();
    $('#color').minicolors();
    var start = {
        elem: '#start',
        format: 'YYYY-MM-DD hh:mm',
        min: laydate.now(), //设定最小日期为当前日期
        max: '2099-06-16 23:59:59', //最大日期
        istime: true,
        istoday: false,
        choose: function(datas){
            end.min = datas; //开始日选好后，重置结束日的最小日期
            end.start = datas //将结束日的初始值设定为开始日
        }
    };
    var end = {
        elem: '#end',
        format: 'YYYY-MM-DD hh:mm',
        min: laydate.now(),
        max: '2099-06-16 23:59:59',
        istime: true,
        istoday: false,
        choose: function(datas){
            start.max = datas; //结束日选好后，重置开始日的最大日期
        }
    };
    laydate(start);
    laydate(end);

});