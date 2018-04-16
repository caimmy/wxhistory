<?php

/**
 * Created by PhpStorm.
 * User: caimiao
 * Date: 2018/1/2
 * Time: 23:17
 */

namespace app\lib\wxsdk;

class ValidateHelper
{
    private static $instance;

    private function __construct()
    {

    }

    /**
     * @return ValidateHelper
     */
    public static function getInstance()
    {
        if (empty(self::$instance))
            self::$instance = new ValidateHelper();
        return self::$instance;
    }

    /**
     * 生成注册url时的校验字符串
     * @param $token
     * @param $timestamp
     * @param $nonce
     * @return string
     */
    public function genEchoStr($token, $timestamp, $nonce)
    {
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = sha1(implode('', $tmpArr));
        return $tmpStr;
    }
}