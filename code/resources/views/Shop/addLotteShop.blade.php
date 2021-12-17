@extends('layouts/new_main')
@section('head')
    <style type="text/css">
        .layui-form-item .layui-input-inline {
            width: 350px;
        }
    </style>
@endsection
@section('content')
    <div class="openadvisory" style="width: 700px">

        <form action="" class="layui-form">
            <div class="layui-form" lay-filter="layuiadmin-form-useradmin" id="layuiadmin-form-useradmin"
                 style="padding: 20px 0 0 0;">
                {{csrf_field()}}
                <input type="hidden" name="id" value="{{$shopInfo->id ?? ''}}">
                <input type="hidden" name="type" value="">
                <div class="layui-form-item">
                    <label class="layui-form-label" style="width:37%;margin-left:7%;"> <b style="color: red">*</b> 店铺名称：</label>
                    <div class="layui-input-inline">
                        <input type="text" name="shop_name" lay-verify="required" placeholder="乐天店铺名称"
                               autocomplete="off" class="layui-input shop_name" value="{{$shopInfo->shop_name ?? ''}}" maxlength="30">
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label" style="width:37%;margin-left:7%;"> <b style="color: red">*</b> 用户名（ユーザー名）：</label>
                    <div class="layui-input-inline">
                        <input type="text" name="user_name" lay-verify="required" placeholder="请输入用户名"
                               autocomplete="off" class="layui-input user_name" value="{{$shopInfo->user_name ?? ''}}" maxlength="50">
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label" style="width:37%;margin-left:7%;"> <b style="color: red">*</b> 店铺URL：</label>
                    <div class="layui-input-inline">
                        <input type="text" name="shop_url" lay-verify="" placeholder="如：http://www.rakuten.co.jp/summooljapan/" autocomplete="off"
                               class="layui-input shop_url" value="{{$shopInfo->shop_url ?? ''}}" maxlength="50">
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label" style="width:37%;margin-left:7%;"> <b style="color: red">*</b> serviceSecret：</label>
                    <div class="layui-input-inline">
                        <input type="text" name="secret" lay-verify="phone" placeholder="请输入serviceSecret" autocomplete="off"
                               class="layui-input secret" value="{{$shopInfo->service_secret ?? ''}}" maxlength="50">
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label" style="width:37%;margin-left:7%;"> <b style="color: red">*</b> LICENSEKEY：</label>
                    <div class="layui-input-inline">
                        <input type="text" name="user_key" lay-verify="" placeholder="请输入licenseKey" autocomplete="off"
                               class="layui-input user_key" value="{{$shopInfo->license_key ?? ''}}" maxlength="50">
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label" style="width:37%;margin-left:7%;"> <b style="color: red"></b> FTP账号：</label>
                    <div class="layui-input-inline">
                        <input type="text" name="ftp_user" lay-verify="" placeholder="请输入FTP账号" autocomplete="off"
                               class="layui-input ftp_user" value="{{$shopInfo->ftp_user ?? ''}}" maxlength="50">
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label" style="width:37%;margin-left:7%;"> <b style="color: red"></b> FTP密码：</label>
                    <div class="layui-input-inline">
                        <input type="text" name="ftp_pass" lay-verify="" placeholder="请输入FTP密码" autocomplete="off"
                               class="layui-input ftp_pass" value="{{$shopInfo->ftp_pass ?? ''}}" maxlength="50">
                    </div>
                </div>

                <div class="layui-form-item layui-hide">
                    <input type="button" lay-submit lay-filter="LAY-front-submit" id="LAY-front-submit" value="确认">
                </div>
            </div>
        </form>
    </div>
@endsection
@section('javascripts')
    <script>
        var callbackdata = function () {
            var data = {
                shop_name: $('.shop_name').val(),
                user_name: $('.user_name').val(),
                shop_url: $('.shop_url').val(),
                secret: $('.secret').val(),
                user_key: $('.user_key').val(),
                ftp_pass: $('.ftp_pass').val(),
                ftp_user: $('.ftp_user').val()
            };
            return data;
        };
        layui.use(['layer','form','element','upload'], function(){
            var layer = layui.layer,form = layui.form,upload = layui.upload,element = layui.element;
        });
    </script>
@endsection
