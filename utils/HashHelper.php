<?php
/**
 * Created by PhpStorm.
 * User: caimiao
 * Date: 16-12-7
 * Time: 上午9:36
 */

namespace app\utils;
use Yii;

class HashHelper
{

    private static $instance;
    private $hashIds;
    private function __construct()
    {
        $this->hashIds = new \Hashids(Yii::$app->params['hashids_salt']);
    }

    /**
     * @return HashHelper
     */
    public static function getInstance()
    {
        if (empty(self::$instance))
            self::$instance = new HashHelper();
        return self::$instance;
    }

    public function encode($id)
    {
        return $this->hashIds->encode($id);
    }

    public function decode($hash)
    {
        return $this->hashIds->decode($hash);
    }

    public function encode_hex($str)
    {
        return $this->hashIds->encode_hex($str);
    }

    public function decode_hex($hash)
    {
        return $this->hashIds->decode_hex($hash);
    }
}