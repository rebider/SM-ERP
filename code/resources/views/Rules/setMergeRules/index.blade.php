@extends('layouts/new_main')
@section('content')
{{--    @include('layouts/shortcutMenus')--}}
    <form action="" class="layui-form">
        {{csrf_field()}}
        <div class="kbmodel_full ">
            <div class="content-wrapper">
                <!-- 添加币种 -->
                <div class=" layui-form" id="addtotext">
                    <div class="produpage">
                        <table class="layui-table" style="width:100%;" lay-even="" lay-skin="nob" >
                            <thead>
                            <tr>
                                <th style="width: 200px;">合并订单规则</th>
                            </tr>
                            </thead>
                            <tbody>
                                <tr style="background-color: white;">
                                    @foreach($platform as $k => $v)
                                    <td style="width: 200px;display:inline-block"> {{$v->name_EN}}&nbsp;&nbsp;<input type="checkbox" id="oneCheck" value="{{$v->id}}"   name="demo" lay-filter="oneChoose" lay-skin="switch"  /></td>
                                    @endforeach
                                </tr>
                            </tbody>
                            <div class="layui-form-item layui-hide">
                                <input type="button" class="layui-btn" data-type="getCheckData" value="确认">
                            </div>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection
@section('javascripts')
    <script>
        layui.use(['form', 'laydate','table','element','upload'], function(){
            var layer = layui.layer, form = layui.form, laypage = layui.laypage, laydate = layui.laydate;
            var element = layui.element;
            var $ = layui.jquery;
            upload = layui.upload;

            var table = layui.table;
            //监听表格复选框选择
            table.on('checkbox(demo)', function(obj){
                console.log(obj)
            });


            $('.demoTable .layui-btn').on('click', function(){
                var type = $(this).data('type');
                active[type] ? active[type].call(this) : '';
            });

            $('.demoTable .layui-btn').on('click', function(){
                var type = $(this).data('type');
                active[type] ? active[type].call(this) : '';
            });

            form.on('switch(oneChoose)', function (data) {
                if(this.checked){
                    var  button = 1;
                }else{
                    var button = 2;
                }

                $.ajax({
                    url:'/rules/setMergeOrder/addSetMergeRules',
                    type:'post',
                    data: {
                        '_token':"{{csrf_token()}}" ,
                        'checked': button ,
                        'value':data.value
                    },
                    dataType:'json',
                    success:function(data){
                        if(data.code == 200){
                            layer.msg(data.msg);
                        }else {
                            layer.msg(data.msg);
                        }
                    },
                });
            });

            // checkbox all
            form.on('submit(LAY-front-submit)', function (data) {
                var field = data.field; //获取提交的字段
                var index = parent.layer.getFrameIndex(window.name); //先得到当前iframe层的索引
                $.ajax({
                    type: 'post',
                    {{--url: '',{{url('SettingLogistics/addSmLogistics')}}--}}
                    data: field,
                    dataType: 'json',
                    success: function (data) {
                        if (data.code == 1) {
                            parent.layer.msg(data.msg, {icon: 1});
                            parent.layer.close(index); //再执行关闭
                        } else {
                            parent.layer.msg(data.msg, {icon: 5});
                        }
                    }
                });
            });

            //添加币种
            $(document).on('click','#addto',function(){
                layer.open({
                    type: 1,
                    title: '添加币种',
                    area: ['600px', '800px'],
                    content: $('#addtotext'),
                    btn: ['确定', '取消'],
                    yes: function(){
                    }
                });
            });

            //添加币种
            $(document).on('click','#oneCheck',function(){
                alert(1);
                /*layer.open({
                    type: 1,
                    title: '添加币种',
                    area: ['600px', '800px'],
                    content: $('#addtotext'),
                    btn: ['确定', '返回'],
                    yes: function(){
                    }
                });*/
            });

        });
    </script>
@endsection