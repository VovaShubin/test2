<?php
namespace app\controllers;

use Yii;
use yii\web\Controller;
use app\models\StatementForm;
use yii\web\UploadedFile;

class StatementController extends Controller
{
    public function actionIndex()
    {
        $model = new StatementForm();
        if (Yii::$app->request->isPost) {
            $model->file = UploadedFile::getInstance($model, 'file');
            if ($model->validate()) {
                $content = file_get_contents($model->file->tempName);
                [$balances, $errors] = $this->parseStatement($content);
                if ($errors) {
                    Yii::$app->session->setFlash('error', implode('<br>', $errors));
                } else {
                    return $this->render('result', ['balances' => $balances]);
                }
            }
        }
        return $this->render('index', ['model' => $model]);
    }

    private function parseStatement($html)
    {
        $errors = [];
        $balances = [];
        $profitCol = null;
        $dom = new \DOMDocument();
        @$dom->loadHTML($html);
        $tables = $dom->getElementsByTagName('table');
        if ($tables->length == 0) {
            $errors[] = 'В файле не найдена таблица.';
            return [[], $errors];
        }
        $table = $tables->item(0);
        $rows = $table->getElementsByTagName('tr');
        if ($rows->length == 0) {
            $errors[] = 'В таблице нет строк.';
            return [[], $errors];
        }
        // Найти столбец profit
        foreach ($rows as $i => $row) {
            $cells = $row->getElementsByTagName('td');
            if ($cells->length == 0) {
                $cells = $row->getElementsByTagName('th');
            }
            foreach ($cells as $j => $cell) {
                if (mb_strtolower(trim($cell->textContent)) == 'profit') {
                    $profitCol = $j;
                    break 2;
                }
            }
        }
        if ($profitCol === null) {
            $errors[] = 'Не найден столбец profit.';
            return [[], $errors];
        }
        // Считать баланс
        $balance = 0;
        foreach ($rows as $i => $row) {
            $cells = $row->getElementsByTagName('td');
            if ($cells->length > $profitCol) {
                $profit = str_replace(',', '.', trim($cells->item($profitCol)->textContent));
                if (is_numeric($profit)) {
                    $balance += (float)$profit;
                    if ($balance < 0) $balance = 0;
                    $balances[] = $balance;
                }
            }
        }
        if (empty($balances)) {
            $errors[] = 'Нет строк с числовым profit.';
        }
        return [$balances, $errors];
    }
} 