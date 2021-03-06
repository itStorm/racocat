<?php

use yii\db\Schema;
use yii\db\Migration;
use app\modules\user\models\User;

class m150901_214648_add_role_for_users extends Migration
{
    public function up()
    {
        $this->addColumn('users', 'role', Schema::TYPE_INTEGER . ' NOT NULL DEFAULT ' . User::ROLE_USER);
    }

    public function down()
    {
        $this->dropColumn('users', 'role');
    }
}
