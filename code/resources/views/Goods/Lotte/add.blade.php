@extends('layouts.new_main')
@section('content')
    <div class="infortext">
        <form action="" class="layui-form">
            {{csrf_field()}}
            <input type="hidden" name="order_id" value="">
            <div class="textpage layui-form">
                <h3>自定义SKU</h3>&nbsp
                <input type="text" name="sku" class="sku" placeholder="" autocomplete="off" style="width:438px;height:38px;padding-left:10px;">
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
                sku: $('.sku').val(),
            };
            return data;
        };
        //绑定回车事件
        $(document).keypress(function(e) {
            if((e.keyCode || e.which)==13) {
                return false;
            }
        });
    </script>
@endsection