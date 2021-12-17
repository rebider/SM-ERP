<?php
namespace App\Console\Commands\GetInventory;
function curl()
{
    $req = file_get_contents('getInventoryByCustomerCode.xml');

    set_time_limit(0);
    $url = "http://202.104.134.94:7180/default/wind-control/web-service";
    
    // 代理服务器
    $proxy = '';
    // 请求

    $str = curlRequest($url, $req, $proxy);
    // 输出结果
 //   header("Content-type: text/xml; Charset=utf-8");

    print_r($str);
}
// -------------------------------------------------------------------------------------------------------------
function curlRequest($url, $postData = '', $proxy = "")
{
    $proxy = trim($proxy);
    $user_agent = "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)";
    $ch = curl_init(); // 初始化CURL 句柄
    if(! empty($proxy)){
        curl_setopt($ch, CURLOPT_PROXY, $proxy); // 设置代理服务器
    }
    curl_setopt($ch, CURLOPT_URL, $url); // 设置请求的URL
                                         // curl_setopt($ch,
                                         // CURLOPT_FAILONERROR, 1); //
                                         // 启用时显示HTTP 状态码，默认行为是忽略编号小于等于400
                                         // 的HTTP 信息
                                         // curl_setopt($ch,
                                         // CURLOPT_FOLLOWLOCATION,
                                         // 1);//启用时会将服务器服务器返回的“Location:”放在header
                                         // 中递归的返回给服务器
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // 设为TRUE
                                                 // 把curl_exec()结果转化为字串，而不是直接输出
    curl_setopt($ch, CURLOPT_POST, 1); // 启用POST 提交
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData); // 设置POST 提交的字符串
                                                     // curl_setopt($ch,
                                                     // CURLOPT_PORT, 80);
                                                     // //设置端口
    curl_setopt($ch, CURLOPT_TIMEOUT, 25); // 超时时间
    curl_setopt($ch, CURLOPT_USERAGENT, $user_agent); // HTTP 请求User-Agent:头
                                                      // curl_setopt($ch,CURLOPT_HEADER,1);//设为TRUE
                                                      // 在输出中包含头信息
                                                      // $fp =
                                                      // fopen("example_homepage.txt",
                                                      // "w");//输出文件
                                                      // curl_setopt($ch,
                                                      // CURLOPT_FILE,
                                                      // $fp);//设置输出文件的位置，值是一个资源类型，默认为STDOUT
                                                      // (浏览器)。
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Accept-Language: zh-cn',
        'Connection: Keep-Alive',
        'Cache-Control: no-cache',
        'Content-type: text/xml'
    )); // 设置HTTP 头信息

    $document = curl_exec($ch); // 执行预定义的CURL
    $info = curl_getinfo($ch); // 得到返回信息的特性
                              // prInt_r($info);
    curl_close($ch);
    if($info['http_code'] == "405"){
        $result['message'] = "bad proxy {$proxy}\n"; // 代理出错
        return $result;
    }

    return $document;
}

// 测试 start
curl();
//测试 end