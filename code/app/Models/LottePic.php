<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LottePic extends Model
{
    protected $table = 'lotte_pics';

    protected $primaryKey = 'id';

    public function updateByLotteId($lotteId, $imgArr)
    {
        $created_man = 1; //todo 获取创建人
        foreach ($imgArr as $value) {
            $this->insertGetId([
                'lotte_id' => $lotteId,
                'created_man' => $created_man,
                'link' => $value,
            ]);
        }

        return true;
    }

    /**/
    public function deleteById($id,$user_id)
    {
        $link = $this->where('id' ,$id)->pluck('link')->first() ;
        $re = $this->where('id' ,$id)->where(['user_id'=>$user_id])->delete() ;
        unlink(storage_path('app/'.$link)) ;
        return $re ;
    }
}
