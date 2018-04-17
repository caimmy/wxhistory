<?php

namespace app\models\raw;

use Yii;

/**
 * This is the model class for table "mgr_wxup_msg".
 *
 * @property int $id
 * @property string $ToUserName
 * @property string $FromUserName
 * @property int $CreateTime
 * @property string $MsgType
 * @property string $Content
 * @property string $MsgId
 */
class MgrWxupMsg extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'mgr_wxup_msg';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['ToUserName', 'FromUserName', 'CreateTime', 'MsgType', 'Content', 'MsgId'], 'required'],
            [['CreateTime'], 'integer'],
            [['Content'], 'string'],
            [['ToUserName', 'FromUserName'], 'string', 'max' => 128],
            [['MsgType'], 'string', 'max' => 20],
            [['MsgId'], 'string', 'max' => 32],
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
            'Content' => 'Content',
            'MsgId' => 'Msg ID',
        ];
    }
}
