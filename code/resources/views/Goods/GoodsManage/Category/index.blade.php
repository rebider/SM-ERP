@extends('layouts/new_main')
@section('head')
    <style>
        .layui-form-select dl { max-height:200px; }
        .layui-form-select  { display:none }
    </style>
@endsection
@section('content')
    <div class="kbmodel_full">
        <div class="content-wrapper" style="height: 445px;">
            <form class="layui-form multiSearch">
                <div style="border: 1px solid rgba(228, 228, 228, 1);height: 390px;">
                    <div id="showAddCatHtml" style="width: auto; min-height: 0px; max-height: none; height: 250px; margin-left: 20px" class="ui-dialog-content ui-widget-content">
                        <div style="float :left;">
                            <div>
                                <span>一级分类</span>
                            </div>
                            <div id="oneCatHtml" >
                                <select style="display:block;;height:200px;width:210px" id="topCat" size="10" onclick="getTwoCat('topCat');" >
                                    @foreach($categories as $category)
                                        <option id="{{ $category['id'] }}" value="{{ $category['id'] }}">{{ $category['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div style="margin-top: 1px">
                                <button id="add_one" type="button" onclick="addCat('topCat');" class="layui-btn">添加</button>
                            </div>
                        </div>
                        <div style="float :left;">&nbsp;&nbsp;&nbsp;&nbsp;</div>
                        <div style="float :left;">
                            <div id="category_2" style="display: none">
                                <span>二级分类</span>
                            </div>
                            <div id="twoCatHtml">

                            </div>
                            <div style="margin-top: 1px">
                                <button id="add_two" type="button" onclick="addCat('CatTwo');" class="layui-btn" style="display: none;">添加</button>
                            </div>
                        </div>
                        <div style="float :left;">&nbsp;&nbsp;&nbsp;&nbsp;</div>
                        <div style="float :left;">
                            <div id="category_3" style="display: none">
                                <span>三级分类</span>
                            </div>
                            <div id="thirdCatHtml">

                            </div>
                            <div style="margin-top: 1px">
                                <button id="add_third" type="button" onclick="addCat('CatThird');" class="layui-btn" style="display: none;">添加</button>
                            </div>
                        </div>
                        <div>
                            <input id="nowLevel" type="hidden" value="topCat">
                        </div>
                    </div>
                    <br><br>
                    {{--新增分类--}}
                    <div id="addCatText" style="padding-left: 10px; padding-top: 10px; margin-top: 10px; float: right; width: 100%; border: 1px solid rgb(214, 214, 214); display: none;background: white;">
                        <table class="dialog-module" border="0" cellpadding="0" cellspacing="0">
                            <tbody>
                            <tr>
                                <td style="height:35px;width: 80px;">*分类名称:</td>
                                <td>
                                    <input type="hidden" id="parent_id" value="">
                                    <input type="text" name="category_name" id="category_name" validator="required" class="input_text">
                                </td>
                            </tr>
                            </tbody>
                        </table>
                        <div id="add_topCat" style="padding-top: 20px;padding-left: 110px;padding-bottom: 20px ;width: 100%">
                            <button type="button" onclick="addCurtGroup('topCat');" class="layui-btn layui-btn-sm">
                                保存
                            </button>
                            <button id="back" type="button" onclick="backNone();" style="border: 1px solid #dedede;background-color: #fff;color: #333;"
                                    class="layui-btn">取消
                            </button>
                        </div>
                        <div id="add_CatTwo" style="padding-top: 20px;padding-left: 110px;padding-bottom: 20px ;width: 100%">
                            <button id="add_CatTwo" type="button" onclick="addCurtGroup('CatTwo');" class="layui-btn layui-btn-sm">
                                保存
                            </button>
                            <button id="back" type="button" onclick="backNone();" style="border: 1px solid #dedede;background-color: #fff;color: #333;"
                                    class="layui-btn">取消
                            </button>
                        </div>
                        <div id="add_CatThird" style="padding-top: 20px;padding-left: 110px;padding-bottom: 20px ;width: 100%">
                            <button type="button" onclick="addCurtGroup('CatThird');" class="layui-btn layui-btn-sm">
                                保存
                            </button>
                            <button id="back" type="button" onclick="backNone();" style="border: 1px solid #dedede;background-color: #fff;color: #333;"
                                    class="layui-btn">取消
                            </button>
                        </div>
                    </div>
                    {{--编辑分类--}}
                    <div id="editGroup" style="padding-left: 10px; padding-top: 10px; margin-top: 10px; float: right; width: 100%; border: 1px solid rgb(214, 214, 214); display: none; background: white;">
                        <table class="dialog-module" border="0" cellpadding="0" cellspacing="0">
                            <tbody>
                            <tr>
                                <td style="height:35px;width: 80px;">*分类名称:</td>
                                <td>
                                    <input type="hidden" id="category_id_edit" value="">
                                    <input type="text" name="category_name_edit" id="category_name_edit" lay-verify="required" class="input_text">
                                </td>
                            </tr>
                            </tbody>
                        </table>
                        <div style="padding-top: 20px;padding-left: 110px;padding-bottom: 20px ;width: 100%">
                            <button id="add_t" type="button" onclick="updateCurtGroup();" class="layui-btn layui-btn-sm">
                                保存
                            </button>
                            <button id="add_t" type="button" onclick="delCategory();" style="border: 1px solid #dedede;background-color: #fff;color: #333;"
                                    class="layui-btn">删除
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('javascripts')
    <script>
        //document加载完成后响应表格渲染
//        $(document).ready(function () {
//            $('.layui-form-select').remove();
//        })
        //layui加载
        layui.config({base: '../../layui/lay/modules/'}).extend({formSelects: 'formSelects-v3'});
        layui.use(['layer', 'form', 'element', 'laydate', 'table', 'formSelects'], function () {
            var layer = layui.layer, form = layui.form, laydate = layui.laydate, table = layui.table,
                element = layui.element, formSelects = layui.formSelects;



            //选择一级分类--展示该一级分类下的所有二级分类
            window.getTwoCat = function () {
                var categories = [];
                var option = '';
                $.ajax({
                    type: "POST",
                    data: {
                        category_id: $('#topCat').val(),
                        _token: "{{ csrf_token() }}"
                    },
                    url: "{{ url('Goods/category/getChildCategoryById') }}",
                    success:function (data) {
                        if (data.status === 1){
                            categories = data.data;

                            $('#CatTwo').remove();   //移除二级分类
                            $('#CatThird').remove(); //移除三级分类
                            $('#category_3').css("display", 'none'); //隐藏分类字段
                            $('#add_two').css("display", 'none');    //隐藏添加字段
                            $('#add_third').css("display", 'none');  //隐藏添加字段
                            $('#addCatText').css("display", 'none');  //隐藏添加表单
                            $.each(categories,function (i,value) {
                                option += '<option id="'+value.id+'" value="'+value.id+'">'+value.name+'</option>'
                            });
                            var html = '<select style="display:block;;height:200px;WIDTH:200px" id="CatTwo" size="10" onclick="getThirdCat(\'CatTwo\');">' +
                                option +
                                '</select>';
                            $('#category_2').css("display", 'block'); //显示分类字段
                            $('#add_two').css("display", 'block');    //显示添加字段
                            $('#editGroup').css("display", 'block');  //显示表单
                            $('#twoCatHtml').append(html);            //渲染数据
                            $('#category_name_edit').val($('#topCat option:selected').text());
                            $('#category_id_edit').val($('#topCat option:selected').val());
                        } else {
                            layer.msg(data.data ,{icon:5});
                        }
                    },
                    error:function (e,x,t) {

                    }
                });
            };

            //选择二级分类--展示三级分类
            window.getThirdCat = function () {
                var categories = [];
                var option = '';
                $.ajax({
                    type: "POST",
                    data: {
                        category_id: $('#CatTwo').val(),
                        _token: "{{ csrf_token() }}"
                    },
                    url: "{{ url('Goods/category/getChildCategoryById') }}",
                    success:function (data) {
                        if (data.status === 1){
                            categories = data.data;

                            $('#CatThird').remove();   //移除三级分类
                            $('#addCatText').css("display", 'none');  //隐藏添加表单
                            $.each(categories,function (i,value) {
                                option += '<option id="'+value.id+'" value="'+value.id+'">'+value.name+'</option>'
                            });
                            var html = '<select style="display:block;;height:200px;WIDTH:200px" id="CatThird" size="10" onclick="getThirdInfo(\'CatThird\');">' +
                                option +
                                '</select>';
                            $('#category_3').css("display", 'block');  //显示分类字段
                            $('#add_third').css("display", 'block');  //显示添加字段
                            $('#editGroup').css("display", 'block');  //显示编辑表单
                            $('#thirdCatHtml').append(html);
                            $('#category_name_edit').val($('#CatTwo option:selected').text());
                            $('#category_id_edit').val($('#CatTwo option:selected').val());
                        }
                    },
                    error:function (e,x,t) {

                    }
                });
            };

            //点击三级分类
            window.getThirdInfo = function () {
                $('#addCatText').css("display", 'none');  //隐藏添加表单
                $('#editGroup').css("display", 'block');  //显示编辑表单
                $('#category_name_edit').val($('#CatThird option:selected').text());
                $('#category_id_edit').val($('#CatThird option:selected').val())
            };

            //添加
            window.addCat = function (event) {
                $('#editGroup').css("display", 'none');    //隐藏编辑表单
                $('#addCatText').css("display", 'block');  //显示添加表单
                $('#category_name').val('');

                if (event === 'topCat'){                       //一级分类
                    $('#parent_id').val('0');
                    $('#add_CatTwo').css("display", 'none');
                    $('#add_CatThird').css("display", 'none');
                    $('#add_topCat').css("display", 'block');

                } else if (event === 'CatTwo'){                //二级分类
                    $('#parent_id').val($('#topCat').val());
                    $('#add_topCat').css("display", 'none');
                    $('#add_CatThird').css("display", 'none');
                    $('#add_CatTwo').css("display", 'block');

                } else {                                       //三级分类
                    $('#parent_id').val($('#CatTwo').val());
                    $('#add_topCat').css("display", 'none');
                    $('#add_CatTwo').css("display", 'none');
                    $('#add_CatThird').css("display", 'block');

                }
            };

            //添加--保存
            window.addCurtGroup = function (event) {
                if ($('#category_name').val().length <= 0){
                    layer.msg('请填写分类名称',{icon: 5});
                }
                $.ajax({
                    type: "POST",
                    data: {
                        parent_id: $('#parent_id').val(),
                        category_name: $('#category_name').val(),
                        _token: "{{ csrf_token() }}"
                    },
                    url: "{{ url('Goods/category/addCategory') }}",
                    success: function (data) {
                        if (data.status === 1) {
                            $('#category_name').val('');
                            layer.msg(data.msg, {icon: 6});
                            var html = '<option value="' + data.id + '" id="'+data.id+'">' + data.category_name + '</option>';
                            if (event === 'topCat'){
                                $('#topCat').append(html);
                            } else if ( event === 'CatTwo'){
                                $('#CatTwo').append(html);
                            } else {
                                $('#CatThird').append(html);
                            }
                        } else {
                            layer.msg(data.msg, {icon: 5})
                        }
                    },
                    error: function (e, x, t) {

                    }
                })
            };

            //添加--取消
            window.backNone = function () {
                $('#addCatText').css("display", 'none');
                $('#category_name').val('');
            };

            //编辑--保存
            window.updateCurtGroup = function () {
                if ($('#category_name_edit').val().length <= 0){
                    layer.msg('请填写分类名称',{icon: 5});
                }
                $.ajax({
                    type: "POST",
                    data: {
                        category_id: $('#category_id_edit').val(),
                        category_name: $('#category_name_edit').val(),
                        _token: "{{ csrf_token() }}"
                    },
                    url: "{{ url('Goods/category/editCategoryById') }}",
                    success:function (data) {
                        if (data.status === 1){
                            layer.msg(data.msg, {icon: 6});
                            var id =  '#'+$('#category_id_edit').val();
                            $(id).text($('#category_name_edit').val());
                        } else {
                            layer.msg(data.msg, {icon: 5})
                        }
                    },
                    error:function (e,x,t) {

                    }
                })
            };

            //删除
            window.delCategory = function () {
                layer.confirm('确定删除吗？',function () {
                    $.ajax({
                        type: "POST",
                        data: {
                            category_id: $('#category_id_edit').val(),
                            _token: "{{ csrf_token() }}"
                        },
                        url: "{{ url('Goods/category/delCategoryById') }}",
                        success:function (data) {
                            if (data.status === 1){
                                layer.msg(data.msg, {icon: 6});
                                window.location.reload()
                            } else {
                                layer.msg(data.msg, {icon: 5})
                            }
                        },
                        error:function (e,x,t) {

                        }
                    })
                })
            }

        });



        $("body").bind("keydown", function (event) {
            if (event.keyCode == 116) {
                event.preventDefault(); //阻止默认刷新
                location = location;
            }
        })

    </script>
@endsection