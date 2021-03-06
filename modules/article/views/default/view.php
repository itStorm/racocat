<?php

use yii\helpers\Html;
use app\modules\article\models\Article;
use common\widgets\SocialButtonsWidget;
use yii\helpers\Url;
use common\widgets\SocialCommentWidget;
use app\modules\banner\models\BannerArea;

/* @var $this common\View */
/* @var $model app\modules\article\models\Article */

$this->title = $model->title;
$this->addKeywords($model->seo_keywords);
$this->setDescription($model->seo_description);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Articles'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$mainUrl = $model->getUrlView(true, true);

$adminButtons = '';
if (Yii::$app->user->can(Article::RULE_UPDATE)) {
    $adminButtons = '<p>'
        . Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) . ' '
        . Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data'  => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method'  => 'post',
            ],
        ])
        . '</p>';
}
?>


<?php $this->beginBlock('before_content'); ?>
<div class="visible-xs-block">
    <?= BannerArea::renderArea('article-view-xs-pages-top'); ?>
</div>
<?php $this->endBlock(); ?>

<p>
    <a tabindex="0"
       class="btn btn-lg btn-danger"
       role="button"
       data-toggle="popover"
       data-placement="bottom"
       data-trigger="focus"
       data-html="true"
       data-content='<div>Нажал? Любопытный? Тогда подписывайся.</div>
                        <a class="link-no-decorate" href="https://vk.com/racocat" target="_blank" rel="nofollow">
                            <img alt="vk" width="60px" height="60px" src="/img/vk.png"/>
                        </a>
                        <a class="link-no-decorate" href="https://www.facebook.com/racocat" target="_blank" rel="nofollow">
                            <img alt="facebook" width="60px" height="60px" src="/img/fb.png"/>
                        </a>'>Не надо нажимать</a>
</p>
<?= $adminButtons ?>

<div class="article-view">

    <h1><?= $this->render('components/_title', ['model' => $model]) ?></h1>
    <?= $this->render('components/_published_date', ['model' => $model]) ?>
    &nbsp;&nbsp;&nbsp;

    <?php if($model->pseudo_alias):?>
        <span class="user-sign">
            <span class="glyphicon glyphicon-user" aria-hidden="true"></span>
            <?= $model->pseudo_alias?>
        </span>
    <?php else: ?>
        <a href="<?= $model->createdBy->getUrlView(); ?>">
        <span class="user-sign">
            <span class="glyphicon glyphicon-user" aria-hidden="true"></span>
            <?= $model->createdBy->username ?>
        </span>
        </a>
    <?php endif;?>
    <div class="tags-block"><?= \common\widgets\TagsWidget::widget(['tags' => $model->tags]); ?></div>

    <div class="article-content">
        <?= $model->content ?>
    </div>

    <?= SocialButtonsWidget::widget([
        'url'         => $mainUrl,
        'title'       => $model->title,
        'description' => $model->description,
        'image'       => $model->getUrlLogoImageFile(true)?: Url::to('/img/social-logo.jpeg', true),
    ]); ?>
    <br/>
    <div class="hidden-xs">
        <?= BannerArea::renderArea('article-view-big-pages-bottom'); ?>
    </div>
    <?= SocialCommentWidget::widget([
        'url' => $mainUrl,
    ]); ?>
</div>
