<?php
/**
 * Created by PhpStorm.
 * User: caimiao
 * Date: 16-11-9
 * Time: 下午2:07
 */

namespace app\utils\wxsdk;


use yii\helpers\Json;
use yii\web\Controller;
use Yii;

class WxpushController extends Controller
{
    public $corp_id;
    public $wx_app_id;
    public $token;
    public $encodingAesKey;
    public $enableCsrfValidation = false;

    public $wx_app_name;

    public function wx_page_exception($error)
    {
        echo "error " . $error->getMessage();
        Yii::$app->end();
    }

    public function init()
    {
        $this->corp_id = Yii::$app->params['corpid'];
        $this->wx_app_id = $this->loadWxappsetting($this->wx_app_name, 'id');
        $this->token = $this->loadWxappsetting($this->wx_app_name, 'token');
        $this->encodingAesKey = $this->loadWxappsetting($this->wx_app_name, 'key');

        set_exception_handler("wx_page_exception");

        parent::init();
    }

    /**
     * 微信用户登陆
     * @param $userinfo
     */
    protected function loginUser($userinfo)
    {
        if (is_array($userinfo))
            Yii::$app->session->set('userinfo', $userinfo);
    }

    /**
     * 获取当前登陆的用户
     * @return bool|mixed
     */
    protected function getLoginedUser(&$userinfo)
    {
        if (Yii::$app->session->has('userinfo'))
        {
            $userinfo = Yii::$app->session->get('userinfo');
            return TRUE;
        }
        else
            return FALSE;
    }

    /**
     * 加载微信app相关的配置
     * @param $app_name
     * @param $app_key
     */
    protected function loadWxappsetting($app_name, $app_key)
    {
        if (array_key_exists($app_name, Yii::$app->params['app_config']) &&
            array_key_exists($app_key, Yii::$app->params['app_config'][$app_name]))
            return Yii::$app->params['app_config'][$app_name][$app_key];
        return NULL;
    }

    protected function replyCheck()
    {
        $wx_callback_tool = new WxCallbackTool($this->token, $this->encodingAesKey, $this->corp_id);
        $res = $wx_callback_tool->VerifyURL($replyStr);
        if (0 == $res)
            return $replyStr;
        else
            return '';
    }


    /**
     * 写入微信被动响应消息
     * @param $sEncryptMsg
     */
    protected function setPassiveMessage($sEncryptMsg)
    {
        Yii::$app->response->content = 'text/xml';
        Yii::$app->response->charset = 'utf-8';
        Yii::$app->response->data = $sEncryptMsg;
    }

    protected function getOauthUserinfo()
    {
        if (array_key_exists('code', $_GET))
        {
            $user_id = AccessEntry::getInstance()->OauthCode2Userid($_GET['code']);
            if (is_array($user_id) && isset($user_id['UserId']))
                $user_info = AccessEntry::getInstance()->getUserInfo($user_id['UserId']);
        }
        elseif (WX_DEBUG())
        {
            $user_info = [
                'userid' => 'test@kingsoft.com',
                'name' => '测试账号',
                'gender' => 1,
                'avatar' => '#'
            ];
        }
        else
            $user_info = NULL;
        return $user_info;
    }

    protected function jsonResponse($response)
    {
        if (is_array($response))
            return Json::encode($response);
        elseif (is_string($response))
            return $response;
        else
            return 'bad output';
    }

    protected function genResponse()
    {
        return [
            'code' => -1,
            'msg' => 'gen error',
            'data' => []
        ];
    }

    protected function changeResponse2Success(&$response, $data=[])
    {
        $response['code']   = 0;
        $response['msg']    = 'success';
        $response['data']   = $data;
        return $response;
    }

    protected function setResponseMsg(&$response, $msg)
    {
        $response['msg'] = $msg;
        return $response;
    }

    protected function safeCaller4Notice()
    {
        return in_array(Yii::$app->request->userIP, Yii::$app->params['notice_call_allow']);
    }
}