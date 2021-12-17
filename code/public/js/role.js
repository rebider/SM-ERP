layui.use(['layer','form','element','laydate','table'], function(){
    var layer = layui.layer,form = layui.form,laydate = layui.laydate,table = layui.table,element = layui.element;

    //查询
    form.on('submit(searBtn)', function(data){
        getRole();
        return false;
    });

    //获取角色管理
    function getRole(){
        var roleName = $('.role_name').val();
        var index = layer.msg('数据请求中', {icon: 16});
        table.render({
            elem: '#EDtable'
            ,url:'/role/search'
            ,method: 'get'
            ,where: {role_name: roleName}
            ,cols: [[
                {field:'role_name', title:'角色'}
                ,{field:'remark', title:'备注'}
                ,{field:'state_name', title:'状态'}
                ,{field:'created_user_id', title:'创建人'}
                ,{field:'created_at', title:'创建时间', width:200}
                ,{field:'updated_user_id', title:'更新人'}
                ,{field:'updated_at', title:'更新时间', width:200}
                ,{fixed: 'right', title:'操作', toolbar: '#barDemo'}
            ]]
            ,page: true
            ,limit:20
            ,page: true
            ,limits:[20,30,40,50]
            ,done:function () {
                layer.close(index);
            }
        });
    };

    //初始化获取
    if ($('input[name="role_name"]').is('.role_name')) {
        getRole();
    }

    //新增角色
    $(document).on('click', '.roleNew', function(){
        roleAddOrEdit('新增', '/role/add');
    });

    //新增或编辑方法
    function roleAddOrEdit(title, url){
        layer.open({
            title: title,
            type: 2,
            area: ["660px","460px"],
            content: url,
            btn: ['确认','关闭'],
            yes: function (index, layero) {
                var myForm = layer.getChildFrame('#myForm');
                $.ajax({
                    type: 'post',
                    dataType: 'json',
                    url: '/role/store',
                    data: myForm.serialize(),
                    success: function(response) {
                        if (response.Status) {
                            layer.msg(response.Message, {time:2000, icon: 1});
                            setTimeout(function () {
                                layer.close(index);
                                getRole();
                            },1000);
                        } else {
                            data = response.Data;
                            alertMessage = response.Message;
                            for (var i=0; i < data.length; i++) {
                                alertMessage += data[i] + '<br/>'
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
    }

    //监听工具条
    table.on('tool(EDtable)', function(obj){
        var data = obj.data;
        if (obj.event === 'edit') { //编辑
            roleAddOrEdit('编辑', '/role/edit/'+data.role_id);
        }
        if (obj.event === 'rolePermissions') { //分配权限
            window.location.href = '/role/permissions/'+data.role_id;
        }
    });


});