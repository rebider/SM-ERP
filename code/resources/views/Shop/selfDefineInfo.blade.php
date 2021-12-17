@extends('layouts/new_main')
@section('head')
    <style>
        .form-uldef .layui-inline {

            width: 24%;
            margin-right: 0;

        }
        .layui-breadcrumb a:hover {

            color: #666 !important;

        }
    </style>
@endsection
@section('content')
    <div class="sectionBody" style="width:800px;">
        <div class="location" style="width:800px;">
    		<span class="layui-breadcrumb">
			  <a >高级设置</a>
			  <a href="{{ '/AfterSale/customerComplaintManager/customerComplaint/' }}">店铺管理</a>
			  <a><cite>查看</cite></a>
			</span>
        </div>
        <div class="edn-row flexshrink" style="">
            <div class="EDbar formbox" style="">
                <div class="edTit" style="width:1000px;"><h3 class="le">店铺名称</h3>&nbsp;&nbsp;&nbsp;<div class="layui-input-inline"><p><span style="color:#999;" >{{$checkInfo->shop_name}}</span></p></div></div>
                <form action="" class="layui-form" style="width:800px;">
                    <ul class="form-uldef" style="width:1000px;height:140px;">
                        <li class="layui-inline">
                            <label class="layui-form-label">来源平台</label>
                            <div class="layui-input-inline">
                                <p>{{$checkInfo->plat->name_EN}}</p>
                            </div>
                        </li>
                        {{--<li class="layui-inline">
                            <label class="layui-form-label">授权状态</label>
                            <div class="layui-input-inline" id="check_status">
                                @if($checkInfo->status == 1)
                                    未授权
                                @elseif($checkInfo->status == 2)
                                    授权成功
                                @elseif($checkInfo->status == 3)
                                    授权失败
                                @elseif($checkInfo->status == 4)
                                    授权过期
                                @elseif($checkInfo->status == 5)
                                    无授权
                                @endif
                            </div>
                        </li>--}}
                        <li class="layui-inline">
                            <label class="layui-form-label">店铺类型</label>
                            <div class="layui-input-inline">
                                @if($checkInfo->shop_type == 1)
                                    亚马逊
                                @elseif($checkInfo->shop_type == 2)
                                    乐天
                                @elseif($checkInfo->shop_type ==3)
                                    自定义
                                @endif
                            </div>
                        </li>
                        <li class="layui-inline" style="width:260px;">
                            <label class="layui-form-label">创建时间</label>
                            <div class="layui-input-inline">
                                {{$checkInfo->created_at}}
                            </div>
                        </li>
                    </ul>
                </form>
            </div>

        </div>
    </div>

@endsection

@section('javascripts')
    <script>

        layui.use(['layer','form','element','upload'], function(){
            var layer = layui.layer,form = layui.form,upload = layui.upload,element = layui.element;

            form.on('submit(BatchSubmit)', function(data){
                return false;
            });

            layer.ready(function(){
                layer.photos({
                    photos: '#layer-photos-demo'
                    ,shift: 5
                });
            });

            form.on("$('#check_status')", function(data){
                console.log(data);
            });



        });

        window.showBig=function(src){ //图片预览
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

        $("body").bind("keydown",function(event){
            if (event.keyCode == 116) {
                event.preventDefault(); //阻止默认刷新
                location=location;
            }
        })

        $(function(){
            $(".Ellipsis").each(function (i) {
                var divH = $(this).height();
                var $p = $("*", $(this)).eq(0);
                while ($p.outerHeight() > divH) {
                    $p.text($p.text().replace(/(\s)*([a-zA-Z0-9]+|\W)(\.\.\.)?$/, "..."));
                };
            });

            $(document).on("click",".delIcon",function(){
                $(this).parent().remove();
            })

    /*        $(function(){
                alert($('#check_status').text());
                var status=  $('#check_status').text();
                switch(n)
                {
                    case 1:
                        return '';
                        break;
                    case 2:
                        执行代码块 2
                        break;
                    default:
                        与 case 1 和 case 2 不同时执行的代码
                }

            })
        });*/



    </script>
@endsection