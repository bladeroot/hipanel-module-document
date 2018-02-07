<?php

use hipanel\modules\client\widgets\combo\ClientCombo;
use hipanel\modules\client\widgets\combo\SellerCombo;
use hipanel\widgets\DatePicker;
use hiqdev\combo\StaticCombo;
use yii\helpers\Html;

/**
 * @var \hipanel\widgets\AdvancedSearch
 * @var array $states
 * @var array $types
 */
?>

<div class="col-md-4 col-sm-6 col-xs-12">
    <?= $search->field('title_ilike') ?>
</div>

<div class="col-md-4 col-sm-6 col-xs-12">
    <?= $search->field('type_in')->widget(StaticCombo::class, [
        'data'      => $types,
        'hasId'     => true,
        'multiple'  => true,
    ]) ?>
</div>

<div class="col-md-4 col-sm-6 col-xs-12">
    <?= $search->field('state_in')->widget(StaticCombo::class, [
        'data'      => $states,
        'hasId'     => true,
        'multiple'  => true,
    ]) ?>
</div>

<?php if (Yii::$app->user->can('support')) {
        ?>
    <div class="col-md-4 col-sm-6 col-xs-12">
        <?= $search->field('client_id')->widget(ClientCombo::class) ?>
    </div>

    <div class="col-md-4 col-sm-6 col-xs-12">
        <?= $search->field('seller_id')->widget(SellerCombo::class) ?>
    </div>
<?php
    } ?>

<div class="col-md-4 col-sm-6 col-xs-12">
    <div class="form-group">
        <?= Html::tag('label', Yii::t('hipanel:document', 'Creation date'), ['class' => 'control-label']); ?>
        <?= DatePicker::widget([
            'model' => $search->model,
            'attribute' => 'create_time_gt',
            'attribute2' => 'create_time_lt',
            'type' => DatePicker::TYPE_RANGE,
            'pluginOptions' => [
                'autoclose' => true,
                'format' => 'dd-mm-yyyy',
            ],
        ]) ?>
    </div>
</div>
