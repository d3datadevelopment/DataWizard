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

$sLangName = "English";
// -------------------------------
// RESOURCE IDENTITFIER = STRING
// -------------------------------
$aLang = [

//Navigation
    'charset'                                         => 'UTF-8',
    'd3mxDataWizard'                                  => '<i class="fas fa-fw fa-hat-wizard"></i> Data Wizard',
    'd3mxDataWizard_Export'                           => 'exports',
    'd3mxDataWizard_Action'                           => 'actions',

    'SHOP_MODULE_GROUP_d3datawizard_general'          => 'basic settings',
    'SHOP_MODULE_d3datawizard_debug'                  => 'shows queries instead of executing them',

    'D3_DATAWIZARD_GROUP_ARTICLES'                    => 'articles',
    'D3_DATAWIZARD_GROUP_CATEGORIES'                  => 'categories',
    'D3_DATAWIZARD_GROUP_ORDERS'                      => 'orders',
    'D3_DATAWIZARD_GROUP_REMARKS'                     => 'remarks',
    'D3_DATAWIZARD_GROUP_SHOP'                        => 'shop',
    'D3_DATAWIZARD_GROUP_CMS'                         => 'CMS items',
    'D3_DATAWIZARD_GROUP_USERS'                       => 'users',

    'D3_DATAWIZARD_EXPORT_SUBMIT'                     => 'generate export',
    'D3_DATAWIZARD_EXPORT_FORMAT_CSV'                 => 'CSV format',
    'D3_DATAWIZARD_EXPORT_FORMAT_PRETTY'              => 'Pretty format',
    'D3_DATAWIZARD_EXPORT_FORMAT_JSON'                => 'JSON format',

    'D3_DATAWIZARD_ACTION_SUBMIT'                     => 'run action',
    'D3_DATAWIZARD_ACTION_SUBMIT_CONFIRM'             => 'Should the action be executed?',

    'D3_DATAWIZARD_DEBUG'                             => 'Debug: %1$s',

    'D3_DATAWIZARD_ERR_NOEXPORTSELECT'                => 'Export cannot be executed. Exports require SELECT Query.',
    'D3_DATAWIZARD_ERR_NOEXPORT_INSTALLED'            => 'No exports are installed or activated.',
    'D3_DATAWIZARD_ERR_NOEXPORTCONTENT'               => 'Export is empty, no content available for download',
    'D3_DATAWIZARD_ERR_NOSUITABLERENDERER'            => 'No renderer registered for format "%1$s"',
    'D3_DATAWIZARD_ERR_EXPORTFILEERROR'               => 'Export file "%1$s" cannot be created',

    'D3_DATAWIZARD_ERR_ACTIONSELECT'                  => 'Action cannot be executed. Actions cannot export SELECTs.',
    'D3_DATAWIZARD_ERR_NOACTION_INSTALLED'            => 'No actions are installed or activated.',
    'D3_DATAWIZARD_ERR_ACTIONRESULT'                  => '%1$s entry changed',
    'D3_DATAWIZARD_ERR_ACTIONRESULTS'                 => '%1$s entries changed',
];
