<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Maatwebsite\Excel\Excel;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected static $data = [];

    /**
     * @param $data
     * Note: lay aJax响应数据格式
     * Data: 2019/3/11 15:54
     * Author: zt7785
     */
    public function layResponseData ($data) {
        //错误码
        $responseData ['code'] = isset($data['code']) ? $data['code'] : 0;
        //提示信息
        $responseData ['msg'] = isset($data['msg']) ? $data['msg'] : '';
        //记录条数
        $responseData ['count'] = isset($data['count']) ? $data['count'] : 0;
        //响应数据
        $responseData ['data'] =  isset($data['data']) ? $data['data'] : [];
        //响应错误数据
        $responseData ['errAll'] =  isset($data['errAll']) ? $data['errAll'] : [];
        return Response()->json($responseData);
    }

    /**
     * @param Excel $excel
     * @param $cellData
     * @param string $file_name
     * @param bool $is_color
     * Data: 2019/3/23 13:30
     */
    public function export(Excel $excel,$cellData,$file_name = 'XX表',$is_color = false ,$is_order = false)
    {
        $file_name = date('Y-m-d').'-'.$file_name;
        $excel->create(iconv("utf-8",'gbk',$file_name), function ($excel) use ($cellData,$is_color,$is_order) {
            $excel->sheet('score', function ($sheet) use ($cellData,$is_color,$is_order) {
                $cellDataLen = count($cellData) ;
                for($i=1 ;$i<=$cellDataLen ;$i++){
                    if ($i !== 1){
                        $sheet->cells($i, function($cells) {
                            $cells->setAlignment('left');

                        });
                    }else{
                        $sheet->cells($i, function($cells) use ($is_color){
                            if (empty($is_color)) {
                                $cells->setFontColor('#ff0000');
                                $cells->setFont(array(
                                    'family'     => 'Calibri',
                                    'size'       => '13',
                                    'bold'       =>  true
                                ));
                                $cells->setAlignment('center');
                            } else {
                                $cells->setFont(array(
                                    'family'     => 'Calibri',
                                    'size'       => '13',
                                    'bold'       =>  true
                                ));
                                $cells->setAlignment('center');
                            }
                        });
                    }
                    $sheet->row($i,$cellData[$i-1]);
                }
                $sheet->setHeight(array(
                    1     =>  25,
                ));
                if ($is_order) {
                    $width = array(
                        'A'     =>  15,
                        'B'     =>  20,
                        'C'     =>  20,
                        'D'     =>  20,
                        'E'     =>  20,
                        'F'     =>  20,
                        'G'     =>  20,
                        'H'     =>  20,
                        'I'     =>  20,
                        'J'     =>  20,
                        'K'     =>  20,
                        'L'     =>  20,
                        'M'     =>  20,
                        'N'     =>  20,
                        'O'     =>  20,
                        'P'     =>  20,
                        'Q'     =>  20,
                        'R'     =>  20,
                        'S'     =>  20,
                        'T'     =>  20,
                        'U'     =>  20,
                        'V'     =>  20,
                        'W'     =>  20,
                        'X'     =>  20,
                        'Y'     =>  20,
                        'Z'     =>  20,
                        'AA'     =>  20,
                        'AB'     =>  20,
                    );
                } else {
                    $width = array(
                        'A'     =>  15,
                        'B'     =>  20,
                        'C'     =>  20,
                        'D'     =>  20,
                        'E'     =>  20,
                        'F'     =>  20,
                        'G'     =>  20,
                        'H'     =>  20,
                        'I'     =>  20,
                        'J'     =>  20,
                        'K'     =>  20,
                        'L'     =>  20,
                        'M'     =>  20,
                        'N'     =>  20,
                        'O'     =>  20,
                        'P'     =>  20,
                        'Q'     =>  20,
                        'R'     =>  20,
                        'S'     =>  20,
                        'T'     =>  20,
                    );
                }
                $sheet->setWidth($width);
            });
        })->store('xls')->export('xls');
    }


    /**
    * 上传文件
    * $data['status'] : 1,上传成功; 2，没找到文件; 3,文件格式错误 ; 4,文件上传失败;
    * $data['message'] : 返回信息
    * $data['path'] : 返回文件路径
     */
    public function postFileupload($request, $url, $ext = ['png', 'jpg', 'jpeg'], $key = 'up_pci')
    {
        //HTTP上传文件的开关，默认为ON即是开
        //ini_set('file_uploads','ON');
        //通过POST、GET以及PUT方式接收数据时间进行限制为90秒 默认值：60
        //ini_set('max_input_time','90');
        //脚本执行时间就由默认的30秒变为180秒
        //ini_set('max_execution_time', '180');
        //Post变量由2M修改为8M，此值改为比upload_max_filesize要大
        //ini_set('post_max_size', '2M');
        //上传文件修改也为8M，和上面这个有点关系，大小不等的关系。
        //ini_set('upload_max_filesize','2M');
        //正在运行的脚本大量使用系统可用内存,上传图片给多点，最好比post_max_size大1.5倍
        //ini_set('memory_limit','2M');

        $data = [
            'status' => 1,
            'message' => '上传成功！'
        ];

        //判断请求中是否包含name=file的上传文件
        if (!$request->hasFile($request->input('files'))) {
            $data = [
                'status' => 0,
                'message' => '文件不能为空！'
            ];
            return $data;
        }

        $file = $request->file($key);
        //判断文件上传过程中是否出错
        if (!$file->isValid()) {
            return $data = [
                'status' => 2,
                'message' => '没有找到该文件！'
            ];
        }
        $date = date('Y-m-d', time());
        $destPath = public_path('uploads/' . $url . '/' . $date);

        if (!file_exists($destPath)) {
            mkdir($destPath, 0755, true);
        }

        $name = iconv('utf-8', 'gbk', $file->getClientOriginalName()); //转码
        $this_ext = strtolower(substr(strrchr($name, '.'), 1));
        if (!in_array($this_ext, $ext)) {
            return $data = [
                'status' => 3,
                'message' => '文件格式错误！'
            ];
        }
        $name = rand(0, 9999) . time() . '.' . $this_ext;
        $filename = rand(0, 9999) . $name;

        if (!$file->move($destPath, $filename)) {
            return $data = [
                'status' => 4,
                'message' => '文件上传失败！'
            ];
        }
        $filename = iconv('gbk', 'utf-8', $filename); //转码
        $data['path'] = $destPath . "/" . $filename;
        $data['route'] = "/uploads/" . $url . "/" . $date . "/$filename";
        $data['true_name'] = $file->getClientOriginalName();
        return $data;
    }

    /*
     * @ $excel  object
     * @ $filePath string
     * @ $data array()
    * */
    public function import(Excel $excel, $filePath)
    {
        $excel->load($filePath, function ($reader) {
            //获取excel的第几张表
            $reader = $reader->getSheet(0);
            //获取表中的数据
            self::$data = $reader->toArray();

        });

        return self::$data;

    }

}
