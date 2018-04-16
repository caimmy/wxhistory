<?php
/**
 * Created by PhpStorm.
 * User: caimiao
 * Date: 16-12-2
 * Time: 上午8:52
 */

namespace app\utils;


class Random
{

    private static $key_pool = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h',
    'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z', 'A', 'B', 'C', 'D', 'E', 'F',
    'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'];

    /**
     * 提取一个随机字符串
     * @return string
     */
    public static function randomKey($len=16)
    {
        $key = '';
        for ($i = 0; $i < $len; $i++)
        {
            $key .= Random::$key_pool[random_int(0, count(Random::$key_pool) - 1)];
        }
        return $key;
    }
}