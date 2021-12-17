@extends('layouts/new_main')
<style>
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
                    <div class="inputTxt">平台：</div>
                    <div class="inputBlock">
                        <div class="multLable">
                            <input type="radio" name="plat_id" value="" title="全部" checked>
                            @foreach($platforms as $platform)
                                <input type="radio" name="plat_id" value="{{$platform['id']}}" title="{{$platform['name_EN']}}" >
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="second">
                    <!--规则名称-->
                    <div >
                        <div class="inputTxt">物流名称：</div>
                        <div class="inputBlock">
                            <select  name="logistic_id">
                                <option value="" readonly="">请选择</option>
                                @foreach($logistics as $logistic)
                                    <option  value="{{$logistic['id']}}" >{{$logistic['logistic_name']}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <!--电商物流名称-->
                    <div>
                        <div class="inputTxt">电商物流名称：</div>
                        <div class="inputBlock">
                            <input type="text" name="plat_logistic_name"  placeholder="电商物流名称" autocomplete="off" style="width:438px;height:38px;padding:5px;">
                        </div>
                    </div>
                </div>

                <div class="search">
                    <button class="layui-btn layui-btn-danger" lay-submit="" lay-filter="searBtn">搜索</button>
                    <button class="layui-btn probRulesBtn layuiadmin-btn-rules addLogisticsMapping">添加物流映射</button>
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
        <a class="layui-table-link" href="javascript:void(0)" lay-event="mappingInfo" >查看</a>
        <a class="layui-table-link" href="javascript:void(0)" lay-event="editMapping">编辑</a>
        <a class="layui-table-link" href="javascript:void(0)" lay-event="delMapping">删除</a>
    </script>
    <script>
        //layui加载
        layui.config({ base: '../../layui/lay/modules/'}).extend({ formSelects: 'formSelects-v3'});
        layui.use(['layer','form','element','laydate','table','formSelects'], function(){
            var layer = layui.layer,form = layui.form,table = layui.table;
            var index = layer.msg('数据请求中', {icon: 16});


            $('.layuiadmin-btn-rules').on('click', function(e){
                e.preventDefault();
                var type = $(this).data('type');
                active[type] ? active[type].call(this) : '';
            });

            form.on('submit(searBtn)', function(data){
                var index = layer.msg('数据请求中', {icon: 16});
                var info = data.field;
                table.render({
                    elem: '#EDtable'
                    ,url:'{{ url('LogisticsMapping/search') }}'
                    ,where:{data:info}
                    ,cols: [[
                        {field: '', title: '序号', width:50, type:'numbers'},
                        {field:'plat_logistic_name', title:'电商物流名称', style:'cursor: pointer;',templet: function(d){
                            return d.plat_logistic_name;
                        }}
                        ,
                        {field:'carrier_name', title:'电商物流承运商', style:'cursor: pointer;',templet: function(d){
                            return d.carrier_name;
                        }}
                        ,
                        {field:'logistic_name', title:'系统物流名称', style:'cursor: pointer;',templet: function(d){
                                return d.logistic_name;
                            }}
                        ,{field:'plat_id', title:'电商平台', templet: function (d) {
                                return d.plat ? d.plat.name_EN : '';
                        }}
                        ,{field:'created_at',width:260, title:'创建时间'}
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


            table.render({
                elem: '#EDtable'
                ,url:'{{url('LogisticsMapping/search')}}'
                ,cols: [[
                    {field: '', title: '序号', width:50, type:'numbers'},
                    {field:'plat_logistic_name', title:'电商物流名称', style:'cursor: pointer;',templet: function(d){
                        return d.plat_logistic_name;
                    }}
                    ,
                    {field:'carrier_name', title:'电商物流承运商', style:'cursor: pointer;',templet: function(d){
                        return d.carrier_name;
                    }}
                    ,
                    {field:'logistic_name', title:'系统物流名称', style:'cursor: pointer;',templet: function(d){
                        return d.logistic_name;
                    }}
                    ,{field:'plat_id', title:'电商平台', templet: function (d) {
                        return d.plat ? d.plat.name_EN : '';
                    }}
                    ,{field:'created_at',width:260, title:'创建时间'}
                    ,{fixed: 'right', width: 200,toolbar: '#barDemo',title:'操作'}
                ]]
                ,limit:20
                ,page: true
                ,limits:[20,30,40,50]
                ,done:function () {   //返回数据执行回调函数
                    layer.close(index);    //返回数据关闭loading
                }
            });


            //添加物流映射
            $(document).on('click','.addLogisticsMapping',function(){
                layer.open({
                    title:"添加物流映射",
                    type: 2,
                    area:["600px","550px"],
                    content: ['/LogisticsMapping/add','no'],
                    btn: ['确定', '取消'],
                    btn1: function(index, layero){
                        var info = window["layui-layer-iframe" + index].callbackdata();
                        if (!info.plat_id || info.plat_id == '') {
                            layer.msg('平台为必选项', {icon: 5});
                            return false;
                        }
                        if (!info.logistic_id || info.logistic_id == '') {
                            layer.msg('系统物流为必选项', {icon: 5});
                            return false;
                        }
                        if (!info.plat_logistic_name || info.plat_logistic_name == '') {
                            layer.msg('电商物流名称为必填项', {icon: 5});
                            return false;
                        }
                        if (!info.carrier_name || info.carrier_name == '') {
                            layer.msg('电商物流承运商为必填项', {icon: 5});
                            return false;
                        }
                        $.ajax({
                            url:'/LogisticsMapping/add',
                            type:'post',
                            data: {
                                '_token':"{{csrf_token()}}" ,
                                'plat_logistic_name': info.plat_logistic_name ,
                                'carrier_name': info.carrier_name ,
                                'logistic_name': info.logistic_name ,
                                'logistic_id' : info.logistic_id,
                                'plat_id' : info.plat_id
                            },
                            dataType:'json',
                            success: function(response) {
                                if (response.Status) {
                                    layer.msg(response.Message, {time:2000, icon: 1});
                                    setTimeout(function () {
                                        location.reload();
                                    }, 2000)
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
                        });
                    }
                });
            });


            table.on('tool(EDtable)', function (obj) {
                var _data = obj.data,id= _data.id;
                switch (obj.event) {
                    case'mappingInfo':
                        layer.open({
                            type:2,
                            title: '查看物流映射详情',
                            fix: false,
                            maxmin: false,
                            shadeClose: true,
                            area: ['600px', '550px'],
                            content: ['{{ url('LogisticsMapping/edit') }}/'+ id + '?edit=0','no'],
                            end: function(index){
                                layer.close(index);
                            }
                        });
                        break;
                    case'editMapping':
                        layer.open({
                            title:"编辑物流映射详情",
                            type: 2,
                            area: ['600px', '550px'],
                            content: ['{{ url('LogisticsMapping/edit') }}/'+ id + '?edit=1','no'],
                            btn: ['保存', '取消'],
                            btn1: function(index, layero){
                                var info = window["layui-layer-iframe" + index].callbackdata();
                                if (!info.plat_id || info.plat_id == '') {
                                    layer.msg('平台为必选项', {icon: 5});
                                    return false;
                                }
                                if (!info.logistic_id || info.logistic_id == '') {
                                    layer.msg('系统物流为必选项', {icon: 5});
                                    return false;
                                }
                                if (!info.plat_logistic_name || info.plat_logistic_name == '') {
                                    layer.msg('电商物流名称为必填项', {icon: 5});
                                    return false;
                                }
                                if (!info.carrier_name || info.carrier_name == '') {
                                    layer.msg('电商物流承运商为必填项', {icon: 5});
                                    return false;
                                }
                                $.ajax({
                                    url:'{{ url('LogisticsMapping/edit') }}/'+ id + '?edit=1',
                                    type:'post',
                                    data: {
                                        '_token':"{{csrf_token()}}" ,
                                        'plat_logistic_name': info.plat_logistic_name ,
                                        'logistic_name': info.logistic_name ,
                                        'carrier_name': info.carrier_name ,
                                        'logistic_id' : info.logistic_id,
                                        'plat_id' : info.plat_id
                                    },
                                    dataType:'json',
                                    success: function(response) {
                                        if (response.Status) {
                                            layer.msg(response.Message, {time:2000, icon: 1});
                                            setTimeout(function () {
                                                location.reload();
                                            }, 2000)
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
                                });
                            }
                        });
                        break;
                    case'delMapping':
                        layer.confirm("确定删除？", function (e) {
                            $.ajax({
                                type: 'get',
                                url: '{{ url('LogisticsMapping/delete') }}/'+id,
                                success: function(response) {
                                    if (response.Status) {
                                        layer.msg(response.Message, {time:2000, icon: 1});
                                        setTimeout(function () {
                                            location.reload();
                                        }, 2000)
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

        });
        $("body").bind("keydown",function(event){
            if (event.keyCode == 116) {
                event.preventDefault(); //阻止默认刷新
                location=location;
            }
        })
    </script>
@endsection