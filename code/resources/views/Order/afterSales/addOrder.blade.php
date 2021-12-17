@extends('layouts.new_main')
@section('content')
    <div class="infortext">
        <form action="" class="layui-form">
            {{csrf_field()}}
            <input type="hidden" name="order_id" value="">
            <div class="textpage layui-form">
                <h3>订单号</h3>&nbsp
                <input type="text" name="order_num" class="order_num" placeholder="" autocomplete="off" style="width:438px;height:38px;padding-left:10px;">
            </div>
            <div class="layui-form-item layui-hide">
                <input type="button" lay-submit lay-filter="LAY-front-submit" id="LAY-front-submit" value="确认">
            </div>
        </form>
    </div>
@endsection

@section('javascripts')
    <script>
        var callbackdata = function () {
            var data = {
                order_num: $('.order_num').val(),
            };
            return data;
        };
    </script>
@endsection