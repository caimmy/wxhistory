<?php
/**
 * Created by PhpStorm.
 * User: caimiao
 * Date: 16-11-9
 * Time: 上午10:44
 */

namespace app\utils\wxsdk;

use app\models\WxGenError;
use app\utils\DataHelper;
use yii\base\Exception;

include_once __DIR__ . '/raw/WXBizMsgCrypt.php';

class WxCallbackTool
{
    /**
     * 消息预定义
     */
    const MSG_TEXT          = 'text';
    const MSG_IMAGE         = 'image';
    const MSG_VOICE         = 'voice';
    const MSG_VIDEO         = 'video';
    const MSG_SHORTVIDEO    = 'shortvideo';
    const MSG_LOCATION      = 'location';
    const MSG_LINK          = 'link';

    /**
     * 事件预定义
     */
    const EVENT_FLAG                = 'event';
    const EVENT_SUBSCRIBE           = 'subscribe';
    const EVENT_UNSUBSCRIBE         = 'unsubscribe';
    const EVENT_LOCATION            = 'LOCATION';
    const EVENT_CLICK               = 'click';
    const EVENT_VIEW                = 'view';
    const EVENT_SCANCODE_PUSH       = 'scancode_push';
    const EVENT_SCANCODE_WAITMSG    = 'scancode_waitmsg';
    const EVENT_PIC_SYSPHOTO        = 'pic_sysphoto';
    const EVENT_PIC_PHOTO_OR_ALBUM  = 'pic_photo_or_album';
    const EVENT_PIC_WEIXIN          = 'pic_weixin';
    const EVENT_LOCATION_SELECT     = 'location_select';
    const EVENT_ENTER_AGENT         = 'enter_agent';
    const EVENT_BATCH_JOB_RESULT    = 'batch_job_result';

    public $raw_wx_msg_crypt = null;
    /**
     * 微信消息动作反应池
     * @var array
     */
    private $msg_reactor_pool   = [];
    /**
     * 微信事件动作反应池
     * @var array
     */
    private $evt_reactor_pool   = [];

    public function __construct($token, $encodingAesKey, $Corpid)
    {
        $this->raw_wx_msg_crypt = new \WXBizMsgCrypt($token, $encodingAesKey, $Corpid);
    }

    /**
     * 注册消息回调反应池
     * @param $msg_type 消息类型
     * @param $callback 回调函数
     */
    public function registerMsgReactor($msg_type, $callback)
    {
        $this->msg_reactor_pool[$msg_type] = $callback;
    }

    /**
     * 注册事件回调反应池
     * @param $sub_evt 事件子类型
     * @param $callback 回调函数
     */
    public function registerEventReactor($sub_evt, $callback)
    {
        $this->evt_reactor_pool[$sub_evt] = $callback;
    }

    /**
     * 将公众平台回复用户的消息加密打包.
     * <ol>
     *    <li>对要发送的消息进行AES-CBC加密</li>
     *    <li>生成安全签名</li>
     *    <li>将消息密文和安全签名打包成xml格式</li>
     * </ol>
     *
     * @param $replyMsg string 公众平台待回复用户的消息，xml格式的字符串
     * @param $timeStamp string 时间戳，可以自己生成，也可以用URL参数的timestamp
     * @param $nonce string 随机串，可以自己生成，也可以用URL参数的nonce
     * @param &$encryptMsg string 加密后的可以直接回复用户的密文，包括msg_signature, timestamp, nonce, encrypt的xml格式的字符串,
     *                      当return返回0时有效
     *
     * @return int 成功0，失败返回对应的错误码
     */
    public function VerifyURL(&$sReplyEchoStr)
    {
        $msg_signature = $_GET['msg_signature'];
        $timestamp = $_GET['timestamp'];
        $nonce = $_GET['nonce'];
        $echostr = $_GET['echostr'];
        return $this->raw_wx_msg_crypt->VerifyURL($msg_signature, $timestamp, $nonce, $echostr, $sReplyEchoStr);
    }

    public function EncryptMsg($sReplyMsg, $sTimeStamp, $sNonce, &$sEncryptMsg)
    {
        return $this->raw_wx_msg_crypt->EncryptMsg($sReplyMsg, $sTimeStamp, $sNonce, $sEncryptMsg);
    }

    /**
     * 检验消息的真实性，并且获取解密后的明文.
     * <ol>
     *    <li>利用收到的密文生成安全签名，进行签名验证</li>
     *    <li>若验证通过，则提取xml中的加密消息</li>
     *    <li>对消息进行解密</li>
     * </ol>
     *
     * @param $msgSignature string 签名串，对应URL参数的msg_signature
     * @param $timestamp string 时间戳 对应URL参数的timestamp
     * @param $nonce string 随机串，对应URL参数的nonce
     * @param $postData string 密文，对应POST请求的数据
     * @param &$msg string 解密后的原文，当return返回0时有效
     *
     * @return int 成功0，失败返回对应的错误码
     */
    public function DecryptMsg($sMsgSignature, $sTimeStamp = null, $sNonce, $sPostData, &$sMsg)
    {
        return $this->raw_wx_msg_crypt->DecryptMsg($sMsgSignature, $sTimeStamp, $sNonce, $sPostData, $sMsg);
    }

    public function getWxpushData()
    {
        $push_data = $this->getWxpushChecker();
        $err_code = $this->raw_wx_msg_crypt->DecryptMsg($push_data['msg_signature'],
            $push_data['timestamp'],
            $push_data['nonce'],
            $push_data['data'],
            $wx_info);

        if (0 == $err_code)
            return $wx_info;
        else
            return null;
    }

    public function analysis()
    {
        $response_data = $this->getWxpushData();
        $info = $this->_parseXml($response_data);
        $this->handlerWxInformation($info);
    }

    /**
     * 处理微信消息及顶层事件
     * @param $information
     */
    private function handlerWxInformation($information)
    {
        if (null == $information)
            return;
        switch (strtolower($information['msgtype']))
        {
            case 'event':
                $this->handlerWxEvents($information);
                break;
            default:
                $this->handlerWxMessage($information);
                # 记录日志并退出
                break;
        }
    }

    /**
     * 处理微信推送的消息
     * @param $information
     */
    private function handlerWxMessage($information)
    {
        if (array_key_exists($information['msgtype'], $this->msg_reactor_pool))
        {
            $wx_trans = new WxInformationTrans($information);
            $callback_params = $wx_trans->xml2array();

            call_user_func_array($this->msg_reactor_pool[$information['msgtype']], [$callback_params]);
        }
    }

    /**
     * 处理微信推送的事件
     * @param $information
     * @return null
     */
    private function handlerWxEvents($information)
    {
        try
        {
            $evt_id = $information['xml']->getElementsByTagName('Event')->item(0)->textContent;
            if (array_key_exists($evt_id, $this->evt_reactor_pool))
            {
                $wx_trans = new WxInformationTrans($information);
                $callback_params = $wx_trans->xml2array();

                call_user_func_array($this->evt_reactor_pool[$evt_id], [$callback_params]);
            }
        }
        catch (Exception $e)
        {
            WxGenError::logError($e);
            return null;
        }
    }

    private function _parseXml($xmlstring)
    {
        try
        {
            $xml = new \DOMDocument();
            $xml->loadXML($xmlstring);
            $msgType = $xml->getElementsByTagName('MsgType')->item(0)->textContent;
            return [
                'msgtype' => $msgType,
                'xml' => $xml,
                'raw' => $xmlstring
            ];
        }
        catch (Exception $e)
        {
            WxGenError::logError($e);
            return null;
        }
    }

    private function getWxpushChecker()
    {
        return [
            'msg_signature' => $this->getParam('msg_signature', ''),
            'timestamp' => $this->getParam('timestamp', ''),
            'nonce' => $this->getParam('nonce', ''),
            'data' => file_get_contents("php://input")
        ];
    }

    /**
     * 获取客户端请求参数
     * @param $pname 参数名称
     * @param null $default 参数不存在时的默认值
     * @return string
     */
    private function getParam($pname, $default=NULL)
    {
        return array_key_exists($pname, $_GET) ? $_GET[$pname] : $default;
    }
}