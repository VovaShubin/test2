<?php
namespace app\models;

use yii\base\Model;
use yii\web\UploadedFile;

class StatementForm extends Model
{
    /** @var UploadedFile */
    public $file;

    public function rules()
    {
        return [
            [['file'], 'file', 'skipOnEmpty' => false, 'extensions' => 'html,htm', 'checkExtensionByMimeType' => false],
        ];
    }

    public function attributeLabels()
    {
        return [
            'file' => 'HTML-отчет',
        ];
    }
} 