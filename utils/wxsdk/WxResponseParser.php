<?php
/**
 * Created by PhpStorm.
 * User: caimiao
 * Date: 16-11-9
 * Time: 上午9:12
 */

namespace app\utils\wxsdk;


use yii\base\Exception;
use yii\helpers\Json;

class WxResponseParser
{
    /**
     * @var Requests_Response
     */
    private $response;

    const RESCODE_SUCCESS = 0;
    /**
     * @var bool
     */
    private $success = false;

    /**
     * 经过解析的响应结果
     * @var array
     */
    private $data = null;

    public function __get($name)
    {
        if (array_key_exists($name, $this->data))
            return $this->data[$name];
        else
            return null;
    }

    /**
     * 网络请求是否正常
     * @return bool
     */
    public function isSuccess()
    {
        return $this->success;
    }

    /**
     * 是否成功解析微信响应报文
     * @return bool
     */
    public function wxRespOK()
    {
        if (is_array($this->data) && array_key_exists('errcode', $this->data) && (self::RESCODE_SUCCESS == $this->data['errcode']))
            return true;
        else
            return false;
    }

    /**
     * 获取微信的响应数据
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    private function __construct($response)
    {
        $this->response = $response;
        $this->_parse();
    }

    private function _parse()
    {
        if (is_object($this->response) && $this->response->success)
        {
            try
            {
                $this->data = Json::decode($this->response->body);
                if (self::RESCODE_SUCCESS == $this->data['errcode'])
                {
                    $this->success = true;
                }
            }
            catch (Exception $e)
            {
                // TODO 错误日志
            }
        }
    }

    /**
     * 构造一个微信响应报文的解析器
     * @param $response
     * @return WxResponseParser
     */
    public static function analysis($response)
    {
        return new WxResponseParser($response);
    }
}