
@extends('layouts/new_dialog')
@section ('css')
    <link href="{{asset('css/menus.css')}}" rel="stylesheet">
@endsection
@section('content')
    <div class="openadvisory">
        <form action="" method="post" class="layui-form" id="myForm">
            {{ csrf_field() }}
                @if(isset($menus))
                    @foreach($menus as  $menuVal)
                        <div class="colCheckbox checkboxGroup {{$menuVal['id']}}_checkboxGroup">
                            <div class="boxall" style="background: lightgrey">
                                <h3>
                                    <input type="checkbox" lay-filter="allsele" class="{{$menuVal['id']}}_selectAll" lay-skin="primary" title="{{$menuVal['name']}}" name="checkPar[]" value="{{$menuVal['id']}}" @if (in_array($menuVal['id'],$permission)) checked @endif data-parid="{{$menuVal['parent_id']}}"/>
                                </h3>
                            </div>
                            @if (isset($menuVal ['_child']))
                                <div class="kb_chbox chebox">
                                    @foreach($menuVal ['_child'] as $menu)
                                        <div class="lip">
                                            <input type="checkbox" class="chekid" name="check[]" lay-filter="oneCho"
                                                   lay-skin="primary" title="{{$menu['name']}}" value="{{$menu['id']}}" data-type="2" data-name="menu_id[]" data-menu="{{$menuVal['id']}}" @if (in_array($menu['id'],$permission)) checked @endif data-parid="{{$menuVal['parent_id']}}"/>
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
    <script type="text/javascript">
        $(document).ready(function () {
            $(document.body).css('cssText', "width:900px !important;min-width: 0!important;margin 2rem");
            $(document.body).animate({'width': '92%', 'margin': '2rem'}, 100)
        })
    </script>
@endsection