//layui加载
layui.config({ base: '../../layui/lay/modules/'}).extend({ formSelects: 'formSelects-v3'});
layui.use(['layer','form','element','laydate','table','formSelects'], function(){
    var layer = layui.layer,form = layui.form,laydate = layui.laydate,table = layui.table;
//            var index = layer.msg('数据请求中', {icon: 16});

    laydate.render({
        elem: '#EDdate',
        type: 'datetime',
    });

    laydate.render({
        elem: '#EDdate1',
        type: 'datetime',
    });

    $(document).on('click','.probRulesBtn',function(){
        var title = '添加订单问题规则';
        layer.open({
            type:2,
            title: title,
            fix: false,
            maxmin: false,
            shadeClose: true,
            area: ['1300px', '850px'],
            content: 'orderTroublesCreated',
            end: function(index){
            layer.close(index);
        }
    });

    });

    //1:查看 2:编辑
    window.trouble_handle = function ($param,$action) {
        var title = '订单问题规则查看';
        var data = '?edit=0';
        if ($action == 2) {
            var title = '订单问题规则编辑';
            var data = '?edit=1';
        }
        layer.open({
            type:2,
            title: title,
            fix: false,
            maxmin: false,
            shadeClose: true,
            area: ['1300px', '850px'],
            content: 'orderTroublesDetail/' + $param+data,
            end: function(index){
            layer.close(index);
        }
    });
    };
    window.trouble_del = function (id) {
        layer.confirm('确认删除', {title: '提示'}, function (index) {
            //ajax 删除采集商品
            $.ajax({
                url: 'orderTroublesDelete/' + id
                , type: "get"
                , success: function (res) {
                    if (res.Status) {
                        layer.msg('删除成功', {time:2000, icon: 1},function(){
                            window.location.reload();
                        });
                    } else {
                        layer.msg('删除失败', {icon: 6});
                    }
                }
            });
            layer.close(index);
        });
    }
    //查询
    form.on('submit(searBtn)', function(data){
        getTroubleList(data);
        return false;
    });

    function getTroubleList (data) {
        var data = data ? data : '';
        if (data == '') {
            var opening_status = $('input[name="opening_status"]:checked').val();
            var trouble_rules_name = $('input[name="trouble_rules_name"]').val();
            var start_date = $('input[name="start_date"]').val();
            var end_date = $('input[name="end_date"]').val();
            var params= new Object();
            params.opening_status=opening_status;
            params.trouble_rules_name=trouble_rules_name;
            params.start_date=start_date;
            params.end_date=end_date;
        } else {
            var params=data.field;
        }
        var index = layer.msg('数据请求中', {icon: 16});
        table.render({
            elem: '#EDtable'
            ,url:'orderTroublesSearch'
            ,where:{data:params}
            ,cols: [[
                {field: '', title: '序号', width:50, type:'numbers'},
                {field:'trouble_rules_name', width:150,title:'规则名称',templet: function(d){
                    return d.trouble_rules_name;
                }}
                ,{field:'trouble_rules_desc', width:150,title:'规则描述', templet: function (d) {
                    var str = '';
                    if (d.rules_trouble_condition) {
                        layui.each(d.rules_trouble_condition, function (index,item) {
                            str +='<div class="condName">'+item['cond_name']+'</div>';
                        })
                    }
                    return str;
                }}
                ,{field:'opening_status',title:'是否启用', templet: function (d) {
                    return d.opening_status == 1 ? '是' : '否';
                }}
                ,{field:'created_at', title:'创建时间'}
                ,{field:'updated_at', title:'更新时间'}
                ,{fixed: 'right',  title:'操作',templet: function(d){
                    //编辑 查看
                    // return '<a class="operating-btn layui-btn layui-btn-primary layui-btn-xs" href="javascript:void(0);" onclick="trouble_handle('+d.id+',1)"><i class="layui-icon">&#xe705;</i> 查看</a>&nbsp;'+
                    //     '<a class="operating-btn layui-btn layui-btn-normal layui-btn-xs" href="javascript:void(0);" onclick="trouble_handle('+d.id+',2)"><i class="layui-icon">&#xe642;</i>编辑</a>&nbsp;'+
                    //     '<a class="operating-btn layui-btn layui-btn-normal layui-btn-xs" href="javascript:void(0);" onclick="trouble_del('+d.id+')"><i class="layui-icon">&#xe642;</i>删除</a>';

                    return '<a class="layui-table-link" href="javascript:void(0);" onclick="trouble_handle('+d.id+',1)">查看</a>&nbsp;'+
                        '<a class="layui-table-link" href="javascript:void(0);" onclick="trouble_handle('+d.id+',2)">编辑</a>&nbsp;'+
                        '<a class="layui-table-link" href="javascript:void(0);" onclick="trouble_del('+d.id+')">删除</a>';

                }}
            ]]
            ,limit:20
            ,page: true
            ,limits:[20,30,40,50]
            ,done:function () {   //返回数据执行回调函数
                layer.close(index);    //返回数据关闭loading
            }
        });
    }

    //初始化获取数据
    if ($('input[name="trouble_rules_name"]').is('.voin')) {
        getTroubleList();
    }



    form.on('submit(reset)', function(data){
        window.location.reload();
        return false;
    });
});

$("body").bind("keydown",function(event){
    if (event.keyCode == 116) {
        event.preventDefault(); //阻止默认刷新
        location=location;
    }
})

