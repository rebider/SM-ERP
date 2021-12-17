<?php
    /**
     * Created by yuwei.
     * User: yuwei
     * Date: 2019/3/11
     * Time: 18:09
     */

    namespace App\Http\Requests;

    use Illuminate\Foundation\Http\FormRequest;
    use Illuminate\Http\Exceptions\HttpResponseException;
    use Illuminate\Contracts\Validation\Validator;

    class BaseRequest extends FormRequest
    {
        protected $rules    = [];
        protected $messages = [];
        protected $code = -1;
        protected $responseCode = 200;

        /**
         * Determine if the user is authorized to make this request.
         * @return bool
         */
        public function authorize()
        {
            return true;
        }

        /**
         * Get the validation rules that apply to the request.
         * @return array
         */
        public function rules()
        {
            if (!empty($this->rules)) {
                return $this->rules;
            }
            return [];
        }

        public function messages()
        {
            if (!empty($this->messages)) {
                return $this->messages;
            }
            return [];
        }

        protected function failedValidation(Validator $validator)
        {
            $error = $validator->errors()->all();
            $res = response()->json(['msg' => join(",", $error), 'code' => $this->code, 'data' =>'' ], $this->responseCode)->setEncodingOptions(JSON_UNESCAPED_UNICODE);
            throw new HttpResponseException($res);
        }



    }