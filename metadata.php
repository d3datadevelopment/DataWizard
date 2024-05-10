<?php

/**
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * https://www.d3data.de
 *
 * @copyright (C) D3 Data Development (Inh. Thomas Dartsch)
 * @author    D3 Data Development - Daniel Seifert <info@shopmodule.com>
 * @link      https://www.oxidmodule.com
 */

declare(strict_types=1);

use D3\DataWizard\Application\Model\Constants;

$sMetadataVersion = '2.1';

$sModuleId = Constants::OXID_MODULE_ID;
$logo = '<img src="https://logos.oxidmodule.com/d3logo.svg" alt="(D3)" style="height:1em;width:1em">';

/**
 * Module information
 */
$aModule = [
    'id'          => $sModuleId,
    'title'       => $logo.' Data Wizard',
    'description' => [
        'de' => '',
        'en' => '',
    ],
    'thumbnail'   => 'picture.svg',
    'version'     => '3.0.0.0',
    'author'      => 'D&sup3; Data Development (Inh.: Thomas Dartsch)',
    'email'       => 'support@shopmodule.com',
    'url'         => 'https://www.oxidmodule.com/',
    'controllers' => [
        'd3ExportWizard'            => D3\DataWizard\Application\Controller\Admin\d3ExportWizard::class,
        'd3ActionWizard'            => D3\DataWizard\Application\Controller\Admin\d3ActionWizard::class,
    ],
    'extend'      => [],
    'events'      => [],
    'templates'   => [
        '@' . Constants::OXID_MODULE_ID . '/admin/d3ExportWizard.tpl' => 'views/smarty/admin/d3ExportWizard.tpl',
        '@' . Constants::OXID_MODULE_ID . '/admin/d3ActionWizard.tpl' => 'views/smarty/admin/d3ActionWizard.tpl',
        '@' . Constants::OXID_MODULE_ID . '/admin/inc/d3Wizards.tpl' => 'views/smarty/admin/inc/Wizards.tpl',
        '@' . Constants::OXID_MODULE_ID . '/admin/inc/d3ExportSubmit.tpl' => 'views/smarty/admin/inc/exportSubmit.tpl',
        '@' . Constants::OXID_MODULE_ID . '/admin/inc/d3ActionSubmit.tpl' => 'views/smarty/admin/inc/actionSubmit.tpl',
    ],
    'settings'    => [
        [
            'group'     => $sModuleId.'_general',
            'name'      => $sModuleId.'_debug',
            'type'      => 'bool',
            'value'     => false,
        ],
    ],
    'blocks'      => [],
];
