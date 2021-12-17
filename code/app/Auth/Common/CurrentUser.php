<?php

namespace App\Auth\Common;

use App\Auth\Common\Enums;
use Illuminate\Http\Request;


/**
 * 当前登录用户
 * Class CurrentUser
 * @package App\Auth\Common
 */
class CurrentUser
{
    const CURRENTUSER_SESSIONKEY = "__CURRENTUSER_SESSIONKEY";

    /**
     * @var 用户idint
     */
    public $userId = 0;

    /**
     * @var 用户账号string
     */
    public $userCode;

    /**
     * @var 用户名称string
     */
    public $userName;

    /**
     * @var 用户名称string
     */
    public $companyName;


    /**
     * @var 账号类型string
     */
    public $userAccountType;

    /**
     * @var 子账号父id
     */
    public $userParentId;

    /**
     * @var 用户角色信息array
     */
    public $userRoleInfo = [];

    /**
     * @var 用户菜单显示array
     */
    public $userNavigation = [];

    /**
     * @var 用户权限array
     */
    public $userPermissions = [];


    /** 获取当前登录用户
     * @return CurrentUser
     */
    public static function getCurrentUser(){
        return session(CurrentUser::CURRENTUSER_SESSIONKEY);
    }

    /** 设置当前登录用户
     * @param CurrentUser $currentUser
     */
    public static function setCurrentUser(CurrentUser $currentUser){
        session([CurrentUser::CURRENTUSER_SESSIONKEY=>$currentUser]);
    }

    /** 移除当前登录用户
     * @param Request $request
     */
    public static function removeCurrentUser(Request $request){
        //融达通逻辑异常
        $request->session()->forget(CurrentUser::CURRENTUSER_SESSIONKEY);
        $request->session()->flush();
        $request->session()->save();
    }
}