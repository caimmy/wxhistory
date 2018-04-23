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
use app\lib\wxsdk\WxPicmsgItem;
use app\lib\wxsdk\WxResponseHelper;
use app\lib\wxsdk\WxSdk;
use app\models\AppWxDrawqian;
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
                if ('抽签' == $wx_up_msg->Content) {
                    $cur_qian = $this->DrawOneQian($wx_up_msg->FromUserName);
                    if (is_object($cur_qian)) {
                        echo WxResponseHelper::genResponsePicMsg($wx_up_msg->FromUserName, $wx_up_msg->ToUserName, [$cur_qian]);
                    } else {
                        echo WxResponseHelper::genResponseTextMsg($wx_up_msg->FromUserName, $wx_up_msg->ToUserName, '需要重新抽取');
                    }
                } else {
                    echo WxResponseHelper::genResponseTextMsg($wx_up_msg->FromUserName, $wx_up_msg->ToUserName, 'echo : ' . $wx_up_msg->Content);
                }
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

    /**
     * 抽取一支签
     * @param $fromuser
     */
    private function DrawOneQian($fromuser) {
        $qian_data = AppWxDrawqian::DrawRandomQian($fromuser);
        if (is_object($qian_data)) {
            $picmsg_item = new WxPicmsgItem();
            $picmsg_item->Title = $qian_data->title;
            $picmsg_item->Description = $qian_data->poem;
            $picmsg_item->PicUrl = $qian_data->getImageUrl();
            $picmsg_item->Url = Yii::$app->urlManager->createAbsoluteUrl(['site/chouqian', 'id' => $qian_data->id]);
            return $picmsg_item;
        }
        return false;
    }
}