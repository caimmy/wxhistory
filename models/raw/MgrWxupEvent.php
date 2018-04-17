<?php

namespace app\models\raw;

use Yii;

/**
 * This is the model class for table "mgr_wxup_event".
 *
 * @property int $id
 * @property string $ToUserName
 * @property string $FromUserName
 * @property int $CreateTime
 * @property string $MsgType
 * @property string $Event
 * @property string $EventKey
 * @property string $Latitude
 * @property string $Longtitude
 * @property string $Precision
 * @property string $Ticket
 */
class MgrWxupEvent extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'mgr_wxup_event';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['ToUserName', 'FromUserName', 'CreateTime', 'MsgType', 'Event'], 'required'],
            [['CreateTime'], 'integer'],
            [['ToUserName', 'FromUserName'], 'string', 'max' => 128],
            [['MsgType', 'Event'], 'string', 'max' => 32],
            [['EventKey', 'Latitude', 'Longtitude', 'Precision', 'Ticket'], 'string', 'max' => 64],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'ToUserName' => 'To User Name',
            'FromUserName' => 'From User Name',
            'CreateTime' => 'Create Time',
            'MsgType' => 'Msg Type',
            'Event' => 'Event',
            'EventKey' => 'Event Key',
            'Latitude' => 'Latitude',
            'Longtitude' => 'Longtitude',
            'Precision' => 'Precision',
            'Ticket' => 'Ticket',
        ];
    }
}
