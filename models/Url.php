<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "url".
 *
 * @property int $id
 * @property string $website
 * @property string $shot
 * @property string|null $qr
 */
class Url extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'url';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['qr'], 'default', 'value' => null],
            [['shot'], 'default', 'value' => ''],
            [['qr'], 'string'],
            [['website', 'shot'], 'string', 'max' => 50],
			['website', 'url', 'defaultScheme' => 'http'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'website' => 'Website',
            'shot' => 'Shot',
            'qr' => 'Qr',
        ];
    }

}
