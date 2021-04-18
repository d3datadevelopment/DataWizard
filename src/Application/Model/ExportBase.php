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

use D3\ModCfg\Application\Model\d3filesystem;
use D3\ModCfg\Application\Model\Exception\d3_cfg_mod_exception;
use D3\ModCfg\Application\Model\Exception\d3ShopCompatibilityAdapterException;
use Doctrine\DBAL\DBALException;
use League\Csv\CannotInsertRecord;
use League\Csv\EncloseField;
use League\Csv\Exception;
use League\Csv\Writer;
use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Exception\DatabaseErrorException;
use OxidEsales\Eshop\Core\Exception\StandardException;
use OxidEsales\Eshop\Core\Registry;

abstract class ExportBase implements QueryBase
{
    const FORMAT_CSV = 'CSV';

    /**
     * @throws CannotInsertRecord
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     * @throws Exception
     * @throws StandardException
     * @throws d3ShopCompatibilityAdapterException
     * @throws d3_cfg_mod_exception
     * @throws DBALException
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

        $rows = DatabaseProvider::getDb(DatabaseProvider::FETCH_MODE_ASSOC)->getAll($query);

        if (count($rows) <= 0) {
            throw oxNew(
                StandardException::class,
                Registry::getLang()->translateString('D3_DATAWIZARD_ERR_NOEXPORTCONTENT')
            );
        }

        $fieldNames = array_keys($rows[0]);

        $csv = $this->getCsv();
        $csv->insertOne($fieldNames);
        $csv->insertAll($rows);

        /** @var $oFS d3filesystem */
        $oFS = oxNew(d3filesystem::class);
        $oFS->startDirectDownload($this->getExportFilename(), $csv->getContent());
    }

    public function getButtonText() : string
    {
        return "D3_DATAWIZARD_EXPORT_SUBMIT";
    }

    /**
     * @return Writer
     * @throws Exception
     */
    protected function getCsv(): Writer
    {
        $csv = Writer::createFromString();

        EncloseField::addTo($csv, "\t\x1f");

        $sEncloser = Registry::getConfig()->getConfigParam('sGiCsvFieldEncloser');
        if (false == $sEncloser) {
            $sEncloser = '"';
        }
        $csv->setEnclosure($sEncloser);

        $sDelimiter = Registry::getConfig()->getConfigParam('sCSVSign');
        if (false == $sDelimiter) {
            $sDelimiter = ';';
        }
        $csv->setDelimiter($sDelimiter);

        return $csv;
    }
}