layui.use(['layer','form','element','upload'], function(){
    var layer = layui.layer,form = layui.form,upload = layui.upload,element = layui.element;
});

window.showBig = function(src){ //图片预览
    var imgobj = new Image(); //创建新img对象
    imgobj.src = src; //指定数据源
    imgobj.className = 'thumb';
    img_prev.src = src;
    layer.open({
        title: '预览',
        type: 1,
        area: ["600px", "500px"],
        content: $('#prevModal')
    });
    return ;
} ;

//查看工单咨询
$('.look').click(function () {
    var ticketId = $(this).data('id');
    var consultType = $(this).data('consulttype');
    var title = consultType == 1 ? '仓库咨询' : '物流咨询';
    layer.open({
        title: title,
        type: 2,
        area: ["360px","400px"],
        content: '/AfterSale/look/'+ticketId+'/'+consultType,
    });
});
//确认归档
$('.confirmArchive').click(function () {
    var ticketId = $(this).data('id');
    var url = '/AfterSale/confirmArchive/'+ticketId;
    layer.open({
        title: '归档',
        type: 2,
        area:["520px","430px"],
        btn: ['确认','关闭'],
        content: url,
        yes: function (index, layero) {
            var myForm = layer.getChildFrame('#myForm');
            $.ajax({
                type: 'post',
                dataType: 'json',
                url: url,
                data: myForm.serialize(),
                success: function(response) {
                    if (response.Status) {
                        layer.msg(response.Message, {time:2000, icon: 1});
                        setTimeout(function () {
                            window.location.reload();
                        },1000);
                    } else {
                        var data = response.Data;
                        var alertMessage = response.Message;
                        if (data != null) {
                            for (var i=0; i < data.length; i++) {
                                alertMessage += data[i] + '<br/>'
                            }
                        }
                        layer.msg(alertMessage, {icon: 5});
                    }
                },
                error: function(e, x, d) {
                    layer.msg(d, {icon: 5})
                }
            });
        }
    });
});