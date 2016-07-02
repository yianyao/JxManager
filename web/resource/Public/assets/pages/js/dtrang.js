$(document).ready(function(){
    $('#Daterange').daterangepicker({
        "showDropdowns":true,
        "autoApply":true,
        "linkedCalendars":true,
        "opens":"center",
        "startDate":moment().subtract(60,'days'),
        "endDate":moment(),
        "locale":{
            format: 'YYYY-MM-DD',
            separator: ' - ',
            applyLabel: 'Apply',
            cancelLabel: 'Cancel',
            fromLabel: 'From',
            toLabel: 'To',
            customRangeLabel: '自定义',
            daysOfWeek: ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr','Sa'],
            monthNames: ['一月', '二月', '三月', '四月', '五月', '六月', '七月', '八月', '九月', '十月', '十一月', '十二月'],
            firstDay: 1
        },
        "ranges": {
            '今天': [moment(), moment()],
            '昨天': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
            '过去七天': [moment().subtract(6, 'days'), moment()],
            '本月': [moment().startOf('month'), moment().endOf('month')],
            '上月': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
            '上年': [moment().subtract(365, 'days'), moment()]
        }
    });
})