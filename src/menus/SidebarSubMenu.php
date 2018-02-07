<?php
/**
 * Client module for HiPanel
 *
 * @link      https://github.com/hiqdev/hipanel-module-client
 * @package   hipanel-module-client
 * @license   BSD-3-Clause
 * @copyright Copyright (c) 2015-2016, HiQDev (http://hiqdev.com/)
 */

namespace hipanel\modules\document\menus;

use Yii;

/**
 * Class SidebarSubMenu
 * @package hipanel\modules\document\menus
 */
class SidebarSubMenu extends \hiqdev\yii2\menus\Menu
{
    public function items()
    {
        return [
            'clients' => [
                'items' => [
                    'documents' => [
                        'label' => Yii::t('hipanel:document', 'Documents'),
                        'url' => ['/document/document/index'],
                        'visible' => Yii::$app->user->can('document.read'),
                    ],
                ],
            ],
        ];
    }
}
