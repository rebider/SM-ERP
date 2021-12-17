@extends('layouts.new_main')
@section('head')
    <style>
        .layui-form-label {
            width: 130px;
        }
    </style>
@endsection
@section('content')
    @if(request()->input("type") == 2)
        {{--添加自定义仓库--}}
        <form action="" class="layui-form">
            <div class="layui-form" lay-filter="layuiadmin-form-useradmin" id="layuiadmin-form-useradmin"
                 style="padding: 20px 0 0 0;">
                {{csrf_field()}}
                <input type="hidden" name="id" value="{{ $warehouse['id'] or '' }}">
                <input type="hidden" name="type" value="{{ request()->input("type")}}">
                <div class="layui-form-item">
                    <label class="layui-form-label"> <b style="color: red">*</b> 服务商名称：</label>
                    <div class="layui-input-inline">
                        <input type="text" name="facilitator" lay-verify="required" placeholder="请输入服务商名称" maxlength="30"
                               autocomplete="off" class="layui-input" value="{{ $warehouse['facilitator'] or '' }}">
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label"> <b style="color: red">*</b> 仓库名称：</label>
                    <div class="layui-input-inline">
                        <input type="text" name="warehouse_name" lay-verify="required" placeholder="请输入仓库名称" maxlength="50"
                               autocomplete="off" class="layui-input" value="{{ $warehouse['warehouse_name'] or '' }}">
                    </div>
                </div>
                <div class="layui-form-item">
                    <div class="layui-form-label"> <b style="color: red">*</b> 是否启用：</div>
                    <div class="layui-input-inline">
                        <input type="radio" name="disable" value="1" title="是"
                               @if(!empty($warehouse['disable'])&&$warehouse['disable']==1)
                                     checked
                               @elseif(empty($warehouse['disable']))
                                     checked
                                @endif>
                        <input type="radio" name="disable" value="2" title="否"
                               @if(!empty($warehouse['disable'])&&$warehouse['disable']==2)
                               checked
                                @endif>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">负责人：</label>
                    <div class="layui-input-inline">
                        <input type="text" name="charge_person" lay-verify="" placeholder="请输入负责人" autocomplete="off" maxlength="30"
                               class="layui-input" value="{{ $warehouse['charge_person'] or '' }}">
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">联系电话：</label>
                    <div class="layui-input-inline">
                        <input type="tel" name="phone_number" lay-verify="" placeholder="请输入联系电话" autocomplete="off" maxlength="50"
                               class="layui-input" value="{{ $warehouse['phone_number'] or '' }}">
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">QQ：</label>
                    <div class="layui-input-inline">
                        <input type="text" name="qq" lay-verify="" placeholder="请输入QQ号码" autocomplete="off" minlength="50"  onkeyup="this.value=this.value.replace(/[^\d]/g,'')" onafterpaste="this.value=this.value.replace(/[^\d]/g,'')"
                               class="layui-input" value="{{ $warehouse['qq'] or '' }}">
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">地址：</label>
                    <div class="layui-input-inline">
                        <input type="text" name="address" lay-verify="" placeholder="请输入地址" autocomplete="off" maxlength="50"
                               class="layui-input" value="{{ $warehouse['address'] or '' }}">
                    </div>
                </div>

                <div class="layui-form-item layui-hide">
                    <input type="button" lay-submit lay-filter="LAY-front-submit" id="LAY-front-submit" value="确认">
                </div>
            </div>
        </form>


    @else
        @if(request()->input("edit") == 1)
            <div class="layui-form" lay-filter="layuiadmin-form-useradmin" id="layuiadmin-form-useradmin"
                 style="padding: 20px 0 0 0;">
                <input type="hidden" name="id" value="{{$warehouse['id']}}">
                <input type="hidden" name="type" value="{{ request()->input("type")}}">
                <input type="hidden" name="edit" value="{{ request()->input("edit")}}">

                <div class="layui-form-item">
                    <div class="layui-form-label">仓库：</div>
                    <div class="layui-input-inline" style="padding: 9px 0px;">
                        {{$warehouse['warehouse_name']}}
                    </div>
                </div>

                <div class="layui-form-item">
                    <div class="layui-form-label">是否可用：</div>
                    <div class="layui-input-inline">
                        <input type="radio" @if($warehouse['disable'] == 1) checked @endif name="disable" value="1" title="是">
                        <input type="radio" @if($warehouse['disable'] == 2) checked @endif name="disable" value="2" title="否">
                    </div>
                </div>
                {{--            <button class="layui-btn layuiadmin-btn-click" style="margin-left:130px;" data-type="add">--}}
                {{--                <i style="line-height: 32px" class="layui-icon">&#xe608;</i> 添加并授权--}}
                {{--            </button>--}}


                <div class="layui-form-item layui-hide">
                    <input type="button" lay-submit lay-filter="LAY-front-submit" id="LAY-front-submit" value="确认">
                </div>
            </div>
        @else
            {{--添加速贸仓库--}}
            <div class="layui-form" lay-filter="layuiadmin-form-useradmin" id="layuiadmin-form-useradmin"
                 style="padding: 20px 0 0 0;">
                <input type="hidden" name="id" value="">
                <input type="hidden" name="type" value="{{ request()->input("type")}}">
                <div class="layui-form-item">
                    <label class="layui-form-label">服务商名称：</label>
                    <label class="layui-form-label" style="width: 15rem;padding-left: 0;text-align: left;">株式会社 Dream Works</label>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">appToken：</label>
                    <div class="layui-input-inline">
                        <input type="text" name="appToken" lay-verify="required" placeholder="" autocomplete="off"
                               class="layui-input">
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">appKey：</label>
                    <div class="layui-input-inline">
                        <input type="text" name="appKey" lay-verify="required" placeholder="" autocomplete="off"
                               class="layui-input">
                    </div>
                </div>

                <div class="layui-form-item layui-hide">
                    <div class="layui-form-label">开启仓库：</div>
                    <div class="layui-input-inline">
                        <input type="radio" checked name="disable" value="1" title="美东仓">
                        <input type="radio" name="disable" value="2" title="美西仓">
                    </div>
                </div>
                {{--            <button class="layui-btn layuiadmin-btn-click" style="margin-left:130px;" data-type="add">--}}
                {{--                <i style="line-height: 32px" class="layui-icon">&#xe608;</i> 添加并授权--}}
                {{--            </button>--}}


                <div class="layui-form-item layui-hide">
                    <input type="button" lay-submit lay-filter="LAY-front-submit" id="LAY-front-submit" value="确认">
                </div>
            </div>
        @endif

    @endif
@endsection
@section('javascripts')

    <script>
        var type = $('input[name=type]').val();
        layui.use(['layer', 'form', 'element', 'table'], function () {
            var layer = layui.layer, form = layui.form, element = layui.element;
            //监听提交
            form.on('submit(LAY-front-submit)', function (data) {
                var field = data.field; //获取提交的字段
                var index = parent.layer.getFrameIndex(window.name); //先得到当前iframe层的索引
                if (type != 1) {
                    $.ajax({
                        type: 'post',
                        url: '{{ route('base_info.warehouse.createOrUpdate') }}',
                        data: field,
                        dataType: 'json',
                        success: function (data) {
                            if (data.code==1) {
                                parent.layer.msg(data.msg, {icon: 1});
                                parent.layui.table.reload('EDtable'); //重载表格
                                parent.layer.close(index); //再执行关闭
                            } else {
                                parent.layer.msg(data.msg, {icon: 5});
                            }

                        }
                    });
                } else {
                    var isEdit = $('input[name="edit"]').val();
                    if (isEdit == 1) {
                        var disableType = $("input[name=disable]:checked").val();
                        var id = $("input[name=id]").val();
                        $.ajax({
                            type: 'post',
                            url: '{{ route('base_info.warehouse.createOrUpdate') }}',
                            data: {disable: disableType, id: id, _token: '{{csrf_token()}}', type: type},
                            dataType: 'json',
                            success: function (data) {
                                if (data.code == 0) {
                                    parent.layer.msg(data.msg, {icon: 1});
                                    parent.layui.table.reload('EDtable'); //重载表格
                                } else {
                                    parent.layer.msg(data.msg, {icon: 5});
                                    return false;
                                }
                                parent.layer.close(index); //再执行关闭
                            }
                        });
                    } else {
                        var _data = {
                            'appToken': $('input[name="appToken"]').val(),
                            'appKey': $('input[name="appKey"]').val(),
                        };
                        $.ajax({
                            type: 'put',
                            url: '{{ route('base_info.warehouse.authorization') }}',
                            data: _data,
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            dataType: 'json',
                            success: function (data) {
                                if (data.code == 0) {
                                    parent.layer.msg(data.msg, {icon: 1});
                                    parent.layui.table.reload('EDtable'); //重载表格
                                } else {
                                    parent.layer.msg(data.msg, {icon: 5});
                                    return false;
                                }
                                parent.layer.close(index); //再执行关闭
                            }
                        });
                    }

                }

            });

            var active = {
                add: function () {
                    var _data = {
                        'appToken': $('input[name="appToken"]').val(),
                        'appKey': $('input[name="appKey"]').val(),
                    };
                    $.ajax({
                        type: 'put',
                        url: '{{ route('base_info.warehouse.authorization') }}',
                        data: _data,
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        dataType: 'json',
                        success: function (data) {
                            if (data.code) {
                                parent.layer.msg(data.msg, {icon: 1});
                                parent.layui.table.reload('EDtable'); //重载表格
                            } else {
                                parent.layer.msg(data.msg, {icon: 5});
                            }
                            parent.layer.close(index); //再执行关闭
                        }
                    });
                },
            };

            $('.layuiadmin-btn-click').on('click', function (e) {
                e.preventDefault();
                var type = $(this).data('type');
                active[type] ? active[type].call(this) : '';
            });


        });


        $("body").bind("keydown", function (event) {
            if (event.keyCode == 116) {
                event.preventDefault(); //阻止默认刷新
                location = location;
            }
        })

    </script>
@endsection