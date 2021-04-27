<?php

/**
 * This Software is the property of Data Development and is protected
 * by copyright law - it is NOT Freeware.
 * Any unauthorized use of this software without a valid license
 * is a violation of the license agreement and will be prosecuted by
 * civil and criminal law.
 * http://www.shopmodule.com
 *
 * @copyright (C) D3 Data Development (Inh. Thomas Dartsch)
 * @author        D3 Data Development - Daniel Seifert <support@shopmodule.com>
 * @link          http://www.oxidmodule.com
 */

namespace D3\DataWizard\Application\Model\Exports;

use D3\DataWizard\Application\Model\ExportBase;
use OxidEsales\Eshop\Core\Registry;

class InactiveCategories extends ExportBase
{
    /**
     * Kategorien -deaktiviert, mit aktiven Artikel
     */

    /**
     * @return string
     */
    public function getTitle() : string
    {
        return Registry::getLang()->translateString('D3_DATAWIZARD_EXPORTS_INACTIVECATEGORIES');
    }

    /**
     * @return string
     */
    public function getDescription() : string
    {
        return '';
    }

    /**
     * @return array
     */
    public function getQuery() : array
    {
        return [
            "SELECT
                oc.OXID,
                oc.OXSHOPID,
                oc.oxtitle as 'Titel',
                (
                    SELECT GROUP_CONCAT(oxtitle ORDER BY oxleft ASC SEPARATOR ' > ') 
                    from oxcategories 
                    WHERE OXLEFT < oc.oxleft AND OXRIGHT > oc.oxright AND OXROOTID = oc.OXROOTID AND OXSHOPID = oc.OXSHOPID
                ) as 'Baum',
                COUNT(oa.oxid) as 'Anzahl'
                FROM oxcategories oc
                LEFT JOIN oxobject2category o2c ON oc.OXID = o2c.OXCATNID
                LEFT JOIN oxarticles oa ON o2c.OXOBJECTID = oa.OXID
                WHERE oc.OXACTIVE = ? AND oa.OXACTIVE = ?
                GROUP BY oc.oxid
                ORDER BY oc.oxleft ASC",
            [0, 1]
        ];
    }
}