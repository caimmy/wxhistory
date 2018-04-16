<?php
/**
 * Created by PhpStorm.
 * User: caimm
 * Date: 2018/4/16
 * Time: 20:40
 */

namespace app\controllers;


use app\lib\wxsdk\ValidateHelper;
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
        return $this->checkEchoStr();
    }
}