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
        $typeCol = null;
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
        // Найти столбцы profit и type
        foreach ($rows as $i => $row) {
            $cells = $row->getElementsByTagName('td');
            if ($cells->length == 0) {
                $cells = $row->getElementsByTagName('th');
            }
            foreach ($cells as $j => $cell) {
                $text = mb_strtolower(trim($cell->textContent));
                if ($text == 'profit') {
                    $profitCol = $j;
                }
                if ($text == 'type') {
                    $typeCol = $j;
                }
            }
            if ($profitCol !== null && $typeCol !== null) {
                break;
            }
        }
        if ($profitCol === null) {
            $errors[] = 'Не найден столбец profit.';
            return [[], $errors];
        }
        // Найти стартовый баланс
        $startBalance = null;
        foreach ($rows as $i => $row) {
            $cells = $row->getElementsByTagName('td');
            if ($cells->length > 0) {
                $type = $typeCol !== null && $cells->length > $typeCol ? mb_strtolower(trim($cells->item($typeCol)->textContent)) : null;
                if ($type === 'balance') {
                    // Ищем последнее числовое значение в строке
                    $lastNumeric = null;
                    foreach ($cells as $cell) {
                        $val = str_replace([',', ' '], ['.', ''], trim($cell->textContent));
                        if (is_numeric($val)) {
                            $lastNumeric = (float)$val;
                        }
                    }
                    if ($lastNumeric !== null) {
                        $startBalance = $lastNumeric;
                        break;
                    }
                }
            }
        }
        // Если не нашли строку balance, ищем первую строку с числовым profit
        if ($startBalance === null) {
            foreach ($rows as $i => $row) {
                $cells = $row->getElementsByTagName('td');
                if ($cells->length > $profitCol) {
                    $profitRaw = trim($cells->item($profitCol)->textContent);
                    $profit = str_replace([',', ' '], ['.', ''], $profitRaw);
                    if (is_numeric($profit)) {
                        $startBalance = (float)$profit;
                        break;
                    }
                }
            }
        }
        if ($startBalance === null) {
            $errors[] = 'Не найден стартовый баланс.';
            return [[], $errors];
        }
        $balance = $startBalance;
        $balances[] = $balance;
        $first = true;
        foreach ($rows as $i => $row) {
            $cells = $row->getElementsByTagName('td');
            if ($cells->length > 0) {
                $type = $typeCol !== null && $cells->length > $typeCol ? mb_strtolower(trim($cells->item($typeCol)->textContent)) : null;
                if ($type === 'balance') {
                    // Для строк balance ищем последнее числовое значение
                    $lastNumeric = null;
                    foreach ($cells as $cell) {
                        $val = str_replace([',', ' '], ['.', ''], trim($cell->textContent));
                        if (is_numeric($val)) {
                            $lastNumeric = (float)$val;
                        }
                    }
                    if ($lastNumeric !== null) {
                        if ($first && $lastNumeric === $startBalance) {
                            $first = false;
                            continue;
                        }
                        $balance += $lastNumeric;
                        if ($balance < 0) $balance = 0;
                        $balances[] = $balance;
                    }
                } elseif ($cells->length > $profitCol) {
                    // Обычная логика для остальных строк
                    $profitRaw = trim($cells->item($profitCol)->textContent);
                    $profit = str_replace([',', ' '], ['.', ''], $profitRaw);
                    if (is_numeric($profit)) {
                        $balance += (float)$profit;
                        if ($balance < 0) $balance = 0;
                        $balances[] = $balance;
                    }
                }
            }
        }
        if (empty($balances)) {
            $errors[] = 'Нет строк с числовым profit.';
        }
        return [$balances, $errors];
    }
} 