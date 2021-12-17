<?php
    /**
     * Created by yuwei.
     * User: yuwei
     * Date: 2019/3/16
     * Time: 8:59
     */

    namespace App\Http\Requests;

    use Illuminate\Http\Request;
    use Illuminate\Http\Exceptions\HttpResponseException;

    class CheckIDsRequest extends BaseRequest
    {
        protected $rules = [
            'ids' => 'required',
        ];
        protected $errMessages = [
        ];

        function __construct(Request $request)
        {
            $values = explode(',', $request->ids);
            if (empty($values)) {
                return false;
            }
            foreach ($values as $id) {
                if (is_numeric($id) && is_int($id + 0) && ($id + 0) > 0) {

                } else {
                    $this->errMessages[] = $id . '必须是正整数';
                }
            }
            $error = $this->errMessages;
            if (!empty($error)) {
                $res = response()->json(['msg' => join(",", $error), 'code' => $this->code, 'data' => ''], $this->responseCode)->setEncodingOptions(JSON_UNESCAPED_UNICODE);
                throw new HttpResponseException($res);
            }
        }
    }