<?php

/**
 * Created by PhpStorm.
 * User: caimiao
 * Date: 16-11-11
 * Time: 下午3:54
 */

namespace app\utils;

class Aes{
    //密钥
    private $_secrect_key;

    public function __construct($secret_key){
        $this->_secrect_key = $secret_key;
    }

    /**
     * 加密方法
     * @param string $str
     * @return string
     */
    public function encrypt($str){
        //AES, 128 ECB模式加密数据
        $screct_key = base64_decode($this->_secrect_key);
        $str = trim($str);
        //$str = $this->addPKCS7Padding($str);
        $iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC), MCRYPT_RAND);
        $iv = '1234567890123456';
        $encrypt_str =  mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $screct_key, $str, MCRYPT_MODE_CBC, $iv);
        return base64_encode($encrypt_str);
    }

    /**
     * 解密方法
     * @param string $str
     * @return string
     */
    public function decrypt($str){
        //AES, 128 ECB模式加密数据
        $str = base64_decode($str);
        $screct_key = base64_decode($this->_secrect_key);
        $iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC), MCRYPT_RAND);
        $iv = '1234567890123456';
        $encrypt_str =  mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $screct_key, $str, MCRYPT_MODE_CBC, $iv);
        $encrypt_str = trim($encrypt_str);
        return $encrypt_str;
    }

    /**
     * 填充算法
     * @param string $source
     * @return string
     */
    function addPKCS7Padding($source){
        $source = trim($source);
        $block = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_ECB);
        $pad = $block - (strlen($source) % $block);
        if ($pad <= $block) {
            $char = chr($pad);
            $source .= str_repeat($char, $pad);
        }
        return $source;
    }
    /**
     * 移去填充算法
     * @param string $source
     * @return string
     */
    function stripPKSC7Padding_($source){
        $source = trim($source);
        $char = substr($source, strlen($source)-1);
        $num = ord($char);
        if($num==62)return $source;
        $source = substr($source,0,-$num);
        return $source;
    }

    function stripPKSC7Padding($source){
        $source = trim($source);
        $char = $source[strlen($source)-1];
        $num = ord($char);
        return substr($source, 0, -$num);
    }
}