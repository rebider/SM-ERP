$(function () {
    //判断是否在iframe中
    if(self != top) {
        window.top.location = '/';
    }

    //显示验证码
    $("#userCode").blur(function () {
            if($("#conVerifyCode").is(":hidden")){
                var userCode = $("#userCode").val();
                if(userCode != ''){
                    $.ajax({
                        url:"/auth/login/requiredVerifyCode",
                        dataType:"json",
                        type:"GET",
                        data:{userCode:userCode},
                        success:function (r) {
                            if(r.Data){
                                $("#conVerifyCode").show();
                                refreshVCode();
                            }
                        }
                    });
                }
            }
        }
    );
});

//刷新验证码
function refreshVCode() {
    $("#VerifyCodeImg").attr("src", "/auth/verifyCode?r=" + Math.random());
}