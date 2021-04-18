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

namespace D3\DataWizard\Application\Model;

use D3\ModCfg\Application\Model\d3database;
use D3\ModCfg\Application\Model\Exception\d3_cfg_mod_exception;
use D3\ModCfg\Application\Model\Exception\d3ShopCompatibilityAdapterException;
use Doctrine\DBAL\DBALException;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Exception\DatabaseErrorException;
use OxidEsales\Eshop\Core\Exception\StandardException;
use OxidEsales\Eshop\Core\Registry;

abstract class ExportBase implements QueryBase
{
    /**
     * @throws StandardException
     * @throws d3ShopCompatibilityAdapterException
     * @throws d3_cfg_mod_exception
     * @throws DBALException
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    public function run()
    {
        $query = trim($this->getQuery());

        if (strtolower(substr($query, 0, 6)) !== 'select') {
            /** @var StandardException $e */
            throw oxNew(
                StandardException::class,
                $this->getTitle().' - '.Registry::getLang()->translateString('D3_DATAWIZARD_EXPORT_NOSELECT')
            );
        }

        d3database::getInstance()->downloadExportCsvByQuery($this->getExportFilename(), $this->getQuery());
    }

    public function getButtonText() : string
    {
        return "D3_DATAWIZARD_EXPORT_SUBMIT";
    }
}