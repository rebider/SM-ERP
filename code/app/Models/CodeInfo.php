<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use League\Flysystem\Exception;

/*
 * 获取一个编码
 * */
class CodeInfo extends Model
{
    protected $table = "code_info" ;

    public $timestamps = true;

    public $primaryKey = 'id';

    public $fillable = ['id','created_man','code_type','prefix_value','current_index','code_length','created_at','updated_at'];

    /**
     * @var CW_ERP_订单
     */
    const CW_ORDERS_CODE = 1;//订单：S+时间流水号

    /**
     * @var 配货单
     */
    const INVOICE_CODE = 2;//配货单号生成规则：SM订单号-1、SM订单号-2…….
    const PREFIX_INVOICE_CODE = 'SM';//配货单号生成规则：SM订单号-1、SM订单号-2…….

    /**
     * @var 售后单
     */
    const AFTER_SALES_CODE = 3; //售后单号的生成规则：RMA+订单号-1、RMA-订单号-2；
    //售后生成新订单号的生成规则：售后单号-a；
    const PREFIX_AFTER_SALES_CODE = 'RMA';
    /**
     * @var 采购单
     */
    const ALTERNATIVE_CODE = 3; //补充前缀
    const PREFIX_ALTERNATIVE_CODE = '';
    /**
     * @var 付款单
     */
    const PAYMENTS_CODE = 4; //付款单号 订单号-1、-2、-3
    const PREFIX_PAYMENTS_CODE = '';

    /**
     * @param $codeType
     * @param bool $is_transaction
     * @return string
     * @throws Exception
     * Note: 获取一个单号
     * Data: 2019/3/7 14:01
     * Author: zt7785
     */
    public static function getACode($codeType,$orderCode = '',$nums = '')
    {
        if ($codeType == self::CW_ORDERS_CODE) {
            $codeInfo = self::where('code_type' ,$codeType)->first();
            $code = (empty($codeInfo->prefix_value) ? '' : $codeInfo->prefix_value) . str_pad($codeInfo->current_index,$codeInfo->code_length,date('Ymdhis'),STR_PAD_LEFT);
            $codeInfo->current_index++;
            $codeInfo->save();
        } else if ($codeType == self::INVOICE_CODE) {
            //配货单
            return  self::PREFIX_INVOICE_CODE. ltrim($orderCode, 'S') . '-' . $nums;
        } else if ($codeType == self::AFTER_SALES_CODE) {
            //售后单
            return  self::PREFIX_AFTER_SALES_CODE. ltrim($orderCode, 'S') . '-' . $nums;
        } else if ($codeType == self::PAYMENTS_CODE) {
            //付款单
            return  self::PREFIX_PAYMENTS_CODE. ltrim($orderCode, 'S') . '-' . $nums;
        } else if ($codeType == self::ALTERNATIVE_CODE) {
            //采购单

        }
        return $code;
    }




}