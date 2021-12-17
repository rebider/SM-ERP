@if(Session::has('success'))
    <div class="alert alert-success">
        {{--<button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>--}}
        {{  Session::get('success') }}
        <script>
            //定时器 异步运行
            function CloseDiv(){
                var x=document.getElementsByClassName('alert alert-success');
                var i;
                for (i = 0; i < x.length; i++) {
                    x[i].style.display="none";
                }
                window.clearTimeout(t1);//去掉定时器
            }
            //使用方法名字执行方法
            var t1 = window.setTimeout(CloseDiv,3000);

        </script></div>
@endif
@if(Session::has('error'))

    <div class="alert alert-warning">
        {{--<button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>--}}
        {{  Session::get('error') }}
        <script>
            //定时器 异步运行
            function CloseDiv(){
                var x=document.getElementsByClassName('alert alert-warning');
                var i;
                for (i = 0; i < x.length; i++) {
                    x[i].style.display="none";
                }
                window.clearTimeout(t1);//去掉定时器
            }
            //使用方法名字执行方法
            var t1 = window.setTimeout(CloseDiv,3000);

        </script></div>
@endif
@if (isset($errors))
@if (count($errors) > 0)
    <div class="alert alert-danger alert-dismissable layui-bg-orange">
        <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
@endif
@if(isset($msg))
    <div class="alert alert-success"  align="center">{{$msg}}</div>
    <script>
        //定时器 异步运行
        function CloseDiv(){
            var x=document.getElementsByClassName('alert alert-success');
            var i;
            for (i = 0; i < x.length; i++) {
                x[i].style.display="none";
            }
            window.clearTimeout(t1);//去掉定时器
        }
        //使用方法名字执行方法
        var t1 = window.setTimeout(CloseDiv,3000);

    </script>
@endif