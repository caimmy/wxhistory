<?php
/**
 * Created by PhpStorm.
 * User: caimiao
 * Date: 2018/1/6
 * Time: 21:41
 */

namespace app\lib\wxsdk;


class WxXMLParser
{
    public static function parse($text) {
        $wx_ups_parser = simplexml_load_string($text);
        return [1, $wx_ups_parser];
    }
}