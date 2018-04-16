<?php
/**
 * Created by PhpStorm.
 * User: caimm
 * Date: 2018/3/3
 * Time: 18:42
 */

namespace app\utils;

use app\models\AppDepDepartment;
use app\models\AppGovDivision;
use Yii;

class CacheHelper
{
    /**
     * 部门名称缓存
     */
    const DEPARTMENT_NAME_CACHE = 'dep_name_';
    /**
     * 乡镇代码缓存
     */
    const TOWN_CODE_CACHE = 'town_code_';
    /**
     * 村社代码缓存
     */
    const VALLEGE_CODE_CACHE = 'vallege_code_';
    /**
     * 乡镇名称缓存
     */
    const TOWN_NAME_CACHE = 'town_name_';
    /**
     * 村社名称缓存
     */
    const VALLEGE_NAME_CACHE = 'vallege_name_';

    public static function setCacheValue($key, $val) {
        Yii::$app->cache->set($key, $val, Yii::$app->params['gen_cache_expire']);
    }

    public static function getCacheValue($key) {
        return Yii::$app->cache->get($key);
    }

    /**
     * 获取部门的名称
     * @param $dep_id
     * @return mixed|string
     */
    public static function getDepartmentName($dep_id) {
        $dep_name = Yii::$app->cache->get(self::DEPARTMENT_NAME_CACHE . $dep_id);
        if (false === $dep_name) {
            $dep = AppDepDepartment::findOne($dep_id);
            if (is_object($dep)) {
                $dep_name = $dep->dep_name;
                self::setCacheValue(self::DEPARTMENT_NAME_CACHE . $dep_id, $dep_name);
            }
        }
        return $dep_name;
    }

    /**
     * 通过名称获取乡镇一级的代码
     * @param $town
     * @return mixed
     */
    public static function getTownCode($town) {
        $key = self::TOWN_CODE_CACHE . $town;
        $town_code = Yii::$app->cache->get($key);
        if (false === $town_code) {
            $town_obj = AppGovDivision::find()->where(['text' => $town, 'catalog' => AppGovDivision::CATALOG_TOWNSHIP])->one();
            if (is_object($town_obj)) {
                $town_code = $town_obj->scode;
                self::setCacheValue($key, $town_code);
            }
        }
        return $town_code;
    }

    /**
     * 通过名称取村社一级的编码
     * @param $town
     * @param $vallege
     */
    public static function getVallegeCode($town, $vallege) {
        $key = self::VALLEGE_CODE_CACHE . $town . $vallege;
        $vallege_code = Yii::$app->cache->get($key);
        if (false === $vallege_code) {
            $town_obj = AppGovDivision::find()->where(['text' => $town, 'catalog' => AppGovDivision::CATALOG_TOWNSHIP])->one();
            if (is_object($town_obj)) {
                $vallege_obj = AppGovDivision::find()->where(['text' => $vallege, 'parent' => $town_obj->id, 'catalog' => AppGovDivision::CATALOG_VILLAGE])->one();
                if (is_object($vallege_obj)) {
                    $vallege_code = $vallege_obj->scode;
                    self::setCacheValue($key, $vallege_code);
                }
            }
        }
        return $vallege_code;
    }

    /**
     * 根据村社代码获取村社名称
     * @param $scode
     * @return mixed
     */
    public static function getVallegeName($scode) {
        $key = self::VALLEGE_NAME_CACHE . $scode;
        $vallege_name = Yii::$app->cache->get($key);
        if (false === $vallege_name) {
            $vallege = AppGovDivision::find()->where(['scode' => $scode, 'catalog' => AppGovDivision::CATALOG_VILLAGE])->one();
            if (is_object($vallege)) {
                $vallege_name = $vallege->text;
                self::setCacheValue($key, $vallege_name);
            }
        }
        return $vallege_name;
    }

    /**
     * 根据乡镇代码获取乡镇名称
     * @param $scode
     * @return mixed
     */
    public static function getTownName($scode) {
        $key = self::TOWN_NAME_CACHE . $scode;
        $town_name = Yii::$app->cache->get($key);
        if (false === $town_name) {
            $town = AppGovDivision::find()->where(['scode' => $scode, 'catalog' => AppGovDivision::CATALOG_TOWNSHIP])->one();
            if (is_object($town)) {
                $town_name = $town->text;
                self::setCacheValue($key, $town_name);
            }
        }
        return $town_name;
    }
}