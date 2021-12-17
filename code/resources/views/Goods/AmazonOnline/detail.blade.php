@extends('layouts.new_dialog')
@section('css')
    <style>
        .productexts h3 {
            color: #1E9FFF;
            font-weight: 700;
        }

        .productexts .layui-table tbody tr:hover {
            background-color: #fff !important
        }

        .kbmodel_full .content-wrappers {
            background: #fff;
            padding: 20px;
        }

        .produpage .layui-tab-content .extra-img .extra-img-block {
            display: inline-block;
            width: 120px;
            margin: 10px;
        }

        .produpage .layui-tab-content .extra-img .extra-img-block label {
            width: 100%;
            display: inline-block;
            text-align: center;
        }
    </style>
@endsection
@section('content')
    <div class="kbmodel_full">
        <div class="content-wrappers">
            <div class="productexts" id="bianjitext">
                <form action="" class="layui-form">
                    {{csrf_field()}}
                    <input type="hidden" name="order_id" value="{{ $goods['id'] ?? '' }}">
                    <!--订单信息-->
                    <div class="produpage layui-form lay-select">
                        <h3>平台信息</h3>
                        <table class="layui-table colora" lay-skin="nob">
                            <tbody>
                            <tr>
                                <td><b>店铺：</b>{{ $goods['shops']['shop_name'] ?? '' }}
                                </td>
                                <td colspan="2"><b>亚马逊分类：</b>{{ $goods['amazon_category_JP'] ?? '' }}</td>
                            </tr>
                            <tr>
                                <td><b>Seller SKU：</b>{{ $goods['seller_sku'] ?? '' }}</td>
                                <td><b>ASIN：</b>{{ $goods['ASIN'] ?? '' }}</td>
                                <td><b>商品编码：</b>{{ empty($goods['upc']) ? $goods['ASIN'] : $goods['upc'] }}（{{empty($goods['upc']) ? 'ASIN' : 'UPC'}}）</td>
                            </tr>
                            <tr>
                                <td><b>销售价格：</b>{{ $goods['sale_price'] ?? '' }}</td>
                                <td colspan="2"><b>平台库存：</b>{{ $goods['platform_in_stock'] ?? '' }}</td>
                            </tr>
                            <tr>
                                <td><b>促销价格：</b>{{ $goods['promotion_price'] ?? '' }}</td>
                                <td colspan="2"><b>促销时间：</b>{{ $goods['promotion_start_time'] ?? '' }} - {{ $goods['promotion_end_time'] ?? '' }}</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="produpage layui-form lay-select">
                        <h3>商品信息</h3>
                        <table class="layui-table colora" lay-skin="nob">
                            <tbody>
                            <tr>
                                <td><b>商品名称：</b>{{ $goods['title'] ?? '' }}</td>
                                <td><b>商品属性：</b>{{ $goods['attribute_name'] ?? '' }}</td>
                            </tr>
                            <tr>
                                <td><b>商品重量：</b>{{ $goods['goods_weight'] ?? '' }}</td>
                                <td><b>商品尺寸：</b>{{ $goods['goods_length'] ?? '' }} × {{$goods['goods_width'] ?? ''}} × {{$goods['goods_height'] ?? ''}}</td>
                            </tr>
                            <tr>
                                <td colspan="2"><b>商品描述：</b>{{ $goods['goods_description'] ?? '' }}</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="produpage layui-form lay-select">
                        <h3>上架信息</h3>
                        <table class="layui-table colora" lay-skin="nob">
                            <tbody>
                            <tr>
                                <td><b>商品标题：</b>{{ $goods['goods_name'] ?? '' }}</td>
                                <td><b>关键词1：</b>{{ $goods['goods_keywords'][0] ?? '' }}</td>
                                <td><b>商品标签1：</b>{{ $goods['goods_label'][0] ?? '' }}</td>
                            </tr>
                            <tr>
                                <td><b>商品品牌：</b>{{ $goods['brand'] ?? '' }}</td>
                                <td><b>关键词2：</b>{{ $goods['goods_keywords'][1] ?? '' }}</td>
                                <td><b>商品标签2：</b>{{ $goods['goods_label'][1] ?? '' }}</td>
                            </tr>
                            <tr>
                                <td><b>制造商：</b>{{ $goods['manufacturer'] ?? '' }}</td>
                                <td><b>关键词3：</b>{{ $goods['goods_keywords'][2] ?? '' }}</td>
                                <td><b>商品标签3：</b>{{ $goods['goods_label'][2] ?? '' }}</td>
                            </tr>
                            <tr>
                                <td><b>商品颜色：</b>{{ $goods['color'] ?? '' }}</td>
                                <td><b>关键词4：</b>{{ $goods['goods_keywords'][3] ?? '' }}</td>
                                <td><b>商品标签4：</b>{{ $goods['goods_label'][3] ?? '' }}</td>
                            </tr>
                            <tr>
                                <td><b>商品型号：</b>{{ $goods['goods_size'] ?? '' }}</td>
                                <td><b>关键词5：</b>{{ $goods['goods_keywords'][4] ?? '' }}</td>
                                <td><b>商品标签5：</b>{{ $goods['goods_label'][4] ?? '' }}</td>
                            </tr>
                            <tr>
                                <td colspan="3"><b>物品状态：</b>{{ $goods['goods_status'] ?? '' }}</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="produpage  layui-form">
                        <div class="layui-tab">
                            <ul class="layui-tab-title">
                                <li class="layui-this">主图</li>
                                <li class="">附图</li>
                            </ul>
                            <div class="layui-tab-content">
                                <!--配货单信息-->
                                <div class="layui-tab-item layui-show" style="width: 100px">
                                    <img onerror="this.src='/img/imgNotFound.jpg'" src="{{!empty($goods['img_url']) ? asset("{$goods['img_url']}") : asset('img/user1.png')}}">
                                    <label style="width: 100%; text-align: center; display: inline-block">主图</label>
                                </div>
                                <div class="layui-tab-item extra-img">
                                    @if (!empty($goods['goods_pics']))
                                        @foreach($goods['goods_pics'] as $key => $val)
                                            <div class="extra-img-block">
                                                <img onerror="this.src='/img/imgNotFound.jpg'" src="{{!empty($val) ? asset("{$val['link']}") : asset('img/user1.png')}}">
                                                <label>图片{{$key+1}}</label>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                </form>
            </div>

        </div>
    </div>
@endsection

@section('javascripts')
    <script>
        layui.use(['form', 'laydate', 'table', 'element', 'upload', 'laypage', 'layer'], function () {
            var laypage = layui.laypage,
                layer = layui.layer;
            var _order_id = $('input[name="order_id"]').val();

            // page完整功能
            laypage.render({
                elem: 'swiPaging'
                , count: 100
                , layout: ['count', 'prev', 'page', 'next', 'limit', 'refresh', 'skip']
                , jump: function (obj) {
                    console.log(obj)
                }
            });
        });
    </script>
@endsection