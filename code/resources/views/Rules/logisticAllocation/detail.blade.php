@extends('layouts/dialog')
@section('content')
    <style>
        body,body{min-width: unset !important;padding: 3%;}
        .ruleBody{height: 270px !important;}
        .ruleBody .ruleSection{height:239px !important;}
        .ruleBody .choseTab{height: 270px;}
        .appLogis .logisname{height: 245px !important;}
        .appLogis .logisname .col{height: 202px !important;}
        .ruleBody .choselist{height: 239px !important;}
    </style>
    <div id="OrderRules">
        <form action="" id="layui-form" class="layui-form">
            <input type="hidden" name="_token" value="{{csrf_token()}}">
            <div class="ruleName">
                <ul class="flexlayer">
                    <li>
                        <div class="inptxt"><b style="color: red;">* </b> 规则名称：</div>
                        <div class="inpblock">
                            <input lay-verify="required" maxlength="300" class="layui-input" type="text" placeholder="请输入规则名称" name="trouble_rules_name" value="{{$data ['trouble_rules_name']}}">
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
                                            @if(in_array($value['id'],[13]))
                                                <li style="display: none;">
                                            @else
                                                <li>
                                            @endif
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
                <div class="appLogis">
                    <div class="logisname logis_list">
                        <h3>选择物流</h3>
                        <div class="col">
                            @if (isset($logistics))
                                @foreach($logistics as $logistic)
                                    <span data-id="{{$logistic['id']}}">{{$logistic['logistic_name']}}</span>
                                @endforeach
                            @endif
                        </div>
                    </div>
                    <div class="logis_toFro">
                        <div class="kpl"><span class="logTo kbico"></span><span class="logfro kbico"></span></div>
                    </div>
                    <div class="logisname logis_settle">
                        <h3>发货物流（优先选择上面物流）</h3>
                        <div class="col">
                            @if(!empty($selectLogistics))
                                @foreach($selectLogistics as $selectItem)
                                    <span data-id="{{$selectItem['id']}}"> {{$selectItem['logistic_name']}}</span>
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label" style="width: 100px">是否启用：</label>
                    <div class="layui-input-block">
                        <input type="radio" name="opening_status" value="1" lay-skin="primary"  @if($data ['opening_status'] == 1) checked @endif title="是"/>
                        <input type="radio" name="opening_status" value="2" lay-skin="primary" @if($data ['opening_status'] == 2) checked @endif title="否"/>
                    </div>
                </div>
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