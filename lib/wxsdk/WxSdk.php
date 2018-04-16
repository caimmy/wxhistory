<?php

/**
 * Created by PhpStorm.
 * User: caimiao
 * Date: 2018/1/4
 * Time: 14:50
 */
namespace app\lib\wxsdk;


use yii\db\Exception;
use yii\helpers\Json;

class WxSdk
{
    const ws_domain         = 'api.weixin.qq.com';
    const SCOPE_BASE        = 'snsapi_base';
    const SCOPE_USERINFO    = 'snsapi_userinfo';

    private static $instance;

    private function __construct()
    {
    }

    /**
     * @return WxSdk
     */
    public static function getInstance() {
        if (empty(self::$instance))
            self::$instance = new WxSdk();
        return self::$instance;
    }

    /**
     * 获取或者更新微信平台的AccessToken
     * @return bool|mixed
     */
    public function getAccesstoken() {
        $ret_val = false;
        $cached_accesstoken = \Yii::$app->cache->get(WxConfigKey::ACCESS_TOKEN);
        if (false === $cached_accesstoken) {
            $url_load_tokenaccess = sprintf(WxApiHelper::URL_GET_ACCESSTOKEN,
                WxConfigKey::getInstance()->getAppID(), WxConfigKey::getInstance()->getCachedConfig(WxConfigKey::APP_SECRET));
            $req_accesstoken = \Requests::get($url_load_tokenaccess);
            if ($req_accesstoken->success) {
                try{
                    $res_json = Json::decode($req_accesstoken->body);
                    if (isset($res_json['access_token']) && isset($res_json['expires_in'])) {
                        $ret_val = $res_json['access_token'];
                        $expireed = intval($res_json['expires_in']);
                        $expireed = ($expireed > 600) ? $expireed - 600 : $expireed;
                        \Yii::$app->cache->set(WxConfigKey::ACCESS_TOKEN, $ret_val, $expireed);
                    }
                } catch (Exception $e){
                    \Yii::error($e->getMessage());
                }
            }
        } else
            $ret_val = $cached_accesstoken;
        return $ret_val;
    }

    public function getJsAPI() {
        $ret_jsapi = WxConfigKey::getInstance()->getCachedConfig(WxConfigKey::JSAPI);
        if (false === $ret_jsapi) {
            $url_get_jsapi = sprintf(WxApiHelper::URL_GET_JSAPI_ACCESSTOKEN, $this->getAccesstoken());
            $ret_response = \Requests::get($url_get_jsapi);
            if ($ret_response->success) {
                try {
                    $json_response = Json::decode($ret_response->body);
                    if (is_array($json_response) && isset($json_response['ticket'])) {
                        $ret_jsapi = $json_response['ticket'];
                        $expired_test = $json_response['expires_in'];
                        $set_expires_tm = ($expired_test > 3600) ? ($expired_test - 600) : $expired_test;
                        WxConfigKey::getInstance()->setCachedConfig(WxConfigKey::JSAPI, $ret_jsapi, $set_expires_tm);
                    }
                } catch (\yii\base\Exception $e) {
                    \Yii::error($e);
                }
            }
        }

        return $ret_jsapi;
    }

    /**
     * 置换网页授权的access_token
     * @param $code
     * @return bool | string
     */
    public function getOauthAccesstoken($code) {
        $ret_access_info = false;
        $url_exchange_oauth_accesstoken = sprintf(WxApiHelper::URL_OAUTH_URL_ACCESSTOKEN,
            WxConfigKey::getInstance()->getAppID(), WxConfigKey::getInstance()->getCachedConfig(WxConfigKey::APP_SECRET), $code);
        $ret_response = \Requests::get($url_exchange_oauth_accesstoken);
        try{
            $json_response = Json::decode($ret_response->body);
            if (isset($json_response['access_token']) && isset($json_response['openid'])) {
                $ret_access_info = [
                    'access_token' => $json_response['access_token'],
                    'openid' => $json_response['openid']
                ];
            }
        } catch (\yii\base\Exception $e) {
            \Yii::error($e);
            $ret_access_info = false;
        }

        return $ret_access_info;
    }

    public function genOauthUrl($url, $scope=self::SCOPE_USERINFO) {
        $state = mt_rand(1000, 9999);
        $url = sprintf(WxApiHelper::URL_OAUTH_URL_TPL, WxConfigKey::getInstance()->getAppID(), urlencode($url), $scope, $state);
        \Yii::$app->session->set('wx_oauth_state', $state);
        return $url;
    }
}