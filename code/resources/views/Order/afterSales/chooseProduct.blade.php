@extends('layouts.new_main')
@section('content')

    <div class="infortext">
        <div class="textpage">
            <table class="layui-table" lay-even lay-skin="row">
                <tbody>
                <tr>
                    <td><b>订单编号：</b>{{-- <a href="###" style="color: #1e9fff;">【查看系统订单】</a>--}}
                    </td>
                </tr>
                </tbody>
            </table>
        </div>

        <form action="" class="layui-form">
            <div class="textpage layui-form">
                {{csrf_field()}}
            <input type="hidden" name="order_id" value="">

                <table class="layui-table" lay-even="" lay-skin="nob">
                    <colgroup>
                    </colgroup>
                    <thead>
                    <tr>
                        <th align="center">序号</th>
                        <th align="center">商品图片</th>
                        <th align="center">产品编号</th>
                        <th align="center">产品名称</th>
                        <th align="center">单价</th>
                        <th align="center">币种</th>
                        <th align="center">数量</th>
                    </tr>
                    </thead>

                    <tbody class="goods-lists" data-type="">
                       @if(!empty($afterProducts))
                           @foreach($afterProducts as $k=> $val)

                    <tr>
                        <td><input type="checkbox" lay-skin="primary" lay-filter="allChoose" class="checkboxAll" >
                        </td>
                        <td>
                            {{--<img onerror="this.src=''" src="{{url('showImage').'?path=' . $val['goods']['goods_pictures']}}" style="max-width: 24px; max-height: 24px;cursor:zoom-in;" @if (isset($val['goods']['goods_pictures'])) onclick="check_img(this.src)"@endif/>--}}
                            <img onerror="this.src='/img/imgNotFound.jpg'" src="{{url('showImage').'?path=' . $val['goods']['goods_pictures']}}" style="max-width: 24px; max-height: 24px;cursor:zoom-in;" @if (isset($val['goods']['goods_pictures'])) onclick="check_img(this.src)"@endif/>
                        </td>
                        <td>{{$val['sku']}}</td>
                        <td style="display: none;"><input type="checkbox" class="pro_sku" name="goods[sku][]" value="{{$val['sku']}}">
                        </td>
                        <td>{{$val['product_name']}}</td>
                        <td style="display: none;"><input type="checkbox" class="pro_name" name="goods[sku][]" value="{{$val['product_name']}}">
                        </td>
                        <td>{{$val['univalence']}}</td>
                        <td style="display: none;"><input type="checkbox" class="pro_unival" name="goods[sku][]" value="{{$val['univalence']}}">
                        </td>
                        <td>{{$val['currency']}}</td>
                        <td style="display: none;"><input type="checkbox" class="pro_curr" name="goods[sku][]" value="{{$val['currency']}}">
                        </td>
                        <td>{{$val['already_stocked_number']}}</td>
                        <td style="display: none;"><input type="checkbox" class="pro_num" name="goods[sku][]" value="{{$val['already_stocked_number']}}">
                        </td>

                    </tr>
                      @endforeach
                  @endif
                    </tbody>
                </table>
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
            var products = $('input[type=checkbox]:checked');
            var porduct_data = [];
            $.each(products,function(i,item){
                porduct_data['']
                var tds = $(this).parent().parent().children('td');
                $.each()
            });
            /*var data = {
                logistic_name: $('.logistic_name').val(),
                warehouse_name: wh_str.substr(0,wh_str.length-1),
                disable: $('.disable').val()
            };*/
            return data;
        };
        layui.use(['layer','form','element','upload'], function(){
            var layer = layui.layer,form = layui.form,upload = layui.upload,element = layui.element;
        });
    </script>
@endsection