layui.use(['layer','form','element','laydate','table'], function(){
    var layer = layui.layer,form = layui.form,laydate = layui.laydate,table = layui.table,element = layui.element;

    form.on('checkbox(owner_all)', function(data){
        var a = data.elem.checked;
        var am = $(this).parents('.Authlimits');
        if (a==true) {
            am.find('.eType').prop("checked",true);
            form.render('checkbox');
        }else{
            am.find('.eType').prop("checked", false);
            form.render('checkbox');
        }
    });

    form.on('checkbox(owner_three)', function(data){
        var a = data.elem.checked;
        var am = $(this).parents('tr').nextUntil(".menu_child");
        if (a==true) {
            am.find('.eType').prop("checked",true);
            $(this).parents('.Authlimits').find('.ecom').prop("checked",true);
            form.render('checkbox');
        }else{
            am.find('.eType').prop("checked", false);
            form.render('checkbox');
        }
        if($(this).parents('.Authlimits').find('.eType:checked').length < 1){
            $('.Authlimits').find('.ecom').prop("checked",false);
            $('.Authlimits').find('.layui-unselect').removeClass('layui-form-checked');
        }
    });

    form.on('checkbox(owner_four)', function(data){
        var a = data.elem.checked;
        var am = $(this).parents('tr');
        console.log(am.prevAll('.menu_child:first').nextUntil('.menu_child').find(".Egrand:checked").length);
        if (a==true) {
            am.prevAll('.menu_child:first').find('.eType').prop("checked",true);
            $(this).parents('.Authlimits').find('.ecom').prop("checked",true);
            am.find('.tree_Features').find('.eType').prop("checked",true);
            form.render('checkbox');
        }else{
            am.find('.tree_Features').find('.eType').prop("checked", false);
            form.render('checkbox');
        }

        if(am.prevAll('.menu_child:first').nextUntil('.menu_child').find(".Egrand:checked").length == 0){
            am.prevAll('.menu_child:first').find('.Echild').prop("checked",false);
            am.prevAll('.menu_child:first').find('.layui-unselect').removeClass('layui-form-checked');
        }

        if($(this).parents('.Authlimits').find('.eType:checked').length < 1){
            $('.Authlimits').find('.ecom').prop("checked",false);
            $('.Authlimits').find('.layui-unselect').removeClass('layui-form-checked');
        }
    });

    form.on('checkbox(owner_end)', function(data){
        var a = data.elem.checked;
        var am = $(this).parents('tr');
        if (a==true) {
            am.find('.auth-grand').find('.eType').prop("checked",true);
            am.prevAll('.menu_child:first').find('.eType').prop("checked",true);
            $(this).parents('.Authlimits').find('.ecom').prop("checked",true);
            form.render('checkbox');
        }else if($(this).parents('.tree_Features').find('.eType:checked').length < 1){
            am.find('.auth-grand').find('.eType').prop("checked",false);
            form.render('checkbox');
        }
        if(am.prevAll('.menu_child:first').nextUntil('.menu_child').find(".Egrand:checked").length == 0){
            am.prevAll('.menu_child:first').find('.Echild').prop("checked",false);
            am.prevAll('.menu_child:first').find('.layui-unselect').removeClass('layui-form-checked');

        }

        if($(this).parents('.Authlimits').find('.eType:checked').length < 1){
            $('.Authlimits').find('.ecom').prop("checked",false);
            $('.Authlimits').find('.layui-unselect').removeClass('layui-form-checked');
        }
    })

    //保存分配权限
    $('.stroePermissions').click(function () {
        $.ajax({
            type: 'post',
            dataType: 'json',
            url: '/role/stroePermissions',
            data: $('#myForm').serialize(),
            success: function(response) {
                if (response.Status) {
                    layer.msg(response.Message, {time:2000, icon: 1});
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
    })
});