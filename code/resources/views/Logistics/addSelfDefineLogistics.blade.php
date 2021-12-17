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
<div class="openadvisory" style="width: 400px">
    <form action="" class="layui-form">
        <ul class="">
            <li class="layui-form-item">
                <div class="layui-form-label" style="width:101px;"><b style="color:red;">*</b>物流名称</div>
                <div class="layui-input-inline">
                    <input type="text" value="{{$logisData->logistic_name??''}}" name="logistic_name"   autocomplete="off" maxlength="30" class="layui-input logistic_name" />
                </div>
            </li>
            <div class="layui-form-item">
                <div class="layui-form-label" style="width:101px;">绑定仓库</div>
                <div class="inputBlock">
                    <div class="layui-input-inline">
                        @foreach($wareHouse as $k=>$v)
                            <li>
                                <div style="position:relative;top:1px;display: inline-block;">
                                    <input type="checkbox" @if(in_array($v->id,$wareHouseArr)) checked @endif name="warehouse_name" class="warehouse_name" value="{{$v->id}}" lay-skin="primary"/>
                                </div>
                                <span style="position:relative;top:7px;display: inline-block">{{$v->warehouse_name}}</span>
                            </li>
                        @endforeach
                    </div>
                </div>
            </div>
            <li class="layui-form-item">
                <div class="layui-form-label" style="width:101px;"><b style="color:red;">*</b>是否启用</div>
                <div class="inputBlock" style="width:259px;margin-left:107px;">
                    <div class="multLable">
                        <select  name="disable" class="disable">
                            <option value="1" @if(isset($logisData->disable) &&  $logisData->disable== 1) selected @endif>启用</option>
                            <option value="2" @if(isset($logisData->disable) && $logisData->disable == 2) selected @endif>禁用</option>
                        </select>
                  {{--      是 <input type="radio" name="disable" class="disable" @if(!isset($logisData->disable) || empty($logisData->disable)) checked @endif @if(isset($logisData->disable) &&  $logisData->disable== 1) checked @endif value="1">
                        否 <input type="radio" name="disable" class="disable" @if(isset($logisData->disable) && $logisData->disable == 2) checked @endif value="2">--}}
                    </div>
                </div>
            </li>

        </ul>
    </form>
</div>
<script>
    var callbackdata = function () {
        var warehouse_name = $('input[type=checkbox]:checked');
        var wh_str = '';

        $.each(warehouse_name,function(i,item){
            wh_str += $(this).val() + ',';
        });
        var data = {
            logistic_name: $('.logistic_name').val(),
            warehouse_name: wh_str.substr(0,wh_str.length-1),
            disable: $('.disable').val()
        };
        return data;
    };
    layui.use(['layer','form','element','upload'], function(){
        var layer = layui.layer,form = layui.form,upload = layui.upload,element = layui.element;
    });
</script>
</body>
</html>