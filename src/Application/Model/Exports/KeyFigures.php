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
use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Core\Registry;

class KeyFigures extends ExportBase
{
    /**
     * Shopkennzahlen
     */

    /**
     * @return string
     */
    public function getTitle() : string
    {
        return Registry::getLang()->translateString('D3_DATAWIZARD_EXPORTS_KEYFIGURES');
    }

    /**
     * @return array
     */
    public function getQuery() : array
    {
        $orderTable     = oxNew(Order::class)->getCoreTableName();
        $ordersTitle    = Registry::getLang()->translateString('D3_DATAWIZARD_EXPORTS_KEYFIGURES_ORDERSPERMONTH');
        $basketsTitle   = Registry::getLang()->translateString('D3_DATAWIZARD_EXPORTS_KEYFIGURES_BASKETSIZE');
        $monthTitle     = Registry::getLang()->translateString('D3_DATAWIZARD_EXPORTS_KEYFIGURES_MONTH');

        return [
            'SELECT
                DATE_FORMAT(oo.oxorderdate, "%Y-%m") as :monthTitle, 
                FORMAT(COUNT(oo.oxid), 0) AS :ordersTitle, 
                FORMAT(SUM(oo.OXTOTALBRUTSUM / oo.oxcurrate) / COUNT(oo.oxid), 2) as :basketsTitle 
            FROM '.$orderTable.' AS oo
            GROUP BY :monthTitle
            ORDER BY :monthTitle DESC
            LIMIT 30',
            [
                'monthTitle'   => $monthTitle,
                'ordersTitle'  => $ordersTitle,
                'basketsTitle' => $basketsTitle
            ]
        ];
    }
}