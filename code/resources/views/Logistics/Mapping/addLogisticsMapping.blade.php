@extends('layouts/new_dialog')
@section('css')
    <style>
        .layui-form-select dl { max-height:200px; }
    </style>
@endsection
@section('content')
    <div class="openadvisory" style="padding-top: 20px;">
        <form action="" method="post" class="layui-form myForm" id="myForm">
            {{ csrf_field() }}
            <ul class="" style="margin: 0 6rem!important;">
                <li class="layui-form-item">
                    <div class="layui-form-label" style="width: 150px;"><b style="color:red;">*</b>平台：</div>
                    <div class="layui-input-inline">
                        <select name="plat_id" id="plat_id" lay-verify="required" >
                            <option value="" readonly="">请选择</option>
                            @foreach($platforms as $platform)
                                <option value="{{ $platform['id'] }}">{{ $platform['name_EN'] }}</option>
                            @endforeach
                        </select>
                    </div>
                </li>

                <li class="layui-form-item">
                    <div class="layui-form-label" style="width: 150px;"><b style="color:red;">*</b>电商物流名称：</div>
                    <div class="layui-input-inline">
                        <input name="plat_logistic_name" id="plat_logistic_name" type="text" autocomplete="off" lay-verify="required" style="
    width: 190px;">
                    </div>
                </li>

                <li class="layui-form-item">
                    <div class="layui-form-label" style="width: 150px;"><b style="color:red;">*</b>电商物流承运商：</div>
                    <div class="layui-input-inline">
                        <input name="carrier_name" id="carrier_name" type="text" autocomplete="off" lay-verify="required" style="
    width: 190px;">
                    </div>
                </li>

                <li class="layui-form-item" >
                    <div class="layui-form-label" style="width: 150px;"><b style="color:red;">*</b>系统物流：</div>
                    <div class="layui-input-inline">
                        <select name="logistic_id" id="logistic_id" lay-verify="required">
                            <option value="" readonly="">请选择</option>
                            @foreach($logistics as $logistic)
                                <option value="{{ $logistic['id'] }}">{{ $logistic['logistic_name'] }}</option>
                            @endforeach
                        </select>
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
                plat_id: $('#plat_id').val(),
                logistic_id: $('#logistic_id').val(),
                logistic_name: $("#logistic_id").find("option:selected").text(),
                plat_logistic_name: $('#plat_logistic_name').val(),
                carrier_name: $('#carrier_name').val(),
            };
            return data;
        };
        layui.use(['layer','form','element','upload'], function(){
            var layer = layui.layer,form = layui.form;
        });
    </script>
@endsection
