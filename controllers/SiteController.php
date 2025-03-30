<?php

namespace app\controllers;

use Da\QrCode\Action\QrCodeAction;
use Da\QrCode\QrCode;
use Yii;
use yii\db\Exception;
use yii\filters\AccessControl;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;
use linslin\yii2\curl;

class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
			'qr' => [
				'class' => QrCodeAction::className(),
				'text' => 'https://basic',
				'param' => 'v',
				'commponent' => 'qr' // if configured in our app as `qr`
			]
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
	public function actionIndex()
	{
		$model = new \app\models\Url();
		$counter = new \app\models\Counter();

		// проверка на шорт адрес и защита от инъекции обрезка до 8 символов
		if ($shot = substr(Yii::$app->request->get('s')??'',0,8)){
			try {
				$url = $model::find()
					->where(['shot' => $shot])
					->one();
			}catch (Exception $e){
			}
			// если в бд есть такой линк то считаем переходы и делаем редирект
			if ($url) {
				$ip = Yii::$app->request->userIP;
				if ($count = $counter::find()->where(['ip' => $ip,'link' => $shot])->one()){
					$count->count += $count->count;
					$count->save();
				}
				else {
					$counter->count = 1;
					$counter->ip = $ip;
					$counter->link = $shot;
					$counter->save();
				}
				return $this->redirect($url->website, 301);
			}
			else return "Данный URL не найден";
		}

		// обычная загрузка главной страницы с проверкой формы
		if ($model->load(Yii::$app->request->post())) {
			if ($model->validate()) {
				$data = \Yii::$app->request->post('Url', []);
				$model->website = isset($data['website']) ? $data['website'] : 'https://basic';
				// проверка в бд такого линка
				if ($url = $model::find()->where(['website' => $model->website])->one()){
					$res['qr'] = '<img src="' . $url->qr . '">';
					$res['shot'] = Url::base(true)."?s=".$url->shot;
				}
				// если нет проверяем линк и записываем в бд
				else{
					$curl = new curl\Curl();
					$response = $curl->setOption(CURLOPT_SSL_VERIFYPEER, false)
						->post($model->website);
					if ($curl->responseCode != 200) $res['error']='Данный URL не доступен';
					else {
						// формирование шорт адреса перевод времени юникс и 3 чисел в 36 систему исчисления + нет колизий
						// и одновремено до 900 запросов не вернут одинаковый результат можно увеличить размер соли
						$model->shot = base_convert(time().rand(100,999),10,36);
						$res['shot'] = Url::base(true)."?s=".$model->shot;
						$model->qr = (new QrCode($res['shot']))
							->setSize(250)
							->setMargin(5)
							->setBackgroundColor(51, 153, 255)->writeDataUri();
						if (!$model->save()) $res['error'] = $model->errors;
						else {
							$res['succes'] = 'succes';
							$res['qr'] = '<img src="' . $model->qr . '">';
						}
					}
				}
				// ответ на запрос qr-кода
				if(\Yii::$app->request->isAjax){
					return $this->asJson($res);
				}
			}
		}

		return $this->render('index', [
			'model' => $model,
		]);
	}

    /**
     * Login action.
     *
     * @return Response|string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }

        $model->password = '';
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return Response|string
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');

            return $this->refresh();
        }
        return $this->render('contact', [
            'model' => $model,
        ]);
    }

    /**
     * Displays about page.
     *
     * @return string
     */
    public function actionAbout()
    {
        return $this->render('about');
    }
}
