<?php

namespace app\models\raw;

use Yii;

/**
 * This is the model class for table "wx_drawqian".
 *
 * @property int $id
 * @property string $fromuser
 * @property string $draw_tm
 * @property int $qianid
 */
class WxDrawqian extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'wx_drawqian';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['fromuser', 'draw_tm', 'qianid'], 'required'],
            [['draw_tm'], 'safe'],
            [['qianid'], 'integer'],
            [['fromuser'], 'string', 'max' => 128],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'fromuser' => 'Fromuser',
            'draw_tm' => 'Draw Tm',
            'qianid' => 'Qianid',
        ];
    }
}
