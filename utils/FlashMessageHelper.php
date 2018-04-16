<?php
/**
 * Created by PhpStorm.
 * User: caimm
 * Date: 2018/2/8
 * Time: 10:40
 */

namespace app\utils;


class FlashMessageHelper
{
    const FLASH_MSG_ERROR = 'c_f_err';
    const FLASH_MSG_CATALOG = 'c_f_catalog';

    /**
     * 设置错误消息闪存
     * @param $err
     */
    public static function setFlashError($err, $catalog='danger') {
        $err = is_string($err) ? $err : print_r($err, true);
        \Yii::$app->cache->set(self::FLASH_MSG_CATALOG, $catalog);
        \Yii::$app->cache->set(self::FLASH_MSG_ERROR, $err . '');
    }

    /**
     * 获取错误消息闪存
     * @return string | false
     */
    public static function getFlashError() {
        $err_msg = \Yii::$app->cache->get(self::FLASH_MSG_ERROR);
        if (is_string($err_msg)) {
            \Yii::$app->cache->delete(self::FLASH_MSG_ERROR);
        }
        return $err_msg;
    }

    /**
     * 获取消息闪现的提示类别
     * @return mixed|string
     */
    public static function getFlashCatalog() {
        $catalog = \Yii::$app->cache->get(self::FLASH_MSG_CATALOG);
        return (false === $catalog) ? 'danger' : $catalog;
    }
}