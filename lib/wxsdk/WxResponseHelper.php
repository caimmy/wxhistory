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
    public static function genResponseTextMsg($touser, $fromuser, $content)
    {
        $res_info = '<xml><ToUserName><![CDATA[%s]]></ToUserName><FromUserName><![CDATA[%s]]></FromUserName><CreateTime>%d</CreateTime><MsgType><![CDATA[text]]></MsgType><Content><![CDATA[%s]]></Content></xml>';
        return sprintf($res_info, $touser, $fromuser, time(), $content);
    }

    /**
     * 生成被动响应的图文消息
     * @param $touser
     * @param array $picitems
     * @return string
     */
    public static function genResponsePicMsg($touser, $fromuser, array $picitems) {
        $items_content = '';
        foreach ($picitems as $pic_item) {
            $items_content .= $pic_item->toString();
        }
        $raw_msg_format = '<xml><ToUserName><![CDATA[%s]]></ToUserName><FromUserName><![CDATA[%s]]></FromUserName><CreateTime>%d</CreateTime><MsgType><![CDATA[news]]></MsgType><ArticleCount>%d</ArticleCount><Articles>%s</Articles></xml>';
        return sprintf($raw_msg_format, $touser, $fromuser, time(), count($picitems), $items_content);
    }
}