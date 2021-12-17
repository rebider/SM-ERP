@extends('layouts/new_main')
@section('head')
    <style>
        .layui-table-cell{
            height:auto !important;
        }
        .layui-layer-btn {
            text-align: center!important;
        }
    </style>
@endsection
@section('content')
    <div class="content-wrapper">

        <form class="multiSearch layui-form">
                <!--发货状态-->
                <div class="second">
                    <div>
                        <div class="inputTxt">店铺名称：</div>
                        <div class="inputBlock">
                            <input type="text" name="shop_name" placeholder="店铺名称" autocomplete="off" style="width:438px;height:38px;padding-left:10px;">
                        </div>
                    </div>
                    <div >
                        <div class="inputTxt">来源平台：</div>
                        <div class="inputBlock">
                            <select  name="source_plat">
                                <option value="">全部</option>
                                @foreach($plats as $k=>$v)
                                    <option value="{{$v->id}}">{{$v->name_EN}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div >
                        <div class="inputTxt">店铺类型：</div>
                        <div class="inputBlock">
                            <select  name="shop_type">
                                <option value="">全部</option>
                                <option value="1">亚马逊</option>
                                <option value="2">乐天</option>
                                <option value="3">自定义</option>
                            </select>
                        </div>
                    </div>
                </div>
            <div class="search">
                <button class="layui-btn layui-btn-danger" lay-submit="" lay-filter="searBtn">搜索</button>
                <button class="layui-btn  layuiadmin-btn-rules WMGnewAmazon" >添加Amazon店铺</button>
                <button class="layui-btn  layuiadmin-btn-rules WMGnewLotle" >添加乐天店铺</button>
                <button class="layui-btn  layuiadmin-btn-rules WMGnew" data-type="addcu">添加自定义店铺</button>
                <button class="layui-btn layui-btn-primary" lay-submit="" lay-filter="reset">重置</button>
            </div>

        </form>
        <div class="edn-row table_index">
            <table class="" id="EDtable" lay-filter="EDtable"></table>
        </div>
    </div>

@endsection

@section('javascripts')
    <script type="text/html" id="barDemo">
        @{{#  if(d.shop_type === 3){ }}
        <a class="layui-table-link" href="javascript:void(0)" lay-event="del">删除</a>
        {{--<a class="layui-table-link" href="javascript:void(0)" lay-event="edit">编辑</a>--}}
        <a class="layui-table-link" href="javascript:void(0)" lay-event="check" >查看</a>
        @{{#  } }}
        @{{#  if(d.shop_type == 1){ }}
        <a class="layui-table-link" href="javascript:void(0)" lay-event="edit_amazon">编辑</a>
        @{{#  } }}
        @{{#  if(d.shop_type == 2){ }}
        <a class="layui-table-link" href="javascript:void(0)" lay-event="edit_lotel">编辑</a>
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

            $('.layuiadmin-btn-rules').on('click', function(e){
                e.preventDefault();
//                var type = $(this).data('type');
//                active[type] ? active[type].call(this) : '';
            });

            form.on('submit(reset)', function(data){
                window.location.reload(true);
                return false;
            });
            form.on('submit(searBtn)', function(data){
                var index = layer.msg('数据请求中', {icon: 16});
                var info = data.field;
                table.render({
                    elem: '#EDtable'
                    ,url:'/shopManage/ajaxGetSettingShopData'
                    ,where:{data:info}
                    ,cols: [[
                        {field: '', title: '序号', type:'numbers'},
                        {field:'shop_name', title:'店铺名称', event: 'getOrderDetails',
                            style:'cursor: pointer;',templet: function(d){
                                return d.shop_name + '<br><span style="color:red;position: relative;top: 1px;">'+d.remark+'</span>';
                            }}
                        ,{field:'plat_id', title:'来源平台', templet: function (d) {
                                switch (d.plat_id) {
                                    case 1:
                                        return 'Amazon';
                                        break;
                                    case 2:
                                        return 'Rakuten';
                                        break;
                                    case 3:
                                        return 'Other';
                                        break;
                                    default:
                                        return '未知平台'
                                }
                            }}
                        ,{field:'shop_type', title:'店铺类型', templet: function (d) {
                                switch (d.shop_type) {
                                    case 1:
                                        return '亚马逊';
                                        break;
                                    case 2:
                                        return '乐天';
                                    case 3:
                                        return '自定义';
                                }

                            }}
                        ,{field:'created_at',title:'创建时间'}
                        ,{field:'updated_at',title:'更新时间'}
                        ,{toolbar: '#barDemo',title:'操作'}
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
                var shop_type = _data.shop_type;
                switch (obj.event) {
                    case'check':
                        layer.open({
                            type:2,
                            title: ' 店铺详情',
                            fix: false,
                            maxmin: false,
                            shadeClose: true,
                            area: ['1100px', '400px'],
                            content: '{{ url('shopManage/checkDefinedShop') }}' + '?id=' + id +'&shop_tyle=' + shop_type,
                            end: function(index){
                                layer.close(index);
                            }
                        });
                        break;
                    case'edit':
                        layer.open({
                            title:"编辑",
                            type: 2,
                            area:["500px","450px"],
                            content: '/shopManage/addDefinedShop/?id='+id,
                            btn: ['保存', '取消'],
                            btn1: function(index, layero){
                                var info = window["layui-layer-iframe" + index].callbackdata();
                                $.ajax({
                                    url:'/shopManage/addDefinedShop',
                                    type:'post',
                                    data: {
                                        '_token':"{{csrf_token()}}" ,
                                        'shop_name': info.shop_name ,
                                        'plat_id' : info.source_plat,
                                        'id' : id
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
                                data: {id: _data.id, '_token':"{{csrf_token()}}"},
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
                    case 'edit_lotel' :
                        layer.open({
                            title:"编辑",
                            type: 2,
                            area:["850px","520px"],
                            content: ['/shopManage/addLotteShop/?id='+id,'no'],
                            btn: ['保存', '取消'],
                            btn1: function(index, layero){
                                var info = window["layui-layer-iframe" + index].callbackdata();

                                exception_mes = [];
                                if(info.shop_name == ''){
                                    layer.msg('店铺名称为必填项', {icon: 5});
                                    return false;
                                }
                                if(info.user_name == ''){
                                    layer.msg('用户名为必填项', {icon: 5});
                                    return false;
                                }
                                if(info.shop_url == 'all' || info.shop_url == ''){
                                    layer.msg('店铺url为必填项', {icon: 5});
                                    return false;
                                }
                                if(info.secret == ''){
                                    layer.msg('serviceSecret为必填项', {icon: 5});
                                    return false;
                                }


                                if(info.user_key == ''){
                                    layer.msg('LICENSEKEY格式错误', {icon: 5});
                                    return false;
                                }

                                /*if(!info.user_key.match(/^[0-9a-zA-Z]*$/)){
                                    layer.msg('LICENSEKEY格式错误', {icon: 5});
                                    return;
                                }*/

                                $.ajax({
                                    url:'/shopManage/addLotteShop',
                                    type:'post',
                                    data: {
                                        '_token':"{{csrf_token()}}" ,
                                        'shop_name': info.shop_name ,
                                        'user_name': info.user_name,
                                        'shop_url': info.shop_url,
                                        'secret': info.secret,
                                        'user_key': info.user_key,
                                        'id' : id,
                                        'ftp_pass' : info.ftp_pass,
                                        'ftp_user' : info.ftp_user
                                    },
                                    dataType:'json',
                                    success:function(data){
                                        if(data.code == 200){
                                            layer.msg(data.msg);
                                            layer.close(index);
                                            table.reload('EDtable');
                                        }else {
                                            layer.msg(data.msg);
                                        }
                                    },
                                });

                            }
                        });
                        break;
                    case 'del_amazon':
                        layer.open({
                            title:"删除",
                            type: 2,
                            area:["500px","450px"],
                            content: '/shopManage/addAmazonShop/?id='+id,
                            btn: ['确定', '取消'],
                            btn1: function(index, layero){
                                var info = window["layui-layer-iframe" + index].callbackdata();
                                $.ajax({
                                    url:'/shopManage/addDefinedShop',
                                    type:'post',
                                    data: {
                                        '_token':"{{csrf_token()}}" ,
                                        'shop_name': info.shop_name ,
                                        'plat_id' : info.source_plat,
                                        'id' : id
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
                    case 'edit_amazon':
                        layer.open({
                            title:"编辑",
                            type: 2,
                            area:["610px","510px"],
                            content: ['/shopManage/addAmazonShop?id='+id,'no'],
                            btn: ['保存', '取消'],
                            btn1: function(index, layero){
                                var info = window["layui-layer-iframe" + index].callbackdata();
                                if(info.shop_name == ''){
                                    layer.msg('店铺名称为必填项', {icon: 5});
                                    return;
                                }
                                if(info.amazon_accout == ''){
                                    layer.msg('amazon账号为必填项', {icon: 5});
                                    return;
                                }
                                if(info.open_state == ''){
                                    layer.msg('开户站为必填项', {icon: 5});
                                    return;
                                }

                                if(info.secret == ''){
                                    layer.msg('Secret Key 为必填项', {icon: 5});
                                    return;
                                }

                                if(info.user_key == ''){
                                    layer.msg('AWSAccessKeyId 为必填项', {icon: 5});
                                    return;
                                }
                                if(info.seller_id == ''){
                                    layer.msg('Amazon卖家编号 为必填项', {icon: 5});
                                    return;
                                }
//                                if(info.shop_name == ''){
//                                    layer.msg('店铺名称为必填项', {icon: 5});
//                                    return;
//                                }
//                                if(info.amazon_accout == ''){
//                                    layer.msg('amazon账号为必填项', {icon: 5});
//                                    return;
//                                }
//                                if(!info.amazon_accout.match(/^[0-9a-zA-Z]*$/)){
//                                    layer.msg('amazon账号格式错误', {icon: 5});
//                                    return;
//                                }
//                                if(info.open_state == ''){
//                                    layer.msg('开户站为必填项', {icon: 5});
//                                    return;
//                                }
//
//                                if(info.secret == ''){
//                                    layer.msg('Merchant ID为必填项', {icon: 5});
//                                    return;
//                                }
//                                if(!info.secret.match(/^[0-9a-zA-Z]*$/)){
//                                    layer.msg('Merchant ID格式错误', {icon: 5});
//                                    return;
//                                }
//
//                                if(info.user_key == ''){
//                                    layer.msg('MWSAuth Token为必填项', {icon: 5});
//                                    return;
//                                }
//                                if(!info.user_key.match(/^[0-9a-zA-Z]*$/)){
//                                    layer.msg('MWSAuth Token格式错误');
//                                    return;
//                                }
                                $.ajax({
                                    url:'/shopManage/addAmazonShop',
                                    type:'post',
                                    data: {
                                        '_token':"{{csrf_token()}}" ,
                                        'shop_name': info.shop_name ,
                                        'amazon_accout' : info.amazon_accout,
                                        'open_state' : info.open_state,
                                        'user_key' : info.user_key,
                                        'secret' : info.secret,
                                        'seller_id' : info.seller_id,
                                        'id' : id
                                    },
                                    dataType:'json',
                                    success:function(data){
                                        if(data.code == 200){
                                            layer.msg(data.msg);
                                            layer.close(index);
                                            table.reload('EDtable');
                                        }else {
                                            layer.msg(data.msg);
                                        }
                                    },
                                });

                            }
                        });
                }

            });

            table.render({
                elem: '#EDtable'
                ,url: '/shopManage/ajaxGetSettingShopData'
                ,cols: [[
                    {field: '', title: '序号', type:'numbers'},
                    {field:'shop_name', title:'店铺名称', event: 'getOrderDetails',
                        style:'cursor: pointer;',templet: function(d){
                            return d.shop_name + '<br><span style="color:red;position: relative;top: 1px;">'+d.remark+'</span>';
                        }}
                    ,{field:'plat_id', title:'来源平台', templet: function (d) {
                            switch (d.plat_id) {
                                case 1:
                                    return 'Amazon';
                                    break;
                                case 2:
                                    return 'Rakuten';
                                    break;
                                case 3:
                                    return 'Other';
                                    break;
                                default:
                                    return '未知平台'
                            }
                        }}
                    ,{field:'shop_type', title:'店铺类型', templet: function (d) {
                            switch (d.shop_type) {
                                case 1:
                                    return '亚马逊';
                                    break;
                                case 2:
                                    return '乐天';
                                case 3:
                                    return '自定义';
                            }

                        }}
                    ,{field:'created_at', title:'创建时间'}
                    ,{field:'updated_at', title:'更新时间'}
                    ,{toolbar: '#barDemo',title:'操作'}
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
                    title:"添加自定义店铺",
                    type: 2,
                    area:["500px","400px"],
                    content: '/shopManage/addDefinedShop',
                    btn: ['确定', '取消'],
                    btn1: function(index, layero){
                        var info = window["layui-layer-iframe" + index].callbackdata();
                        exception_mes = [];
                        if(info.shop_name == ''){
                            exception_mes.push('店铺名称为必填项')
                        }
                        if(info.source_plat == 'all' || info.source_plat == ''){
                            exception_mes.push('来源平台为必选项')
                        }
                        var alertMessage = '';
                        if(exception_mes.length > 0){
                            for (var i=0; i < exception_mes.length; i++) {
                                alertMessage += exception_mes[i] + '<br/>'
                            }
                            layer.msg(alertMessage, {icon: 5});
                            return false;
                        }
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
                                 }
                            },
                        });
                    },
                    btn2:function(){}
                });
            });

            $(document).on('click','.WMGnewLotle',function(){
                layer.open({
                    title:"添加乐天店铺",
                    type: 2,
                    area:["850px","520px"],
                    content: ['/shopManage/addLotteShop','no'],
                    btn: ['确定', '取消'],
                    btn1: function(index, layero){
                        var info = window["layui-layer-iframe" + index].callbackdata();
                        exception_mes = [];
                        if(info.shop_name == ''){
                            exception_mes.push('店铺名称为必填项')
                        }
                        if(info.user_name == ''){
                            exception_mes.push('用户名为必填项')
                        }
                        if(info.shop_url == 'all' || info.shop_url == ''){
                            exception_mes.push('店铺url为必填项')
                        }
                        if(info.secret == ''){
                            exception_mes.push('serviceSecret为必填项')
                        }

                        /*if(!info.secret.match(/^[0-9a-zA-Z]*$/)){
                            layer.msg('serviceSecret格式错误', {icon: 5});
                            return;
                        }*/

                        if(info.user_key == ''){
                            exception_mes.push('licenseKey为必填项')
                        }

                        /*if(!info.user_key.match(/^[0-9a-zA-Z]*$/)){
                            layer.msg('LICENSEKEY格式错误', {icon: 5});
                            return;
                        }*/

                        var alertMessage = '';
                        if(exception_mes.length > 0){
                            for (var i=0; i < exception_mes.length; i++) {
                                alertMessage += exception_mes[i] + '<br/>'
                            }
                            layer.msg(alertMessage, {icon: 5});
                            return false;
                        }

                        $.ajax({
                            url:'/shopManage/addLotteShop',
                            type:'post',
                            data: {
                                '_token': "{{csrf_token()}}" ,
                                'shop_name': info.shop_name ,
                                'user_name': info.user_name,
                                'shop_url' : info.shop_url,
                                'secret'   : info.secret,
                                'user_key' : info.user_key,
                                'ftp_pass' : info.ftp_pass,
                                'ftp_user' : info.ftp_user
                            },
                            dataType:'json',
                            success:function(data){
                                 if(data.code == 200){
                                     layer.msg(data.msg);
                                     layer.close(index);
                                     table.reload('EDtable');
                                 }else {
                                     layer.msg(data.msg);
                                 }
                            },
                        });

                    }
                });
            });

            //WMGnewAmazon
            $(document).on('click','.WMGnewAmazon',function(){
                layer.open({
                    title:"添加Amazon店铺",
                    type: 2,
                    area:["650px","560px"],
                    content: ['/shopManage/addAmazonShop','no'],
                    btn: ['确定', '取消'],
                    btn1: function(index, layero){
                        var info = window["layui-layer-iframe" + index].callbackdata();

                        if(info.shop_name == ''){
                            layer.msg('店铺名称为必填项', {icon: 5});
                            return;
                        }
                        if(info.amazon_accout == ''){
                            layer.msg('amazon账号为必填项', {icon: 5});
                            return;
                        }
                        if(info.open_state == ''){
                            layer.msg('开户站为必填项', {icon: 5});
                            return;
                        }

                        if(info.secret == ''){
                            layer.msg('Secret Key 为必填项', {icon: 5});
                            return;
                        }

                        if(info.user_key == ''){
                            layer.msg('AWSAccessKeyId 为必填项', {icon: 5});
                            return;
                        }
                        if(info.seller_id == ''){
                            layer.msg('Amazon卖家编号 为必填项', {icon: 5});
                            return;
                        }
                        // if(!info.user_key.match(/^[0-9a-zA-Z]*$/)){
                        //     layer.msg('LICENSEKEY格式错误');
                        //     return;
                        // }
                        $.ajax({
                            url:'/shopManage/addAmazonShop',
                            type:'post',
                            data: {
                                '_token':"{{csrf_token()}}" ,
                                'shop_name': info.shop_name ,
                                'amazon_accout' : info.amazon_accout,
                                'secret' : info.secret,
                                'user_key' : info.user_key,
                                'open_state' : info.open_state,
                                'seller_id' : info.seller_id
                            },
                            dataType:'json',
                            success:function(data){
                                 if(data.code == 200){
                                     // window.location.href='https://www.amazon.co.jp/ap/signin?_encoding=UTF8&ignoreAuthState=1&openid.assoc_handle=jpflex&openid.claimed_id=http%3A%2F%2Fspecs.openid.net%2Fauth%2F2.0%2Fidentifier_select&openid.identity=http%3A%2F%2Fspecs.openid.net%2Fauth%2F2.0%2Fidentifier_select&openid.mode=checkid_setup&openid.ns=http%3A%2F%2Fspecs.openid.net%2Fauth%2F2.0&openid.ns.pape=http%3A%2F%2Fspecs.openid.net%2Fextensions%2Fpape%2F1.0&openid.pape.max_auth_age=0&openid.return_to=https%3A%2F%2Fwww.amazon.co.jp%2F%3Fref_%3Dnav_signin&switch_account=';
                                     layer.msg(data.msg);
                                     layer.close(index);
                                     table.reload('EDtable');
                                 }else {
                                     layer.msg(data.msg);
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
        });
    </script>
@endsection