<?php
/**
 * Created by PhpStorm.
 * User: caimiao
 * Date: 16-11-10
 * Time: 下午2:54
 */

namespace app\utils\wxsdk;

use Yii;

class WxwebUser
{
    private $key_login_sess = '__wx_login';

    private static $instance;
    private function __construct(){}

    /**
     * @return WxwebUser
     */
    public static function getInstance()
    {
        if (empty(WxwebUser::$instance))
            WxwebUser::$instance = new WxwebUser();
        return WxwebUser::$instance;
    }
    /**
     * @param $user \app\models\WxLoginUser
     */
    public function setLogin($user)
    {
        Yii::$app->session->set($this->key_login_sess, ['userid' => $user->userid]);
    }

    /**
     * 是否是未登陆用户
     * @return bool
     */
    public function getIsGuest()
    {
        return empty(Yii::$app->session->get($this->key_login_sess));
    }

    /**
     * 获取登陆的用户编号
     * @return mixed|null
     */
    public function getUserid()
    {
        $login_sess = Yii::$app->session->get($this->key_login_sess);
        if (is_array($login_sess))
            return $login_sess['userid'];
        else
            return null;
    }

    /**
     * 登出
     */
    public function logOut()
    {
        Yii::$app->session->remove($this->key_login_sess);
    }
}