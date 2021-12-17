<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <title>速贸云仓平台</title>
    <meta name="description" content="">
    <meta name="keywords" content="">
    <link rel="stylesheet" href="{{asset('layui/css/layui.css')}}" media="all">
    <script type="text/javascript" src="{{asset('layui/layui.js')}}"></script>
    <!-- <link href="css/layout.css" rel="stylesheet"> -->
    <script type="text/javascript" src="{{asset('js/jquery-1.11.3.min.js')}}"></script>
    <script type="text/javascript" src="{{asset('js/korbin.js')}}"></script>
    <script type="text/javascript" src="{{asset('js/kbPulic.js')}}"></script>
</head>
<body>
<form action="" class="layui-form">
    {{csrf_field()}}
    <div class="kbmodel_full">
        <div class="content-wrapper">
            <!-- 添加币种 -->
            <div class="productext layui-form" id="addtotext">
                <div class="produpage">
                    <table class="layui-table" lay-even="" lay-skin="nob">
                        <thead>
                        <tr>
                            <th><input type="checkbox" lay-skin="primary" lay-filter="allChoose" class="checkboxAll" ></th>
                            <th>物流产品</th>
                            <th>物流方式</th>
                            <th>已绑定仓库</th>
                        </tr>
                        </thead>
                        <tbody>
                        @if(!empty($wareLogicArr))
                        @foreach($wareLogicArr as $k => $v)
                            <tr class="oneChoosePr">
                                <td><input type="checkbox"  name="code" @if(isset($v['show'])) checked @endif  class="checkOne check_zero" value="{{$v['code']}}" lay-filter="oneChoose" lay-skin="primary" /></td>
                                <td>{{$v['code']}}</td>
                                <td>{{$v['name']}}</td>
                                <td style="display: none;"><input type="checkbox" @if(isset($v['show'])) checked @endif name="check[{{$v['code']}}][logicticsName]" class="checkOne uncheck_logic" value="{{$v['name']}}"  lay-filter="allChoose" lay-skin="primary">{{$v['name']}}</td>
                                <td>{{$v['warehouse_name']}}</td>
                                <td style="display: none;"><input type="checkbox" @if(isset($v['show'])) checked @endif disabled="disabled" name="check[{{$v['code']}}][warehouseName]" class="checkOne" lay-filter="allChoose" lay-skin="primary" value="{{$v['warehouse_code']}}">{{$v['warehouse_name']}}</td>
                            </tr>
                        @endforeach
                        @endif    
                        </tbody>
                        <div class="layui-form-item layui-hide">
                            <input type="button" lay-submit lay-filter="LAY-front-submit" id="LAY-front-submit" value="确认">
                        </div>
                    </table>
                </div>
            </div>
        </div>
    </div>
</form>
<!-- 乐天三联选择 -->
<script type="text/javascript" src="{{asset('js/jquery.sort.js')}}"></script>
<!-- 亚马逊三联选择 -->
<script type="text/javascript" src="{{asset('js/jquery.ymxsort.js')}}"></script>
<!-- 商品认领采集三联选择 -->
<script type="text/javascript" src="{{asset('js/jquery.rlsort.js')}}"></script>
<script>
    layui.use(['form', 'laydate','table','element','upload'], function(){
        var layer = layui.layer, form = layui.form, laypage = layui.laypage, laydate = layui.laydate;
        var element = layui.element;
        var $ = layui.jquery;
        upload = layui.upload;

        form.on('checkbox(allChoose)', function (data) {
            $(this).parents('.layui-form').find(".checkOne").each(function () {
                this.checked = data.elem.checked;
            });
            form.render('checkbox');
            var list = [];
            $(this).parents('.layui-form').find(".checkOne").each(function () {
                var checked_val = this.value;
                if(this.value == 'on'){
                    return true;
                }
                list.push(checked_val);
            });
        });

        form.on('submit(LAY-front-submit)', function (data) {
            var input = $('.check_zero');
            var field = data.field; //获取提交的字段
            var index = parent.layer.getFrameIndex(window.name);
            var uncheck_box = $('.check_zero').not("input:checked");
            var count = 0;
            var len   = input.length;
            var uncheck_code = new Array();

            $.each(input,function(index,item) {
                if(!item.checked) {
                    count++;
                }
            });

            $.each(uncheck_box,function() {
                uncheck_code.push($(this).val());
            });
            field.uncheck = uncheck_code;
            if(count == len) {
                layer.msg('至少选择一条数据!');
                return false;
            }

            $.ajax({
                type: 'post',
                url: '{{url('SettingLogistics/addSmLogistics')}}',
                data: field,
                dataType: 'json',
                success: function (data) {
                    if (data.code == 200) {
                        parent.layer.msg(data.msg, {icon: 1});
                        window.parent.location.reload();
                        parent.layer.close(index);

                    } else {
                        parent.layer.msg(data.msg, {icon: 5});
                    }
                }
            });
        });

        form.on('checkbox(oneChoose)', function (data) {
            var i = 0;
            var j = 0;
            $(this).parents('.layui-form').find(".check_zero").each(function () {
                if( this.checked === true ){
                    i++;
                }
                j++;
            });
            if( i == j ){
                $(this).parents('.layui-form').find(".checkboxAll").prop("checked",true);
                form.render('checkbox');
            }else{
                $(this).parents('.layui-form').find(".checkboxAll").removeAttr("checked");
                form.render('checkbox');
            }
            var list = [];
            $(this).parents('.oneChoosePr').find(".checkOne").each(function(){
                this.checked = data.elem.checked;
            });
            form.render('checkbox');
        });

        $(document).on('click','#addto',function(){
            layer.open({
                type: 1,
                title: '添加币种',
                area: ['600px', '800px'],
                content: $('#addtotext'),
                btn: ['确定', '取消'],
                yes: function(){
                }
            });
        });
    });
</script>

</body>
</html>