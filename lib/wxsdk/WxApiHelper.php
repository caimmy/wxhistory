<?php
/**
 * Created by PhpStorm.
 * User: caimiao
 * Date: 2018/1/7
 * Time: 19:32
 */

namespace app\lib\wxsdk;


class WxApiHelper
{
    /**
     * 获取微信访问口令
     */
    const URL_GET_ACCESSTOKEN           = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=%s&secret=%s';

    const URL_OAUTH_URL_TPL             = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=%s&redirect_uri=%s&response_type=code&scope=%s&state=%s#wechat_redirect';

    const URL_OAUTH_URL_ACCESSTOKEN     = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid=%s&secret=%s&code=%s&grant_type=authorization_code';

    const URL_GET_JSAPI_ACCESSTOKEN     = 'https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=%s&type=jsapi';
    /**
     * 获取微信用户信息
     */
    const URL_GET_USERINFO              = 'https://api.weixin.qq.com/cgi-bin/user/info?access_token=%s&openid=%s&lang=zh_CN';
}