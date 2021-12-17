<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <title>500</title>
    <link rel="stylesheet" href="{{ asset('layui/layui.css') }}">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}" >
</head>
<body>
<div class="sectionBody">
    <div class="RDT-error">
        <div class="error-Main">
            <div class="er_icon">
                <img src="{{ asset('images/500.png') }}" />
            </div>
            <div class="error-text">
                <div class="holder">
                    <h3><em>500</em> 服务器内部错误</h3>
                    <div class="error-Btn">
                        @if(substr(url()->previous(), -1) != '/')
                            <a href="javascript:;" onclick="back('{{ url()->previous() }}')">返回上一页</a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
<script>
    //返回上一页
    function back(url) {
        location.href = url;
    }
</script>
</html>