@extends('layouts/new_app')

@section('content')
    <style>
        .KBnotice li.red a{color: red;}
    </style>
    <div class="speedtc_col">
        <div class="panel_group">
            <div class="st_panel pro1">
                <div class="kb_title"><h2>客服服务</h2></div>
                <div class="st-Pbody">
                    <div class="linelist contactSev">
                        <p><i class="kbico ico1"></i>客服邮件：logistics@dws789.com</p>
                        <p><i class="kbico ico2"></i>联系电话：06-4963-3897</p>
                        <p><i class="kbico ico3"></i>联系方式：(微信)dwswangjunfeng</p>
                    </div>
                </div>
            </div>
            <div class="st_panel pro2">
                <div class="kb_title"><h2>待办事项</h2></div>
                <div class="st-Pbody schedule_body">
                    <ul class="st-schedule">
                        <li><div class="iso">
                                <h3>问题订单</h3>
                                <p class="num">{{$orderProblemCounts}}</p>
                                @php
                                    $currentUser = \App\Auth\Common\CurrentUser::getCurrentUser();
                                @endphp
                                @if (in_array('order/orderIndex',$currentUser->userPermissions))
                                <a class="view" @if ($orderProblemCounts > 0 ) href="{{url('order/orderIndex')}}?is_problem=true" @endif target="_blank" data-name="订单">立即处理></a>
                                @else
                                    <a class="view" href="javascript:void(0)" data-name="订单">立即处理></a>
                                @endif
                            </div>
                        </li>
                        <li>
                            <div class="iso">
                                <h3>配货无物流</h3>
                                <p class="num">{{$logisticsMissing}}</p>
                                @if (in_array('order/pending/index',$currentUser->userPermissions))
                                    <a class="view" @if ($logisticsMissing > 0 ) href="{{url('order/pending/index').'?problem=4'}}" @endif target="_blank" data-name="待配货订单">立即处理></a>
                                @else
                                    <a class="view" href="javascript:void(0)" data-name="待配货订单">立即处理></a>
                                @endif
                            </div>
                        </li>
                        <li>
                            <div class="iso">
                                <h3>配货无仓库</h3>
                                <p class="num">{{$warehouseMissing}}</p>
                                @if (in_array('order/pending/index',$currentUser->userPermissions))
                                    <a class="view" @if ($warehouseMissing > 0 ) href="{{url('order/pending/index').'?problem=3'}}" @endif target="_blank" data-name="待配货订单">立即处理></a>
                                @else
                                    <a class="view" href="javascript:void(0)" data-name="待配货订单">立即处理></a>
                                @endif
                            </div>
                        </li>
                        <li>
                            <div class="iso">
                                <h3>在途采购单</h3>
                                <p class="num">{{$purchase}}</p>
                                @if (in_array('purchase/index',$currentUser->userPermissions))
                                    <a class="view" @if ($purchase > 0 ) href="{{url('purchase/index')}}" @endif target="_blank" data-name="采购单">立即处理></a>
                                @else
                                    <a class="view" href="javascript:void(0)" data-name="采购单">立即处理></a>
                                @endif
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="st_panel">
            <div class="kb_title"><h2>快速上手</h2></div>
            <div class="st-Pbody getstarted fclear">
                <ul class="videoList">
                    <li><a href="javascript:void(0)" title="暂未上传教程"><em class="vid"></em>店铺授权指引教程</a></li>
                    <li><a href="javascript:void(0)" title="暂未上传教程"><em class="vid"></em>店铺授权指引教程</a></li>
                    <li><a href="javascript:void(0)" title="暂未上传教程"><em class="vid"></em>店铺授权指引教程</a></li>
                    <li><a href="javascript:void(0)" title="暂未上传教程"><em class="vid"></em>店铺授权指引教程</a></li>
                    <li><a href="javascript:void(0)" title="暂未上传教程"><em class="vid"></em>店铺授权指引教程</a></li>
                </ul>
                <ul class="fileList">
                    <li>
                        <a href="/file/handbook/总册：速贸云仓平台-产品规划书.docx">
                            <em class="file"></em>[文档]总册：速贸云仓平台-产品规划书
                        </a>
                    </li>
                    <li>
                        <a href="/file/handbook/速贸云仓平台-平台店铺授权.docx">
                            <em class="file"></em>[文档]速贸云仓平台-平台店铺授权
                        </a>
                    </li>
                    <li>
                        <a href="/file/handbook/速贸云仓平台-仓库授权添加物流及平台映射.docx">
                            <em class="file"></em>[文档]速贸云仓平台-仓库授权添加物流及平台映射
                        </a>
                    </li>
                    <li>
                        <a href="/file/handbook/速贸云仓平台-采购流程.docx">
                            <em class="file"></em>[文档]速贸云仓平台-采购流程
                        </a>
                    </li>
                    <li>
                        <a href="/file/handbook/速贸云仓平台-乐天刊登商品.docx">
                            <em class="file"></em>[文档]速贸云仓平台-乐天刊登商品
                        </a>
                    </li>
                    <li>
                        <a href="/file/handbook/速贸云仓平台-亚马逊刊登商品.docx">
                            <em class="file"></em>[文档]速贸云仓平台-亚马逊刊登商品
                        </a>
                    </li>
                    <li>
                        <a href="/file/handbook/速贸云仓平台-订单规则配置指南.docx">
                            <em class="file"></em>[文档]速贸云仓平台-订单规则配置指南
                        </a>
                    </li>
                </ul>
            </div>
        </div>
        <div class="st_panel kbin_tab">
            <div class="kb_title schtitle"><h2>订单情况</h2><div class="title-right"><em class="circle pe curr"></em><em class="circle pe"></em><em class="circle pe"></em></div></div>
            <div class="st-Pbody STcartogram Orderment fclear">
                <div class="item tim show">
                    <div class="areaStat" id="movement1" style="width:100%;height:320px"></div>
                </div>
                <div class="item tim">
                    <div class="areaStat" id="movement2" style="width:100%;height:320px"></div>
                </div>
                <div class="item tim">
                    <div class="areaStat" id="movement3" style="width:100%;height:320px"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="sliderbar">
        <div class="st_panel">
            <div class="kb_title"><h2>基本信息</h2></div>
            <div class="st-Pbody">
                <div class="linelist">
                    <p>欢迎您，{{$userName}}</p>
                    <p>公司：{{$company_name}}</p>
                </div>
            </div>
        </div>
        <div class="st_panel">
            <div class="kb_title"><h2>通知公告</h2></div>
            <div class="st-Pbody KBnotice">
                <ul id="biuuu_city_list">
                </ul>
                <div id="swiPaging"></div>
            </div>
        </div>
    </div>
    <div class="system_Footer">
    </div>
@endsection

@section('javascripts')
    <script type="text/javascript">
     //公告
        layui.use(['laydate','table'], function(){
            var layer = layui.layer, laypage = layui.laypage;

            var data = '@php echo json_encode($notices['data']) @endphp';
                data = JSON.parse(data);
            laypage.render({
                elem: 'swiPaging'
                ,count: '{{ $notices['total'] }}'
                ,groups: 2
                ,layout: ['count', 'prev', 'page', 'next']
                ,jump: function(obj){
                    document.getElementById('biuuu_city_list').innerHTML = function(){
                        var arr = []
                            ,thisData = data.concat().splice(obj.curr*obj.limit - obj.limit, obj.limit);

                        layui.each(thisData, function(index, item){
                            var icon;
                            if(item.important){
                                 icon = 'red';
                            }else{
                                 icon='';
                            }
                            arr.push('<li class="read-announcement '+icon+'" data-id="' + item.id +'"><a href="javascript:;">+ ' + item.title +'</a><div class="time">'+item.created_at+'</div></li>');

                        });
                        return arr.join('');
                    }();
                }
            });
            //立即处理
            $(document).on('click','.openIframe',function(){
                var _url = $(this).data('url');
                var _name = $(this).data('name');
                let dataId = $(this).attr('data-id');
                parent.pgout(_url,_name, dataId);
                showShortCutJumpMenu(dataId);
            });
            $(document).on('click','.read-announcement',function(){
                var _id = $(this).data('id');
                layer.open({
                    type: 2
                    ,title: '查看公告'
                    ,content: '{{route('base_info.announcement.editIndex')}}'+'?type=read&id='+_id
                    ,area: ['60%', '90%']
                    ,maxmin: true
                    ,yes: function(index, layero){
                        //点击确认触发 iframe 内容中的按钮提交
                        var submit = layero.find('iframe').contents().find("#LAY-front-submit");
                        submit.click();
                    }
                });

            });
        });


        var chart = Highcharts.chart('movement1', {
            chart: {
                type: 'areaspline'
            },
            title: false,
            xAxis: {
                categories: [
                    // '周一','周二','周三','周四','周五','周六','周日'
                    @if(isset($orderSummary))
                        @foreach($orderSummary['weekly'] as $key => $val)
                        '{{$currentMonth}}月{{$val['day']}}日',
                        @endforeach
                    @endif
                ],
                tickInterval: 1
            },
            yAxis: {
                title: false
            },
            tooltip: {
                shared: true,
                crosshairs: true,
                dateTimeLabelFormats: {
                    day: '%Y-%m-%d'
                }
            },
            plotOptions: {
                areaspline: {
                }
            },
            credits: {
                enabled: false
            },
            series: [{
                name: '近七天',
                data: [
                    @if(isset($orderSummary))
                        @foreach($orderSummary['weekly'] as $key => $val)
                        {{$val['quantity']}},
                        @endforeach
                    @endif
                ],

            }]
        });

        var chart = Highcharts.chart('movement2', {
            chart: {
                type: 'areaspline'
            },
            title: false,
            xAxis: {
                categories: [
                    // '周1','周二','周三','周四','周五','周六','周日'
                    @if(isset($orderSummary))
                        @foreach($orderSummary['monthly'] as $key => $val)
                            '{{$val['day']}}日',
                        @endforeach
                    @endif
                ],
                tickInterval: 1
            },
            yAxis: {
                title: false
            },
            tooltip: {
                shared: true,
                crosshairs: true,
                dateTimeLabelFormats: {
                    day: '%Y-%m-%d'
                }
            },
            plotOptions: {
                areaspline: {

                }
            },
            credits: {
                enabled: false
            },
            series: [{
                name: '{{date('Y年m月')}}',
                data: [
                    @if(isset($orderSummary))
                        @foreach($orderSummary['monthly'] as $key => $val)
                        {{$val['quantity']}},
                        @endforeach
                    @endif
                ],

            }]
        });

        var chart = Highcharts.chart('movement3', {
            chart: {
                type: 'areaspline'
            },
            title: false,
            xAxis: {
                categories: [
                    // '一月','二月','三月','四月','五月','六月','七月'
                    @if(isset($orderSummary))
                        @foreach($orderSummary['annually'] as $key => $val)
                        '{{$val['month']}}月',
                        @endforeach
                    @endif
                ],
                tickInterval: 1
            },
            yAxis: {
                title: false
            },
            tooltip: {
                shared: true,
                crosshairs: true,
                dateTimeLabelFormats: {
                    day: '%Y-%m-%d'
                }
            },
            plotOptions: {
                areaspline: {
                    stacking: 'normal',
                }
            },
            credits: {
                enabled: false
            },
            series: [{
                name: '{{date('Y年')}}',
                data: [
                    @if(isset($orderSummary))
                        @foreach($orderSummary['annually'] as $key => $val)
                        {{$val['quantity']}},
                        @endforeach
                    @endif
                ],

            }]
        });
    </script>



@endsection

