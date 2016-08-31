<?php namespace App\Models\Traits;

use App\Models\User;

trait UserSocialiteHelper
{
    public static function getByDriver($driver, $id)
    {
        $functionMap = [
            'qq' => 'getByQQId',
            'wechat' => 'getByWechatId'
        ];
        $function = $functionMap[$driver];
        if (!$function) {
            return null;
        }

        return self::$function($id);
    }

    // public static function getByGithubId($id)
    // {
    //     return User::where('github_id', '=', $id)->first();
    // }
    public static function getByQQId($id)
    {
        return User::where('qq_id', '=', $id)->first();
    }

    public static function getByWechatId($id)
    {
        return User::where('wechat_openid', '=', $id)->first();
    }
}
