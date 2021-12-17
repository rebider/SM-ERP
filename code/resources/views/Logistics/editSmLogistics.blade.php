<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <title>添加</title>
    <meta name="description" content="">
    <meta name="keywords" content="">
    <link rel="stylesheet" href="{{asset('layui/layui.css')}}" media="all">
    <link href="{{asset('css/iframeCss.css')}}" rel="stylesheet">
    <script type="text/javascript" src="{{asset('js/jquery-1.11.3.min.js')}}"></script>
    <script type="text/javascript" src="{{asset('layui/layui.js')}}"></script>
</head>
<body>
<div class="openadvisory" style="width: 100%;">
    <form action="" class="layui-form">
        <ul class="">
            <li class="layui-form-item">
                <div class="layui-form-label"><b style="color:red;"></b>物流产品</div>
                <div class="layui-input-inline">
                    <div style="margin-top:4%;">{{$smLogicInfo['logistic_code']??''}}</div>
                    {{--<input type="text" style="border-color: white;" value="{{$smLogicInfo['logistic_code']??''}}" disabled name="logistic_code"   autocomplete="off" maxlength="30" class="layui-input logistic_code" />--}}
                </div>
            </li>
            <li class="layui-form-item">
                <div class="layui-form-label"><b style="color:red;"></b>物流名称</div>
                <div class="layui-input-inline">
                    <div style="margin-top: 4%;">{{$smLogicInfo['logistic_name']??''}}</div>
                    {{--<input type="text" style="border-color: white;" value="{{$smLogicInfo['logistic_name']??''}}" disabled name="logistic_name"   autocomplete="off" maxlength="30" class="layui-input logistic_name" />--}}
                </div>
            </li>
            <div class="layui-form-item">
                <div class="layui-form-label">绑定仓库</div>
                <div class="inputBlock">
                    <div class="layui-input-inline" style="border:1px solid rgb(230,230,230);width:80%;padding:5px 7px;">
                        @foreach($smLogicInfo['ware_house'] as $k=>$v)
                            <li>
                                <div style="position:relative; top:-6px;display: inline-block"><input type="checkbox" disabled name="warehouse_name" style="position: relative;top:-1px;" class="warehouse_name" value="{{$v['id']}}" lay-skin="primary"  /></div>
                                <span>{{$v['warehouse_name']}}</span>
                            </li>

                        @endforeach
                    </div>
                </div>
            </div>
            <li class="layui-form-item">
                <div class="layui-form-label" style="width:102px;"><b style="color:red;">*</b>是否启用</div>
                <div class="inputBlock" style="width:259px;margin-left:107px;">
                    <div class="multLable">
                        是 <input type="radio" name="disable" class="disable" @if(!isset($smLogicInfo['disable']) || empty($smLogicInfo['disable'])) checked @endif @if(isset($smLogicInfo['disable']) &&  $smLogicInfo['disable']== 1) checked @endif value="1">
                        否 <input type="radio" name="disable" class="disable" @if(isset($smLogicInfo['disable']) && $smLogicInfo['disable'] == 2) checked @endif value="2">
                    </div>
                </div>
            </li>

        </ul>
        <input type="hidden" value="{{$smLogicInfo['id']}}" class="logicId" name="id"  lay-skin="switch"  />
    </form>
</div>
<script>
    var callbackdata = function () {
        var warehouse_name = $('input[type=checkbox]:checked');
        var wh_str = '';
        $.each(warehouse_name,function(i,item){
            wh_str += $(this).val() + ',';
        });
        var disable = $('.disable:checked').val();
        var data = {
            logistic_name: $('.logistic_name').val(),
            warehouse_name: wh_str.substr(0,wh_str.length-1),
            disable: disable,
            logic_id:$('.logicId').val()
        };
        return data;
    };
    layui.use(['layer','form','element','upload'], function(){
        var layer = layui.layer,form = layui.form,upload = layui.upload,element = layui.element;
    });
</script>
</body>
</html>