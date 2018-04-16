<?php
/**
 * Created by PhpStorm.
 * User: caimiao
 * Date: 17-6-1
 * Time: 下午12:13
 *
 * 组织架构辅助类
 */


namespace app\utils;
use app\utils\wxsdk\AccessEntry;
use Yii;

class OrgnizeHelper
{
    private static $instance = null;

    const CACHE_KEY_TAG_NAME = 'c_o_tag_';
    const CACHE_DURATIONS = 43200;

    /**
     * @return OrgnizeHelper
     */
    public static function getInstance()
    {
        if (null == OrgnizeHelper::$instance)
            OrgnizeHelper::$instance = new OrgnizeHelper();
        return OrgnizeHelper::$instance;
    }

    private function __construct()
    {

    }

    /**
     * 通过标签名获取标签编号
     * @param $tag_name
     * @return bool|mixed
     */
    public function getTagidByName($tag_name)
    {
        $tag_info = Yii::$app->cache->get(self::CACHE_KEY_TAG_NAME . $tag_name);
        if (empty($tag_info))
        {
            $tag_list = AccessEntry::getInstance()->getTaglist();
            if (is_array($tag_list) && isset($tag_list['taglist']))
            {
                foreach ($tag_list['taglist'] as $tag_item)
                {
                    if ($tag_name == $tag_item['tagname'])
                    {
                        Yii::$app->cache->set(self::CACHE_KEY_TAG_NAME . $tag_name, serialize($tag_item), self::CACHE_DURATIONS);
                        $tag_info = $tag_item;
                        break;
                    }
                }
            }
        }
        else
            $tag_info = unserialize($tag_info);
        if (is_array($tag_info))
            return $tag_info['tagid'];
        else
            return false;
    }
}