<?php
/**
 * Created by PhpStorm.
 * User: caimiao
 * Date: 2018/1/7
 * Time: 20:46
 */

namespace app\lib\wxsdk;


class WxResponseHelper
{
    /**
     * 生成被动响应的文本消息
     * @param $touser
     * @param $content
     * @return string
     */
    public static function genResponseTextMsg($touser, $content)
    {
        $res_info = '<xml><ToUserName><![CDATA[%s]]></ToUserName><FromUserName><![CDATA[%s]]></FromUserName><CreateTime>%d</CreateTime><MsgType><![CDATA[text]]></MsgType><Content><![CDATA[%s]]></Content></xml>';
        return sprintf($res_info, $touser, WxConfigKey::getInstance()->getCachedConfig(WxConfigKey::WEIXIN_CODE), time(), $content);
    }
}