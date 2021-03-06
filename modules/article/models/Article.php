<?php

namespace app\modules\article\models;

use common\behaviors\BlameableBehavior;
use common\behaviors\SlugBehavior;
use common\libs\safedata\SafeDataFinder;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use \yii\db\ActiveRecord;
use app\modules\user\models\User;
use common\behaviors\TextCutterBehavior;
use yii\helpers\Url;
use common\libs\safedata\interfaces\SafeDataInterface;
use app\modules\filestorage\models\File;


/**
 * This is the model class for table "articles".
 *
 * @property integer $id
 * @property string $title
 * @property string $description
 * @property string $content
 * @property int $created
 * @property int $updated
 * @property int $published_date
 * @property integer $created_by
 * @property integer $updated_by
 * @property bool $is_deleted
 * @property bool $is_enabled
 * @property User $createdBy
 * @property User $updatedBy
 * @property string $slug
 * @property string $pseudo_alias
 * @property int $view_count
 * @property int $logo_image_file_id
 * @property string $seo_description
 * @property string $seo_keywords
 *
 * @property File $logoImageFile
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
    const RULE_UPLOAD_FILES = 'article_upload_files';

    const CONTENT_FILE_PATH = 'articles';
    const CONTENT_FILE_LOGO_PATH = 'articles_logo';

    const FILE_LOGO_MIN_WIDTH = '450';
    const FILE_LOGO_MIN_HEIGHT = '300';


    /** @inheritdoc */
    public function behaviors()
    {
        return [
            'timestamp'  => [
                'class'      => TimestampBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => 'created',
                    ActiveRecord::EVENT_BEFORE_UPDATE => 'updated',
                ],
                'value'      => function () {
                    return time();
                },
            ],
            'textCutter' => [
                'class'  => TextCutterBehavior::className(),
                'fields' => [
                    'content' => 100,
                ]
            ],
            'blameable'  => BlameableBehavior::className(),
            'slug'       => SlugBehavior::className(),
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

            [['title', 'pseudo_alias', 'slug'], 'string', 'max' => 255],

            [['description', 'seo_keywords'], 'string', 'max' => 512],

            [['description', 'seo_description'], 'string', 'max' => 1024],

            ['content', 'string'],

            [['created_by', 'updated_by', 'created', 'updated', 'published_date', 'view_count', 'logo_image_file_id'], 'integer'],

            [['is_deleted', 'is_enabled'], 'boolean'],
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
     * @param boolean|string $scheme the URI scheme to use in the generated URL
     * @param boolean $useOnlyId Использовать в сссылку только с учетом id
     * @return string
     */
    public function getUrlView($scheme = false, $useOnlyId = false)
    {
        return Url::to([
            '/article/default/view',
            'slug' => $this->slug && !$useOnlyId ? $this->slug : $this->id
        ], $scheme);
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

        return $this->save(false, [SafeDataFinder::FIELD_IS_DELETED]);
    }

    /**
     * Сохранить/установить теги
     * @param array|string $tags
     */
    public function saveTags($tags = [])
    {
        // фильтруем пустые теги
        $tags = is_array($tags) ? $tags : [$tags];
        $tags = array_filter($tags);

        $saveTagsTransaction = Article::getDb()->beginTransaction();
        try {
            // отсоединяем старые теги
            $this->unlinkAllTags();

            // сохраняем заново все теги
            foreach ($tags as $tagName) {
                $tagName = trim($tagName);
                /** @var Tag $tag */
                $tag = Tag::findOne(['name' => $tagName]);
                if (!$tag) {
                    $tag = new Tag();
                    $tag->name = $tagName;
                    $tag->save();
                }
                $this->link('tags', $tag);
            }

            $saveTagsTransaction->commit();
        } catch (\Exception $e) {
            $saveTagsTransaction->rollBack();
        }
    }

    /**
     * Отсоединить все теги
     */
    protected function unlinkAllTags()
    {
        /** @var Tag $tag */
        foreach ($this->tags as $tag) {
            $this->unlink('tags', $tag, true);
        }
    }

    /**
     * @return ActiveQuery
     */
    public function getLogoImageFile()
    {
        return $this->hasOne(File::className(), ['id' => 'logo_image_file_id']);
    }

    /**
     * @param File $file
     */
    public function setLogoImageFile(File $file)
    {
        $this->logo_image_file_id = $file->id;
    }

    /**
     * @param boolean|string $scheme the URI scheme to use in the generated URL
     * @return string|null
     */
    public function getUrlLogoImageFile($scheme = false)
    {
        if (!$this->logoImageFile) {
            return null;
        }

        return $this->logoImageFile->getUrl($scheme);
    }
}
