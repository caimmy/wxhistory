<?php
/**
 * Created by PhpStorm.
 * User: caimiao
 * Date: 16-11-8
 * Time: 下午5:37
 */

namespace app\utils\wxsdk;


use app\utils\Random;
use yii\helpers\Json;

class WxMessageTool
{
    const MSG_TYPE_TEXT = 'text';
    const MSG_TYPE_IMAGE = 'image';

    /**
     * 消息是否加密发送
     */
    const SAFE_MSG = '1';
    CONST UNSAFE_MSG = '0';

    /**
     * 企业编号
     * @var string
     */
    private $corp_id;

    private static $instance;
    private function __construct() {
        $this->corp_id = \Yii::$app->params['corpid'];
    }

    /**
     * 被动文本消息格式
     * @var string
     */
    private static $passive_txt_fmt = '<xml>
               <ToUserName><![CDATA[%s]]></ToUserName>
               <FromUserName><![CDATA[%s]]></FromUserName> 
               <CreateTime>%d</CreateTime>
               <MsgType><![CDATA[text]]></MsgType>
               <Content><![CDATA[%s]]></Content>
            </xml>';


    /**
     * 被动图片消息格式
     * @var string
     */
    private static $passive_image_fmt = '<xml>
               <ToUserName><![CDATA[%s]]></ToUserName>
               <FromUserName><![CDATA[%s]]></FromUserName>
               <CreateTime>%d</CreateTime>
               <MsgType><![CDATA[image]]></MsgType>
               <Image>
                   <MediaId><![CDATA[%s]]></MediaId>
               </Image>
            </xml>';


    private static $passive_news_fmt = '<xml>
               <ToUserName><![CDATA[%s]]></ToUserName>
               <FromUserName><![CDATA[%s]]></FromUserName>
               <CreateTime>%d</CreateTime>
               <MsgType><![CDATA[news]]></MsgType>
               <ArticleCount>%d</ArticleCount>
               <Articles>
               %s
               </Articles>
            </xml>';

    /**
     * 单例构造
     * @return WxMessageTool
     */
    public static function getInstance() {
        if (empty(WxMessageTool::$instance)) {
            WxMessageTool::$instance = new WxMessageTool();
        }
        return WxMessageTool::$instance;
    }


    /**
     * 发送消息接口
     * @param $agentid
     * @param $content
     * @param $touser
     * @param string $toparty
     * @param string $totag
     * @param string $safe
     * @return \Requests_Response
     */
    public function sendTextMsg($agentid, $content, $touser='', $toparty='', $totag='', $safe='0')
    {
        $data_packege = [
            'touser' => $touser . '',
            'toparty' => $toparty . '',
            'totag' => $totag . '',
            'msgtype' => self::MSG_TYPE_TEXT,
            'agentid' => $agentid,
            'text' => [
                'content' => $content
            ],
            'safe' => $safe
        ];
        $msg_req_url = sprintf("%s%s?access_token=%s", AccessEntry::URL_API_PLAT, AccessEntry::CGI_SEND_MESSAGE, AccessEntry::getInstance()->getAccessToken());
        return \Requests::post($msg_req_url, [], Json::encode($data_packege));
    }

    /**
     * 发送文本卡片消息
     * @param $agentid
     * @param $title
     * @param $desc
     * @param $url
     * @param string $touser
     * @param string $toparty
     * @param string $totag
     * @param string $btntxt
     * @return \Requests_Response
     */
    public function sendTextCardMsg($agentid, $title, $desc, $url, $touser='', $toparty='', $totag='', $btntxt='详情')
    {
        $data_package = [
            'touser' => $touser . '',
            'toparty' => $toparty . '',
            'totag' => $totag . '',
            'msgtype' => 'textcard',
            'agentid' => $agentid,
            'textcard' => [
                'title' => $title,
                'description' => $desc,
                'url' => $url,
                'btntxt' => $btntxt
            ]
        ];
        $msg_req_url = sprintf("%s%s?access_token=%s", AccessEntry::URL_API_PLAT, AccessEntry::CGI_SEND_MESSAGE, AccessEntry::getInstance()->getAccessToken());
        return \Requests::post($msg_req_url, [], Json::encode($data_package));
    }

    /**
     * 发送图片消息
     * @param $agentid
     * @param $media_id
     * @param $touser
     * @param string $toparty
     * @param string $totag
     * @param string $safe
     * @return \Requests_Response
     */
    public function sendImageMsg($agentid, $media_id, $touser, $toparty='', $totag='', $safe='0')
    {
        $data_package = [
            'touser' => $touser,
            'toparty' => $toparty,
            'totag' => $totag,
            'msgtype' => 'image',
            'agentid' => $agentid,
            'image' => [
                'media_id' => $media_id
            ],
            'safe' => $safe
        ];

        $image_req_url = sprintf("%s%s?access_token=%s", AccessEntry::URL_API_PLAT, AccessEntry::CGI_SEND_MESSAGE, AccessEntry::getInstance()->getAccessToken());
        return \Requests::post($image_req_url, [], Json::encode($data_package));
    }

    /**
     * 发送新闻消息
     * @param $agentid
     * @param $articles [title, description, url, picurl]
     * @param $touser
     * @param string $toparty
     * @param string $totag
     * @param string $safe
     * @return \Requests_Response
     */
    public function sendNewsMsg($agentid, $articles, $touser, $toparty='', $totag='', $safe='0')
    {
        $data_package = [
            'touser' => $touser,
            'toparty' => $toparty,
            'totag' => $totag . '',
            'msgtype' => 'news',
            'agentid' => $agentid,
            'news' => [
                'articles' => $articles
            ]
        ];

        $news_req_url = sprintf("%s%s?access_token=%s", AccessEntry::URL_API_PLAT, AccessEntry::CGI_SEND_MESSAGE, AccessEntry::getInstance()->getAccessToken());
        return \Requests::post($news_req_url, [], Json::encode($data_package));
    }

    /**
     * 构造被动消息 - 文本消息
     * @param $content
     * @param $touser
     * @param $token
     * @param $encodingAesKey
     * @return bool
     */
    public function passiveTextMsg($content, $touser, $token, $encodingAesKey)
    {
        $timestamp = time();
        $nonce = Random::randomKey();
        $txt_info = sprintf(self::$passive_txt_fmt, $touser, $this->corp_id, $timestamp, $content);
        $wx_callback_tool = new WxCallbackTool($token, $encodingAesKey, $this->corp_id);
        if (0 === $wx_callback_tool->raw_wx_msg_crypt->EncryptMsg($txt_info, $timestamp, $nonce, $afterEncoding))
            return $afterEncoding;
        else
            return false;
    }

    /**
     * 被动响应图片消息
     * @param $media_id
     * @param $touser
     * @param $token
     * @param $encodingAesKey
     * @return bool
     */
    public function passiveImageMsg($media_id, $touser, $token, $encodingAesKey)
    {
        $timestamp = time();
        $nonce = Random::randomKey();
        $image_info = sprintf(self::$passive_image_fmt, $touser, $this->corp_id, $timestamp, $media_id);
        $wx_callback_tool = new WxCallbackTool($token, $encodingAesKey, $this->corp_id);
        if (0 == $wx_callback_tool->raw_wx_msg_crypt->EncryptMsg($image_info, $timestamp, $nonce, $afterEncoding))
            return $afterEncoding;
        else
            return false;
    }

    /**
     * 被动响应新闻消息
     * @param $news_list
     * @param $touser
     * @param $token
     * @param $encodingAesKey
     * @return bool
     */
    public function passiveNewsMsg($news_list, $touser, $token, $encodingAesKey)
    {
        $timestamp = time();
        $nonce = Random::randomKey();
        $news_count = count($news_list);
        $news_information_list = [];
        foreach ($news_list as $_news)
        {
            array_push($news_information_list, sprintf('<item>
                       <Title><![CDATA[%s]]></Title> 
                       <Description><![CDATA[%s]]></Description>
                       <PicUrl><![CDATA[%s]]></PicUrl>
                       <Url><![CDATA[%s]]></Url>
                   </item>', $_news['title'], $_news['description'], $_news['picurl'], $_news['url']));
        }
        $news_info = sprintf(self::$passive_news_fmt, $touser, $this->corp_id, $timestamp, $news_count, implode('', $news_information_list));
        recordObj($news_info);
        $wx_callback_tool = new WxCallbackTool($token, $encodingAesKey, $this->corp_id);
        if (0 == $wx_callback_tool->raw_wx_msg_crypt->EncryptMsg($news_info, $timestamp, $nonce, $afterEncoding))
            return $afterEncoding;
        else
            return false;
    }
}