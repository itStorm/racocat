<?php

namespace app\modules\main\controllers;

use app\modules\article\models\Article;
use Yii;
use yii\captcha\CaptchaAction;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\filters\VerbFilter;
use app\modules\main\models\ContactForm;

class DefaultController extends Controller
{
	/**
	 * Количество статей на странице
	 */
	const ARTICLES_COUNT_PER_PAGE = 6;

	public function behaviors()
	{
		return [
			'access' => [
				'class' => AccessControl::className(),
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
				'class' => VerbFilter::className(),
				'actions' => [
					'logout' => ['post'],
				],
			],
		];
	}

	public function actions()
	{
		return [
			'error' => [
				'class' => 'yii\web\ErrorAction',
			],
			'captcha' => [
				'class' => CaptchaAction::className(),
				'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
			],
		];
	}

	/**
	 * Главная страница
	 * @return string
	 */
	public function actionIndex()
	{
		$articles = Article::find()->limit(self::ARTICLES_COUNT_PER_PAGE)->all();

		return $this->render('index', [
			'articles' => $articles
		]);
	}

	/**
	 * Обратная связь
	 * @return string
	 */
	public function actionContact()
	{
		$model = new ContactForm();
		if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
			Yii::$app->session->setFlash('contactFormSubmitted');

			return $this->refresh();
		} else {
			return $this->render('contact', [
				'model' => $model,
			]);
		}
	}

	/**
	 * О проекте
	 * @return string
	 */
	public function actionAbout()
	{
		return $this->render('about');
	}
}
