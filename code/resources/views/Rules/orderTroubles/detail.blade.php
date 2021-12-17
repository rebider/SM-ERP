@extends('layouts/dialog')

@section('content')
    <div id="OrderRules">
        <form action="" id="layui-form" class="layui-form">
            <input type="hidden" name="_token" value="{{csrf_token()}}">
            <div class="ruleName">
                <ul class="flexlayer">
                    <li>
                        <div class="inptxt">规则名称</div>
                        <div class="inpblock">
                            <input lay-verify="required" class="layui-input" type="text" placeholder="请输入规则名称" name="trouble_rules_name" value="{{$data ['trouble_rules_name']}}">
                        </div>
                    </li>
                </ul>
            </div>
            <div class="ruleBody">
                <div class="setRule">
                    <div class="ruletitle"><h3>已设置规则</h3></div>
                    <div class="ruleSection">
                        <ul class="flexlayer">
                            @if(isset($conditions))
                                @foreach ($conditions as $condition)
                                    <li relid="{{$condition['relid']}}" id="{{$condition['sertid']}}">
                                    {!!$condition['cond_name']!!}
                                    </li>
                                @endforeach
                            @endif
                        </ul>
                    </div>
                </div>
                <div class="choseTab">
                    <div class="ruletitle">
                        <h3>选择条件</h3>
                    </div>
                    <div class="choselist liergodic">
                        @if (isset($rules))
                            @foreach($rules as $key => $rule)
                                <div class="row">
                                    <h4>@if ($key == 'orders')
                                            {{'订单来源'}}
                                        @elseif ($key == 'logistics')
                                            {{'物流信息'}}
                                        @elseif ($key == 'products')
                                            {{'商品信息'}}
                                        @elseif ($key == 'deliver')
                                            {{'发货信息'}}
                                        @else
                                        @endif
                                    </h4>
                                    <ul class="list">
                                        @foreach($rule as $value)
                                            <li>
                                                <input type="checkbox" lay-skin="primary" @if(in_array($value['id'],$conditionIds)) checked @endif title="<div class='nm'>{{$value['condition_prefix']}}</div><b class='ws'>{{$value['condition_name']}}</b>"/>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
            <div class="writelist">
                <ul class="flexlayer">
                    <li>
                        <div class="inptxt">问题类型</div>
                        <div class="inpblock">
                            <select name="trouble_type_id" id="" lay-verify="required">
                                <option value="">请选择</option>
                                @if (isset($troubles))
                                    @foreach($troubles as $problem)
                                        <option value="{{$problem['id']}}" @if($problem['id'] == $data ['trouble_type_id']) selected @endif>{{$problem['trouble_type_name']}}</option>
                                    @endforeach
                                @endif
                            </select></div>
                    </li>
                    <li>
                        <div class="inptxt">问题描述</div>
                        <div class="inpblock"><input class="layui-input" type="text" name="trouble_desc" value="{{$data['trouble_desc']}}" lay-verify="required"/></div>
                    </li>
                    <li>
                        <div class="inptxt">是否启用</div>
                        <div class="inpblock">
                            <input type="radio" name="opening_status" value="1" lay-skin="primary" title="是" @if($data ['opening_status'] == 1) checked @endif/>
                            <input type="radio" name="opening_status" value="2" lay-skin="primary" title="否" @if($data ['opening_status'] == 2) checked @endif/>
                        </div>
                    </li>
                </ul>
            </div>
        </form>
    </div>
@endsection

@section('javascripts')
    <script>
        //layui加载

        layui.config({ base: '../../layui/lay/modules/'}).extend({ formSelects: 'formSelects-v3'});
        layui.use(['layer','form','element','laydate','table','formSelects'], function(){
            var layer = layui.layer,form = layui.form,laydate = layui.laydate,table = layui.table,element = layui.element,formSelects = layui.formSelects,laypage = layui.laypage;

        });

        $("body").bind("keydown",function(event){
            if (event.keyCode == 116) {
                event.preventDefault(); //阻止默认刷新
                location=location;
            }
        })
    </script>
@endsection