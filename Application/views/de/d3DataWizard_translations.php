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

// @codeCoverageIgnoreStart
declare(strict_types=1);

return [
//Navigation
    'charset'                                         => 'UTF-8',
    'd3mxDataWizard'                                  => '<i class="fas fa-fw fa-hat-wizard"></i> Data Wizard',
    'd3mxDataWizard_Export'                           => 'Exporte',
    'd3mxDataWizard_Action'                           => 'Aktionen',

    'SHOP_MODULE_GROUP_d3datawizard_general'          => 'Grundeinstellungen',
    'SHOP_MODULE_d3datawizard_debug'                  => 'zeigt Abfragen anstatt diese auszuführen',

    'D3_DATAWIZARD_GROUP_ARTICLES'                    => 'Artikel',
    'D3_DATAWIZARD_GROUP_CATEGORIES'                  => 'Kategorien',
    'D3_DATAWIZARD_GROUP_ORDERS'                      => 'Bestellungen',
    'D3_DATAWIZARD_GROUP_REMARKS'                     => 'Bewertungen',
    'D3_DATAWIZARD_GROUP_SHOP'                        => 'Shop',
    'D3_DATAWIZARD_GROUP_CMS'                         => 'CMS-Texte',
    'D3_DATAWIZARD_GROUP_USERS'                       => 'Benutzer',

    'D3_DATAWIZARD_EXPORT_SUBMIT'                     => 'Export starten',
    'D3_DATAWIZARD_EXPORT_FORMAT_CSV'                 => 'CSV-Format',
    'D3_DATAWIZARD_EXPORT_FORMAT_PRETTY'              => 'Pretty-Format',
    'D3_DATAWIZARD_EXPORT_FORMAT_JSON'                => 'JSON-Format',

    'D3_DATAWIZARD_ACTION_SUBMIT'                     => 'Aktion starten',
    'D3_DATAWIZARD_ACTION_SUBMIT_CONFIRM'             => 'Soll die Aktion gestartet werden?',

    'D3_DATAWIZARD_DEBUG'                             => 'Debug: %1$s',

    'D3_DATAWIZARD_ERR_NOEXPORTSELECT'                => 'Export kann nicht ausgeführt werden. Exporte erfordern SELECT Query.',
    'D3_DATAWIZARD_ERR_NOEXPORT_INSTALLED'            => 'Es sind keine Exporte installiert oder aktiviert.',
    'D3_DATAWIZARD_ERR_NOEXPORTCONTENT'               => 'Export ist leer, kein Inhalt zum Download verfügbar',
    'D3_DATAWIZARD_ERR_NOSUITABLERENDERER'            => 'kein Renderer für Format "%1$s" registriert',
    'D3_DATAWIZARD_ERR_EXPORTFILEERROR'               => 'Exportdatei "%1$s" kann nicht angelegt werden',

    'D3_DATAWIZARD_ERR_ACTIONSELECT'                  => 'Aktion kann nicht ausgeführt werden. Aktionen können keine SELECTs exportieren.',
    'D3_DATAWIZARD_ERR_NOACTION_INSTALLED'            => 'Es sind keine Aktionen installiert oder aktiviert.',
    'D3_DATAWIZARD_ERR_ACTIONRESULT'                  => '%1$s Eintrag verändert',
    'D3_DATAWIZARD_ERR_ACTIONRESULTS'                 => '%1$s Einträge verändert',
];
// @codeCoverageIgnoreEnd