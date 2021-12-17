<div class="submenu-left" style="overflow: visible;z-index: 2;top: 60px;display: none">
{{--    @if(isset($shortcutMenus))--}}
{{--        @foreach($shortcutMenus as $shortcutMenu)--}}
{{--            <div class="col">--}}
{{--                <h2>{{$shortcutMenu['name']}}</h2>--}}
{{--                <ul>--}}
{{--                    @if(isset($shortcutMenu['_child']))--}}
{{--                        @foreach($shortcutMenu['_child'] as $childKey => $child)--}}
{{--                            <li>--}}
{{--                                @if(!empty($child['url']))--}}
{{--                                    @if(!is_bool(strpos(Request::path(),$child ['url'])))--}}
{{--                                        <a href="javascript:void(0);">{{$child['name']}}--}}
{{--                                    @else--}}
{{--                                        <a href="javascript:pgout('{{$child['url']}}','{{$child['name']}}');">{{$child['name']}}--}}
{{--                                    @endif--}}
{{--                                @else--}}
{{--                                    <a href="javascript:pgout('{{$child['url']}}','{{$child['name']}}');">{{$child['name']}}--}}
{{--                                @endif--}}
{{--                                @if(isset($child['count']))({{$child['count']}})@endif--}}
{{--                                                        </a>--}}
{{--                                                </a>--}}
{{--                                        </a>--}}
{{--                            </li>--}}
{{--                        @endforeach--}}
{{--                    @endif--}}
{{--                </ul>--}}
{{--            </div>--}}
{{--        @endforeach--}}
{{--    @endif--}}
</div>