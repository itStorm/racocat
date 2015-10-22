<?php

namespace app\modules\article\models;

use common\behaviors\Blameable;
use common\lib\safedata\SafeDataFinder;
use Yii;
use yii\db\ActiveQuery;
use \yii\db\ActiveRecord;
use app\modules\user\models\User;
use common\behaviors\TextCutter;
use yii\helpers\Url;
use common\lib\safedata\interfaces\SafeDataInterface;


/**
 * This is the model class for table "articles".
 *
 * @property integer $id
 * @property string $title
 * @property string $content
 * @property string $created
 * @property string $updated
 * @property integer $created_by
 * @property integer $updated_by
 * @property bool $is_deleted
 * @property bool $is_enabled
 * @property User $createdBy
 * @property User $updatedBy
 * @property Tag[] $tags
 *
 * @see common\behaviors\TextCutter::cut()
 * @method string cut() cut(string $field_name, int $length)
 */
class Article extends ActiveRecord implements SafeDataInterface
{
    const RULE_VIEW = 'article_view';
    const RULE_CREATE = 'article_create';
    const RULE_UPDATE = 'article_update';


    public function behaviors()
    {
        return [
            'textCutter' => [
                'class'  => TextCutter::className(),
                'fields' => [
                    'content' => 100,
                ]
            ],
            'blameable'  => Blameable::className(),
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'articles';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['title', 'content', 'created_by', 'updated_by', 'created', 'updated'], 'required'],
            [['title'], 'string', 'max' => 255],
            ['content', 'string'],
            [['created_by', 'updated_by', 'created', 'updated'], 'integer'],
        ];
    }

    /**
     * @return ActiveQuery
     */
    public function getCreatedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }

    /**
     * @return ActiveQuery
     */
    public function getUpdatedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'updated_by']);
    }

    /**
     * @return ActiveQuery
     */
    public function getTags()
    {
        return $this->hasMany(Tag::className(), ['id' => 'tag_id'])
            ->viaTable('article_tag', ['article_id' => 'id']);
    }

    /**
     * @param int $length
     * @return string
     */
    public function getShortContent($length = null)
    {
        return $this->cut('content', $length);
    }

    /**
     * Получить Url к статье
     * @return string
     */
    public function getUrl()
    {
        return Url::to(['/article/default/view', 'id' => $this->id]);
    }

    /** @inheritdoc */
    public static function hasAccessToDisabled($user)
    {
        return $user->can(self::RULE_UPDATE);
    }

    /** @inheritdoc */
    public static function hasAccessToDeleted($user)
    {
        return $user->can(User::ROLE_NAME_ADMIN);
    }

    /**
     * Пометить удаленным
     * @return bool
     */
    public function markDeleted()
    {
        $this->is_deleted = SafeDataFinder::IS_DELETED;

        return $this->save(false, ['is_deleted']);
    }
}
