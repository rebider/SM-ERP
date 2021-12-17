<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AmazonPic extends Model
{
    protected $table = 'amazon_pics';

    protected $primaryKey = 'id';

    public function updateByAmazonId($lotteId, $imgArr,$user_id)
    {
        foreach ($imgArr as $value) {
            $this->insertGetId([
                'amazon_id' => $lotteId,
                'created_man' => $user_id,
                'user_id' => $user_id,
                'link' => $value,
            ]);
        }

        return true;
    }

    /**/
    public function deleteById($id)
    {
        $link = $this->where('id' ,$id)->pluck('link')->first() ;
        $re = $this->where('id' ,$id)->delete() ;
        unlink(storage_path('app/'.$link)) ;
        return $re ;
    }
}
