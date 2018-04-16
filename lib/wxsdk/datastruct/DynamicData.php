<?php

/**
 * Created by PhpStorm.
 * User: caimiao
 * Date: 2018/1/7
 * Time: 17:10
 */

namespace app\lib\wxsdk\datastruct;

use app\lib\wxsdk\CacheHelper;
use app\lib\wxsdk\WxApiHelper;
use app\lib\wxsdk\WxSdk;
use app\utils\Random;
use yii\base\Exception;
use yii\helpers\Json;

class DynamicData
{
    /**
     * 获取用户的微信账号信息
     * @param $open_id
     * @return false | array
     */
    public static function getWxUserInformation($open_id, $oauth_access_token=null) {
        $ret_user_info_array = false;
        $cached_key = CacheHelper::CachedUserInfo_Prefix . $open_id;
        $cached_user_info = \Yii::$app->cache->get($cached_key);
        if (false === $cached_user_info) {
            $real_user_info_string = \Requests::get(sprintf(WxApiHelper::URL_GET_USERINFO,
                (null === $oauth_access_token) ? WxSdk::getInstance()->getAccesstoken() : $oauth_access_token, $open_id));
            if ($real_user_info_string->success) {
                try {
                    $user_info_object = Json::decode($real_user_info_string->body);
                    if (isset($user_info_object['subscribe']) && (1 == $user_info_object['subscribe'])) {
                        $ret_user_info_array = $user_info_object;
                    }
                } catch (\Exception $e) {
                    \Yii::error($e);
                }
            }
        } else
            $ret_user_info_array = $cached_user_info;
        return $ret_user_info_array;
    }

    /**
     * 构造jsapi的签名信息
     * noncestr=Wm3WZYTPz0wzccnW
    jsapi_ticket=sM4AOVdWfPE4DxkXGEs8VMCPGGVi4C3VM0P37wVUCFvkVAy_90u5h9nbSlYy3-Sl-HhTdfl2fzFy1AOcHKP7qg
    timestamp=1414587457
    url=http://mp.weixin.qq.com?params=value
     */
    public static function getJsapiSignatureInfors($url = null) {
        if (null === $url) {
            $url = $_SERVER['REQUEST_SCHEME'] . '://' .  $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        }
        $params = [
            'noncestr'      => Random::randomKey(8),
            'jsapi_ticket'  => WxSdk::getInstance()->getJsAPI(),
            'timestamp'     => time(),
            'url'           => $url
        ];
        $to_sort_keys = array_keys($params);
        sort($to_sort_keys, SORT_ASC);
        $temp_arr       = [];
        foreach ($to_sort_keys as $key) {
            array_push($temp_arr, $key."=".$params[$key]);
        }
        $pre_encrypt    = implode('&', $temp_arr);
        return array_merge($params, ['signature' => sha1($pre_encrypt)]);
    }
}