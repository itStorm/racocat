<?php

namespace app\modules\user\models;

use Yii;
use yii\base\Model;

/**
 * LoginForm is the model behind the login form.
 */
class RegistrationForm extends Model
{
	public $username;
	public $email;
	public $password;
	public $verifyCode;

	/**
	 * @return array the validation rules.
	 */
	public function rules()
	{
		return [
			['username', 'filter', 'filter' => 'trim'],
			['username', 'required'],
			['username', 'string', 'min' => 2, 'max' => 32],
			['username', 'match', 'pattern' => '/^[\w_-а-яА-ЯёЁ]+$/i'],

			['email', 'filter', 'filter' => 'trim'],
			['email', 'required'],
			['email', 'email'],
			['email', 'unique', 'targetClass' => User::className()],

			['password', 'required'],
			['password', 'string', 'min' => 6, 'max' => 32],

			['verifyCode', 'captcha', 'captchaAction' => '/user/default/captcha'],
		];
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels()
	{
		return [
			'username' => Yii::t('app', 'Username'),
			'email'      => Yii::t('app', 'Email'),
			'password'   => Yii::t('app', 'Password'),
			'verifyCode' => Yii::t('app', 'Verify Code'),
		];
	}

	/**
	 * Сохранить нового пользователя пользователя
	 * @return bool
	 */
	public function registration() {
		if(!$this->validate()) {
			return false;
		}

		$user           = new User();
		$user->username = $this->username;
		$user->email    = $this->email;
		$user->role = User::ROLE_USER;
		$user->setPassword($this->password);

		return $user->save();
	}
}
