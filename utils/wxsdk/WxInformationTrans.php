<?php
/**
 * Created by PhpStorm.
 * User: caimiao
 * Date: 16-12-5
 * Time: 下午1:01
 */

namespace app\utils\wxsdk;


use app\models\AppWxMsgLog;
use app\models\WxGenError;
use yii\base\Exception;
use yii\helpers\ArrayHelper;

class WxInformationTrans
{
    /**
     * DOMDocument
     * @var \DOMDocument
     */
    private $raw_info;
    private $_xml;

    private $toUserName;
    private $fromUserName;
    private $createTime;
    private $msgType;
    private $agent;

    public function __construct($xml)
    {
        $this->raw_info = $xml;
        $this->_xml = $xml['xml'];
        $this->toUserName = $this->_xpathContent(['ToUserName']);
        $this->fromUserName = $this->_xpathContent(['FromUserName']);
        $this->createTime = $this->_xpathContent(['CreateTime']);
        $this->msgType = $this->_xpathContent(['MsgType']);
        $this->agent = $this->_xpathContent(['AgentID']);

        $this->logWxmsg();
    }

    private function logWxmsg()
    {
        try
        {
            $wx_msg = new AppWxMsgLog();
            $wx_msg->ToUserName = $this->toUserName;
            $wx_msg->FromUserName = $this->fromUserName;
            $wx_msg->CreateTime = $this->createTime;
            $wx_msg->MsgType = $this->msgType;
            $wx_msg->Event = ('event' == $this->msgType) ? $this->_xpathContent(['Event']) : '';
            $wx_msg->data = $this->raw_info['raw'];
            $wx_msg->agent = $this->agent;
            $wx_msg->dt = date('Y-m-d H:i:s');
            if (!$wx_msg->save())
                recordObj($wx_msg->getErrors());
        }
        catch (Exception $e)
        {
            WxGenError::logError($e);
        }
    }

    public function xml2array()
    {
        if (WxCallbackTool::EVENT_FLAG == $this->msgType)
            return $this->transEvent();
        else
            return $this->transMsg();
    }

    /**
     * 翻译消息
     */
    private function transMsg()
    {
        $baseNode = [
            'ToUserName' => $this->toUserName,
            'FromUserName' => $this->fromUserName,
            'CreateTime' => $this->createTime,
            'MsgType' => $this->msgType
        ];
        $extNode = [];
        switch ($this->msgType)
        {
            case WxCallbackTool::MSG_TEXT:
                $extNode = $this->_transTextMessage();
                break;
            default:
                break;
        }
        return ArrayHelper::merge($baseNode, $extNode);
    }

    /**
     * 翻译事件
     * @return array
     */
    private function transEvent()
    {
        $baseNode = [
            'ToUserName' => $this->toUserName,
            'FromUserName' => $this->fromUserName,
            'CreateTime' => $this->createTime,
            'MsgType' => $this->msgType,
            'Event' => $this->_xpathContent(['Event'])
        ];

        $extNode = [];
        switch ($baseNode['Event'])
        {
            case WxCallbackTool::EVENT_LOCATION:
                $extNode = $this->_transeLocationEvent();
                break;
            case WxCallbackTool::EVENT_ENTER_AGENT:
                $extNode = $this->_transeEnteragent();
                break;
            default:
                break;
        }
        return ArrayHelper::merge($baseNode, $extNode);
    }

    /**
     * 提取wx消息节点内容
     * @param array $_xpath
     * @param $_xml
     * @return string
     */
    private function _xpathContent(array $_xpath)
    {
        try
        {
            $cnt_deep = count($_xpath);
            if (0 == $cnt_deep)
                return '';
            elseif (1 == $cnt_deep)
            {
                return $this->_xml->getElementsByTagName($_xpath[0])->item(0)->textContent;
            }
            else
            {
                $_node = $this->_xml->getElementsByTagName($_xpath[0])->item(0);
                for ($i = 1; $i < $cnt_deep; $i++)
                {
                    $_node = $_node->getElementsByTagName($_xpath[$i])->item(0);
                }
                return $_node->textContent;
            }
        }
        catch (Exception $e)
        {
            WxGenError::logError($e);
            return '';
        }
    }

    /**********************************************************************************/
    /*****************************  解析事件  ******************************************/
    /**********************************************************************************/

    /**
     * 解析上报地理位置事件
     * @return array
     */
    private function _transeLocationEvent()
    {
        return [
            'Event'             => $this->_xpathContent(['Event']),
            'Latitude'          => $this->_xpathContent(['Latitude']),
            'Longitude'         => $this->_xpathContent(['Longitude']),
            'Precision'         => $this->_xpathContent(['Precision']),
            'AgentID'           => $this->_xpathContent(['AgentID'])
        ];
    }

    /**
     * 解析用户进入应用事件
     * @return array
     */
    private function _transeEnteragent()
    {
        return [
            'EventKey'          => $this->_xpathContent(['EventKey']),
            'AgentID'           => $this->_xpathContent(['AgentID'])
        ];
    }


    /**********************************************************************************/
    /*****************************  解析消息  ******************************************/
    /**********************************************************************************/

    /**
     * 翻译用户发送的文本消息
     * @return array
     */
    private function _transTextMessage()
    {
        return [
            'Content'           => $this->_xpathContent(['Content']),
            'MsgId'             => $this->_xpathContent(['MsgId']),
            'AgentID'           => $this->_xpathContent(['AgentID'])
        ];
    }
}