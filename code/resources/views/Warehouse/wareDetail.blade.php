@extends('layouts/main')

@section('content')
    <div class="sectionBody">
        <div class="location">
    		<span class="layui-breadcrumb">
			  <a href="">@if($customerInfo['ticket']['warehouse_consult_state'] == '2')待接收工单@else待回复工单@endif</a>
			  <a><cite>详情</cite></a>
			</span>
        </div>
        <div class="edn-row flexshrink">
            <div class="EDbar formbox">
                <div class="edTit"><h3 class="le">工单信息</h3></div>
                <form action="" class="layui-form">
                    <ul class="form-uldef">
                        <li class="layui-inline">
                            <label class="layui-form-label">客诉单号</label>
                            <div class="layui-input-inline">
                                <p>{{$customerInfo['cc_code']}}</p>
                            </div>
                        </li>
                        <li class="layui-inline">
                            <label class="layui-form-label">订单号</label>
                            <div class="layui-input-inline">
                                {{$customerInfo['order_number']}}
                            </div>
                        </li>
                        <li class="layui-inline">
                            <label class="layui-form-label">运单号</label>
                            <div class="layui-input-inline">
                                {{$customerInfo['tracking_number']}}
                            </div>
                        </li>
                        <li class="layui-inline">
                            <label class="layui-form-label">电商平台</label>
                            <div class="layui-input-inline">
                                @if(isset($customerInfo['platform']))
                                    {{$customerInfo['platform']['platform_name']}}
                                @else
                                    无
                                @endif
                            </div>
                        </li>
                        <li class="layui-inline">
                            <label class="layui-form-label">工单类型</label>
                            <div class="layui-input-inline">
                                @if($customerInfo['cc_type'] == 1)
                                    物流停滞
                                @elseif($customerInfo['cc_type'] == 2)
                                    破损
                                @elseif($customerInfo['cc_type'] == 3)
                                    丢件
                                @elseif($customerInfo['cc_type'] == 4)
                                    虚假签收
                                @elseif($customerInfo['cc_type'] == 5)
                                    多发或漏发
                                @elseif($customerInfo['cc_type'] == 6)
                                    特殊赔付
                                @elseif($customerInfo['cc_type'] == 7)
                                    其他
                                @endif
                            </div>
                        </li>
                        <li class="layui-inline">
                            <label class="layui-form-label">重要等级</label>
                            <div class="layui-input-inline">
                                @if($customerInfo['priority'] == 1)
                                    普通
                                @elseif($customerInfo['priority'] == 2)
                                    重要
                                @elseif($customerInfo['priority'] == 3)
                                    紧急
                                @endif
                            </div>
                        </li>
                        <li class="layui-inline">
                            <label class="layui-form-label">收件人</label>
                            <div class="layui-input-inline">
                                {{$customerInfo['consignee']}}
                            </div>
                        </li>
                        <li class="layui-inline">
                            <label class="layui-form-label">收件人电话</label>
                            <div class="layui-input-inline">
                                {{$customerInfo['consignee_phone']}}
                            </div>
                        </li>
                        <li class="layui-inline">
                            <label class="layui-form-label">收件人地址</label>
                            <div class="layui-input-inline">
                                {{$customerInfo['consignee_address']}}
                            </div>
                        </li>
                        <li class="layui-inline">
                            <label class="layui-form-label">订单重量</label>
                            <div class="layui-input-inline">
                                {{$customerInfo['order_weight']}}
                            </div>
                        </li>
                        <li class="layui-inline">
                            <label class="layui-form-label">咨询人</label>
                            <div class="layui-input-inline">
                                @if(isset($user))
                                    {{$user->username}}
                                @else
                                    无
                                @endif
                            </div>
                        </li>
                        <li class="layui-inline">
                            <label class="layui-form-label">咨询时间</label>
                            <div class="layui-input-inline">
                                @if(isset($customerInfo['ticket_consult'][0]))
                                    {{$customerInfo['ticket_consult'][0]['consult_time']}}
                                @else
                                    无
                                @endif
                            </div>
                        </li>
                        @if(isset($customerInfo['ticket_consult'][0]))
                            <hr>
                        @endif
                        @foreach(array_reverse($customerInfo['ticket_consult']) as $consult)
                            <li class="layui-form-item">
                                <label class="layui-form-label">@if($consult['is_first_consult'] == 1)咨询问题@else补充问题@endif</label>
                                <div class="layui-input-block">
                                    {{$consult['consult_content']}}
                                </div>
                            </li>
                        @endforeach

                        @if(isset($customerInfo['ticket_consult'][0]))
                            <hr>
                        @endif
                        <div class="ticket_reply">
                            @foreach($replyInfo as $reply)
                                <li class="layui-form-item innerdesc">
                                    <label class="layui-form-label">@if($reply->is_first_reply == 1)回复@else补充回复@endif</label>
                                    <div class="layui-input-block">
                                        {{$reply->reply_content}}
                                    </div>
                                </li>
                            @endforeach
                        </div>
                        @if($customerInfo['ticket']['warehouse_consult_state'] == '3' )
                            <li class="layui-form-item bottomBtn">
                                <div class="layui-input-block">
                                    <a class="layui-btn supplement_reply" id="supdes">回复工单</a>
                                </div>
                            </li>
                        @elseif($customerInfo['ticket']['warehouse_consult_state']  == '4')
                            <li class="layui-form-item bottomBtn">
                                <div class="layui-input-block">
                                    <a class="layui-btn" id="supdes">补充回复</a>
                                </div>
                            </li>
                        @endif
                    </ul>
                </form>
            </div>
            <div class="EDbar sidegoods-infor">
                <div class="layui-tab layui-tab-brief" lay-filter="docDemoTabBrief">
                    <ul class="layui-tab-title">
                        <li class="layui-this">商品信息</li>
                        @if(!empty($ticketPictures))
                            <li>图片信息</li>
                        @endif
                    </ul>
                    <div class="layui-tab-content">
                        <div class="layui-tab-item layui-show">
                            <div class="listName"><span>商品名称</span><span>订单数量</span><span>异常数量</span></div>
                            @foreach($ticketGoods as $ticketGood)
                                <div class="goodsitem">
                                    <div class="gdtitle Ellipsis"><p>{{$ticketGood->product_sku}}</p></div>
                                    <div class="kor orNum">{{$ticketGood->quantity}}</div>
                                    <div class="kor Num">{{$ticketGood->cancel}}</div>
                                </div>
                            @endforeach

                        </div>
                        <div class="layui-tab-item goodsUpIMG">
                            <div class="layui-upload">

                                <button type="button" class="layui-btn" id="uploadBtn">支持jpg/jped/png格式，单张不超过3M
                                </button>
                                <div id="div_prev" title="">
                                    @if(!empty($ticketPictures))
                                        @foreach($ticketPictures as $ticketPicture)
                                            <div class="hoverbox" @if($ticketPicture['pic_from'] == 1) onmouseover="showDelIco({{$ticketPicture['id']}})"
                                                 onmouseleave="hiddenDelIco({{$ticketPicture['id']}})" @endif id="{{ $ticketPicture['id'].'div' }}">
                                            <span id="{{$ticketPicture['id'].'del'}}" class="delIcon" style="display: none"
                                                  onclick="delpic({{$ticketPicture['id']}})"></span>
                                                <img onerror="this.src='/img/imgNotFound.jpg'" id="{{ $ticketPicture['id'] }}"
                                                     class="thumb"
                                                     onclick="showBig('{{ '/imgsys/app/public/'.$ticketPicture['src'] }}')"
                                                     src="{{ '/imgsys/app/public/'.$ticketPicture['src'] }}" alt="图片异常"/>
                                            </div>

                                        @endforeach
                                    @endif
                                </div>
                                <div id="prevModal"><img onerror="this.src='/img/imgNotFound.jpg'" id="img_prev" />

                                </div>
                            </div>

                            <div>
                                <span class="btn-grey" id="uploadPic"></span>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </div>
    <div id="Additional">
        <textarea name=""></textarea>
        <input type="hidden" name="customer_complaint_id" value="{{$customerInfo['customer_complaint_id']}}">
        <input type="hidden" name="ticket_id" value="{{$customerInfo['ticket']['ticket_id']}}">
        <input type="hidden" name="ticket_consult_id" value="{{$customerInfo['ticket_consult'][0]['ticket_consult_id']}}">
    </div>
@endsection
@section('javascripts')
    <script>
        layui.use(['layer','form','element','upload'], function(){
            var layer = layui.layer,form = layui.form,upload = layui.upload,element = layui.element;

            form.on('submit(BatchSubmit)', function(data){
                return false;
            });

            upload.render({
                elem: '#uploadBtn',
                url: '/photo/upload'
                , multiple: true
                , size: 3072
                , auto: true
                , data: {
                    '_token': "{{csrf_token()}}",
                    'cc_id': "{{$customerInfo['customer_complaint_id']}}",
                    'pic_from':"1"
                },

                allDone: function (obj) { //当文件全部被提交后，才触发
                    //图像预览，如果是多文件，会逐个添加。(不支持ie8/9)
                    imgObjPre.preview(function (index, file, result) {
                        var imgobj = new Image(); //创建新img对象
                        imgobj.src = result; //指定数据源
                        imgobj.className = 'thumb';
                        imgobj.id = currentImgId;
                        imgobj.onclick = function (result) {
                            //单击预览
                            img_prev.src = this.src;
                            layer.open({
                                title: '预览',
                                type: 1,
                                area: ["600px", "500px"],
                                content: $('#prevModal')
                            });
                        };

                        var cret = document.createElement("div");
                        cret.setAttribute("class", "hoverbox aabb");
                        cret.appendChild(imgobj);
                        document.getElementById("div_prev").appendChild(cret); //添加到预览区域
                    });

                },

                choose: function (obj) {
                    imgObjPre = obj;
                }

                , done: function (res, index, upload) { //每个文件提交一次触发一次。详见“请求成功的回调”
                    layer.msg('图片上传成功!',{icon:6});
                    currentImgId = res.id;

                },
                error: function (e, x, t) {
                    //请求异常回调
                }
            });

            layer.ready(function(){
                layer.photos({
                    photos: '#layer-photos-demo'
                    ,shift: 5
                });
            });

            window.showDelIco = function (id) {
                let pic = id + 'del';
                $("#" + pic).show();
            };

            window.hiddenDelIco = function (id) {
                let pic = id + 'del';
                $("#" + pic).hide();
            };

            window.delpic = function (id) {
                $.MXAjax({
                    type: "post",
                    url: "/ECommerce/delPic",
                    data: {
                        '_token': "{{csrf_token()}}",
                        'id': id
                    },
                    success: function (data) {
                        //获取这个ID并移除
                        if (!data.status) {
                            layer.msg(data.msg ,{icon:5});
                        } else {
                            layer.msg('删除图片成功!' ,{icon:6});
                            $('#' + id + 'div').remove();
                        }
                    }
                });
            };

            $(document).on("mouseenter mouseleave", '#div_prev .aabb', function (event) {
                if (event.type == "mouseenter") {
                    let index = $(this).find('img').attr('id');
                    $(this).append('<span class="delIcon" data-index=' + index + '></span>')
                } else if (event.type == "mouseleave") {
                    $(this).find('.delIcon').remove();
                }
            });

            $(document).on("click", ".delIcon", function () {
                let index = $(this).attr('data-index');
                if (index !== undefined) {
                    delpic(index);
                }

                $(this).parent().remove();
            });

            $('#supdes').click(function(){
                layer.open({
                    title:"回复",
                    type: 1,
                    area:["400px","300px"],
                    content: $('#Additional'),
                    btn: ['确定', '取消'],
                    btn1: function(index, layero){
                        var replyContent = $('#Additional textarea').val();
                        var customer_complaint_id = $('#Additional input[name=customer_complaint_id]').val();
                        var ticket_id = $('#Additional input[name=ticket_id]').val();
                        var ticket_consult_id = $('#Additional input[name=ticket_consult_id]').val();

                        if(replyContent == ''){
                            layer.msg('请输入回复内容!',{icon:5});
                            return false;
                        }

                        $.MXAjax({
                            url:'/Warehouse/ware_reply_work_order',
                            type:'get',
                            data:{
                                replyContent:replyContent,
                                customer_complaint_id:customer_complaint_id,
                                ticket_id:ticket_id,
                                ticket_consult_id:ticket_consult_id
                            },
                            dataType:'json',
                            success:function(data){
                                if(data.status == 1){
                                    layer.close(index);
                                    layer.msg(data.message,{icon:6});
                                    var html = '';
                                    html +='<li class="layui-form-item innerdesc">'+
                                        '<label class="layui-form-label">'+ data.attrs +'</label>'+
                                        '<div class="layui-input-block">'+ replyContent+
                                        '</div>'+
                                        '</li>';
                                    $(".ticket_reply ").append(html);
                                    $(".supplement_reply ").html('补充回复');
                                    $('#Additional textarea').val('');

                                }else{
                                    layer.close(index);
                                    layer.msg(data.message,{icon:5});
                                    $('#Additional textarea').val('');

                                }
                            }
                        });
//
                    }
                });
            })


        });

        window.showBig=function(src){ //图片预览
            var imgobj = new Image(); //创建新img对象
            imgobj.src = src; //指定数据源
            imgobj.className = 'thumb';
            img_prev.src = src;
            layer.open({
                title: '预览',
                type: 1,
                area: ["600px", "500px"],
                content: $('#prevModal')
            });

            return ;
        } ;

        $("body").bind("keydown",function(event){
            if (event.keyCode == 116) {
                event.preventDefault(); //阻止默认刷新
                location=location;
            }
        })

        $(function(){
            $(".Ellipsis").each(function (i) {
                var divH = $(this).height();
                var $p = $("*", $(this)).eq(0);
                while ($p.outerHeight() > divH) {
                    $p.text($p.text().replace(/(\s)*([a-zA-Z0-9]+|\W)(\.\.\.)?$/, "..."));
                };
            });


        })

    </script>
@endsection