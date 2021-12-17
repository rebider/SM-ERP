@extends('layouts/new_dialog')
@section ('css')
    <link href="{{asset('css/menus.css')}}" rel="stylesheet">
@endsection
@section('content')
    <div class="openadvisory">
        <form action="" method="post" class="layui-form" id="myForm">
            {{ csrf_field() }}
                @if(isset($shops))
                    @foreach($shops as  $shopVal)
                        <div class="colCheckbox checkboxGroup {{$shopVal['id']}}_checkboxGroup">
                            <div class="boxall">
                                <h3>
                                    <input type="checkbox" lay-filter="allsele" class="{{$shopVal['id']}}_selectAll" lay-skin="primary" title="{{$shopVal['name_CN']}}" name="checkPlat[]" value="{{$shopVal['id']}}"/>
                                </h3>
                            </div>
                            @if (isset($shopVal ['setting_shops']))
                                <div class="kb_chbox chebox">
                                    @foreach($shopVal ['setting_shops'] as $setting_shops)
                                        <div class="lip">
                                            <input type="checkbox" class="chekid" name="check[]" lay-filter="oneCho"
                                                   lay-skin="primary" title="{{$setting_shops['shop_name']}}" value="{{$setting_shops['id']}}"   data-menu="{{$shopVal['id']}}"/>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach
                @endif
        </form>
    </div>

@endsection

@section('javascripts')
    <script type="text/javascript" src="{{ asset('js/user.js?'.time()) }}"></script>
    <script>
        $(document).ready(function () {
            $(document.body).css('cssText', "width:900px !important;min-width: 0!important;margin 2rem");
            $(document.body).animate({'width': '92%', 'margin': '2rem'}, 100)
        })

        layui.config({ base: '../../layui/lay/modules/'});
        layui.use(['layer','form','element','laydate','table'], function() {
            var layer = layui.layer, form = layui.form;

            //自动渲染初始化事件
            $(document).ready(function () {
                shopReload();
                form.render();
            });


            //店铺数据重载
            function shopReload() {
                var shop_id = new Array();
                var nums = 0;
                @if(isset ($permissionArrShop))
                    @foreach ($permissionArrShop as $k=>$v)
                    shop_id ['{{$k}}'] = '{{$v}}'
                nums++;
                @endforeach
                        @endif
                if (nums > 0) {
                    for (var num = 0; num < nums; num++) {
                        var i = 0;
                        var j = 0;
                        $('#myForm').find('input[value="' + shop_id[num] + '"]').attr('checked', 'true');
                        var value = shop_id[num];
                        var menu = $('#myForm').find('input[value="' + shop_id[num] + '"]').attr('data-menu');
                        var menu_class = menu+'_checkboxGroup';
                        var menuAll_class = menu+'_selectAll';
                        //新增隐藏域

                        var checked = $('#myForm').find('input[value="' + shop_id[num] + '"]').attr('checked');
                        $('#myForm .' + menu_class).find("input[name='check[]']").each(function () {
                            if (this.checked === true) {
                                i++;
                            }
                            j++;
                        });
                        if (i == j) {
                            $("." + menuAll_class).prop("checked", true);
                        } else {
                            $("." + menuAll_class).removeAttr("checked");
                        }
                    }
                }
            }
        })

    </script>
@endsection