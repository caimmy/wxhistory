<?php

namespace app\models\raw;

use Yii;

/**
 * This is the model class for table "mgr_config".
 *
 * @property int $id
 * @property string $name
 * @property string $value
 * @property string $memo
 * @property string $addtime
 * @property int $adder
 */
class MgrConfig extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'mgr_config';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'value', 'addtime', 'adder'], 'required'],
            [['addtime'], 'safe'],
            [['adder'], 'integer'],
            [['name'], 'string', 'max' => 64],
            [['value', 'memo'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'value' => 'Value',
            'memo' => 'Memo',
            'addtime' => 'Addtime',
            'adder' => 'Adder',
        ];
    }
}
