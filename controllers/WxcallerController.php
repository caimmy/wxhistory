<?php
/**
 * Created by PhpStorm.
 * User: caimm
 * Date: 2018/4/16
 * Time: 20:40
 */

namespace app\controllers;


use app\lib\wxsdk\ValidateHelper;
use app\lib\wxsdk\WxMsgTransfer\WxTransfer;
use app\lib\wxsdk\WxResponseHelper;
use yii\web\Controller;
use Yii;

class WxcallerController extends Controller
{
    public $enableCsrfValidation = false;

    private function checkEchoStr()
    {
        $check_str =  ValidateHelper::getInstance()->genEchoStr(
            'abcd1234',
            Yii::$app->request->get('timestamp'),
            Yii::$app->request->get('nonce')
        );
        if (Yii::$app->request->get('signature') === $check_str){
            return Yii::$app->request->get('echostr');
        } else{
            return '';
        }
    }

    public function actionInterface() {
        $wx_up_msg = new WxTransfer(file_get_contents("php://input"));
        recordObj($wx_up_msg);
        if (YII_DEBUG) {
            $wx_up_msg->saveMsgLog();
        }
        switch ($wx_up_msg->MsgType) {
            case WxTransfer::MSG_TYPE_TEXT:
                echo WxResponseHelper::genResponseTextMsg($wx_up_msg->FromUserName, 'echo : ' . $wx_up_msg->Content);
                break;
            default:
                return '';
                break;
        }
    }

    /**
     * 接收到微信服务器转发的文本消息
     * @param $packet
     */
    public function onTextMessage(WxTransfer $packet) {
    }
}