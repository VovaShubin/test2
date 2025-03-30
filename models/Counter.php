<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "counter".
 *
 * @property int $id
 * @property string $ip
 * @property string $link
 * @property int $count
 */
class Counter extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'counter';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['link'], 'default', 'value' => ''],
            [['count'], 'default', 'value' => 0],
            [['count'], 'integer'],
            [['ip', 'link'], 'string', 'max' => 50],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'ip' => 'Ip',
            'link' => 'Link',
            'count' => 'Count',
        ];
    }

}
