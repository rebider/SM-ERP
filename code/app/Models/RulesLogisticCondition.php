<?php

    namespace App\Models;

    use App\Common\Common;
    use Illuminate\Database\Eloquent\Model;

    /**
     * Class RulesTroubleCondition
     * Notes: 订单规则问题 详情表
     * @package App\Models
     * Data: 2019/3/14 11:31
     * Author: zt7785
     */
    class RulesLogisticCondition extends Model
    {
        protected $table = 'rules_logistic_condition';

        public $timestamps = true;

        public $primaryKey = 'id';

        public $fillable = ['id','created_man','user_id','trouble_rule_id','condition_id','is_used','condition_prefix','condition_name','cond_val','cond_unit','cond_name','cond_type','operator','filed_val','input_field','condition_sql','related','relid','sertid','created_at','updated_at'];


        /**
         * @var 开启
         */
        const STATUS_OPENING = 1;
        /**
         * @var 关闭
         */
        const STATUS_CLOSEDING = 0;

        /**
         * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
         * Note: 规则条件 基础设置表
         * Data: 2019/3/14 11:41
         * Author: zt7785
         */
        public function RulesLogisticCondition() {
            return $this->belongsTo(RulesLogisticCondition::class, 'condition_id', 'id');
        }

        /**
         * @param $datas
         * @param $conditionDatas
         * @return array troubleData:订单问题主表数据  conditionData:条件详情
         * Note: 添加规则逻辑
         * Data: 2019/3/20 11:03
         * Author: zt7785
         */
        public static function troubleDatasLogics($datas,$conditionDatas,$userInfo) {
            $user_id = $userInfo ['user_id'];//父 用户id
            $created_man = $userInfo ['created_man'];//created_man 用户id
            //S0. 数据处理
            $troubleData = [];
            //问题主表数据
            $troubleData ['created_man'] = $created_man;
            $troubleData ['user_id'] = $user_id;
            $troubleData ['ids'] = $user_id;
            $troubleField = ['trouble_rules_name','trouble_desc','trouble_type_id','opening_status','logistic_ids'];
            //in 查询语句
            $inQueryTypeArr = [1,2,3,8,12,13];
            //NOT IN 排除邮编
            $notInQueryTypeArr = [14];
            //邮编
            $postCodeArr = [4];
            //sku
            $skuArr = [9];
            //带运算符语句
            $conversionArr = [7,15];
            //js定义的relid id
            $relidArr = array_column($conditionDatas,'relid');
            $fieldArr = array_column($conditionDatas,'input_field');
            $conditionData = [];
            foreach ($datas as $dataKey => $dataVal) {
                $conversionStatus = false;
                //1 key 判断
                if (in_array($dataKey,$troubleField)) {
                    $troubleData [$dataKey] = $dataVal;
                    continue;
                }
                $conditionFieldKey = array_search($dataKey,$fieldArr);
                $conditionRelidKey = array_search($dataKey,$relidArr);
                if (!is_bool($conditionRelidKey)) {
                    $conditionData [$conditionDatas [$conditionRelidKey] ['id']] ['cond_name'] = $dataVal;
                    $conditionData [$conditionDatas [$conditionRelidKey] ['id']] ['relid'] = $dataKey;
                    continue;
                }
                if (is_bool($conditionFieldKey)) {
                    continue;
                }
                $conditionKey = is_bool($conditionFieldKey) ? $conditionRelidKey : $conditionFieldKey;
                $condition_id = $conditionDatas [$conditionKey] ['id'];
                $conditionData [$condition_id] ['condition_id'] = $condition_id;
                $conditionData [$condition_id] ['created_man'] = $created_man;
                $conditionData [$condition_id] ['user_id'] = $user_id;
                //条件前缀
                $conditionData [$condition_id] ['condition_prefix'] = $conditionDatas [$conditionKey] ['condition_prefix'];
                //条件名
                $conditionData [$condition_id] ['condition_name'] = $conditionDatas [$conditionKey] ['condition_name'];
                //关联模型 用于联表
                $conditionData [$condition_id] ['related'] = $conditionDatas [$conditionKey] ['related'];
                //字段名 渲染
                $conditionData [$condition_id] ['input_field'] = $conditionDatas [$conditionKey] ['input_field'];
                //li id 渲染 事件
                $conditionData [$condition_id] ['sertid'] = $conditionDatas [$conditionKey] ['sertid'];
                //开启关联状态
                $conditionData [$condition_id] ['is_used'] = self::STATUS_OPENING;

                //2 val 判断
                if (in_array($condition_id,$inQueryTypeArr)) {
                    //in 查询 val 拼接
                    //                $dataVal = self::arrayValToStr($dataVal);
                    $conditionData [$condition_id] ['cond_val'] = implode (',',$dataVal);
                    $conditionData [$condition_id] ['filed_val'] = '('.$conditionData [$condition_id]['cond_val'].')';
                    $condition_operator = 'IN';
                    $conditionData [$condition_id] ['condition_sql'] = str_replace(['$filedVal','$operator'],[$conditionData [$condition_id]  ['filed_val'],$condition_operator]  ,$conditionDatas [$conditionKey]['condition_sql']);
                }

                //涉及单位
                if (in_array($condition_id,$conversionArr)) {
                    $conditionData [$condition_id] ['cond_unit'] = end($dataVal);// 币种 重量单位 g kg
                    $conditionData [$condition_id] ['cond_val'] = reset($dataVal);//原始数据
                    $conditionData [$condition_id] ['cond_type'] = 1;//目前全部是1 2019年3月19日19:23:48
                    if ($condition_id == 7 && strtolower($conditionData [$condition_id] ['cond_unit']) == 'g') {
                        //克 转 千克
                        $conversionStatus = true;
                    }

                    if ($condition_id == 15 && strtoupper($conditionData [$condition_id] ['cond_unit']) == 'USD') {
                        //美元转人民币
                        $conversionStatus = true;
                    }

                    //数据运算转换
                    $response = self::operatorSections(['cond_val'=>$conditionData [$condition_id] ['cond_val'],'cond_unit'=>$conditionData [$condition_id] ['cond_unit']],$conditionDatas,$conditionFieldKey,$conversionStatus);
                    $conditionData [$condition_id] = array_merge($conditionData [$condition_id],$response);
                }

                //空字段
                if ($condition_id == 5) {
                    $sql = '';
                    foreach ($dataVal as $valArr) {
                        if ($valArr == 0) {
                            $field1 = 'city';//city province
                            $field2 = 'province';//city province
                            $filedSql1 = str_replace('$filedVal',$field1  ,$conditionDatas [$conditionFieldKey]['condition_sql']);
                            $filedSql2 = str_replace('$filedVal',$field2  ,$conditionDatas [$conditionFieldKey]['condition_sql']);
                            $filedSql = "($filedSql1 OR $filedSql2)";
                        } else if ($valArr == 1){
                            $field1 = 'phone';//phone mobile_phone
                            $field2 = 'mobile_phone';//phone mobile_phone
                            $filedSql1 = str_replace('$filedVal',$field1  ,$conditionDatas [$conditionFieldKey]['condition_sql']);
                            $filedSql2 = str_replace('$filedVal',$field2  ,$conditionDatas [$conditionFieldKey]['condition_sql']);
                            $filedSql = "($filedSql1 OR $filedSql2)";
                        } else if ($valArr == 2) {
                            $field = 'postal_code';
                            $filedSql = str_replace('$filedVal',$field  ,$conditionDatas [$conditionFieldKey]['condition_sql']);

                        } else {
                            break;
                        }

                        if (empty($sql)) {
                            $sql = $filedSql;
                        } else {
                            $sql .= ' AND '.$filedSql;
                        }
                        $conditionData [$condition_id] ['condition_sql'] = $sql;
                    }
                    $conditionData [$condition_id] ['cond_val'] = implode(',',$dataVal);
                }
                //长宽高
                if ($condition_id == 6) {
                    $sql = '';
                    $field = '';
                    $conditionData [$condition_id] ['cond_unit'] = 'CM';// cm
                    $conditionData [$condition_id] ['cond_val'] = $dataVal;//原始数据
                    //尺寸 key 0 :长 1:宽 2:高 >=,1 ;>=,1 ;>=,1;
                    $conditionVals = explode(';',$dataVal);
                    foreach ($conditionVals as $key=> $conditionVal) {
                        if (empty($conditionVal)) {
                            continue;
                        }
                        if ($key == 0) {
                            $field = 'goods.goods_length';
                        } else if ($key == 1) {
                            $field = 'goods.goods_width';
                        } else if ($key == 2) {
                            $field = 'goods.goods_height';
                        } else {
                            continue;
                        }
                        $goodsParams = explode(',',$conditionVal);
                        $filedSql = str_replace(['$filedVal','$operator'],[$goodsParams[1],$field.' '.$goodsParams[0]]  ,$conditionDatas [$conditionFieldKey]['condition_sql']);
                        if (empty($sql)) {
                            $sql = $filedSql;
                        } else {
                            $sql .= ' AND '.$filedSql;
                        }
                    }
                    $conditionData [$condition_id] ['condition_sql'] = $sql;
                }
                //运算符
                if ($condition_id == 11) {
                    $conditionData [$condition_id] ['cond_unit'] = '';// 币种 重量单位 g kg
                    $conditionData [$condition_id] ['cond_val'] = reset($dataVal);//原始数据
                    $response = self::operatorSections(['cond_val'=>$conditionData [$condition_id] ['cond_val'],'cond_unit'=>$conditionData [$condition_id] ['cond_unit']],$conditionDatas,$conditionFieldKey);
                    $conditionData [$condition_id] = array_merge($conditionData [$condition_id],$response);
                }
                //邮编
                if (in_array($condition_id,$postCodeArr)) {
                    $dataVals = $dataVal;
                    $dataVal = str_replace('丶',',',$dataVal);
                    //邮编
                    if ($condition_id == 4 ) {
                        $dataVal = str_replace('-','',$dataVal);
                    }
                    //字符串 '10000','100001'
                    $dataVal = explode(',',$dataVal);
                    $sql_in_val = '';
                    $sql_between_val = '';
                    foreach ($dataVal as $dataValKey => $dataValV) {
                        if (!is_bool(strpos($dataValV,'~'))) {
                            $dataValV_between = explode('~',$dataValV);
                            sort($dataValV_between);
                            $sql_between_val [] = " orders.postal_code BETWEEN '".$dataValV_between[0]."' AND '".$dataValV_between[1]."' ";
                        } else {
                            if (is_numeric($dataValV)) {
                                $sql_in_val .="'$dataValV',";
                            }
                        }
                    }
                    if ($sql_in_val && $sql_between_val) {
                        $sql_in_val =  substr($sql_in_val,0,-1);
                        $filed_val = '('.$sql_in_val.') ';
                        $conditionData [$condition_id] ['operator']  = 'IN';
                        $conditionData [$condition_id] ['filed_val']  = $filed_val;
                        $condition_sql = str_replace(['$filedVal','$operator'],[$conditionData [$condition_id] ['filed_val'],$conditionData [$condition_id] ['operator']]  ,$conditionDatas [$conditionFieldKey]['condition_sql']);
                        $filed_val = implode(' AND ',$sql_between_val);
                        $conditionData [$condition_id] ['condition_sql'] = $condition_sql.' AND '.$filed_val;
                    } else if (empty($sql_in_val) && !empty($sql_between_val)) {
                        $filed_val = implode(' AND ',$sql_between_val);
                        $conditionData [$condition_id] ['operator']  = 'BETWEEN';
                        $conditionData [$condition_id] ['filed_val']  = $filed_val;
                        $conditionData [$condition_id] ['condition_sql'] = " WHERE ".$filed_val;
                    } else if (!empty($sql_in_val) && empty($sql_between_val)) {
                        $sql_in_val =  substr($sql_in_val,0,-1);
                        $filed_val = '('.$sql_in_val.') ';
                        $conditionData [$condition_id] ['operator']  = 'IN';
                        $conditionData [$condition_id] ['filed_val']  = $filed_val;
                        $conditionData [$condition_id] ['condition_sql'] = str_replace(['$filedVal','$operator'],[$conditionData [$condition_id] ['filed_val'],$conditionData [$condition_id] ['operator']]  ,$conditionDatas [$conditionFieldKey]['condition_sql']);
                    }

                    //                $sql_val = self::arrayValToSql(explode(',',$dataVal));
                    $conditionData [$condition_id] ['cond_val'] = $dataVals;//原始数据
                    //                $conditionData [$condition_id] ['operator']  = 'IN';
                    ////                $conditionData [$condition_id] ['filed_val']  = '('.$sql_val.')';
                    //                $conditionData [$condition_id] ['filed_val']  = $filed_val;
                    //                $conditionData [$condition_id] ['condition_sql'] = str_replace(['$filedVal','$operator'],[$conditionData [$condition_id] ['filed_val'],$conditionData [$condition_id] ['operator']]  ,$conditionDatas [$conditionFieldKey]['condition_sql']);
                }
                //sku
                if (in_array($condition_id,$skuArr)) {
                        $sql_val = explode('、',$dataVal);
                        $sql_val = array_filter($sql_val);
                        foreach ($sql_val as &$val_sku){
                            $val_sku = "'".$val_sku."'";
                        }
                        $conditionData [$condition_id] ['cond_val'] = $dataVal;//原始数据
                        $conditionData [$condition_id] ['operator']  = 'IN';
                        $conditionData [$condition_id] ['filed_val']  = '('.implode(',',$sql_val).')';
                        $conditionData [$condition_id] ['condition_sql'] = str_replace(['$filedVal','$operator'],[$conditionData [$condition_id] ['filed_val'],$conditionData [$condition_id] ['operator']]  ,$conditionDatas [$conditionFieldKey]['condition_sql']);

                }
                //邮编排除
                if (in_array($condition_id,$notInQueryTypeArr)) {
                    $dataVals = $dataVal;
                    $dataVal = str_replace('丶',',',$dataVal);
                    //邮编
                    if ($condition_id == 14 ) {
                        $dataVal = str_replace('-','',$dataVal);
                    }
                    //字符串 '10000','100001'
                    $dataVal = explode(',',$dataVal);
                    $sql_in_val = '';
                    $sql_between_val = '';
                    foreach ($dataVal as $dataValKey => $dataValV) {
                        if (!is_bool(strpos($dataValV,'~'))) {
                            $dataValV_between = explode('~',$dataValV);
                            sort($dataValV_between);
                            $sql_between_val [] = " orders.postal_code NOT BETWEEN '".$dataValV_between[0]."' AND '".$dataValV_between[1]."' ";
                        } else {
                            if (is_numeric($dataValV)) {
                                $sql_in_val .="'$dataValV',";
                            }
                        }
                    }
                    if ($sql_in_val && $sql_between_val) {
                        $sql_in_val =  substr($sql_in_val,0,-1);
                        $filed_val = '('.$sql_in_val.') ';
                        $conditionData [$condition_id] ['operator']  = 'NOT IN';
                        $conditionData [$condition_id] ['filed_val']  = $filed_val;
                        $condition_sql = str_replace(['$filedVal','$operator'],[$conditionData [$condition_id] ['filed_val'],$conditionData [$condition_id] ['operator']]  ,$conditionDatas [$conditionFieldKey]['condition_sql']);
                        $filed_val = implode(' AND ',$sql_between_val);
                        $conditionData [$condition_id] ['condition_sql'] = $condition_sql.' AND '.$filed_val;
                    } else if (empty($sql_in_val) && !empty($sql_between_val)) {
                        $filed_val = implode(' AND ',$sql_between_val);
                        $conditionData [$condition_id] ['operator']  = 'NOT BETWEEN';
                        $conditionData [$condition_id] ['filed_val']  = $filed_val;
                        $conditionData [$condition_id] ['condition_sql'] = " WHERE ".$filed_val;
                    } else if (!empty($sql_in_val) && empty($sql_between_val)) {
                        $sql_in_val =  substr($sql_in_val,0,-1);
                        $filed_val = '('.$sql_in_val.') ';
                        $conditionData [$condition_id] ['operator']  = 'NOT IN';
                        $conditionData [$condition_id] ['filed_val']  = $filed_val;
                        $conditionData [$condition_id] ['condition_sql'] = str_replace(['$filedVal','$operator'],[$conditionData [$condition_id] ['filed_val'],$conditionData [$condition_id] ['operator']]  ,$conditionDatas [$conditionFieldKey]['condition_sql']);
                    }

                    //                $sql_val = self::arrayValToSql(explode(',',$dataVal));
                    $conditionData [$condition_id] ['cond_val'] = $dataVals;//原始数据
                    //                $conditionData [$condition_id] ['operator']  = 'IN';
                    ////                $conditionData [$condition_id] ['filed_val']  = '('.$sql_val.')';
                    //                $conditionData [$condition_id] ['filed_val']  = $filed_val;
                    //                $conditionData [$condition_id] ['condition_sql'] = str_replace(['$filedVal','$operator'],[$conditionData [$condition_id] ['filed_val'],$conditionData [$condition_id] ['operator']]  ,$conditionDatas [$conditionFieldKey]['condition_sql']);
                }
            }
            return ['troubleData'=>$troubleData,'conditionData'=>$conditionData];
        }


        /**
         * @param $conditions
         * @return array
         * Note: 返回选中数据
         * Data: 2019/3/28 14:25
         * Author: zt7785
         */
        public static function getCheckedDatas($conditions)
        {
            //已选参数
            $checkedData = [];
            //in 查询语句
            $inQueryTypeArr = [1,2,3,5,8,12,13,4,9];
            //sku 邮编
            //带运算符语句
            $conversionArr = [7,15,11];//临时加11
            foreach ($conditions as $condition) {
                if (in_array($condition ['condition_id'] , $inQueryTypeArr)) {

                    $checkedData [$condition ['input_field']] = explode(',', $condition ['cond_val']);

                    if($condition ['condition_id'] == 4 || $condition ['condition_id'] == 9){
                        $checkedData [$condition ['input_field']] = $condition ['cond_val'];
                    }
                    if ($condition ['condition_id'] == 3) {
                        //国家
                        $includCount = SettingCountry::getAllCountryInclud($checkedData [$condition ['input_field']]);
                        $checkedData [$condition ['input_field']] = $includCount;
                    }

                    if ($condition ['condition_id'] == 12) {
                        //仓库
                        $includCount = SettingWarehouse::getAllCountryInclud($checkedData [$condition ['input_field']]);
                        $checkedData [$condition ['input_field']] = $includCount;
                    }

                    if ($condition ['condition_id'] == 13) {
                        //物流
                        $includCount = SettingLogistics::getAllCountryInclud($checkedData [$condition ['input_field']]);
                        $checkedData [$condition ['input_field']] = $includCount;
                    }
                }
                if (in_array($condition ['condition_id'] , $conversionArr)) {
                    $checkedData [$condition ['input_field']] ['unit'] = $condition ['cond_unit'] ;
                    if ($condition ['operator'] == 'BETWEEN') {
                        $checkedData [$condition ['input_field']] ['operator'] = '~' ;
                        $checkedData [$condition ['input_field']] ['value'] = explode('~',$condition ['cond_val']);
                    } else {
                        $checkedData [$condition ['input_field']] ['operator'] = $condition ['operator'] ;
                        $checkedData [$condition ['input_field']] ['value'] = str_replace($condition ['operator'],'',$condition ['cond_val']) ;
                    }
                }
                //长宽高
                if ($condition ['condition_id'] == 6) {
                    //尺寸 key 0 :长 1:宽 2:高 >=,1 ;>=,1 ;>=,1;
                    $conditionVals = explode(';',$condition ['cond_val']);
                    foreach ($conditionVals as $key=> $conditionVal) {
                        if (empty($conditionVal)) {
                            continue;
                        }
                        if ($key == 0) {
                            $field = 'length';
                        } else if ($key == 1) {
                            $field = 'width';
                        } else if ($key == 2) {
                            $field = 'height';
                        } else {
                            continue;
                        }
                        $goodsParams = explode(',',$conditionVal);
                        $checkedData [$condition ['input_field']] [$field] ['operator'] = $goodsParams [0];
                        $checkedData [$condition ['input_field']] [$field] ['value'] = $goodsParams [1];
                    }
                }
            }
            return $checkedData;
        }

        /**
         * @param $data
         * @param $conditions
         * @param $conditionKey
         * @param bool $conversionStatus
         * @return array
         * Note: 区间 运算符  条件 : 7 15 11 适用
         * Data: 2019/3/19 19:51
         * Author: zt7785
         */
        public static function operatorSections($data,$conditions,$conditionKey,$conversionStatus = false)
        {
            $responseData = [];
            //指定数量 cond_val >=,1111(运算) 111,111(区间)
            if (!is_bool(strpos($data['cond_val'],'~'))) {
                //区间
                $condVal = explode('~',$data['cond_val']);
                //js判断大小
                $responseData ['cond_name'] = '('.$condVal [0].'~'.$condVal[1].')'.$data ['cond_unit'];
                $responseData ['operator'] = 'BETWEEN';
                if ($conversionStatus) {
                    if ($conditions[$conditionKey]['id'] == 7) {
                        //克 转千克
                        $condVal [0] = bcdiv ($condVal [0],1000,2);
                        $condVal [1] = bcdiv ($condVal [1],1000,2);
                    } else if ($conditions[$conditionKey]['id'] == 15) {
                        $rate = 6.7;
                        //克 转千克
                        $condVal [0] = bcmul ($condVal [0],$rate,2);
                        $condVal [1] = bcmul ($condVal [1],$rate,2);
                    }
                }
                $responseData ['filed_val'] = $condVal [0].' AND '.$condVal[1];
                //提前响应
                $responseData ['condition_sql'] = str_replace(['$filedVal','$operator'],[$responseData ['filed_val'],$responseData ['operator']]  ,$conditions[$conditionKey]['condition_sql']);
                return $responseData;
            } else if (!is_bool(strpos($data['cond_val'],'>='))) {
                $condVal = explode('>=',$data['cond_val']);
                $responseData ['operator'] = '>=';
            } else if (!is_bool(strpos($data['cond_val'],'<='))) {
                $condVal = explode('<=',$data['cond_val']);
                $responseData ['operator'] = '<=';
            } else if (!is_bool(strpos('=',$data['cond_val']))) {
                $condVal = explode('=',$data['cond_val']);
                $responseData ['operator'] = '=';
            } else {
                return [];
            }
            $responseData ['cond_name'] = '('.$responseData ['operator'].' '.$condVal[1].')'.$data ['cond_unit'];
            if ($conversionStatus) {
                if ($conditions[$conditionKey]['id'] == 7) {
                    //克 转千克
                    $condVal [1] = bcdiv ($condVal [1],1000,2);
                } else if ($conditions[$conditionKey]['id'] == 15) {
                    $rate = 6.7;
                    $condVal [1] = bcmul ($condVal [1],$rate,2);
                }
            }
            $responseData ['filed_val'] = $condVal[1];
            $responseData ['condition_sql'] = str_replace(['$filedVal','$operator'],[$responseData ['filed_val'],$responseData ['operator']]  ,$conditions[$conditionKey]['condition_sql']);
            return $responseData;
        }

        /**
         * @param int $id
         * @param $data
         * @return Model
         * Note: 新增更新
         * Data: 2019/3/13 18:41
         * Author: zt7785
         */
        public static function postGoods($id = 0, $data)
        {
            return self::updateOrCreate(['id' => $id], $data);
        }

        /**
         * @param $data
         * Note: 订单规则问题条件 数据处理 弃用
         * Data: 2019/3/14 14:56
         * Author: zt7785
         */
        public static function ruleSqlManage ($data,$rule_id,$conditions)
        {
            //、: 换行 ~ : 区间 ;:尺寸组 ,:关联参数
            $responseData = [];
            //规则id
            $responseData ['trouble_rule_id'] = $rule_id;
            //IN 查询的整合在一起
            //平台(1) 店铺(2) 国家(3) 商品属性id(8) SKU(9) 仓库(12) 物流(13)
            //逻辑相似
            //IN查询
            $inQueryTypeArr = [1,2,3,8,9,12,13];
            //邮编单独处理
            $postCodeArr = [4,14];
            //转换组
            $conversionArr = [7,15];
            $conditionKey = array_search($data['condition_id'],array_column($conditions,'id'));
            //前缀
            $responseData ['condition_prefix'] = $conditions[$conditionKey]['condition_prefix'];
            //条件名指定平台 等
            $responseData ['condition_name'] = $conditions[$conditionKey]['condition_name'];
            //模型
            $responseData ['related'] = $conditions[$conditionKey]['related'];
            $responseData ['condition_id'] = $data['condition_id'];
            $responseData ['cond_val'] = $data['cond_val'];//1,2,3
            $responseData ['cond_name'] = '('.$data['cond_name'].')';//（德国、乌克兰、澳大利亚）
            if (in_array($data['condition_id'],$inQueryTypeArr)) {
                //运算符
                $responseData ['operator'] = 'IN';
                $responseData ['filed_val'] = '('.$data['cond_val'].')';
                $responseData ['condition_sql'] = str_replace(['$filedVal','$operator'],[$responseData ['filed_val'],$responseData ['operator']]  ,$conditions[$conditionKey]['condition_sql']);
            }
            //指定字段不为空 WHERE $filedVal != ''
            if ($data['condition_id'] == 5) {
                $sql = '';
                $field = '';
                //可以多选
                //V1 判断val 0: 州省或城市为空 1:电话或手机为空 2:邮编为空
                $valArrs = explode (',',$data['cond_val']);
                foreach ($valArrs as $valArr) {
                    if ($valArr == 0) {
                        $field = 'province';
                    } else if ($valArr == 1){
                        $field = 'city';
                    } else if ($valArr == 2) {
                        $field = 'postal_code';
                    } else {
                        continue;
                    }
                    $filedSql = str_replace('$filedVal',$field  ,$conditions[$conditionKey]['condition_sql']);
                    if (empty($sql)) {
                        $sql = $filedSql;
                    } else {
                        $sql .= ' AND '.$filedSql;
                    }
                }
                $responseData ['condition_sql'] = $sql;
            }

            //运算符
            if ($data['condition_id'] == 11) {
                $response = self::operatorSection($data,$conditions,$conditionKey);
                $responseData = array_merge($responseData,$response);
            }

            //订单金额 商品重量
            if (in_array($data ['condition_id'],$conversionArr)) {
                $responseData ['cond_type'] = $data ['cond_type'];// 计算方式 单品重量
                $responseData ['cond_unit'] = $data ['cond_unit'];// 币种 重量单位 g kg
                if ($data['condition_id'] == 7 && strtolower($responseData ['cond_unit']) == 'g') {
                    //克 转 千克
                    $conversionStatus = true;
                }
                $response = self::operatorSection($data,$conditions,$conditionKey,$conversionStatus);
                $responseData = array_merge($responseData,$response);
            }

            //尺寸
            if ($data ['condition_id'] == 6) {
                $sql = '';
                $field = '';
                //尺寸 key 0 :长 1:宽 2:高 >=,1 ;>=,1 ;>=,1;
                $conditionVals = explode(';',$data['cond_val']);
                foreach ($conditionVals as $key=> $conditionVal) {
                    if (empty($conditionVal)) {
                        continue;
                    }
                    if ($key == 0) {
                        $field = 'goods.goods_length';
                    } else if ($key == 1) {
                        $field = 'goods.goods_width';
                    } else if ($key == 2) {
                        $field = 'goods.goods_height';
                    } else {
                        continue;
                    }
                    $goodsParams = explode(',',$conditionVal);
                    $filedSql = str_replace(['$filedVal','$operator'],[$goodsParams[1],$field.' '.$goodsParams[0]]  ,$conditions[$conditionKey]['condition_sql']);
                    if (empty($sql)) {
                        $sql = $filedSql;
                    } else {
                        $sql .= ' AND '.$filedSql;
                    }
                }
                $responseData ['condition_sql'] = $sql;
            }

            //邮编
            if (in_array($data['condition_id'],$postCodeArr)) {
                $conditionVals = explode('、',$data['cond_val']);
                if ($data['condition_id'] == 4 ) {
                    $responseData ['operator'] = 'IN';
                } else {
                    $responseData ['operator'] = 'NOT IN';
                }
                $responseData ['filed_val'] = '('.implode(',',$conditionVals).')';

                $responseData ['condition_sql'] = str_replace(['$filedVal','$operator'],[$responseData ['filed_val'],$responseData ['operator']]  ,$conditions[$conditionKey]['condition_sql']);
            }

            return $responseData;
        }

        /**
         * @param $data
         * @param $conditions
         * @param $conditionKey
         * @return array
         * Note: 区间 运算符 弃用
         * Data: 2019/3/14 17:12
         * Author: zt7785
         */
        public static function operatorSection($data,$conditions,$conditionKey,$conversionStatus = false)
        {
            $responseData = [];
            //指定数量 cond_val >=,1111(运算) 111,111(区间)
            $condVal = explode(',',$data['cond_val']);
            //是数字则是区间
            if (is_numeric($condVal [0])) {
                //js判断大小
                $responseData ['cond_name'] = '('.$condVal [0].'~'.$condVal[1].')'.$data ['cond_unit'];
                $responseData ['operator'] = 'BETWEEN';
                if ($conversionStatus) {
                    //克 转千克
                    $condVal [0] = bcdiv ($condVal [0],1000,2);
                    $condVal [1] = bcdiv ($condVal [1],1000,2);
                }
                $responseData ['filed_val'] = $condVal [0].' AND '.$condVal[1];
                $responseData ['condition_sql'] = str_replace(['$filedVal','$operator'],[$responseData ['filed_val'],$responseData ['operator']]  ,$conditions[$conditionKey]['condition_sql']);
            } else {
                $responseData ['cond_name'] = '('.$condVal [0].' '.$condVal[1].')'.$data ['cond_unit'];
                $responseData ['operator'] = $condVal [0];
                if ($conversionStatus) {
                    //克 转千克
                    $condVal [1] = bcdiv ($condVal [1],1000,2);
                }
                $responseData ['filed_val'] = $condVal[1];
                $responseData ['condition_sql'] = str_replace(['$filedVal','$operator'],[$responseData ['filed_val'],$responseData ['operator']]  ,$conditions[$conditionKey]['condition_sql']);
            }
            return $responseData;
        }


        /**
         * @param $postParams
         * @param $conditionDatas
         * @param $orgDatas
         * @return array
         * Note: 弃用
         * Data: 2019/3/20 14:39
         * Author: zt7785
         */
        public static function troubleDatasLogic($postParams,$conditionDatas,$orgDatas)
        {
            $conditionData = [];
            //in 查询语句
            $inQueryTypeArr = [1,2,3,8,12,13];
            //sku 邮编
            $postCodeArr = [4,9];
            //带运算符语句
            $conversionArr = [7,15];
            $fieldArr = array_column($conditionDatas,'input_field');
            $conversionStatus = false;
            foreach ($postParams as $postParamKey => $postParamVal) {
                //key 逻辑
                $conditionFieldKey = array_search($postParamKey,$fieldArr);
                if (is_bool($conditionFieldKey)) {
                    continue;
                }
                //基础数据
                $condition_id = $conditionDatas [$conditionFieldKey] ['id'];
                $conditionData [$condition_id] ['condition_id'] = $condition_id;
                //前缀 订单地址为
                $conditionData [$condition_id] ['condition_prefix'] = $conditionDatas [$conditionFieldKey] ['condition_prefix'];
                //加粗字段
                $conditionData [$condition_id] ['condition_name'] = $conditionDatas [$conditionFieldKey] ['condition_name'];
                //关联模型
                $conditionData [$condition_id] ['related'] = $conditionDatas [$conditionFieldKey] ['related'];
                //in 查询 val 拼接
                if (in_array($condition_id,$inQueryTypeArr)) {
                    $conditionData [$condition_id] ['cond_val']  = implode(',',$postParamVal);
                    $conditionData [$condition_id] ['operator']  = 'IN';
                    $conditionData [$condition_id] ['filed_val']  = '('.$conditionData [$condition_id] ['cond_val'] .')';
                    $conditionData [$condition_id] ['condition_sql'] = str_replace(['$filedVal','$operator'],[$conditionData [$condition_id] ['filed_val'],$conditionData [$condition_id] ['operator']]  ,$conditionDatas [$conditionFieldKey]['condition_sql']);
                }
                //涉及单位
                if (in_array($condition_id,$conversionArr)) {
                    $conditionData [$condition_id] ['cond_unit'] = end($postParamVal);// 币种 重量单位 g kg
                    $conditionData [$condition_id] ['cond_val'] = reset($postParamVal);//原始数据
                    $conditionData [$condition_id] ['cond_type'] = 1;//目前全部是1 2019年3月19日19:23:48
                    if ($condition_id== 7 && strtolower($conditionData [$condition_id] ['cond_unit']) == 'g') {
                        //克 转 千克
                        $conversionStatus = true;
                    }
                    //数据运算转换
                    $response = self::operatorSections(['cond_val'=>$conditionData [$condition_id] ['cond_val'],'cond_unit'=>$conditionData [$condition_id] ['cond_unit']],$conditionDatas,$conditionFieldKey,$conversionStatus);
                    $conditionData [$condition_id] = array_merge($conditionData [$condition_id],$response);
                }


                //空字段
                if ($condition_id == 5) {
                    foreach ($postParamVal as $valArr) {
                        if ($valArr == 0) {
                            $field = 'province';
                        } else if ($valArr == 1){
                            $field = 'city';
                        } else if ($valArr == 2) {
                            $field = 'postal_code';
                        } else {
                            break;
                        }
                        $filedSql = str_replace('$filedVal',$field  ,$conditionDatas [$conditionFieldKey]['condition_sql']);
                        if (empty($sql)) {
                            $sql = $filedSql;
                        } else {
                            $sql .= ' AND '.$filedSql;
                        }
                        $conditionData [$condition_id] ['condition_sql'] = $sql;
                    }
                }
                //长宽高
                if ($condition_id == 6) {
                    $sql = '';
                    $field = '';
                    $conditionData [$condition_id] ['cond_unit'] = 'CM';// cm
                    $conditionData [$condition_id] ['cond_val'] = reset($postParamVal);//原始数据
                    //尺寸 key 0 :长 1:宽 2:高 >=,1 ;>=,1 ;>=,1;
                    $conditionVals = explode(';',reset($postParamVal));
                    foreach ($conditionVals as $key=> $conditionVal) {
                        if (empty($conditionVal)) {
                            continue;
                        }
                        if ($key == 0) {
                            $field = 'goods.goods_length';
                        } else if ($key == 1) {
                            $field = 'goods.goods_width';
                        } else if ($key == 2) {
                            $field = 'goods.goods_height';
                        } else {
                            continue;
                        }
                        $goodsParams = explode(',',$conditionVal);
                        $filedSql = str_replace(['$filedVal','$operator'],[$goodsParams[1],$field.' '.$goodsParams[0]]  ,$conditionDatas [$conditionFieldKey]['condition_sql']);
                        if (empty($sql)) {
                            $sql = $filedSql;
                        } else {
                            $sql .= ' AND '.$filedSql;
                        }
                    }
                    $conditionData [$condition_id] ['condition_sql'] = $sql;
                }
                //运算符
                if ($condition_id == 11) {
                    $conditionData [$condition_id] ['cond_unit'] = '';// 币种 重量单位 g kg
                    $conditionData [$condition_id] ['cond_val'] = reset($postParamVal);//原始数据
                    $response = self::operatorSections(['cond_val'=>$conditionData [$condition_id] ['cond_val'],'cond_unit'=>$conditionData [$condition_id] ['cond_unit']],$conditionDatas,$conditionFieldKey);
                    $conditionData [$condition_id] = array_merge($conditionData [$condition_id],$response);
                }
                //邮编 sku
                if (in_array($condition_id,$postCodeArr)) {
                    $cond_val = str_replace('、',',',reset($postParamVal));
                    $conditionData [$condition_id] ['cond_val'] = $cond_val;//原始数据
                    $conditionData [$condition_id] ['operator']  = 'IN';
                    $conditionData [$condition_id] ['filed_val']  = '('.$conditionData [$condition_id] ['cond_val'] .')';
                    $conditionData [$condition_id] ['condition_sql'] = str_replace(['$filedVal','$operator'],[$conditionData [$condition_id] ['filed_val'],$conditionData [$condition_id] ['operator']]  ,$conditionDatas [$conditionFieldKey]['condition_sql']);
                }
                $conditionData [$condition_id] ['cond_name'] = $orgDatas [$conditionDatas [$conditionFieldKey]['relid']];
            }
            return $conditionData;
        }


        public static function arrayValToStr($dataVal)
        {
            return array_map(function($val) {
                return (string)$val;
            },$dataVal);
        }


        public static function arrayValToSql($dataVal)
        {
            $sql = '';
            array_map(function($val)use (&$sql) {
                $sql .="$val,";
                return $sql;
            },$dataVal);

            return substr($sql,0,-1);
        }
    }
