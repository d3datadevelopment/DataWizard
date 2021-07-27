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

/**
 * Metadata version
 */
$sMetadataVersion = '2.1';

$sModuleId = 'd3datawizard';
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
    'thumbnail'   => '',
    'version'     => '1.2.0.0',
    'author'      => 'D&sup3; Data Development (Inh.: Thomas Dartsch)',
    'email'       => 'support@shopmodule.com',
    'url'         => 'https://www.oxidmodule.com/',
    'controllers' => [
        'd3ExportWizard'            => D3\DataWizard\Application\Controller\Admin\d3ExportWizard::class,
        'd3ActionWizard'            => D3\DataWizard\Application\Controller\Admin\d3ActionWizard::class
    ],
    'extend'      => [],
    'events'      => [],
    'templates'   => [
        'd3ExportWizard.tpl'         => 'd3/datawizard/Application/views/admin/tpl/d3ExportWizard.tpl',
        'd3ActionWizard.tpl'         => 'd3/datawizard/Application/views/admin/tpl/d3ActionWizard.tpl',
    ],
    'settings'    => [
        [
            'group'     => $sModuleId.'_general',
            'name'      => $sModuleId.'_debug',
            'type'      => 'bool',
            'value'     => false
        ],
        [
            'group'     => $sModuleId.'_general',
            'name'      => $sModuleId.'_hideexamples',
            'type'      => 'bool',
            'value'     => false
        ],
    ],
    'blocks'      => []
];
