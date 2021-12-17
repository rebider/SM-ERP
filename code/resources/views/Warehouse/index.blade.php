@extends('layouts/main')


@section('content')
    <div class="sectionBody">
        <div class="location">
    		<span class="layui-breadcrumb">
			</span>
        </div>
        <div class="flexshrink edn-row">
            <div class="log_Status">
                <div class="Item EDbar">
                    <div class="itemIcon con1"></div>
                    <div class="infor"><h4>待接收工单<em>{{$receive['receive_count']}}</em></h4><p>点击“查看全部”可以快速接收所有工单！</p></div>
                    <div class="log_btn"><a href="{{ url('Warehouse/ware_receive') }}" class="layui-btn layui-btn-normal">查看全部</a></div>
                </div>
                <div class="Item EDbar">
                    <div class="itemIcon con2"></div>
                    <div class="infor"><h4>紧急工单<em>{{$reply['urgent_count']}}</em></h4><p>紧急工单建议优先解决！点击界面选卡可以快速查看工单！</p></div>
                    <div class="log_btn"><a href="{{ url('Warehouse/ware_reply/3') }}" class="layui-btn layui-btn-normal">查看全部</a></div>
                </div>
                <div class="Item EDbar">
                    <div class="itemIcon con3"></div>
                    <div class="infor"><h4>重要工单<em>{{$reply['important_count']}}</em></h4><p>重要工单建议尽快回复！</p></div>
                    <div class="log_btn"><a href="{{ url('Warehouse/ware_reply/2') }}" class="layui-btn layui-btn-normal">查看全部</a></div>
                </div>
                <div class="Item EDbar">
                    <div class="itemIcon con4"></div>
                    <div class="infor"><h4>普通工单<em>{{$reply['general_count']}}</em></h4><p>普通工单别忘记处理哟！</p></div>
                    <div class="log_btn"><a href="{{ url('Warehouse/ware_reply/1') }}" class="layui-btn layui-btn-normal">查看全部</a></div>
                </div>
            </div>
            <div class="EDReply">
                @if(empty($receive['receive_info']))
                    <div class="NoEffect"><div class="nullSM"><img src="../images/null.png" alt="" /><p>暂无工单</p></div></div>
                @else
                    @foreach($receive['receive_info'] as $re)
                        <div class="EDbar replyItem">
                            <a href="/?url={{ 'Warehouse/ware_detail/'.$re->customer_complaint_id }}" target="_blank">
                                <div class="woNum"><h4>工单：{{$re->order_number}}</h4></div>
                                <div class="woReply">咨询：{{$re->consult_content}}</div>
                            </a>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    </div>
@endsection
@section('javascripts')
    <script>

        layui.use(['layer','form','element'], function(){
            var layer = layui.layer,form = layui.form,element = layui.element;

        })
    </script>
@endsection