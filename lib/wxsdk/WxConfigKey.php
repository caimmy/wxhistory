<?php
/**
 * Created by PhpStorm.
 * User: caimiao
 * Date: 2018/1/4
 * Time: 14:29
 */
namespace app\lib\wxsdk;

use app\models\AppMgrConfig;

class WxConfigKey
{
    const APP_ID        = 'appid';
    const APP_SECRET    = 'appsecret';
    const WEIXIN_CODE   = 'wx_code';

    const ACCESS_TOKEN  = 'accesstoken';
    const JSAPI         = 'jsapi';

    private static $instance;

    private function __construct()
    {
    }

    /**
     * @return WxConfigKey
     */
    public static function getInstance()
    {
        if (self::$instance == null)
            self::$instance = new WxConfigKey();
        return self::$instance;
    }

    /**
     * 获取应用编号
     * @return false | string
     */
    public function getAppID()
    {
        $ret_val = false;
        $cached_app_id = \Yii::$app->cache->get(self::APP_ID);
        if (false === $cached_app_id) {
            $datalog = AppMgrConfig::find()->where(['name' => self::APP_ID])->one();
            if (is_object($datalog)) {
                $ret_val = $datalog->value;
                @\Yii::$app->cache->set(self::APP_ID, $datalog->value);
            }
        } else
            $ret_val = $cached_app_id;
        return $ret_val;
    }

    public function setCachedConfig($config_key, $config_value, $expired=1800) {
        return \Yii::$app->cache->set($config_key, $config_value, $expired);
    }

    /**
     * @param $config_key
     * @param int $expired
     * @return array|bool
     */
    public function getCachedConfig($config_key, $expired=0)
    {
        $ret_val = false;
        $cachec_val = \Yii::$app->cache->get($config_key);
        if (false === $cachec_val) {
            $_value = AppMgrConfig::find()->where(['name' => $config_key])->all();
            if (is_array($_value)) {
                switch (count($_value)) {
                    case 0:
                        break;
                    case 1:
                        $ret_val = $_value[0]->value;
                        \Yii::$app->cache->set($config_key, $ret_val, $expired);
                        break;
                    default:
                        $ret_val = [];
                        foreach ($_value as $item) {
                            array_push($ret_val, $item->value);
                        }
                        \Yii::$app->cache->set($config_key, $ret_val, $expired);
                        break;
                }
            }
        } else
            $ret_val = $cachec_val;
        return $ret_val;
    }
}