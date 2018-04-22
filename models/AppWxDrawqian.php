<?php
/**
 * Created by PhpStorm.
 * User: caimm
 * Date: 2018/4/22
 * Time: 20:51
 */

namespace app\models;


use app\models\raw\WxDrawqian;

class AppWxDrawqian extends WxDrawqian
{
    /**
     * 获得已知签
     * @param $fromuser
     * @param null $day
     * @return array|mixed|null|\yii\db\ActiveRecord
     * @throws \Exception
     */
    public static function DrawRandomQian($fromuser, $day=NULL) {
        $today = (NULL == $day) ? date('Y-m-d') : $day;
        $qian_item = null;
        $cur_qian = AppWxDrawqian::find()->where(['fromuser' => $fromuser, 'draw_tm' => $today])->one();
        if (empty($cur_qian)) {
            $rand_draw = mt_rand(1, 100);
            $qian_item = AppWxGuanyinqian::findOne($rand_draw);
            $today_qian = new AppWxDrawqian();
            $today_qian->fromuser = $fromuser;
            $today_qian->draw_tm = $today;
            $today_qian->qianid = $qian_item->id;
            $today_qian->save();
        } else {
            $qian_item = AppWxGuanyinqian::findOne($cur_qian->qianid);
        }
        return $qian_item;
    }
}