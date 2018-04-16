<?php

/**
 * Created by PhpStorm.
 * User: caimiao
 * Date: 2018/1/7
 * Time: 14:06
 */

namespace app\lib\wxsdk\WxMsgTransfer;

use app\models\AppMgrWxupEvent;
use app\models\AppMgrWxupMsg;

class WxTransfer
{
    const MSG_TYPE_TEXT         = 'text';
    const MSG_TYPE_IMAGE        = 'image';
    const MSG_TYPE_VOICE        = 'voice';
    const MSG_TYPE_VIDEO        = 'video';
    const MSG_TYPE_SHORTVIDEO   = 'shortvideo';
    const MSG_TYPE_LOCATION     = 'location';
    const MSG_TYPE_LINK         = 'link';

    const MSG_TYPE_EVENT        = 'event';
    const EVT_SUBSCRIBE         = 'subscribe';
    const EVT_SCANSUBSCRIBE     = 'SCAN';
    const EVT_UNSUBSCRIBE       = 'unsubscribe';
    const EVT_LOCATION          = 'LOCATION';
    const EVT_CLICK             = 'CLICK';


    protected $xml_string;
    protected $xml_doc;
    protected $xml_root_keys;
    public $data = false;

    public function __construct($text)
    {
        $this->xml_string = $text;
        $this->xml_doc = simplexml_load_string($text);
        $this->xml_root_keys = $this->parseXmlKeys();
    }

    public function __get($name)
    {
        $data = $this->toArray();
        if (is_array($data) && isset($data[$name])) {
            return $data[$name];
        }
        return null;
    }

    /**
     * 解析xml的节点
     * @return array
     */
    protected function parseXmlKeys()
    {
        $ret_keys = [];
        foreach ($this->xml_doc as $_node) {
            array_push($ret_keys, $_node->getName());
        }
        return $ret_keys;
    }

    /**
     * @return array | false
     */
    public function toArray()
    {
        if (empty($this->data)) {
            $ret_info = [];
            foreach ($this->xml_root_keys as $_key) {
                $ret_info[$_key] = $this->xml_doc->$_key.'';
            }
            $this->data = $ret_info;
        }

        return $this->data;
    }
    /**
     * @return string
     */
    public function checkType()
    {
        $msg_node = $this->xml_doc->MsgType;
        return $msg_node.'';
    }

    public function saveMsgLog()
    {
        switch ($this->checkType()) {
            case self::MSG_TYPE_TEXT:
                $this->saveTextLog();
                break;
            case self::MSG_TYPE_EVENT:
                $this->saveEventLog();
                break;
            default:
                break;
        }
    }

    private function saveTextLog()
    {
        $log_data = $this->toArray();
        if (is_array($log_data)) {
            $log = new AppMgrWxupMsg();
            foreach ($log_data as $_k => $_v) {
                $log->$_k = $_v;
            }
            $log->save();
        }
    }

    private function saveEventLog()
    {
        $log_data = $this->toArray();
        if (is_array($log_data)) {
            $log = new AppMgrWxupEvent();
            foreach ($log_data as $_k => $_v) {
                $log->$_k = $_v;
            }
            $log->save();
        }
    }
}