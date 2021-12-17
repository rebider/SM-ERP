@extends('layouts/new_main')
@section('content')
    <div class="openadvisory" style="width: 610px; margin-top: 2rem">
        <form action="" class="layui-form">
            <ul class="">
                <li class="layui-form-item">
                    <div class="layui-form-label" style="margin-left:-6%;width:42%"><b style="color:red;">*</b> 店铺名称：</div>
                    <div class="layui-input-inline" style="width:259px;">
                        <input type="text" value="{{$shopInfo->shop_name ??''}}" placeholder="Amazon店铺名称" name="shop_name" lay-verify="required"  autocomplete="off" maxlength="30" class="layui-input shop_name"/>
                    </div>
                </li>
                <li class="layui-form-item" >
                    <div class="layui-form-label" style="margin-left:-6%;width:42%"><b style="color:red;">*</b> Amazon账号：</div>
                    <div class="layui-input-inline" style="width:259px;">
                        <input type="text" value="{{$shopInfo->amazon_accout ??''}}" placeholder="Amazon注册邮箱" name="amazon_accout" lay-verify="required"  autocomplete="off" maxlength="50" class="layui-input amazon_accout" />
                    </div>
                </li>
                <li class="layui-form-item" >
                    <div class="layui-form-label" style="margin-left:-6%;width:42%"><b style="color:red;">*</b> SellerID：</div>
                    <div class="layui-input-inline" style="width:259px;">
                        <input type="text" value="{{$shopInfo->seller_id ??''}}" placeholder="Amazon卖家编号" name="seller_id" lay-verify="required"  autocomplete="off" maxlength="50" class="layui-input seller_id" />
                    </div>
                </li>
                <li class="layui-form-item">
                    <div class="layui-form-label" style="margin-left:-6%;width:42%"><b style="color:red;">*</b> 开户站：</div>
                    <div class="inputBlock" style="width:360px;margin-left:36%;">
                        <div class="multLable" style="width: 259px;display: inline-block;">
                            <select  name="open_state" class="open_state">
                                <option value="">开户站</option>
                                {{--时间有点紧 先写成静态的 后面配置到数据库--}}
                                <option value="1" @if(isset($shopInfo->open_state) && ($shopInfo->open_state == 1)) selected @endif>日本</option>
                                <option value="2" @if(isset($shopInfo->open_state) && ($shopInfo->open_state == 2)) selected @endif>美国</option>
                                <option value="3" @if(isset($shopInfo->open_state) && ($shopInfo->open_state == 3)) selected @endif>加拿大</option>
                                <option value="4" @if(isset($shopInfo->open_state) && ($shopInfo->open_state== 4)) selected @endif>德国</option>
                                <option value="5" @if(isset($shopInfo->open_state) && ($shopInfo->open_state == 5)) selected @endif>西班牙</option>
                                <option value="6" @if(isset($shopInfo->open_state) && ($shopInfo->open_state == 6)) selected @endif>法国</option>
                                <option value="7" @if(isset($shopInfo->open_state) && ($shopInfo->open_state == 7)) selected @endif>印度</option>
                                <option value="8" @if(isset($shopInfo->open_state) && ($shopInfo->open_state == 8)) selected @endif>意大利</option>
                                <option value="9" @if(isset($shopInfo->open_state) && ($shopInfo->open_state == 9)) selected @endif>英国</option>
                                <option value="10" @if(isset($shopInfo->open_state) && ($shopInfo->open_state == 10)) selected @endif>中国</option>
                                <option value="11" @if(isset($shopInfo->open_state) && ($shopInfo->open_state == 11)) selected @endif>澳大利亚</option>
                                <option value="12" @if(isset($shopInfo->open_state) && ($shopInfo->open_state == 12)) selected @endif>墨西哥</option>
                            </select>
                        </div>

                        <a href="https://developer.amazonservices.com/" target="_blank" style="margin: 0 10px; color: #0088cc;">去开户站</a>
                    </div>
                </li>

                <li class="layui-form-item">
                    <div class="layui-form-label" style="margin-left:-6%;width:42%"><b style="color:red;">*</b> AWSAccessKeyId：</div>
                    <div class="layui-input-inline "  style="width:259px;">
                        <input type="text" value="{{$shopInfo->license_key ??''}}"  name="user_key" placeholder="AWSAccessKeyId"   autocomplete="off" maxlength="50" class="layui-input user_key" />
                    </div>
                </li>
                <li class="layui-form-item">
                    <div class="layui-form-label" style="margin-left:-6%;width:42%"><b style="color:red;">*</b> Secret Key：</div>
                    <div class="layui-input-inline " style="width:259px;">
                        <input type="text" value="{{$shopInfo->service_secret ??''}}" name="secret" placeholder="Secret Key"  autocomplete="off" maxlength="50" class="layui-input secret" />
                    </div>
                </li>
            </ul>
        </form>
    </div>
@endsection
@section('javascripts')
    <script>
        var callbackdata = function () {
            var data = {
                shop_name: $('.shop_name').val(),
                secret: $('.secret').val(),
                user_key: $('.user_key').val(),
                amazon_accout: $('.amazon_accout').val(),
                open_state: $('.open_state').val(),
                seller_id: $('.seller_id').val(),
            };
            return data;
        };
        layui.use(['layer','form','element','upload'], function(){
            var layer = layui.layer,form = layui.form,upload = layui.upload,element = layui.element;
        });
    </script>
@endsection
