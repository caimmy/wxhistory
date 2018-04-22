<?php
/**
 * Created by PhpStorm.
 * User: caimm
 * Date: 2018/4/18
 * Time: 0:31
 */

namespace app\models;


use app\models\raw\WxGuanyinqian;
use app\utils\ExportHelper;

class AppWxGuanyinqian extends WxGuanyinqian
{

    /**
     * 通过缓存加载一支签
     * @param $id
     * @return array|mixed
     * @throws \Exception
     */
    public static function loadQian($id) {
        $key = 'qian_' . $id;
        $qian_data = \Yii::$app->cache->get($key);
        if (false === $qian_data) {
            $db_data = AppWxGuanyinqian::findOne($id);
            if (is_object($db_data)) {
                $qian_data = $db_data->getAttributes();
                \Yii::$app->cache->set($key, $qian_data);
            } else {
                throw new \Exception('draw qian failure');
            }
        }
        return $qian_data;
    }

    /**
     * 获取签图的路径
     */
    public function getImageUrl() {
        return \Yii::$app->request->getHostInfo() . \Yii::$app->request->getBaseUrl() . '/qianimg/'.$this->id.'.gif';
    }

}