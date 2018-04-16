<?php

namespace app\utils\wxsdk;
use app\models\WxGenError;
use phpDocumentor\Reflection\Types\Null_;
use Yii;
use \Requests;
use yii\base\Exception;
use yii\helpers\Json;

/**
 * Created by PhpStorm.
 * User: caimiao
 * Date: 16-11-8
 * Time: 下午2:09
 */
class AccessEntry
{
    const URL_API_PLAT = 'https://qyapi.weixin.qq.com/cgi-bin/';
    const TIMEOUT_SECOND = 7000;

    /**
     * 用户状态
     */
    const USER_ALL = 0;
    const USER_FOLLOWED = 1;
    const USER_FORBIDDEN = 2;
    const USER_UNFOLLOWED = 4;

    private $CORPID;
    private $CORPID_TOP_ADMIN;

    /**
     * 获取、更新访问令牌
     */
    const CGI_GETTOKEN = 'gettoken';
    /**
     * 微信网关列表
     */
    // 发送消息
    const CGI_SEND_MESSAGE = 'message/send';
    // 置换Auth2.0的用户代码
    const CGI_GET_USERINFO_VIA_AUTH20CODE = 'user/getuserinfo';

    /***** 获取企业号应用列表 *****/
    const CGI_AGENT_LIST = 'agent/list';
    /***** 获取企业号应用信息 *****/
    const CGI_AGENT_GET = 'agent/get';

    /***** 获取应用菜单列表 *****/
    const CGI_MENU_GET = 'menu/get';

    /***** 获取部门列表 *****/
    const CGI_DEPARTMENT_LIST = 'department/list';

    /***** 用户管理 *****/
    // 获取用户信息
    const CGI_GET_USERINFO = 'user/get';
    // 通过oauth code获取用户信息
    const CGI_GET_CODE2USERINFO = 'user/getuserinfo';
    // 获取指定部门下用户简单详情
    const CGI_USER_SIMPLELIST = 'user/simplelist';
    // oauth2.0 openid转userid
    const CGI_USER_CONVERT_TO_USER_ID = 'user/convert_to_userid';
    // oauth2.0 userid转openid
    const CGI_USER_CONVERT_TO_OPEN_ID = 'user/convert_to_openid';

    /***** 标签管理 *****/
    // 获取标签列表
    const CGI_TAG_LIST = 'tag/list';
    // 获取标签成员
    const CGI_TAG_GET = 'tag/get';
    // 创建标签
    const CGI_TAG_CREATE = 'tag/create';

    /***** 素材管理 *****/
    // 获取素材列表
    const CGI_MATERIAL_BATCHGET = 'material/batchget';
    // 获取素材总数
    const CGI_MATERIAL_GETCOUNT = 'material/get_count';
    // 上传永久素材
    const CGI_MATERIAL_ADD = 'material/add_material';
    // 获取永久素材
    const CGI_MATERIAL_GET = 'material/get';
    // 删除永久素材
    const CGI_MATERIAL_DEL = 'material/del';

    private static $instance;
    private function __construct($corpid, $admin){
        $this->CORPID = $corpid;
        $this->CORPID_TOP_ADMIN = $admin;
    }

    /**
     * 单例构造
     * @return AccessEntry
     */
    public static function getInstance()
    {
        if (empty(AccessEntry::$instance))
            AccessEntry::$instance = new AccessEntry(Yii::$app->params['corpid'], Yii::$app->params['top_admin_secret']);
        return AccessEntry::$instance;
    }

    /**
     * 获取企业应用管理组编号
     * @param $corpid
     */
    public function getAccessToken($mgr_group = null)
    {
        if (empty($mgr_group))
            $mgr_group = $this->CORPID_TOP_ADMIN;
        $token_cache = Yii::$app->cache->get($mgr_group);
        if (empty($token_cache) || (time() > $token_cache['timeout']))
        {
            // 更新AccessToken缓存
            // 缓存格式 token=>'', timeout=>'' token及超时时间
            $token = $this->updateAccessToken($mgr_group);
        }
        else
            $token = $token_cache['token'];
        return $token;
    }

    /**
     * 获取符合Ｏauth标准的访问地址
     * @param $url
     * @param $state
     */
    public function genOauthvisiturl($url, $agentid, $state, $asmember=true)
    {
        $raw_url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=%s&redirect_uri=%s&response_type=code&scope=%s&agentid=%d&state=%s#wechat_redirect";
        $scope = $asmember ? 'snsapi_userinfo' : 'snsapi_base';
        return sprintf($raw_url, $this->CORPID, urlencode($url), $scope, $agentid, $state);
    }

    /**
     * 更新微信访问令牌
     * @param $corpid
     * @param $mgr_group 管理组，对应的secret
     * @return string
     */
    private function updateAccessToken($mgr_group)
    {
        $token = 'nosettoken';
        $url = sprintf('%s%s?corpid=%s&corpsecret=%s', self::URL_API_PLAT, self::CGI_GETTOKEN, $this->CORPID, $mgr_group);
        $resp_req = Requests::get($url);
        if ($resp_req->success)
        {
            try
            {
                $token_info = Json::decode($resp_req->body);
                if (is_array($token_info) && isset($token_info['access_token']))
                {
                    $_new_token = $token_info['access_token'];
                    if (Yii::$app->cache->set($mgr_group, ['token'=>$_new_token, 'timeout'=>time()+self::TIMEOUT_SECOND], self::TIMEOUT_SECOND))
                    {
                        $token = $_new_token;
                    }
                }
            }
            catch (Exception $e)
            {
                WxGenError::logError($e);
            }
        }

        return $token;
    }

    /**
     * 构造请求url
     * @param $url string
     * @param $params array
     */
    public function genRequestUrl($cgi, $params=[])
    {
        if (!array_key_exists('access_token', $params))
            $params['access_token'] = $this->getAccessToken();
        return sprintf('%s%s?%s', self::URL_API_PLAT, $cgi, http_build_query($params));
    }

    private function parseResponse($url, $body=null)
    {
        if (null == $body)
            $response = WxResponseParser::analysis(Requests::get($url));
        else
        {
            $response = WxResponseParser::analysis(Requests::post($url, [], Json::encode($body)));
        }

        return $response->isSuccess() && $response->wxRespOK() ? $response->getData() : null;
    }

    /**
     * 上传文件
     * @param $url
     * @param $updata
     * @return mixed
     */
    private function uploadMedia($url, $updata)
    {
        $ret_info = null;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SAFE_UPLOAD, TRUE);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $updata);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $response = curl_exec($ch);
        try
        {
            $ret_info = Json::decode($response);
        }
        catch (Exception $e)
        {
            Yii::error(print_r($e, true));
            $ret_info = null;
        }
        return $ret_info;
    }

    /********************************* 用户管理 ************************************/

    /**
     * 获取微信用户信息
     * @param $userid
     * @return WxResponseParser
     */
    public function getUserInfo($userid)
    {
        $cache_key = self::CGI_GET_USERINFO . '_' . $userid;
        $cached_info = Yii::$app->cache->get($cache_key);
        if (empty($cached_info))
        {
            $req_url = $this->genRequestUrl(self::CGI_GET_USERINFO, ['userid' => $userid]);
            $user_info = $this->parseResponse($req_url);
            if (is_array($user_info) && isset($user_info['errcode']) && (0 == $user_info['errcode']))
            {
                Yii::$app->cache->set($cache_key, $user_info, 3600);
                return $user_info;
            }
            return null;
        }
        return $cached_info;
    }

    /**
     * Oauth code 到 userid 的信息置换
     * @param $code
     * @return array|null
     */
    public function OauthCode2Userid($code)
    {
        $req_url = $this->genRequestUrl(self::CGI_GET_CODE2USERINFO, ['code' => $code]);
        $response = Requests::get($req_url);
        if ($response->success)
        {
            try
            {
                $data_info = Json::decode($response->body);
            }
            catch (Exception $e)
            {
                $data_info = NULL;
            }

            return $data_info;
        }
        return NULL;
    }

    /**
     * 置换非企业号用户的openid
     * @param $code
     * @return mixed|null
     */
    public function OauthCode2Openid($code)
    {
        $req_url = $this->genRequestUrl(self::CGI_GET_CODE2USERINFO, ['code' => $code]);
        $response = Requests::get($req_url);
        if ($response->success)
        {
            try
            {
                $data_info = Json::decode($response->body);
            }
            catch (Exception $e)
            {
                $data_info = NULL;
            }
            return $data_info;
        }
        return NULL;
    }

    /**
     * userid置换openid
     * @param $userid
     * @param $agentid
     * @return array|null
     */
    public function UseridConvertOpenid($userid, $agentid = 0)
    {
        $req_url = $this->genRequestUrl(self::CGI_USER_CONVERT_TO_OPEN_ID, []);
        $ext_params = (0 == $agentid) ? ['userid' => $userid] : ['userid' => $userid, 'agentid' => $agentid];
        $response = $this->parseResponse($req_url, $ext_params);
        if (is_array($response) && (0 == $response['errcode']))
            return ['openid' => $response['openid']];
        else
            return null;
    }

    public function OpenidConvertUserid($openid)
    {
        $req_url = $this->genRequestUrl(self::CGI_USER_CONVERT_TO_USER_ID);
        // TODO
        //return $this->parseResponse($req_url, ['openid'])
    }

    /********************************* 应用管理 ************************************/
    /**
     * 获取当前微信企业号的应用
     */
    public function getAgentList()
    {
        $req_url = $this->genRequestUrl(self::CGI_AGENT_LIST, []);
        return $this->parseResponse($req_url);
    }

    /**
     * 获取指定应用的详细信息
     * @param $agent_id
     * @return WxResponseParser|null
     */
    public function getAgentInfo($agent_id)
    {
        $req_url = $this->genRequestUrl(self::CGI_AGENT_GET, ['agentid' => $agent_id]);
        return $this->parseResponse($req_url);
    }

    /**
     * 获取指定应用的菜单列表
     * @param $agent_id
     * @return array|null
     */
    public function getMenulist($agent_id)
    {
        $req_url = $this->genRequestUrl(self::CGI_MENU_GET, ['agentid' => $agent_id]);
        return $this->parseResponse($req_url);
    }

    /**
     * 获取企业号的部门列表
     * @return array|null
     */
    public function getDepartmentlist()
    {
        $req_url = $this->genRequestUrl(self::CGI_DEPARTMENT_LIST, []);
        return $this->parseResponse($req_url);
    }

    /**
     * 获取部门下用户列表（简单信息）
     * @param $dep_id
     * @param $user_status 用户状态 0获取全部成员，1获取已关注成员列表，2获取禁用成员列表，4获取未关注成员列表。status可叠加，未填写则默认为4
     * @return array|null
     */
    public function getUserlistSimple($dep_id, $user_status=self::USER_FOLLOWED)
    {
        $req_url = $this->genRequestUrl(self::CGI_USER_SIMPLELIST, [
            'department_id' => $dep_id,
            'fetch_child' => '1',
            'status' => $user_status
        ]);
        $resp_data = $this->parseResponse($req_url);
        if (is_array($resp_data) && isset($resp_data['userlist']))
            return $resp_data['userlist'];
        else
            return [];
    }

    /**
     * 获取标签列表
     * @return array|null
     */
    public function getTaglist()
    {
        $req_url = $this->genRequestUrl(self::CGI_TAG_LIST, []);
        return $this->parseResponse($req_url);
    }

    /**
     * 获取标签下的成员列表
     * @param $tag_id
     */
    public function getTagusers($tag_id)
    {
        $req_url = $this->genRequestUrl(self::CGI_TAG_GET, ['tagid' => $tag_id]);
        return $this->parseResponse($req_url);
    }

    /**
     * 新建标签
     * @param $tag_name
     * @return array|null
     */
    public function createTag($tag_name)
    {
        $req_url = $this->genRequestUrl(self::CGI_TAG_CREATE, []);
        return $this->parseResponse($req_url, ['tagname' => $tag_name]);
    }

    /**
     * 批量获取图片素材列表
     * @param int $offset
     * @param int $count
     * @return array|null
     */
    public function getImageMaterialList($offset=0, $count=50)
    {
        $req_url = $this->genRequestUrl(self::CGI_MATERIAL_BATCHGET, []);
        return $this->parseResponse($req_url, ['type' => 'image', 'offset' => $offset, 'count' => $count]);
    }

    /**
     * 获取素材总数
     * @return array|null
     */
    public function getMaterialCount()
    {
        $req_url = $this->genRequestUrl(self::CGI_MATERIAL_GETCOUNT, []);
        return $this->parseResponse($req_url);
    }

    /**
     * 新增素材
     * @param $type
     * @param $media_info
     * @return array|null
     */
    public function addMaterial($type, $media_info)
    {
        $req_url = $this->genRequestUrl(self::CGI_MATERIAL_ADD, ['type' => $type]);
        return $this->uploadMedia($req_url, $media_info);
    }

    /**
     * 获取素材
     * @param $media_id
     * @return array|null
     */
    public function getMaterialnormal($media_id)
    {
        $req_url = $this->genRequestUrl(self::CGI_MATERIAL_GET, ['media_id' => $media_id]);
        return Requests::get($req_url);
    }

    /**
     * 删除永久素材
     * @param $media_id
     * @return array|null
     */
    public function delMaterial($media_id)
    {
        $req_url = $this->genRequestUrl(self::CGI_MATERIAL_DEL, ['media_id' => $media_id]);
        return $this->parseResponse($req_url);
    }
}