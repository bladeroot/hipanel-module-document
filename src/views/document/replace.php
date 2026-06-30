<?php

/**
 * @var \yii\web\View $this
 * @var \hipanel\modules\document\models\Document $model
 */

use hipanel\widgets\Box;
use hipanel\widgets\FileRender;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

$this->title = Yii::t('hipanel:document', 'Replace document file');
$this->params['subtitle'] = Html::encode($model->getDisplayTitle());
$this->params['breadcrumbs'][] = ['label' => Yii::t('hipanel:document', 'Documents'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->getDisplayTitle(), 'url' => ['@document/view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('hipanel:document', 'Replace');

?>
<div class="row">
    <div class="col-md-3">
        <?php Box::begin(['options' => ['class' => 'box-solid'], 'title' => Yii::t('hipanel:document', 'Current file')]) ?>
        <div class="text-center">
            <?= FileRender::widget([
                'file' => $model->file,
                'thumbWidth' => 200,
                'thumbHeight' => 200,
                'lightboxLinkOptions' => [
                    'data-lightbox' => 'files-' . $model->file->id,
                ],
            ]) ?>
        </div>
        <p class="text-center text-muted small"><?= Html::encode($model->filename) ?></p>
        <?php Box::end() ?>
    </div>

    <div class="col-md-6">
        <?php Box::begin(['title' => Yii::t('hipanel:document', 'Replace file')]) ?>
        <?php $form = ActiveForm::begin([
            'id' => 'document-replace-form',
            'enableAjaxValidation' => true,
            'validationUrl' => Url::toRoute([
                'validate-single-form',
                'scenario' => $model->scenario,
            ]),
            'options' => ['enctype' => 'multipart/form-data'],
        ]) ?>

        <?= Html::activeHiddenInput($model, 'id') ?>

        <?= $form->field($model, 'attachment')->fileInput(['required' => true]) ?>

        <?= $form->field($model, 'reason')->textarea([
            'rows' => 4,
            'required' => true,
            'placeholder' => Yii::t('hipanel:document', 'Describe why this file is being replaced'),
        ]) ?>

        <?= Html::submitButton(Yii::t('hipanel:document', 'Replace'), ['class' => 'btn btn-warning']) ?>
        &nbsp;
        <?= Html::a(Yii::t('hipanel', 'Cancel'), ['@document/view', 'id' => $model->id], ['class' => 'btn btn-default']) ?>

        <?php $form->end() ?>
        <?php Box::end() ?>
    </div>
</div>
