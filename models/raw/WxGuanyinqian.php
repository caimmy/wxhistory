<?php

namespace app\models\raw;

use Yii;

/**
 * This is the model class for table "wx_guanyinqian".
 *
 * @property int $id
 * @property string $title
 * @property string $summary
 * @property string $poem
 * @property string $qianyu
 * @property string $jieqian
 * @property string $xianji
 * @property string $story
 * @property int $img_id
 */
class WxGuanyinqian extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'wx_guanyinqian';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['title', 'summary', 'poem', 'qianyu', 'jieqian', 'xianji', 'story', 'img_id'], 'required'],
            [['img_id'], 'integer'],
            [['title', 'summary'], 'string', 'max' => 64],
            [['poem'], 'string', 'max' => 128],
            [['qianyu', 'jieqian', 'xianji', 'story'], 'string', 'max' => 256],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Title',
            'summary' => 'Summary',
            'poem' => 'Poem',
            'qianyu' => 'Qianyu',
            'jieqian' => 'Jieqian',
            'xianji' => 'Xianji',
            'story' => 'Story',
            'img_id' => 'Img ID',
        ];
    }
}
