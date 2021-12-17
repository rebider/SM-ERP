@extends('layouts.new_main')
@section('head')
    <style>
        .layui-layedit {
            margin: 0 35px;
        }
    </style>
@endsection
@section('content')
    @if(request()->input('type')!='read')
        <form action="" class="layui-form announcement">
            {{csrf_field()}}
            <div class="layui-form" lay-filter="layuiadmin-form-useradmin" id="layuiadmin-form-useradmin"
                 style="padding:20px 0;">
                <input type="hidden" name="id" value="{{ request()->input("id")}}">
                <div class="layui-form-item">
                    <label class="layui-form-label"><b style="color: red">*</b>公告标题：</label>
                    <div class="layui-input-inline" style="width: 70%;">
                        <input type="text" name="title" lay-verify="required" placeholder="请输入标题"
                               autocomplete="off" class="layui-input" value="{{ $SettingNotices['title'] or '' }}">
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">重要信息：</label>
                    <div class="layui-input-block">
                        <input type="radio" name="important" value="1" title="是"
                               @if(!empty($SettingNotices['important'])&&$SettingNotices['important']==1)checked @endif
                        >
                        <input type="radio" name="important" value="0" title="否"
                               @if(!empty($SettingNotices['important'])&&$SettingNotices['important']==0)checked @endif
                               @if(empty($SettingNotices['important']))checked @endif
                        >
                    </div>
                </div>
                <textarea id="editor" lay-verify="content" style="display: none;"
                          name="content">{{ $SettingNotices['content'] or '' }}</textarea>
                <div class="layui-form-item">
                    <label class="layui-form-label">是否显示：</label>
                    <div class="layui-input-block">
                        <input type="radio" name="status" value="1" title="显示"
                               @if(!empty($SettingNotices['status'])&&$SettingNotices['status']==1)checked @endif
                               @if(empty($SettingNotices['status']))checked @endif>
                        <input type="radio" name="status" value="2" title="隐藏"
                               @if(!empty($SettingNotices['status'])&&$SettingNotices['status']==2)checked @endif>
                    </div>
                </div>
                <div class="layui-form-item layui-hide">
                    <input type="button" lay-submit lay-filter="LAY-front-submit" id="LAY-front-submit" value="确认">
                </div>
            </div>
        </form>
    @else
        <style>
            .layui-layedit-tool {
                display: none;
            }

            .layui-layedit {
                border: none;
            }
        </style>
        <div class="layui-form" lay-filter="layuiadmin-form-useradmin" id="layuiadmin-form-useradmin"
             style="padding:20px 0;">
            <div class="layui-input-inline" style="width: 90%;text-align: center;margin:20px auto;display: block;">
                {{ $SettingNotices['title'] or '' }}
            </div>
            <p style="text-align: center;margin:0 0 20px 0;color: #868282">
                发布时间： {{ $SettingNotices['created_at'] or '' }}</p>

            <textarea id="editor" lay-verify="content" style="display: none;"
                      name="content">{{ $SettingNotices['content'] or '' }}</textarea>

        </div>


    @endif

@endsection
@section('javascripts')

    <script>
        layui.use(['layer', 'form', 'element', 'table', 'layedit'], function () {
            var layer = layui.layer, form = layui.form, element = layui.element, layedit = layui.layedit;
            layedit.set({
                tool: [
                    'strong' //加粗
                    ,'italic' //斜体
                    ,'underline' //下划线
                    ,'del' //删除线

                    ,'|' //分割线

                    ,'left' //左对齐
                    ,'center' //居中对齐
                    ,'right' //右对齐
                    ,'' //超链接
                    ,'' //清除链接
                    ,'face' //表情
                    ,'image' //插入图片
                    ,'' //帮助
                    ]
                ,
                uploadImage: {
                    url: '{{ route('upload_edit') }}'
                    ,
                    type: 'post'
                    ,
                    data: {
                        '_token': $('meta[name="csrf-token"]').attr('content')
                    }//修改了底层的layedit.js不要更新
                }
            });

                    @if(request()->input('type')!='read')
            var editor = layedit.build('editor');
                    @else
            var editor = layedit.build('editor', {
                    tool: ['left'],
                    hideTool: ['left'],
                    height: 600
                });
            @endif


            //富文本提交赋值
            form.verify({
                content: function (value) {
                    layedit.sync(editor);
                }
            });

            //监听提交
            form.on('submit(LAY-front-submit)', function (data) {
                var field = data.field; //获取提交的字段
                var index = parent.layer.getFrameIndex(window.name); //先得到当前iframe层的索引
                var _text = document.querySelector('#LAY_layedit_1').contentWindow.document.body.textContent;
                if((_text.trim()).length===0){
                    layer.msg("内容为必填项",{icon:5});
                    return false;
                }

                $.ajax({
                    type: 'post',
                    url: '{{ route('base_info.announcement.create_update') }}',
                    data: field,
                    dataType: 'json',
                    success: function (data) {
                        if (data.code == 1) {
                            parent.layer.msg(data.msg, {icon: 1});
                            parent.layer.close(index); //再执行关闭
                            parent.layui.table.reload('EDtable'); //重载表格
                        } else {
                            parent.layer.msg(data.msg, {icon: 5});
                        }

                    }
                });
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