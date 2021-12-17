@extends('layouts/new_main')
@section('content')
    <div class="layui-form" lay-filter="layuiadmin-form-useradmin" id="layuiadmin-form-useradmin"
         style="padding: 20px 0 0 0;margin: 0px 80px;">
        {{csrf_field()}}
        <input type="hidden" name="id">
        <input type="hidden" name="type" value="{{ request()->input("type")}}">
        <div class="layui-form-item">
            <label class="layui-form-label" style="width: 115px;"><b style="color:red;">*</b> 店铺名称：</label>
            <div class="layui-input-inline">
                <input type="text" value="{{$shopInfo->shop_name??''}}" name="facilitator" lay-verify="required" placeholder="请输入店铺名称"
                       autocomplete="off"  class="layui-input shop_name" maxlength="30">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label" style="width: 115px;"><b style="color:red;">*</b> 来源平台：</label>
            <div class="layui-input-inline">
                <select class="layui-input plat_id">
                    <option value="all" selected >全部</option>
                    @foreach($plats as $k=>$v)
                        <option value="{{$v->id}}" @if($v->id == ($shopInfo->plat_id??'')) selected @endif >{{$v->name_EN}}</option>
                    @endforeach()
                </select>
            </div>
        </div>
    </div>
@endsection
@section('javascripts')
    <script>
        var callbackdata = function () {
            var data = {
                shop_name: $('.shop_name').val(),
                /* warehouse_name: $('.warehouse_name').val(),*/
                source_plat: $('.plat_id').val()
            };
            // console.log(data,1111);
            return data;
        };
        layui.use(['layer','form','element','upload'], function(){
            var layer = layui.layer,form = layui.form,upload = layui.upload,element = layui.element;
        });
    </script>
@endsection