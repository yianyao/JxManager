var AppCalendar = function () {
    return {
        init: function () {
            this.initCalendar()
        }, initCalendar: function () {
            if (jQuery().fullCalendar) {
                /* �������ڶ���e����ȡ���ա��¡��겢����t��a��n���������ն���r */
                var e = new Date, t = e.getDate(), a = e.getMonth(), n = e.getFullYear(), r = {};
                /*
                 *  ��������Ԫ����������ж��ճ̱�Ԫ��#calender��Ԫ�ؼ���Ӧ����portlet��ʽ�ĸ�Ԫ�ؿ���Ƿ�С��720���Ե����ճ̱�Ԫ�ض�������λ�ã�
                 *  �ٸ��ݸ���Ԫ�ؿ���ж��ճ̱�Ԫ�ؿ���Ƿ���ҪӦ��mobile��ʽ������ж��Ƿ�ʹ��RTLģʽ��ѡ����ʵı��ַ��
                 *  ����Щ���ݸ���r
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
                    //ȥ��eԪ�ص��ı�������β�ո�
                    var t = {title: $.trim(e.text())};
                    //����eventObject��ֵΪt
                    e.data("eventObject", t), e.draggable({zIndex: 999, revert: !0, revertDuration: 0})
                }, o = function (e) {
                    e = 0 === e.length ? "Untitled Event" : e;
                    var t = $('<div class="external-event label label-default">' + e + "</div>");
                    //�½�����Ϊe��untitled Event���ճ̱�ǩ�����ӵ�#event_boxԪ�غ�
                    jQuery("#event_box").append(t), l(t)
                };
                /*
                 ����#external-events����ʽΪexternal-event��div��ִ��l()����
                 ��Ŀ����ȥ��div�����ı�����β�ո񣬲�������Ϊ��ֵ���е�ֵ����������ֵ�Ը������t��������eventObject��ֵΪt
                 */
                $("#external-events div.external-event").each(function () {
                    l($(this))
                }), $("#event_add").unbind("click").click(function () {
                    /*
                     ��ִ�е�������ǰ�Ƚ�����ڸ�Ԫ���ϵĵ����¼�
                     ��ȡ#event_titleԪ�أ�������������ճ̱�ǩ����ֵ������e
                     ִ��o()���������Ѹ��ճ̱�ǩ���ӵ�#event_boxԪ�غ�
                     ����֮���⼸�д����������������ı����ճ̱�ǩ
                     */
                    var e = $("#event_title").val();
                    o(e)
                }), $("#event_box").html(""),$("#calendar").fullCalendar("destroy"),
                    /*
                     * ������
                     * */
                    $("#calendar").fullCalendar({
                        //��ǰ��r������Ϊ�ճ̱������ݣ���������ѡ��
                        header: r,
                        defaultView: "month",
                        slotMinutes: 15,
                        editable: !0,
                        droppable: !0,
                        weekMode: 'liquid',
                        events: "getJsonData",
                        //�϶�
                        drop: function (e, t,ui) {
                            var a = $(this).data("eventObject"), n = $.extend({}, a);
                            n.start = e, n.allDay = t, n.className = $(this).attr("data-class"), $("#calendar").fullCalendar("renderEvent", n, !0), $("#drop-remove").is(":checked") && $(this).remove();
                            alert(moment(e).format('YYYY-MM-DD'));
                            console.warn(ui);//��ӡ������е���
                            console.log(JSON.stringify(e));//���л�����
                        },
                        dayClick:function(date,allDay,jsEvent,view){
                            var selDate = date.format("YYYY-MM-DD HH:mm");
                            $('#start').val(selDate);
                            $('#end').val(moment(selDate).add(1,'d').format("YYYY-MM-DD HH:mm"));
                            layer.open({
                                type: 1,
                                title: '�ճ̹滮',
                                move:'.layui-layer-title',
                                shift:5,
                                closeBtn: 1,
                                btn:['�ύ','ȡ��'],
                                area:['80%','100%'],
                                skin: 'layui-layer-lan',
                                shadeClose: true,
                                content: $('#modal'),
                                yes:function(index,layero){
                                    $("#newCalendar").validate();
                                    $.ajax({
                                        type:"post",
                                        url:"add",
                                        data:$('#newCalendar').serialize(),
                                        success:function(r,s,x){
                                            if (s==="success"){
                                                if (r===true){
                                                    $("#calendar").fullCalendar("refetchEvents");
                                                    layer.close(index);
                                                }else{
                                                    layer.msg(r);
                                                }
                                            }else{
                                                layer.alert(s);
                                                layer.close(index);
                                            }
                                        }
                                    })
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
                            alert(moment(evt.start).format("YYYY-MM-DD"));
                            alert(evt.id);
                            alert(moment(moment(evt.start).add(delta)).format("YYYY-MM-DD"));
                            console.warn(evt);
                        },
                        eventClick:function(event,jsEvent,view){

                            var t = event.title;
                            var s = moment(event.start).format("YYYY-MM-DD");
                            var e = moment(event.end).format("YYYY-MM-DD");
                            $("#title").val(t);
                            $("#start").val(s);
                            $("#end").val(e);
                            var evtData = {
                                id:event.id,
                                title:$("#title").val(),
                                contents:$("contents").val(),
                                start:$("#start").val(),
                                end:$("#end").val(),
                                color:$("#color").val()
                            };
                            layer.open({
                                type: 1,
                                title: '�ճ̱༭',
                                move:'.layui-layer-title',
                                shift:5,
                                closeBtn: 1,
                                btn:['�ύ','ȡ��','ɾ��'],
                                area:['80%','100%'],
                                skin: 'layui-layer-lan',
                                shadeClose: true,
                                content: $('#modal'),
                                yes:function(index,layero){
                                    $("#newCalendar").validate();
                                    $.ajax({
                                        type:"post",
                                        url:"edit",
                                        data:evtData,
                                        success:function(r,s,x){
                                            if (s==="success"){
                                                if (r===true){
                                                    $("#Calendar").fullCalendar("refetchEvents");
                                                    layer.close(index);
                                                }else{
                                                    layer.msg(r);
                                                }
                                            }else{
                                                layer.alert(s);
                                                layer.close(index);
                                            }
                                        }
                                    })
                                },
                                btn3:function(){
                                    $.ajax({
                                        type:"post",
                                        url:"delete/id/"+ id,
                                        success:function(r,s,x){
                                            if (s==="success"){
                                                if (r===true){
                                                    $("#Calendar").fullCalendar("refetchEvents");
                                                    layer.closeAll('page');
                                                }else{
                                                    layer.msg(r);
                                                }
                                            }else{
                                                layer.alert(s);
                                                layer.closeAll('page');
                                            }
                                        }
                                    })
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
        min: laydate.now(), //�趨��С����Ϊ��ǰ����
        max: '2099-06-16 23:59:59', //�������
        istime: true,
        istoday: false,
        choose: function(datas){
            end.min = datas; //��ʼ��ѡ�ú����ý����յ���С����
            end.start = datas //�������յĳ�ʼֵ�趨Ϊ��ʼ��
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
            start.max = datas; //������ѡ�ú����ÿ�ʼ�յ��������
        }
    };
    laydate(start);
    laydate(end);

});