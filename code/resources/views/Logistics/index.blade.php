@extends('layouts/new_main')
<style type="text/css">
    .layui-layer-btn {
        text-align: center!important;
    }
</style>
@section('head')
@endsection

@section('content')
{{--    @include('layouts/shortcutMenus')--}}
    <div class="content-wrapper">
        <form class="multiSearch layui-form">
            <div class="flexSearch flexquar fclear">
                <!--启用状态-->
                <div class="frist">
                    <div class="inputTxt">是否启用：</div>
                    <div class="inputBlock">
                        <div class="multLable">
                            <input type="radio" name="disable" value="" title="全部" checked>
                            <input type="radio" name="disable" value="1" title="是" >
                            <input type="radio" name="disable" value="2" title="否" >
                        </div>
                    </div>
                </div>

                <div class="second">
                    <!--规则名称-->
                    <div>
                        <div class="inputTxt">物流名称：</div>
                        <div class="inputBlock">
                            <input type="text" name="logistic_name"  placeholder="物流名称" autocomplete="off" style="width:438px;height:38px;padding:5px;">
                        </div>
                    </div>

                    <!--规则名称-->
                    <div >
                        <div class="inputTxt">来源：</div>
                        <div class="inputBlock">
                            <select  name="logic_source">
                                <option value="">全部</option>
                                <option value="1">速贸物流</option>
                                <option value="2">自定义</option>
                            </select>
                        </div>
                    </div>

                    <!--规则名称-->
                    <div>
                        <div class="inputTxt">绑定仓库：</div>
                        <div class="inputBlock">
                            <select  name="logic_house">
                                <option value="">全部</option>
                                @if(!empty($wareHouse))
                                @foreach($wareHouse as $K=>$v)
                                    <option value="{{$v->id}}">{{$v->warehouse_name}}</option>
                                @endforeach
                                @endif
                            </select>
                        </div>
                    </div>
                </div>

                <div class="search">
                    <button class="layui-btn layui-btn-danger" lay-submit="" lay-filter="searBtn">搜索</button>
                    <button class="layui-btn  layuiadmin-btn-rules WMGnewSM" @if(empty($sm_warehouse)) disabled style="background-color: #A9A9A9;" @endif data-type="add">添加速贸物流</button>
                    <button class="layui-btn  layuiadmin-btn-rules WMGnewAmazon" data-type="add">添加自定义物流</button>
                    <button class="layui-btn layui-btn-primary" lay-submit="" lay-filter="reset" >重置</button>
                </div>
            </div>
        </form>

        <div class="edn-row table_index">
            <table class="" id="EDtable" lay-filter="EDtable"></table>
        </div>
    </div>

@endsection

@section('javascripts')
    <script type="text/html" id="barDemo">
        @{{#  if(d.source === 2){ }}
        <a class="layui-table-link" href="javascript:void(0)" lay-event="edit">编辑</a>
        <a class="layui-table-link" href="javascript:void(0)" lay-event="check" >查看</a>
        @{{#  } }}
        @{{#  if(d.source === 1){ }}
        <a class="layui-table-link" href="javascript:void(0)" lay-event="editSm">编辑</a>
        {{--<a class="layui-table-link" href="javascript:void(0)" lay-event="check" >查看</a>--}}
        @{{#  } }}
    </script>
    <script>
        //layui加载
        layui.config({ base: '../../layui/lay/modules/'}).extend({ formSelects: 'formSelects-v3'});
        layui.use(['layer','form','element','laydate','table','formSelects'], function(){
            var layer = layui.layer,form = layui.form,laydate = layui.laydate,table = layui.table,element = layui.element,formSelects = layui.formSelects;
            var index = layer.msg('数据请求中', {icon: 16});

            laydate.render({
                elem: '#EDdate'
            });
            laydate.render({
                elem: '#EDdate1'
            });

            form.on('submit(reset)', function(data){
                window.location.reload(true);
                return false;
            });

            var active = {
                addsm: function () {
                    layer.open({
                        type: 2
                        ,title: '仓库授权'
                        ,content: ''
                        ,area: ['500px', '400px']
                        ,btn: ['确定', '取消']
                        ,maxmin: true
                        ,yes: function(index, layero){
                            //点击确认触发 iframe 内容中的按钮提交
                            var submit = layero.find('iframe').contents().find("#LAY-front-submit");
                            submit.click();
                        }
                    });
                },
            };

            $('.layuiadmin-btn-rules').on('click', function(e){
                e.preventDefault();
            });

            form.on('submit(searBtn)', function(data){
                var index = layer.msg('数据请求中', {icon: 16});
                var info = data.field;
                table.render({
                    elem: '#EDtable'
                    ,url:'{{ url('SettingLogistics/ajaxGetLogistics') }}'
                    ,where:{data:info}
                    ,cols: [[
                        {field: '', title: '序号', width:50, type:'numbers'},
                        {field:'logistic_name', title:'物流方式名称',event: 'getOrderDetails', style:'cursor: pointer;',templet: function(d){
                                return d.logistic_name;
                            }}
                        ,{field:'source', title:'来源',width:261, templet: function (d) {
                                switch (d.source) {
                                    case 1:
                                        return '速贸物流';
                                        break;
                                    case 2:
                                        return '自定义';
                                        break;
                                }
                            }}
                        ,{field:'ware_house', width:194, title:'绑定仓库', templet: function (d) {
                                var str = '';
                                for(var i = 0; i < d.ware_house.length; i++){
                                    str +=d.ware_house[i].warehouse_name+',';
                                }
                                return str.substr(0,str.length-1);
                            }}
                        ,{field:'disable', width:194, title:'是否启用', templet: function (d) {
                                switch (d.disable) {
                                    case 1:
                                        return '是';
                                        break;
                                    case 2:
                                        return '否';
                                        break;
                                }

                            }}
                        ,{field:'created_at',width:260, title:'创建时间'}
                        ,{field:'updated_at',width:260, title:'更新时间'}
                        ,{fixed: 'right', width: 200,toolbar: '#barDemo',title:'操作'}
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

            table.on('tool(EDtable)', function (obj) {
                var _data = obj.data,id= _data.id;
                switch (obj.event) {
                    case'check':
                        layer.open({
                            type:2,
                            title: ' 物流详情',
                            fix: false,
                            maxmin: false,
                            shadeClose: true,
                            area: ['1100px', '300px'],
                            content: '{{ url('SettingLogistics/checkSelfDefinedLogistics') }}' + '?id=' + id,
                            end: function(index){
                                layer.close(index);
                            }
                        });
                        break;
                    case'edit':
                        layer.open({
                            title:"编辑",
                            type: 2,
                            area:["450px","360px"],
                            content: '/SettingLogistics/addSelfDefinedLogistics/?id='+id,
                            btn: ['保存', '取消'],
                            btn1: function(index, layero){
                                var info = window["layui-layer-iframe" + index].callbackdata();
                                if (!info.logistic_name || info.logistic_name == '') {
                                    layer.msg('物流名称为必填项', {icon: 5});
                                    return false;
                                }
                                $.ajax({
                                    url:'{{url('/SettingLogistics/addSelfDefinedLogistics/')}}?id='+id,
                                    type:'post',
                                    data: {
                                        '_token':"{{csrf_token()}}" ,
                                        'logistic_name': info.logistic_name ,
                                        'warehouse_name' : info.warehouse_name,
                                        'disable' : info.disable
                                    },
                                    dataType:'json',
                                    success:function(data){
                                        if(data.code == 200){
                                            layer.msg(data.msg);
                                            layer.close(index);
                                            table.reload('EDtable');
                                        }else {
                                            layer.msg(data.msg);
                                            layer.close(index);
                                            table.reload('EDtable');
                                        }
                                    },
                                });

                            }
                        });
                        break;
                    case'editSm':
                        layer.open({
                            title:"编辑",
                            type: 2,
                            area:["650px","450px"],
                            content: '/SettingLogistics/editSmLogistics/?id='+id,
                            btn:['保存','取消'],
                            btn1: function(index,layero){
                                var info = window["layui-layer-iframe" + index].callbackdata();
                                $.ajax({
                                    url:'{{url('/SettingLogistics/editSmLogistics/')}}',
                                    type:'post',
                                    data: {
                                        '_token':"{{csrf_token()}}" ,
                                        'id': info.logic_id,
                                        'disable':info.disable,
                                        'logistic_name':info.logistic_name
                                    },
                                    dataType:'json',
                                    success:function(data){
                                        if(data.code == 200){
                                            layer.msg(data.msg);
                                            layer.close(index);
                                            table.reload('EDtable');
                                        }else {
                                            layer.msg(data.msg);
                                            layer.close(index);
                                            table.reload('EDtable');
                                        }
                                    },
                                });

                            }
                        });
                        break;
                    case'del':
                        layer.confirm("确定删除？", function (e) {
                            $.ajax({
                                type: 'delete',
                                url: '{{ url('shopManage/deleteDefinedShop') }}',
                                data: {id: _data.id},
                                dataType: 'json',
                                success: function (data) {
                                    if (data.code == 200) {
                                        layer.msg(data.msg);
                                        table.reload('EDtable');
                                    } else {
                                        layer.msg(data.msg);
                                    }
                                }
                            });
                        });
                        break;
                    case 'auth':
                        window.location.href='https://www.amazon.co.jp';
                        break;
                    case 'authLotle':
                        window.location.href='https://www.rakuten.co.jp';
                }

            });

            table.render({
                elem: '#EDtable'
                ,url: '{{ url('SettingLogistics/ajaxGetLogistics') }}'
                ,cols: [[
                    {field: '', title: '序号', width:50, type:'numbers'},
                    {field:'logistic_name', title:'物流方式名称',event: 'getOrderDetails', style:'cursor: pointer;',templet: function(d){
                            return d.logistic_name;
                        }}
                    ,{field:'source', title:'来源',width:261, templet: function (d) {
                            switch (d.source) {
                                case 1:
                                    return '速贸物流';
                                    break;
                                case 2:
                                    return '自定义';
                                    break;
                            }
                        }}
                    ,{field:'ware_house', width:194, title:'绑定仓库', templet: function (d) {
                        var str = '';
                        for(var i = 0; i < d.ware_house.length; i++){
                            str +=d.ware_house[i].warehouse_name+',';
                        }
                        return str.substr(0,str.length-1);
                        }}
                    ,{field:'disable', width:194, title:'是否启用', templet: function (d) {
                            switch (d.disable) {
                                case 1:
                                    return '是';
                                    break;
                                case 2:
                                    return '否';
                                    break;
                            }

                        }}
                    ,{field:'created_at',width:260, title:'创建时间'}
                    ,{field:'updated_at',width:260, title:'更新时间'}
                    ,{fixed: 'right', width: 200,toolbar: '#barDemo',title:'操作'}
                ]]
                ,limit:20
                ,page: true
                ,limits:[20,30,40,50]
                ,done:function () {   //返回数据执行回调函数
                    layer.close(index);    //返回数据关闭loading
                }
            });

            $(document).on('click','.WMGnew',function(){
                layer.open({
                    title:"添加",
                    type: 2,
                    area:["450px","360px"],
                    content: '/shopManage/addDefinedShop',
                    btn: ['确定', '取消'],
                    btn1: function(index, layero){
                        var info = window["layui-layer-iframe" + index].callbackdata();
                        $.ajax({
                            url:'/shopManage/addDefinedShop',
                            type:'post',
                            data: {
                                '_token':"{{csrf_token()}}" ,
                                'shop_name': info.shop_name ,
                                // 'warehouse_name' : info.warehouse_name ,
                                'plat_id' : info.source_plat
                            },
                            dataType:'json',
                            success:function(data){
                                if(data.code == 200){
                                    layer.msg(data.msg);
                                    layer.close(index);
                                    table.reload('EDtable');
                                }else {
                                    layer.msg(data.msg);
                                    layer.close(index);
                                    table.reload('EDtable');
                                }
                            },
                        });
                    },
                    btn2:function(){}
                });
            });

            $(document).on('click','.WMGnewSM',function(){
                layer.open({
                    title:"添加速贸物流",
                    type: 2,
                    area:["660px","460px"],
                    content: '/SettingLogistics/addSmLogistics',
                    btn: ['确定', '取消'],
                    btn1: function(index, layero){
                        var submit = layero.find('iframe').contents().find("#LAY-front-submit");
                        submit.click();

                    }
                });
            });

            //WMGnewAmazon
            $(document).on('click','.WMGnewAmazon',function(){
                layer.open({
                    title:"添加自定义物流",
                    type: 2,
                    area:["450px","360px"],
                    content: '/SettingLogistics/addSelfDefinedLogistics',
                    btn: ['确定', '取消'],
                    btn1: function(index, layero){
                        var info = window["layui-layer-iframe" + index].callbackdata();
                        if (!info.logistic_name || info.logistic_name == '') {
                            layer.msg('物流名称为必填项', {icon: 5});
                            return false;
                        }
                        // if(!info.warehouse_name || info.warehouse_name == '') {
                        //     layer.msg('绑定仓库为必填项', {icon: 5});
                        //     return false;
                        // }
                        $.ajax({
                            url:'/SettingLogistics/addSelfDefinedLogistics',
                            type:'post',
                            data: {
                                '_token':"{{csrf_token()}}" ,
                                'logistic_name': info.logistic_name ,
                                'warehouse_name' : info.warehouse_name,
                                'disable' : info.disable
                            },
                            dataType:'json',
                            success:function(data){
                                if(data.code == 200){
                                    // window.location.href='https://www.amazon.co.jp/ap/signin?_encoding=UTF8&ignoreAuthState=1&openid.assoc_handle=jpflex&openid.claimed_id=http%3A%2F%2Fspecs.openid.net%2Fauth%2F2.0%2Fidentifier_select&openid.identity=http%3A%2F%2Fspecs.openid.net%2Fauth%2F2.0%2Fidentifier_select&openid.mode=checkid_setup&openid.ns=http%3A%2F%2Fspecs.openid.net%2Fauth%2F2.0&openid.ns.pape=http%3A%2F%2Fspecs.openid.net%2Fextensions%2Fpape%2F1.0&openid.pape.max_auth_age=0&openid.return_to=https%3A%2F%2Fwww.amazon.co.jp%2F%3Fref_%3Dnav_signin&switch_account=';
                                    layer.msg(data.msg);
                                    layer.close(index);
                                    table.reload('EDtable');
                                }else {
                                    layer.msg(data.msg, {icon: 5});
                                    return false;
                                }
                            },
                        });
                    }
                });
            });

            $(document).on('click','.delevent',function(){
                layer.confirm("确定删除？", function(e) {
                    $.ajax({
                        type:'delete',
                        url:'{{ url('shopManage/deleteDefinedShop') }}',
                        data:{id:_data.id},
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