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

declare(strict_types=1);

namespace D3\DataWizard\Application\Model\Actions;

use D3\DataWizard\Application\Model\ActionBase;
use OxidEsales\Eshop\Core\Model\BaseModel;
use OxidEsales\Eshop\Core\Model\MultiLanguageModel;
use OxidEsales\Eshop\Core\Registry;

class FixArtextendsItems extends ActionBase
{
    /**
     * fehlende oxartextends-Einträge nachtragen
     */

    /**
     * @return string
     */
    public function getTitle() : string
    {
        return Registry::getLang()->translateString('D3_DATAWIZARD_ACTIONS_FIXARTEXTENDSITEMS');
    }

    /**
     * @return array
     */
    public function getQuery() : array
    {
        $aDefaultValueFields = array(
            'oxtimestamp'   => "''",
        );

        $aNonArtExtendsFields = array(
            'oxid'  => 'oxarticles.oxid',
        );

        $aArtExtendsFields = array_fill_keys($this->getArtExtendsFields(), "''");
        $aMergedFields = array_merge($aNonArtExtendsFields, $aArtExtendsFields);
        $aQueryFields = array_diff_key($aMergedFields, $aDefaultValueFields);

        $sArtExtendsFields = implode(', ', array_keys($aQueryFields));

        $select = "SELECT ".implode(', ', $aQueryFields).
            " FROM oxarticles".
            " LEFT JOIN oxartextends AS arx ON oxarticles.oxid = arx.oxid".
            " WHERE arx.oxid IS NULL";

        $query = "INSERT INTO oxartextends ($sArtExtendsFields) ".
            $select;

        return [$query, []];
    }

    /**
     * @return array
     */
    public function getArtExtendsFields(): array
    {
        /** @var $oArtExtends MultiLanguageModel */
        $oArtExtends = oxNew(BaseModel::class);
        $oArtExtends->init('oxartextends', false);

        $aFieldNames = $oArtExtends->getFieldNames();

        if (false == $aFieldNames) {
            $oArtExtends->disableLazyLoading();
            $aFieldNames = $oArtExtends->getFieldNames();
        }

        unset($aFieldNames[array_search('oxid', $aFieldNames)]);

        return $aFieldNames;
    }
}