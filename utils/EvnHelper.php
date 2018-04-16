<?php
/**
 * Created by PhpStorm.
 * User: caimm
 * Date: 2018/3/7
 * Time: 9:43
 */

namespace app\utils;


class EvnHelper
{
    /**
     * 环境检查，确认当前是否在微信场景内
     * @return bool
     */
    public static function isWeixinScene() {
        return ('weixin' == \Yii::$app->session->get('scene'));
    }
}