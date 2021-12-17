@extends('layouts.new_main')
@section('content')
    <style>.kbmodel_full .content-wrapper{margin: 35px !important;}</style>
    <div class="content-wrapper">
        <form class="multiSearch layui-form">
            <div class="flexSearch flexquar fclear">
                <div class="search">
                    <button class="layui-btn layuiadmin-btn-rules" data-type="add">新建公告</button>
                </div>
            </div>
        </form>
        <div class="edn-row table_index">
            <table class="" id="EDtable" lay-filter="EDtable"></table>
            <script type="text/html" id="table-announcement-edit">
                <a class="layui-btn layui-btn-normal layui-btn-xs" lay-event="edit"><i class="layui-icon layui-icon-edit"></i>编辑</a>
                <a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="del"><i class="layui-icon layui-icon-close"></i>删除</a>
            </script>
        </div>
    </div>

@endsection

@section('javascripts')

    <script>
        //layui加载
        layui.config({ base: '../../layui/lay/modules/'}).extend({ formSelects: 'formSelects-v3'});
        layui.use(['layer','form','element','laydate','table'], function(){
            var layer = layui.layer,form = layui.form,table = layui.table,element = layui.element;
            var active = {
            add: function () {
                    layer.open({
                        type: 2
                        ,title: '创建公告'
                        ,content: '{{ route('base_info.announcement.editIndex') }}'
                        ,area: ['800px', '650px']
                        ,btn: ['确定', '取消']
                        ,maxmin: true
                        ,yes: function(index, layero){
                            //点击确认触发 iframe 内容中的按钮提交
                            var submit = layero.find('iframe').contents().find("#LAY-front-submit");
                            submit.click();
                        }
                    });
                }
            };

            $('.layuiadmin-btn-rules').on('click', function(e){
                e.preventDefault();
                var type = $(this).data('type');
                active[type] ? active[type].call(this) : '';
            });

            table.on('tool(EDtable)', function(obj){
                var _data = obj.data;
                switch (obj.event) {
                    case'edit':
                        layer.open({
                            type: 2
                            ,title: '修改公告'
                            ,content: '{{route('base_info.announcement.editIndex')}}'+'?id='+_data.id
                            ,area: ['800px', '650px']
                            ,btn: ['确定', '取消']
                            ,maxmin: true
                            ,yes: function(index, layero){
                                //点击确认触发 iframe 内容中的按钮提交
                                var submit = layero.find('iframe').contents().find("#LAY-front-submit");
                                submit.click();
                            }
                        });
                        break;
                    case'del':
                        layer.confirm("确定删除？", function(e) {
                            $.ajax({
                                type:'delete',
                                url:'{{ route('base_info.announcement.del') }}',
                                data:{id:_data.id},
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                },
                                dataType:'json',
                                success:function(data){
                                    if(data.code){
                                        layer.msg(data.msg);
                                        table.reload('EDtable');
                                    }else{
                                        layer.msg(data.msg);
                                    }
                                }
                            });
                        })
                }

            });

            form.on('submit(searBtn)', function(data){

                var index = layer.msg('数据请求中', {icon: 16});
                var info = data.field;
                table.render({
                    elem: '#EDtable'
                    ,url:'{{ route('base_info.announcement.lists') }}'
                    ,where:{data:info}
                    ,cols: [[
                        {field: '', title: 'NO',width:90, type:'numbers'},
                        {field:'title', title:'公告标题', style:'cursor: pointer;',templet: function(d){
                                return d.facilitator;
                            }}
                        ,{field:'created_at', title:'发布时间', templet: function (d) {
                                return d.created_at;
                            }}
                        ,{field:'created_man', title:'操作人', templet: function (d) {
                                return d.created_man;
                            }}
                        ,{field:'logistics_number', title:'操作',toolbar:"#table-announcement-edit"}
                    ]]
                    ,limit:20
                    ,page: true
                    ,limits:[20,30,40,50]
                    ,done:function () {   //返回数据执行回调函数
                        layer.close(index);    //返回数据关闭loading
                    }
                });
                return false;
            });

            table.render({
                elem: '#EDtable'
                ,url: '{{ route('base_info.announcement.lists') }}'
                ,cols: [[
                    {field: '', title: 'NO',width:90, type:'numbers'},
                    {field:'title', title:'公告标题', style:'cursor: pointer;',templet: function(d){
                            return d.title;
                        }}
                    ,{field:'created_at', title:'发布时间', templet: function (d) {
                            return d.created_at;
                        }}
                    ,{field:'created_man', title:'操作人', templet: function (d) {
                            return d.users.username;
                        }}
                    ,{field:'logistics_number', title:'操作',toolbar:"#table-announcement-edit"}
                ]]
                ,limit:20
                ,page: true
                ,limits:[20,30,40,50]
                ,done:function () {   //返回数据执行回调函数
                    layer.close(index);    //返回数据关闭loading
                }
            });

        });


        $("body").bind("keydown",function(event){
            if (event.keyCode == 116) {
                event.preventDefault(); //阻止默认刷新
                location=location;
            }
        })

    </script>
@endsection