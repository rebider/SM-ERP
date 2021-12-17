<?php
    /**
     * Created by yuwei.
     * User: yuwei
     * Date: 2019/3/14
     * Time: 9:54
     */

Route::any('caiyi/woqu','Test\CaiyiController@woqu');
Route::any('caiyi/woqua','Test\CaiyiController@woqua');
Route::any('caiyi/yi','Test\CaiyiController@yigezaici');
Route::any('caiyi/import','Test\CaiyiController@importTest');
Route::any('goods/index','Test\CaiyiController@goodsIndex');
Route::any('goods/collect','Test\CaiyiController@goodsCollect');
Route::any('test','testController@index');



