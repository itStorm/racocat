<?php
namespace app\modules\rbac;

use Yii;
use yii\rbac\Rule;
use yii\helpers\ArrayHelper;
use app\modules\user\models\User;

class UserRoleRule extends Rule
{
    public $name = 'userRole';

    /** @inheritdoc */
    public function execute($user, $item, $params)
    {
        //Получаем массив пользователя из базы
        $user = ArrayHelper::getValue($params, 'user', User::findOne($user));
        if ($user) {
            $role = $user->role; //Значение из поля role базы данных
            if ($item->name === 'admin') {
                return $role == User::ROLE_ADMIN;
            } elseif ($item->name === 'moderator') {
                //moder является потомком admin, который получает его права
                return $role == User::ROLE_ADMIN || $role == User::ROLE_MODERATOR;
            } elseif ($item->name === 'user') {
                return $role == User::ROLE_ADMIN || $role == User::ROLE_MODERATOR
                || $role == User::ROLE_USER;
            }
        }

        return false;
    }
}