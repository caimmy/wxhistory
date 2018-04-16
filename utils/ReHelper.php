<?php
/**
 * Created by PhpStorm.
 * User: caimm
 * Date: 2018/2/9
 * Time: 11:07
 */

namespace app\utils;


class ReHelper
{
    /**
     * 检查手机号码的填写是否符合规则
     * @param $phone
     * @return bool
     */
    public static function checkPhoneNumber($phone) {
        return 1 === preg_match('/^1\d{10}/', $phone);
    }

    /**
     * 检查身份证号码是否符合规则
     * @param $idcard
     */
    public static function checkIdcard($idcard) {
        return 1 === preg_match('/\d{17}[xX\d]/', $idcard);
    }
}