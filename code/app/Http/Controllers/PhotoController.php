<?php
/**
 * Created by PhpStorm.
 * User: jungle
 * Date: 2018/9/19
 * Time: 11:47
 */

namespace App\Http\Controllers;

use App\Models\GoodsLocalPic;
use Illuminate\Http\Request ;
use Illuminate\Support\Facades\Storage ;

class PhotoController
{

    public function __construct()
    {

    }

    public function imageStorageRoute(Request $request)
    {
        //获取当前的url
        $realpath = str_replace('imgsys','',$request->path());
        $path = storage_path() . $realpath;

        if(!file_exists($path)){
            //报404错误
            header("HTTP/1.1 404 Not Found");
            header("Status: 404 Not Found"); exit;
        }
        //输出图片
        header('Content-type: image/jpg');

        echo file_get_contents($path);
    }

    /**
     * 查看图片
     * @param Request $request
     */
    public function showImage(Request $request)
    {
        $realpath = $request->get('path');
        if(!Storage::disk('upload_imgs')->exists($realpath)){
            //报404错误
            header("HTTP/1.1 404 Not Found");
            header("Status: 404 Not Found"); exit;
        }
        //输出图片
        header('Content-type: image/jpg');
        echo Storage::disk('upload_imgs')->get ($realpath);// 获取文件内容;
    }

    /**
     * @desc
     * @author zt6650
     * CreateTime: 2019-04-10 16:51
     * @param Request $request
     * @return array
     */
    public function upload(Request $request){
        if ($request->isMethod('POST')) {
            //判断是否是POST上传
            //在源生的php代码中是使用$_FILE来查看上传文件的属性
            //但是在laravel里面有更好的封装好的方法，就是下面这个
            //显示的属性更多
            $fileCharater = $request->file('file' ,'file');
            if ($fileCharater->isValid()) { //括号里面的是必须加的哦
                //如果括号里面的不加上的话，下面的方法也无法调用的

                //获取文件的扩展名
                $ext = $fileCharater->getClientOriginalExtension();

                //获取文件的绝对路径
                $path = $fileCharater->getRealPath();

                //定义文件名
                $localName = date('Y-m-d-h-i-s').'--'.rand(1000,9999).rand(1000,9999) ;
                $filename = $localName.'.'.$ext;

                //存储文件。disk里面的public。总的来说，就是调用disk模块里的public配置
                Storage::disk('public')->put($filename, file_get_contents($path));

                return [
                    "code" => 200,
                    "msg" => "",
                    'fileName'=> $localName ,
                    "data" => [
                        "src" => 'public'.'/'.$filename
                    ]
                ];
            }


            return [
                "code" => 400,
                "msg" => "",
                "data" => [
                    "src" => ''
                ]
            ];
        }

        return [
            "code" => 400,
            "msg" => "",
            "data" => [
                "src" => ''
            ]
        ];
    }

    /**
     * @note
     * 乐天图片上传 名字不能有特殊符号
     * @since: 2019/6/19
     * @author: zt7837
     * @return: array
     */
    public function rakutenUpload(Request $request){
        if ($request->isMethod('POST')) {
            //判断是否是POST上传
            //在源生的php代码中是使用$_FILE来查看上传文件的属性
            //但是在laravel里面有更好的封装好的方法，就是下面这个
            //显示的属性更多
            $fileCharater = $request->file('file' ,'file');
            if ($fileCharater->isValid()) { //括号里面的是必须加的哦
                //如果括号里面的不加上的话，下面的方法也无法调用的

                //获取文件的扩展名
                $ext = $fileCharater->getClientOriginalExtension();

                //获取文件的绝对路径
                $path = $fileCharater->getRealPath();

                //定义文件名
                $localName = date('Ymdhis').rand(1000,9999) ;
                $filename = $localName.'.'.$ext;

                //存储文件。disk里面的public。总的来说，就是调用disk模块里的public配置
                Storage::disk('public')->put($filename, file_get_contents($path));

                return [
                    "code" => 200,
                    "msg" => "",
                    'fileName'=> $localName ,
                    "data" => [
                        "src" => 'public'.'/'.$filename
                    ]
                ];
            }


            return [
                "code" => 400,
                "msg" => "",
                "data" => [
                    "src" => ''
                ]
            ];
        }

        return [
            "code" => 400,
            "msg" => "",
            "data" => [
                "src" => ''
            ]
        ];
    }

    /**
     * Created by PhpStorm.
     * User: yuwei
     * Date: 2019/03/29
     * Time: 15:24
     * DESC：富文本上传方法
     */

    public function editorUpload(Request $request){
        $results = ['code'=>0,'msg'=>'','data'=>''];
        if ($request->isMethod('POST')) {
            $fileCharater = $request->file('file' ,'file');
            if ($fileCharater->isValid()) {
                //获取文件的扩展名
                $ext = $fileCharater->getClientOriginalExtension();

                //获取文件的绝对路径
                $path = $fileCharater->getRealPath();

                //定义文件名
                $localName = date('Y-m-d-h-i-s').'--'.rand(1000,9999).rand(1000,9999) ;
                $filename = $localName.'.'.$ext;

                //存储文件。disk里面的public。总的来说，就是调用disk模块里的public配置
                Storage::disk('public')->put($filename, file_get_contents($path));

                $results = [
                    "code" => 0,
                    "msg" => "",
                    'fileName'=> $localName ,
                    "data" => [
                        "src" => '../../storage/public/'.$filename,
                        "title" => $localName,
                    ]
                ];
            }else{
                $results = [
                    "code" => 400,
                    "msg" => "",
                    "data" => [
                        "src" => ''
                    ]
                ];
            }
        }else{
            $results = [
                "code" => 400,
                "msg" => "",
                "data" => [
                    "src" => ''
                ]
            ];
        }
        return response()->json($results);

    }

    /**
     * @description 完美实现下载远程图片保存到本地
     * @author zt6650
     * @creteTime 2019/3/13 17:24
     * @param $url 保存文件目录,保存文件名称，使用的下载方式 当保存文件名称为空时则使用远程文件原来的名称
     * @param string $save_dir
     * @param string $filename
     * @param int $type
     * @return array
     */
    function getImage($url,$save_dir='public/collect',$filename='',$type=0){
        $storage = storage_path(config('app.upload_img_path'));
        if(trim($url)==''){
            return array('file_name'=>'','save_path'=>'','error'=>1);
        }
        if(trim($save_dir)==''){
            $save_dir='./';
        }
        if(trim($filename)==''){//保存文件名
            $ext=strrchr($url,'.');
            if($ext!='.gif'&&$ext!='.jpg' && $ext != '.png'){
                return array('file_name'=>'','save_path'=>'','error'=>3);
            }
            $filename=rand(1000 ,9999).time().$ext; //随机命名文件
        }
        if(0!==strrpos($save_dir,'/')){
            $save_dir.='/';
        }
        $orig_save_dir = $save_dir;
        $save_dir = $storage.$save_dir.DIRECTORY_SEPARATOR;
        //创建保存目录
        if(!file_exists($save_dir)&&!mkdir($save_dir,0777,true)){
            return array('file_name'=>'','save_path'=>'','error'=>5);
        }
        //获取远程文件所采用的方法
        if($type){
            $ch=curl_init();
            $timeout=5;
            curl_setopt($ch,CURLOPT_URL,$url);
            curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
            curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);
            $img=curl_exec($ch);
            curl_close($ch);
        }else{
            ob_start();
            readfile($url);
            $img=ob_get_contents();
            ob_end_clean();
        }
        //$size=strlen($img);
        //文件大小
        $fp2=@fopen($save_dir.$filename,'a');
        fwrite($fp2,$img);
        fclose($fp2);
        unset($img,$url);
//        dd(array('file_name'=>$filename,'save_path'=>$save_dir.$filename,'error'=>0)) ;
        return array('file_name'=>$filename,'save_path'=>$orig_save_dir.$filename,'error'=>0);
    }
}