<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>邮箱确认链接</title>
</head>
<body>
<table cellpadding="0" cellspacing="0" border="0" style="width:710px !important;line-height:22px; margin-left:10px; table-layout:fixed; color:#000;" class="email_tab" align="left">
    <tbody>
    <tr><td height="45"></td></tr>
    <tr><td style="width:710px !important;">
            <h1 style="font-size:14px; font-weight:bold; text-align:center;">验证邮箱</h1>
            <p style="padding:10px 0 !important; margin:0;">亲爱的{{$username}}，您好。</p>
            <p style="padding:10px 0 !important; margin:0;">您的账户邮箱:<strong><span style="border-bottom: 1px dashed rgb(204, 204, 204); z-index: 1; position: static;" t="7" onclick="return false;">{{$email}}</span></strong>正在进行的操作需要验证您的联系邮箱真实有效。验证成功后，相关结果会通过邮件的方式发送到本邮箱。</p>
            <p style="padding:10px 0 !important; margin:0;"><a href="{{$url}}" target="_blank" rel="noopener" class="detail"><strong>点击这里，立即验证此邮箱</strong></a></p>
            <p class="link" style="color:#999; font-size:12px; line-height:18px; width:704px !important;table-layout:fixed; margin:0; word-break:break-all; padding:10px 0 !important;">如果您点击上述链接无效，请将下面的链接复制到浏览器地址栏中访问：<br><a href="{{$url}}" rel="noopener" target="_blank">{{$url}}</a></p>
        </td></tr>
    <tr><td height="15"></td></tr>
    <tr><td>
            <p class="tenpay_foot" style="font-size:14px;">速贸天下云仓平台
                <br>
                <span style="color:#999; font-size:12px;">此邮件由速贸天下云仓平台系统发出，请勿回复</span></p>
        </td>
    </tr>
    <tr><td height="40"> </td></tr>
    </tbody></table>
</body>
</html>