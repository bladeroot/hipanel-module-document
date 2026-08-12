<?php
/**
 * Documents module for HiPanel
 *
 * @link      https://hipanel.com/
 * @package   hipanel-module-document
 * @license   BSD-3-Clause
 * @copyright Copyright (c) 2016-2018, HiQDev (http://hiqdev.com/)
 */

namespace hipanel\modules\document\models;

use hipanel\base\SearchModelTrait;
use Yii;

class DocumentSearch extends Document
{
    use SearchModelTrait {
        SearchModelTrait::searchAttributes as defaultSearchAttributes;
    }

    public function searchAttributes(): array
    {
        return array_merge($this->defaultSearchAttributes(), [
            'charge_ids',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return $this->mergeAttributeLabels([
            'type_in' => Yii::t('hipanel', 'Type'),
            'state_in' => Yii::t('hipanel', 'State'),
        ]);
    }
}
